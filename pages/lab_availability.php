<?php
require_once '../includes/auth.php';
requireLogin();
require_once '../includes/db.php';

$selected_date = $_GET['date'] ?? date('Y-m-d');
$selected_slot = $_GET['slot'] ?? '08:00:00';

// Calculate end of the 3-hour slot in PHP for SQL compatibility
$end_time = date('H:i:s', strtotime($selected_slot) + 10800); // 3 hours = 10800 seconds

// Refined query using LEFT JOIN to check for classes in the selected slot
$query = "
    SELECT 
        l.lab_room, 
        l.room_no, 
        c.pcs, 
        c.Equipment_List,
        COUNT(s.section_no) as class_count
    FROM Classroom_Lab_Room l
    JOIN Classroom c ON TRIM(l.room_no) = TRIM(c.room_no)
    LEFT JOIN Section s ON TRIM(l.room_no) = TRIM(s.room_no) 
        AND s.date = ? 
        AND s.time >= ? 
        AND s.time < ?
    GROUP BY l.lab_room, l.room_no, c.pcs, c.Equipment_List
    ORDER BY l.lab_room
";

$stmt = $conn->prepare($query);
$stmt->bind_param("sss", $selected_date, $selected_slot, $end_time);
$stmt->execute();
$result = $stmt->get_result();

$labs_arr = [];
while($r = $result->fetch_assoc()) {
    $is_occupied = $r['class_count'] > 0;
    // Set Occupied_Slots and Free_Slots based on the conflict check
    $r['Occupied_Slots'] = $is_occupied ? (int)$r['pcs'] : 0;
    $r['Free_Slots'] = $is_occupied ? 0 : (int)$r['pcs'];
    $labs_arr[] = $r;
}

// Calculate summary stats dynamically
$total_free = array_sum(array_column($labs_arr, 'Free_Slots'));
$total_occ  = array_sum(array_column($labs_arr, 'Occupied_Slots'));
?>
<!DOCTYPE html>
<html lang="en">
<head>
<meta charset="UTF-8">
<meta name="viewport" content="width=device-width, initial-scale=1.0">
<title>Lab Availability – BracU Portal</title>
<link rel="stylesheet" href="../assets/css/style.css">
</head>
<body>
<div class="layout">
<?php include '../includes/sidebar.php'; ?>
<div class="main">
    <div class="topbar">
        <div><h1>🖥️ Lab Availability</h1><p>Real-time computer lab slot checker</p></div>
        <div class="topbar-right">
            <span style="font-size:13px; color:var(--text-muted);">🕐 <?= date('h:i A') ?> · <?= date('D, d M Y') ?></span>
        </div>
    </div>
    <div class="page-content">

        <!-- Slot Selection Form -->
        <div class="card mb-6">
            <div class="card-body">
                <form method="GET" style="display:flex; gap:20px; align-items:flex-end; flex-wrap:wrap;">
                    <div class="form-group" style="margin-bottom:0; flex:1; min-width:200px;">
                        <label>Select Date</label>
                        <input type="date" name="date" value="<?= htmlspecialchars($selected_date) ?>">
                    </div>
                    <div class="form-group" style="margin-bottom:0; flex:1; min-width:200px;">
                        <label>Time Slot</label>
                        <select name="slot">
                            <option value="08:00:00" <?= $selected_slot == '08:00:00' ? 'selected' : '' ?>>08:00 AM - 11:00 AM</option>
                            <option value="11:00:00" <?= $selected_slot == '11:00:00' ? 'selected' : '' ?>>11:00 AM - 02:00 PM</option>
                            <option value="14:00:00" <?= $selected_slot == '14:00:00' ? 'selected' : '' ?>>02:00 PM - 05:00 PM</option>
                        </select>
                    </div>
                    <button type="submit" class="btn btn-primary" style="width:auto; height:46px;">Check Availability</button>
                </form>
            </div>
        </div>

        <div class="stats-grid mb-6">
            <div class="stat-card">
                <div class="stat-icon green">✅</div>
                <div class="stat-info"><div class="value"><?= $total_free ?></div><div class="label">Free Slots</div></div>
            </div>
            <div class="stat-card">
                <div class="stat-icon red">🔴</div>
                <div class="stat-info"><div class="value"><?= $total_occ ?></div><div class="label">Occupied Slots</div></div>
            </div>
            <div class="stat-card">
                <div class="stat-icon blue">🖥️</div>
                <div class="stat-info"><div class="value"><?= count($labs_arr) ?></div><div class="label">Total Labs</div></div>
            </div>
        </div>

        <div class="lab-grid">
        <?php foreach($labs_arr as $r):
            $is_free = $r['Free_Slots'] > 0;
            $total_pcs = (int)$r['pcs'];
            $pct = ($r['Occupied_Slots'] / max($total_pcs, 1)) * 100;
        ?>
        <div class="lab-card <?= $is_free ? 'free' : 'occupied' ?>">
            <div class="lab-name">🖥️ <?= htmlspecialchars($r['lab_room']) ?></div>
            <div class="lab-status"><?= $is_free ? '✅ Available' : '🔴 Occupied by Class' ?></div>
            <div class="lab-detail">Room: <?= $r['room_no'] ?></div>
            <div class="lab-detail">PCs: <?= $total_pcs ?> total</div>
            <div class="lab-detail">Free slots: <strong><?= $r['Free_Slots'] ?></strong> / Occupied: <?= $r['Occupied_Slots'] ?></div>
            <div class="progress-bar" style="margin-top:8px;">
                <div class="fill" style="width:<?= $pct ?>%; background:<?= $is_free ? 'var(--success)' : 'var(--danger)' ?>"></div>
            </div>
            <div class="lab-detail"><?= htmlspecialchars($r['Equipment_List']) ?></div>
        </div>
        <?php endforeach; ?>
        </div>

    </div>
</div>
</div>
</body>
</html>

