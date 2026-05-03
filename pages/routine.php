<?php
require_once '../includes/auth.php';
requireLogin();
require_once '../includes/db.php';
$sid = getStudentId();

$q = $conn->prepare("SELECT Courses, Exam_date, Exam_routine, Attribute FROM AcademicRoutine WHERE Student_id=? ORDER BY Exam_date ASC");
$q->bind_param('s', $sid);
$q->execute();
$routines = $q->get_result();
?>
<!DOCTYPE html>
<html lang="en">
<head>
<meta charset="UTF-8">
<meta name="viewport" content="width=device-width, initial-scale=1.0">
<title>Academic Routine – BracU Portal</title>
<link rel="stylesheet" href="../assets/css/style.css">
</head>
<body>
<div class="layout">
<?php include '../includes/sidebar.php'; ?>
<div class="main">
    <div class="topbar">
        <div><h1>Academic Routine</h1><p>Your weekly class schedule & exam routine</p></div>
    </div>
    <div class="page-content">
        <div class="card">
            <div class="card-header"><h2>📅 Class & Exam Routine · Spring 2026</h2></div>
            <div class="table-wrapper">
                <?php if ($routines->num_rows > 0): ?>
                <table>
                    <thead><tr><th>Course</th><th>Exam Date</th><th>Section / Time / Room</th><th>Type</th></tr></thead>
                    <tbody>
                    <?php while($r = $routines->fetch_assoc()): ?>
                    <tr>
                        <td class="fw-bold"><?= htmlspecialchars($r['Courses']) ?></td>
                        <td><?= date('d M Y', strtotime($r['Exam_date'])) ?></td>
                        <td style="font-size:13px;"><?= htmlspecialchars($r['Exam_routine']) ?></td>
                        <td><span class="badge badge-primary"><?= htmlspecialchars($r['Attribute']) ?></span></td>
                    </tr>
                    <?php endwhile; ?>
                    </tbody>
                </table>
                <?php else: ?>
                <div class="empty-state"><div class="empty-icon">📭</div><p>No routine data available.</p></div>
                <?php endif; ?>
            </div>
        </div>
    </div>
</div>
</div>
</body>
</html>
