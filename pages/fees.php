<?php
require_once '../includes/auth.php';
requireLogin();
require_once '../includes/db.php';
$sid = getStudentId();

$fees = $conn->prepare("SELECT * FROM Semester_Fees WHERE Student_id=? ORDER BY Payment_id DESC");
$fees->bind_param('s', $sid);
$fees->execute();
$fees_res = $fees->get_result();

$total_paid = 0; $total_due = 0;
$fees_arr = [];
while($r = $fees_res->fetch_assoc()) {
    $fees_arr[] = $r;
    if ($r['Payment_date']) $total_paid += $r['Amount'];
    else $total_due += $r['Amount'];
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
<meta charset="UTF-8">
<meta name="viewport" content="width=device-width, initial-scale=1.0">
<title>Semester Fees – BracU Portal</title>
<link rel="stylesheet" href="../assets/css/style.css">
</head>
<body>
<div class="layout">
<?php include '../includes/sidebar.php'; ?>
<div class="main">
    <div class="topbar">
        <div><h1>💳 Semester Fee Payment Status</h1><p>Your tuition and fee records</p></div>
    </div>
    <div class="page-content">

        <div class="stats-grid mb-6">
            <div class="stat-card">
                <div class="stat-icon green">✅</div>
                <div class="stat-info"><div class="value">৳<?= number_format($total_paid) ?></div><div class="label">Total Paid</div></div>
            </div>
            <div class="stat-card">
                <div class="stat-icon red">⏳</div>
                <div class="stat-info"><div class="value">৳<?= number_format($total_due) ?></div><div class="label">Total Pending</div></div>
            </div>
        </div>

        <div class="card">
            <div class="card-header"><h2>📋 Payment History</h2></div>
            <div class="card-body" style="padding:0;">
                <?php if (count($fees_arr) > 0): ?>
                <?php foreach($fees_arr as $f): $paid = !empty($f['Payment_date']); ?>
                <div class="fee-row" style="padding:16px 24px;">
                    <div>
                        <div class="fw-bold"><?= htmlspecialchars($f['Segment']) ?></div>
                        <div class="text-muted">Payment ID: #<?= $f['Payment_id'] ?> · <?= $paid ? 'Paid on '.$f['Payment_date'] : 'Due' ?></div>
                    </div>
                    <div style="text-align:right;">
                        <div class="fw-bold" style="font-size:18px;">৳<?= number_format($f['Amount']) ?></div>
                        <span class="badge <?= $paid ? 'badge-success' : 'badge-danger' ?>">
                            <?= $paid ? '✅ Paid' : '⏳ Pending' ?>
                        </span>
                    </div>
                </div>
                <?php endforeach; ?>
                <?php else: ?>
                <div class="empty-state"><div class="empty-icon">📭</div><p>No fee records found.</p></div>
                <?php endif; ?>
            </div>
        </div>

        <?php if ($total_due > 0): ?>
        <div class="alert alert-warning" style="margin-top:16px;">
            ⚠️ You have <strong>৳<?= number_format($total_due) ?></strong> in pending fees. Please visit the Finance Office or pay via uPay.
        </div>
        <?php endif; ?>

    </div>
</div>
</div>
</body>
</html>
