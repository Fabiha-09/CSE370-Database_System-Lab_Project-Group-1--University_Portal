<?php
require_once '../includes/auth.php';
requireLogin();
require_once '../includes/db.php';
$sid = getStudentId();

$q = $conn->prepare("SELECT s.*, d.dept_name FROM Student s LEFT JOIN Department d ON s.dept_id=d.dept_id WHERE s.Student_id=?");
$q->bind_param('s', $sid);
$q->execute();
$st = $q->get_result()->fetch_assoc();

$phones = $conn->prepare("SELECT Phone FROM Student_Phone WHERE Student_id=?");
$phones->bind_param('s', $sid);
$phones->execute();
$phones_res = $phones->get_result();
$phone_list = [];
while($p = $phones_res->fetch_assoc()) $phone_list[] = $p['Phone'];

$clubs_q = $conn->prepare("SELECT Club_Name, Activities FROM Clubs WHERE Student_id=?");
$clubs_q->bind_param('s', $sid);
$clubs_q->execute();
$clubs = $clubs_q->get_result();
?>
<!DOCTYPE html>
<html lang="en">
<head>
<meta charset="UTF-8">
<meta name="viewport" content="width=device-width, initial-scale=1.0">
<title>My Profile – BracU Portal</title>
<link rel="stylesheet" href="../assets/css/style.css">
</head>
<body>
<div class="layout">
<?php include '../includes/sidebar.php'; ?>
<div class="main">
    <div class="topbar">
        <div><h1>👤 My Profile</h1><p>Your academic and personal information</p></div>
    </div>
    <div class="page-content">
        <div class="grid-2">
            <div class="card">
                <div class="card-body" style="text-align:center; padding:32px;">
                    <div style="width:80px;height:80px;background:var(--primary);border-radius:50%;display:inline-flex;align-items:center;justify-content:center;font-size:32px;color:#fff;font-weight:700;font-family:'Playfair Display',serif;margin-bottom:16px;">
                        <?= strtoupper(substr($st['Fname'],0,1)) ?>
                    </div>
                    <h2 style="font-family:'Playfair Display',serif; font-size:22px;"><?= htmlspecialchars($st['Fname'].' '.$st['Lname']) ?></h2>
                    <div class="text-muted" style="margin:4px 0;"><?= htmlspecialchars($st['Email']) ?></div>
                    <span class="badge badge-primary" style="margin-top:8px;"><?= $st['dept_id'] ?> · <?= $st['Student_type'] ?></span>
                </div>
                <div style="border-top:1px solid var(--border); padding:20px 24px;">
                    <div style="display:grid; grid-template-columns:1fr 1fr; gap:12px; font-size:14px;">
                        <div><div class="text-muted">Student ID</div><div class="fw-bold"><?= $st['Student_id'] ?></div></div>
                        <div><div class="text-muted">CGPA</div><div class="fw-bold"><?= number_format($st['cgpa'],2) ?></div></div>
                        <div><div class="text-muted">Date of Birth</div><div class="fw-bold"><?= $st['DOB'] ? date('d M Y',strtotime($st['DOB'])) : '—' ?></div></div>
                        <div><div class="text-muted">Gender</div><div class="fw-bold"><?= $st['Gender'] ?? '—' ?></div></div>
                        <div><div class="text-muted">Department</div><div class="fw-bold"><?= htmlspecialchars($st['dept_name'] ?? '—') ?></div></div>
                        <div><div class="text-muted">Phone</div><div class="fw-bold"><?= implode(', ', $phone_list) ?: '—' ?></div></div>
                    </div>
                </div>
            </div>

            <div class="card">
                <div class="card-header"><h2>🎭 Club Memberships</h2></div>
                <div style="padding:16px;">
                    <?php if ($clubs->num_rows > 0): ?>
                    <?php while($c = $clubs->fetch_assoc()): ?>
                    <div style="padding:12px; border:1px solid var(--border); border-radius:8px; margin-bottom:10px;">
                        <div class="fw-bold"><?= htmlspecialchars($c['Club_Name']) ?></div>
                        <div class="text-muted" style="font-size:13px; margin-top:4px;"><?= htmlspecialchars($c['Activities']) ?></div>
                    </div>
                    <?php endwhile; ?>
                    <?php else: ?>
                    <div class="empty-state"><div class="empty-icon">🎭</div><p>Not a member of any clubs yet.</p></div>
                    <?php endif; ?>
                </div>
            </div>
        </div>
    </div>
</div>
</div>
</body>
</html>
