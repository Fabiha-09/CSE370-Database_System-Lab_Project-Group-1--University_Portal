<?php
require_once '../includes/auth.php';
requireLogin();
require_once '../includes/db.php';

$sid = getStudentId();

// Fetch student info
$st = $conn->prepare("SELECT s.*, d.dept_name FROM Student s LEFT JOIN Department d ON s.dept_id=d.dept_id WHERE s.Student_id=?");
$st->bind_param('s', $sid);
$st->execute();
$student = $st->get_result()->fetch_assoc();

// Enrolled courses count
$ec = $conn->prepare("SELECT COUNT(*) as cnt FROM Enrolled_In WHERE Student_id=?");
$ec->bind_param('s', $sid);
$ec->execute();
$enrolled_count = $ec->get_result()->fetch_assoc()['cnt'];

// Reward points total
$rp = $conn->prepare("SELECT SUM(Points_Awarded) as total FROM Reward_Points WHERE Student_id=?");
$rp->bind_param('s', $sid);
$rp->execute();
$rp_total = $rp->get_result()->fetch_assoc()['total'] ?? 0;

// Notes contributed
$nc = $conn->prepare("SELECT COUNT(*) as cnt FROM Note WHERE Student_id=?");
$nc->bind_param('s', $sid);
$nc->execute();
$notes_contributed = $nc->get_result()->fetch_assoc()['cnt'] ?? 0;

// Upcoming exams
$exq = $conn->prepare("SELECT * FROM Exam WHERE Student_id=? AND Exam_date >= CURDATE() ORDER BY Exam_date LIMIT 3");
$exq->bind_param('s', $sid);
$exq->execute();
$exams = $exq->get_result();

// Fee status
$feeq = $conn->prepare("SELECT * FROM Semester_Fees WHERE Student_id=? ORDER BY Payment_id DESC LIMIT 1");
$feeq->bind_param('s', $sid);
$feeq->execute();
$fee = $feeq->get_result()->fetch_assoc();

// Recent grades
$grq = $conn->prepare("SELECT g.Course_code, g.Grade_Point, g.Marks_obtained FROM Grade g WHERE g.Student_id=? ORDER BY g.Course_code DESC LIMIT 5");
$grq->bind_param('s', $sid);
$grq->execute();
$grades = $grq->get_result();
?>
<!DOCTYPE html>
<html lang="en">
<head>
<meta charset="UTF-8">
<meta name="viewport" content="width=device-width, initial-scale=1.0">
<title>Dashboard – BracU Portal</title>
<link rel="stylesheet" href="../assets/css/style.css">
</head>
<body>
<div class="layout">
<?php include '../includes/sidebar.php'; ?>
<div class="main">
    <div class="topbar">
        <div>
            <h1>Welcome back, <?= htmlspecialchars($student['Fname']) ?>! 👋</h1>
            <p><?= htmlspecialchars($student['dept_name']) ?> · <?= htmlspecialchars($sid) ?> · Spring 2026</p>
        </div>
        <div class="topbar-right">
            <span class="badge badge-primary">📅 Spring 2026</span>
            <span class="badge badge-gold">⭐ <?= $rp_total ?> pts</span>
        </div>
    </div>

    <div class="page-content">

        <!-- Stats -->
        <div class="stats-grid mb-6">
            <div class="stat-card">
                <div class="stat-icon blue">📚</div>
                <div class="stat-info">
                    <div class="value"><?= $enrolled_count ?></div>
                    <div class="label">Enrolled Courses</div>
                </div>
            </div>
            <div class="stat-card">
                <div class="stat-icon green">🎓</div>
                <div class="stat-info">
                    <div class="value"><?= number_format($student['cgpa'],2) ?></div>
                    <div class="label">Current CGPA</div>
                </div>
            </div>
            <div class="stat-card">
                <div class="stat-icon gold">🏆</div>
                <div class="stat-info">
                    <div class="value"><?= $rp_total ?></div>
                    <div class="label">Reward Points</div>
                </div>
            </div>
            <div class="stat-card">
                <div class="stat-icon red">💳</div>
                <div class="stat-info">
                    <div class="value"><?= $fee ? ($fee['Payment_date'] ? 'Paid' : 'Pending') : 'N/A' ?></div>
                    <div class="label">Fee Status</div>
                </div>
            </div>
            <div class="stat-card">
                <div class="stat-icon purple">📝</div>
                <div class="stat-info">
                    <div class="value"><?= $notes_contributed ?></div>
                    <div class="label">Notes Contributed</div>
                </div>
            </div>
        </div>

        <div class="grid-2">
            <!-- Upcoming Exams -->
            <div class="card">
                <div class="card-header">
                    <div>
                        <h2>📝 Upcoming Exams</h2>
                        <p>Your next scheduled exams</p>
                    </div>
                    <a href="exam_schedule.php" class="btn btn-secondary btn-sm">View All</a>
                </div>
                <div class="table-wrapper">
                    <?php if ($exams->num_rows > 0): ?>
                    <table>
                        <thead><tr><th>Type</th><th>Date</th><th>Time</th><th>Room</th></tr></thead>
                        <tbody>
                        <?php while($e = $exams->fetch_assoc()): ?>
                        <tr>
                            <td><span class="badge badge-primary"><?= $e['Exam_type'] ?></span></td>
                            <td><?= date('d M Y', strtotime($e['Exam_date'])) ?></td>
                            <td><?= date('h:i A', strtotime($e['Start_time'])) ?></td>
                            <td><?= htmlspecialchars($e['Room_number']) ?></td>
                        </tr>
                        <?php endwhile; ?>
                        </tbody>
                    </table>
                    <?php else: ?>
                    <div class="empty-state"><div class="empty-icon">🎉</div><p>No upcoming exams</p></div>
                    <?php endif; ?>
                </div>
            </div>

            <!-- Recent Grades -->
            <div class="card">
                <div class="card-header">
                    <div>
                        <h2>📊 Recent Grades</h2>
                        <p>Your latest academic results</p>
                    </div>
                    <a href="grades.php" class="btn btn-secondary btn-sm">View All</a>
                </div>
                <div class="table-wrapper">
                    <?php if ($grades->num_rows > 0): ?>
                    <table>
                        <thead><tr><th>Course</th><th>Grade</th><th>Marks</th></tr></thead>
                        <tbody>
                        <?php while($g = $grades->fetch_assoc()): ?>
                        <tr>
                            <td class="fw-bold"><?= $g['Course_code'] ?></td>
                            <td><span class="grade-pill"><?= $g['Grade_Point'] ?></span></td>
                            <td><?= $g['Marks_obtained'] ?>/100</td>
                        </tr>
                        <?php endwhile; ?>
                        </tbody>
                    </table>
                    <?php else: ?>
                    <div class="empty-state"><div class="empty-icon">📭</div><p>No grades yet</p></div>
                    <?php endif; ?>
                </div>
            </div>
        </div>

        <!-- Quick Actions -->
        <div class="card" style="margin-top:20px;">
            <div class="card-header"><h2>⚡ Quick Actions</h2></div>
            <div class="card-body">
                <div style="display:flex; flex-wrap:wrap; gap:12px;">
                    <a href="enrollment.php" class="btn btn-secondary">📋 Enroll in Course</a>
                    <a href="recommendation.php" class="btn btn-secondary">🤖 Get Recommendations</a>
                    <a href="notes.php" class="btn btn-secondary">📚 Browse Notes</a>
                    <a href="complaint.php" class="btn btn-secondary">🔔 File Complaint</a>
                    <a href="lab_availability.php" class="btn btn-secondary">🖥️ Check Lab Slots</a>
                    <a href="fees.php" class="btn btn-secondary">💳 View Fees</a>
                </div>
            </div>
        </div>

    </div>
</div>
</div>
</body>
</html>
