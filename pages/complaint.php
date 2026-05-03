<?php
require_once '../includes/auth.php';
requireLogin();
require_once '../includes/db.php';
$sid = getStudentId();
$msg = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $statement  = trim($_POST['statement']);
    $issue_type = trim($_POST['issue_type']);
    $date       = date('Y-m-d');

    if ($statement && $issue_type) {
        $ins = $conn->prepare("INSERT INTO Complaint (Statement, Issue_Type, submitted_date, Student_id) VALUES (?,?,?,?)");
        $ins->bind_param('ssss', $statement, $issue_type, $date, $sid);
        if ($ins->execute()) {
            // Insert into Files too
            $cid = $conn->insert_id;
            $f = $conn->prepare("INSERT IGNORE INTO Files (Student_id, Complain_id) VALUES (?,?)");
            $f->bind_param('si', $sid, $cid);
            $f->execute();
            $msg = ['type'=>'success', 'text'=>'✅ Complaint submitted successfully. Complaint ID: #'.$cid];
        }
    } else {
        $msg = ['type'=>'danger', 'text'=>'Please fill in all fields.'];
    }
}

$my_complaints = $conn->prepare("SELECT * FROM Complaint WHERE Student_id=? ORDER BY submitted_date DESC");
$my_complaints->bind_param('s', $sid);
$my_complaints->execute();
$complaints = $my_complaints->get_result();

$issue_types = ['Broken AC','Projector not working','WiFi problem','Lab computer damaged','Broken furniture','Elevator issue','Water leakage','Security concern','Other'];
?>
<!DOCTYPE html>
<html lang="en">
<head>
<meta charset="UTF-8">
<meta name="viewport" content="width=device-width, initial-scale=1.0">
<title>Complaints – BracU Portal</title>
<link rel="stylesheet" href="../assets/css/style.css">
</head>
<body>
<div class="layout">
<?php include '../includes/sidebar.php'; ?>
<div class="main">
    <div class="topbar">
        <div><h1>🔔 Campus Facility Complaints</h1><p>Report infrastructure and equipment problems</p></div>
    </div>
    <div class="page-content">

        <?php if ($msg): ?>
        <div class="alert alert-<?= $msg['type'] ?> mb-4"><?= $msg['text'] ?></div>
        <?php endif; ?>

        <div class="grid-2">
            <!-- Submit -->
            <div class="card">
                <div class="card-header"><h2>📝 Submit New Complaint</h2></div>
                <div class="card-body">
                    <form method="POST">
                        <div class="form-group">
                            <label>Issue Type *</label>
                            <select name="issue_type" required>
                                <option value="">Select issue type</option>
                                <?php foreach($issue_types as $it): ?>
                                <option><?= $it ?></option>
                                <?php endforeach; ?>
                            </select>
                        </div>
                        <div class="form-group">
                            <label>Description *</label>
                            <textarea name="statement" rows="5" placeholder="Describe the issue in detail (location, nature of problem)..." required style="resize:vertical;"></textarea>
                        </div>
                        <button type="submit" class="btn btn-primary">Submit Complaint</button>
                    </form>
                </div>
            </div>

            <!-- My complaints -->
            <div class="card">
                <div class="card-header"><h2>📋 My Complaints</h2></div>
                <div style="max-height:500px; overflow-y:auto;">
                    <?php if ($complaints->num_rows > 0): ?>
                    <?php while($c = $complaints->fetch_assoc()): ?>
                    <div style="padding:16px; border-bottom:1px solid var(--border);">
                        <div style="display:flex; justify-content:space-between; align-items:start; margin-bottom:6px;">
                            <span class="badge badge-warning"><?= htmlspecialchars($c['Issue_Type']) ?></span>
                            <span class="text-muted" style="font-size:12px;">#<?= $c['Complain_id'] ?> · <?= $c['submitted_date'] ?></span>
                        </div>
                        <div style="font-size:14px; color:var(--text);"><?= htmlspecialchars($c['Statement']) ?></div>
                    </div>
                    <?php endwhile; ?>
                    <?php else: ?>
                    <div class="empty-state"><div class="empty-icon">🎉</div><p>No complaints submitted yet.</p></div>
                    <?php endif; ?>
                </div>
            </div>
        </div>

    </div>
</div>
</div>
</body>
</html>
