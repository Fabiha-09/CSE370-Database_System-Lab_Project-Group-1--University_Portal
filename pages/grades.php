<?php
require_once '../includes/auth.php';
requireLogin();
require_once '../includes/db.php';
$sid = getStudentId();

$grades_q = $conn->prepare("SELECT g.Course_code, g.Grade_Point, g.Marks_obtained, c.credit_hours, c.Semester_id FROM Grade g JOIN Course c ON g.Course_code=c.course_code WHERE g.Student_id=? ORDER BY CASE WHEN c.Semester_id LIKE '%2023' THEN 1 WHEN c.Semester_id LIKE '%2024' THEN 2 WHEN c.Semester_id LIKE '%2025' THEN 3 ELSE 4 END, c.Semester_id, g.Course_code");
$grades_q->bind_param('s', $sid);
$grades_q->execute();
$grades_res = $grades_q->get_result();

// Function to convert letter grade to numeric value
function gradeToPoints($grade) {
    $gradeMap = [
        'A' => 4.0, 'A-' => 3.7,
        'B+' => 3.3, 'B' => 3.0, 'B-' => 2.7,
        'C+' => 2.3, 'C' => 2.0, 'C-' => 1.7,
        'D' => 1.0, 'F' => 0.0
    ];
    return $gradeMap[$grade] ?? 0.0;
}

$grades_by_semester = [];
$best_grades = [];
$total_credits = 0; $total_points = 0;

while($r = $grades_res->fetch_assoc()) {
    $r['Grade_Points'] = gradeToPoints($r['Grade_Point']);
    $sem = $r['Semester_id'];
    if (!isset($grades_by_semester[$sem])) $grades_by_semester[$sem] = [];
    $grades_by_semester[$sem][] = $r;
    
    // For CGPA, keep track of best grade for retakes
    $cc = $r['Course_code'];
    if (!isset($best_grades[$cc]) || $r['Grade_Points'] > $best_grades[$cc]['Grade_Points']) {
        $best_grades[$cc] = $r;
    }
}

foreach($best_grades as $g) {
    $total_credits += $g['credit_hours'];
    $total_points  += $g['Grade_Points'] * $g['credit_hours'];
}
$gpa = $total_credits > 0 ? round($total_points/$total_credits, 2) : 0;

$student_q = $conn->prepare("SELECT Fname, Lname, dept_id FROM Student WHERE Student_id=?");
$student_q->bind_param('s', $sid);
$student_q->execute();
$st = $student_q->get_result()->fetch_assoc();

// Fetch enrolled courses without grades
$enr_q = $conn->prepare("
    SELECT e.course_code, c.credit_hours 
    FROM Enrolled_In e 
    JOIN Course c ON e.course_code = c.course_code 
    WHERE e.Student_id = ?
");
$enr_q->bind_param('s', $sid);
$enr_q->execute();
$enrolled_courses_res = $enr_q->get_result();
$enrolled_courses = [];
while ($row = $enrolled_courses_res->fetch_assoc()) {
    $enrolled_courses[] = $row;
}

if (count($enrolled_courses) > 0) {
    $grades_by_semester['Spring 2026 (Current)'] = [];
    foreach ($enrolled_courses as $ec) {
        $grades_by_semester['Spring 2026 (Current)'][] = [
            'Course_code' => $ec['course_code'],
            'Semester_id' => 'Spring 2026',
            'credit_hours' => $ec['credit_hours'],
            'Marks_obtained' => '-',
            'Grade_Point' => 'Pending',
            'Grade_Points' => 0
        ];
    }
}
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
                <div class="stat-info"><div class="value"><?= count($best_grades) ?></div><div class="label">Courses Completed</div></div>
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
                <?php if (count($grades_by_semester) > 0): ?>
                <?php foreach($grades_by_semester as $sem => $semester_grades): ?>
                <div style="background: var(--surface2); padding: 8px 16px; font-weight: bold; border-top: 1px solid var(--border); border-bottom: 1px solid var(--border);">
                    <?= htmlspecialchars($sem) ?>
                </div>
                <table>
                    <thead>
                        <tr>
                            <th>Course Code</th>
                            <th>Credit Hours</th>
                            <th>Marks Obtained</th>
                            <th>Grade</th>
                            <th>Grade Point</th>
                            <th>Quality Points</th>
                        </tr>
                    </thead>
                    <tbody>
                    <?php
                    $sem_credits = 0; $sem_points = 0;
                    foreach($semester_grades as $g):
                        $gp_val = $g['Grade_Points'];
                        if ($g['Grade_Point'] !== 'Pending') {
                            $qp = $gp_val * $g['credit_hours'];
                            $sem_credits += $g['credit_hours'];
                            $sem_points += $qp;
                        } else {
                            $qp = 0;
                        }
                    ?>
                    <tr>
                        <td class="fw-bold"><?= $g['Course_code'] ?>
                        <?php if(isset($g['Grade_Point']) && $g['Grade_Point'] !== 'F' && $g['Grade_Point'] !== 'Pending'): ?>
                            <?php 
                            // check if this is a retake success
                            // i.e. it exists multiple times in grades
                            // Simplified: just show it.
                            ?>
                        <?php endif; ?>
                        </td>
                        <td><?= $g['credit_hours'] ?></td>
                        <td><?= $g['Marks_obtained'] !== '-' ? $g['Marks_obtained'].'/100' : '-' ?></td>
                        <td><span class="grade-pill"><?= $g['Grade_Point'] ?></span></td>
                        <td><?= $g['Grade_Point'] !== 'Pending' ? number_format($gp_val,2) : '-' ?></td>
                        <td><?= $g['Grade_Point'] !== 'Pending' ? number_format($qp,2) : '-' ?></td>
                    </tr>
                    <?php endforeach; ?>
                    </tbody>
                    <tfoot>
                        <tr style="background:var(--surface2); font-weight:700;">
                            <td colspan="2">SEMESTER TOTAL</td>
                            <td><?= $sem_credits ?> cr</td>
                            <td>—</td>
                            <td>—</td>
                            <td>—</td>
                            <td><?= $sem_credits > 0 ? number_format($sem_points / $sem_credits, 2) : '—' ?></td>
                        </tr>
                    </tfoot>
                </table>
                <?php endforeach; ?>
                <?php else: ?>
                <div class="empty-state"><div class="empty-icon">📭</div><p>No grades available yet.</p></div>
                <?php endif; ?>
            </div>
        </div>
        <div class="card" style="margin-top: 20px;">
            <div class="card-header">
                <div><h2>🔮 GPA Predictor</h2><p>Estimate your future CGPA</p></div>
                <div style="font-size: 24px; font-weight: 700; color: var(--primary);">
                    Target CGPA: <span id="predicted-gpa"><?= number_format($gpa, 2) ?></span>
                </div>
            </div>
            <div class="table-wrapper">
                <?php if (count($enrolled_courses) > 0): ?>
                <table>
                    <thead>
                        <tr>
                            <th>Course Code</th>
                            <th>Credit Hours</th>
                            <th>Predicted Grade</th>
                        </tr>
                    </thead>
                    <tbody>
                    <?php foreach($enrolled_courses as $c): ?>
                        <tr>
                            <td class="fw-bold"><?= htmlspecialchars($c['course_code']) ?></td>
                            <td><?= $c['credit_hours'] ?></td>
                            <td>
                                <select class="form-control predict-select" data-credits="<?= $c['credit_hours'] ?>" onchange="calculatePrediction()" style="width: 140px; padding: 6px 10px;">
                                    <option value="">Select...</option>
                                    <option value="A">A (4.0)</option>
                                    <option value="A-">A- (3.7)</option>
                                    <option value="B+">B+ (3.3)</option>
                                    <option value="B">B (3.0)</option>
                                    <option value="B-">B- (2.7)</option>
                                    <option value="C+">C+ (2.3)</option>
                                    <option value="C">C (2.0)</option>
                                    <option value="C-">C- (1.7)</option>
                                    <option value="D">D (1.0)</option>
                                    <option value="F">F (0.0)</option>
                                </select>
                            </td>
                        </tr>
                    <?php endforeach; ?>
                    </tbody>
                </table>
                <?php else: ?>
                <div class="empty-state"><div class="empty-icon">🎉</div><p>No active courses available for prediction.</p></div>
                <?php endif; ?>
            </div>
        </div>

    </div>
</div>
</div>

<script>
const currentPoints = <?= $total_points ?>;
const currentCredits = <?= $total_credits ?>;
const gradeMap = {'A':4.0,'A-':3.7,'B+':3.3,'B':3.0,'B-':2.7,'C+':2.3,'C':2.0,'C-':1.7,'D':1.0,'F':0.0};

function calculatePrediction() {
    let predictedPoints = 0;
    let predictedCredits = 0;
    document.querySelectorAll('.predict-select').forEach(select => {
        let grade = select.value;
        if(grade && gradeMap[grade] !== undefined) {
            let credits = parseFloat(select.dataset.credits);
            predictedCredits += credits;
            predictedPoints += gradeMap[grade] * credits;
        }
    });
    let newTotalCredits = currentCredits + predictedCredits;
    let newTotalPoints = currentPoints + predictedPoints;
    let predictedGPA = newTotalCredits > 0 ? (newTotalPoints / newTotalCredits).toFixed(2) : "0.00";
    document.getElementById('predicted-gpa').innerText = predictedGPA;
}
</script>
</body>
</html>
