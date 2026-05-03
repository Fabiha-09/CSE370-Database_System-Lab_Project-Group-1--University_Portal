<?php
require_once '../includes/auth.php';
requireLogin();
require_once '../includes/db.php';
$sid = getStudentId();

$grades_q = $conn->prepare("SELECT g.Course_code, g.Grade_Point, g.Marks_obtained, c.credit_hours, c.Semester_id FROM Grade g JOIN Course c ON g.Course_code=c.course_code WHERE g.Student_id=? ORDER BY c.Semester_id, g.Course_code");
$grades_q->bind_param('s', $sid);
$grades_q->execute();
$grades_res = $grades_q->get_result();

$grades = [];
$total_credits = 0; $total_points = 0;
while($r = $grades_res->fetch_assoc()) {
    $grades[] = $r;
    $gp_map = ['A'=>4.00,'A-'=>3.70,'B+'=>3.30,'B'=>3.00,'B-'=>2.70,'C+'=>2.30,'C'=>2.00,'C-'=>1.70,'D'=>1.00,'F'=>0.00];
    $gp = $gp_map[$r['Grade_Point']] ?? 0;
    $total_credits += $r['credit_hours'];
    $total_points  += $gp * $r['credit_hours'];
}
$gpa = $total_credits > 0 ? round($total_points/$total_credits, 2) : 0;

$student_q = $conn->prepare("SELECT Fname, Lname, dept_id FROM Student WHERE Student_id=?");
$student_q->bind_param('s', $sid);
$student_q->execute();
$st = $student_q->get_result()->fetch_assoc();
?>
<!DOCTYPE html>
<html lang="en">
<head>
<meta charset="UTF-8">
<meta name="viewport" content="width=device-width, initial-scale=1.0">
<title>Grade Sheet – BracU Portal</title>
<link rel="stylesheet" href="../assets/css/style.css">
</head>
<body>
<div class="layout">
<?php include '../includes/sidebar.php'; ?>
<div class="main">
    <div class="topbar">
        <div><h1>Grade Sheet</h1><p>Academic transcript · <?= htmlspecialchars($st['Fname'].' '.$st['Lname']) ?></p></div>
        <div class="topbar-right">
            <button onclick="window.print()" class="btn btn-secondary btn-sm">🖨️ Print</button>
        </div>
    </div>

    <div class="page-content">
        <div class="stats-grid mb-6">
            <div class="stat-card">
                <div class="stat-icon blue">📚</div>
                <div class="stat-info"><div class="value"><?= count($grades) ?></div><div class="label">Courses Completed</div></div>
            </div>
            <div class="stat-card">
                <div class="stat-icon green">🎓</div>
                <div class="stat-info"><div class="value"><?= $gpa ?></div><div class="label">Semester GPA</div></div>
            </div>
            <div class="stat-card">
                <div class="stat-icon gold">📊</div>
                <div class="stat-info"><div class="value"><?= $total_credits ?></div><div class="label">Total Credits Earned</div></div>
            </div>
        </div>

        <div class="card">
            <div class="card-header">
                <div><h2>📜 Academic Transcript</h2><p>Spring 2026 · BRAC University</p></div>
            </div>
            <div class="table-wrapper">
                <?php if (count($grades) > 0): ?>
                <table>
                    <thead>
                        <tr>
                            <th>Course Code</th>
                            <th>Semester</th>
                            <th>Credit Hours</th>
                            <th>Marks Obtained</th>
                            <th>Grade</th>
                            <th>Grade Point</th>
                            <th>Quality Points</th>
                        </tr>
                    </thead>
                    <tbody>
                    <?php
                    $gp_map = ['A'=>4.00,'A-'=>3.70,'B+'=>3.30,'B'=>3.00,'B-'=>2.70,'C+'=>2.30,'C'=>2.00,'C-'=>1.70,'D'=>1.00,'F'=>0.00];
                    foreach($grades as $g):
                        $gp_val = $gp_map[$g['Grade_Point']] ?? 0;
                        $qp = $gp_val * $g['credit_hours'];
                    ?>
                    <tr>
                        <td class="fw-bold"><?= $g['Course_code'] ?></td>
                        <td><?= $g['Semester_id'] ?></td>
                        <td><?= $g['credit_hours'] ?></td>
                        <td><?= $g['Marks_obtained'] ?>/100</td>
                        <td><span class="grade-pill"><?= $g['Grade_Point'] ?></span></td>
                        <td><?= number_format($gp_val,2) ?></td>
                        <td><?= number_format($qp,2) ?></td>
                    </tr>
                    <?php endforeach; ?>
                    </tbody>
                    <tfoot>
                        <tr style="background:var(--surface2); font-weight:700;">
                            <td colspan="2">SEMESTER TOTAL</td>
                            <td><?= $total_credits ?> credits</td>
                            <td>—</td>
                            <td>—</td>
                            <td><?= number_format($gpa,2) ?></td>
                            <td><?= number_format($total_points,2) ?></td>
                        </tr>
                    </tfoot>
                </table>
                <?php else: ?>
                <div class="empty-state"><div class="empty-icon">📭</div><p>No grades available yet.</p></div>
                <?php endif; ?>
            </div>
        </div>
    </div>
</div>
</div>
</body>
</html>
