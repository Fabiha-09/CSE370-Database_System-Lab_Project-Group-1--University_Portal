<?php
require_once '../includes/auth.php';
requireLogin();
require_once '../includes/db.php';
$sid = getStudentId();

$q = $conn->prepare("SELECT Exam_id, Exam_type, Room_number, Start_time, Exam_date FROM Exam WHERE Student_id=? ORDER BY Exam_date ASC");
$q->bind_param('s', $sid);
$q->execute();
$exams = $q->get_result();
?>
<!DOCTYPE html>
<html lang="en">
<head>
<meta charset="UTF-8">
<meta name="viewport" content="width=device-width, initial-scale=1.0">
<title>Exam Schedule – BracU Portal</title>
<link rel="stylesheet" href="../assets/css/style.css">
</head>
<body>
<div class="layout">
<?php include '../includes/sidebar.php'; ?>
<div class="main">
    <div class="topbar">
        <div><h1>Exam Schedule</h1><p>Your upcoming midterm and final examinations</p></div>
    </div>
    <div class="page-content">
        <div class="card">
            <div class="card-header"><h2>📝 Examination Timetable · Spring 2026</h2></div>
            <div class="table-wrapper">
                <?php if ($exams->num_rows > 0): ?>
                <table>
                    <thead><tr><th>#</th><th>Exam Type</th><th>Date</th><th>Day</th><th>Start Time</th><th>Room</th><th>Status</th></tr></thead>
                    <tbody>
                    <?php $i=1; while($e = $exams->fetch_assoc()):
                        $upcoming = strtotime($e['Exam_date']) >= strtotime(date('Y-m-d'));
                    ?>
                    <tr>
                        <td><?= $i++ ?></td>
                        <td>
                            <span class="badge <?= $e['Exam_type']==='Final' ? 'badge-danger' : 'badge-primary' ?>">
                                <?= $e['Exam_type'] ?>
                            </span>
                        </td>
                        <td><?= date('d M Y', strtotime($e['Exam_date'])) ?></td>
                        <td><?= date('l', strtotime($e['Exam_date'])) ?></td>
                        <td><?= date('h:i A', strtotime($e['Start_time'])) ?></td>
                        <td class="fw-bold"><?= htmlspecialchars($e['Room_number']) ?></td>
                        <td>
                            <span class="badge <?= $upcoming ? 'badge-warning' : 'badge-success' ?>">
                                <?= $upcoming ? '⏳ Upcoming' : '✅ Completed' ?>
                            </span>
                        </td>
                    </tr>
                    <?php endwhile; ?>
                    </tbody>
                </table>
                <?php else: ?>
                <div class="empty-state"><div class="empty-icon">🎉</div><p>No exams scheduled.</p></div>
                <?php endif; ?>
            </div>
        </div>
    </div>
</div>
</div>
</body>
</html>
