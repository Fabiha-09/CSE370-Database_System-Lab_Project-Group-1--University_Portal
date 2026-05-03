<?php
require_once '../includes/auth.php';
requireLogin();
require_once '../includes/db.php';

$labs = $conn->query("SELECT l.lab_room, l.room_no, l.Occupied_Slots, l.Free_Slots, c.capacity, c.pcs, c.Equipment_List FROM Classroom_Lab_Room l JOIN Classroom c ON l.room_no=c.room_no ORDER BY l.lab_room");
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

        <div class="stats-grid mb-6">
            <?php
            $labs_arr = [];
            while($r = $labs->fetch_assoc()) $labs_arr[] = $r;
            $total_free = array_sum(array_column($labs_arr, 'Free_Slots'));
            $total_occ  = array_sum(array_column($labs_arr, 'Occupied_Slots'));
            ?>
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
            $free = $r['Free_Slots'] > 0;
            $pct = $r['Occupied_Slots'] / max($r['Occupied_Slots']+$r['Free_Slots'],1) * 100;
        ?>
        <div class="lab-card <?= $free ? 'free' : 'occupied' ?>">
            <div class="lab-name">🖥️ <?= htmlspecialchars($r['lab_room']) ?></div>
            <div class="lab-status"><?= $free ? '✅ Available' : '🔴 Occupied' ?></div>
            <div class="lab-detail">Room: <?= $r['room_no'] ?></div>
            <div class="lab-detail">PCs: <?= $r['pcs'] ?> total</div>
            <div class="lab-detail">Free slots: <strong><?= $r['Free_Slots'] ?></strong> / Occupied: <?= $r['Occupied_Slots'] ?></div>
            <div class="progress-bar" style="margin-top:8px;">
                <div class="fill" style="width:<?= $pct ?>%; background:<?= $free ? 'var(--success)' : 'var(--danger)' ?>"></div>
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
