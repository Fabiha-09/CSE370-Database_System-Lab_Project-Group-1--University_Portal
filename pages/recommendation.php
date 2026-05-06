<?php
/* ── Permanent redirect to the new Smart Recommendations engine ── */
header('Location: smart_recommendations.php', true, 301);
exit;
/*
 * Legacy file kept for backwards compatibility.
 * All logic now lives in smart_recommendations.php.
 */
require_once '../includes/auth.php';
requireLogin();
require_once '../includes/db.php';
$sid = getStudentId();

$q = $conn->prepare("SELECT Courses_done, courses_not_done FROM Smart_Recommendation WHERE Student_id=?");
$q->bind_param('s', $sid);
$q->execute();
$rec = $q->get_result()->fetch_assoc();

// Also compute from prerequisites dynamically
$prereq_q = $conn->query("SELECT course_code, prereq_course_code FROM Prerequisite");
$prereqs = [];
while($r = $prereq_q->fetch_assoc()) {
    $prereqs[$r['course_code']][] = $r['prereq_course_code'];
}

$done_q = $conn->prepare("SELECT Course_code, Grade_Point FROM Grade WHERE Student_id=?");
$done_q->bind_param('s', $sid);
$done_q->execute();
$done_res = $done_q->get_result();
$best_grades = [];
while($r = $done_res->fetch_assoc()) {
    $cc = $r['Course_code'];
    if ($r['Grade_Point'] !== 'F') {
        $best_grades[$cc] = true;
    } elseif (!isset($best_grades[$cc])) {
        $best_grades[$cc] = false;
    }
}

$done = [];
$failed_courses = [];
foreach($best_grades as $cc => $passed) {
    if ($passed) {
        $done[] = $cc;
    } else {
        $failed_courses[] = $cc;
    }
}

// Remove from failed if already enrolled
$failed_to_retake = [];
foreach($failed_courses as $fc) {
    $enq = $conn->prepare("SELECT 1 FROM Enrolled_In WHERE Student_id=? AND course_code=?");
    $enq->bind_param('ss', $sid, $fc);
    $enq->execute();
    if ($enq->get_result()->num_rows === 0) {
        $failed_to_retake[] = $fc;
    }
}

// Find what student can take
$all_courses_q = $conn->query("SELECT course_code FROM Course");
$eligible = [];
while($r = $all_courses_q->fetch_assoc()) {
    $cc = $r['course_code'];
    if (in_array($cc, $done)) continue;
    $enrolled_q = $conn->prepare("SELECT 1 FROM Enrolled_In WHERE Student_id=? AND course_code=?");
    $enrolled_q->bind_param('ss', $sid, $cc);
    $enrolled_q->execute();
    if ($enrolled_q->get_result()->num_rows > 0) continue;

    if (isset($prereqs[$cc])) {
        $all_done = true;
        foreach($prereqs[$cc] as $p) {
            if (!in_array($p, $done)) { $all_done = false; break; }
        }
        if ($all_done) $eligible[] = $cc;
    }
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
<meta charset="UTF-8">
<meta name="viewport" content="width=device-width, initial-scale=1.0">
<title>Smart Recommendations – BracU Portal</title>
<link rel="stylesheet" href="../assets/css/style.css">
</head>
<body>
<div class="layout">
<?php include '../includes/sidebar.php'; ?>
<div class="main">
    <div class="topbar">
        <div><h1>🤖 Smart Course Recommendations</h1><p>AI-powered suggestions based on your academic history</p></div>
    </div>
    <div class="page-content">

        <?php if ($rec): ?>
        <div class="grid-2 mb-6">
            <div class="card">
                <div class="card-header"><h2>✅ Courses Completed</h2></div>
                <div class="card-body">
                    <?php foreach(explode(',', $rec['Courses_done']) as $c): ?>
                    <span class="course-chip chip-done"><?= trim($c) ?></span>
                    <?php endforeach; ?>
                </div>
            </div>
            <div class="card">
                <div class="card-header"><h2>📌 Pre-loaded Recommendations</h2></div>
                <div class="card-body">
                    <?php foreach(explode(',', $rec['courses_not_done']) as $c): ?>
                    <span class="course-chip chip-todo"><?= trim($c) ?></span>
                    <?php endforeach; ?>
                </div>
            </div>
        </div>
        <?php endif; ?>

        <?php if (count($failed_to_retake) > 0): ?>
        <div class="card mb-6" style="border: 2px solid var(--danger);">
            <div class="card-header">
                <div><h2 style="color: var(--danger);">🚨 Action Required: Retake Failed Courses</h2><p>You must retake these courses to satisfy prerequisites and clear your academic record.</p></div>
            </div>
            <div class="card-body">
                <?php foreach($failed_to_retake as $cc): ?>
                <div style="border:1px solid var(--danger); border-radius:10px; padding:14px 18px; margin-bottom:10px; display:flex; align-items:center; justify-content:space-between; background:#fff0f0;">
                    <div>
                        <div class="fw-bold" style="font-size:15px; color: var(--danger);"><?= $cc ?></div>
                        <div class="text-muted" style="margin-top:4px; font-size:12px;">
                            Priority: High
                        </div>
                    </div>
                    <a href="enrollment.php" class="btn btn-primary btn-sm" style="background: var(--danger); border-color: var(--danger);">Retake Now</a>
                </div>
                <?php endforeach; ?>
            </div>
        </div>
        <?php endif; ?>

        <div class="card mb-6">
            <div class="card-header">
                <div><h2>🎯 Dynamically Computed Eligible Courses</h2><p>Courses you can enroll in next — prerequisites satisfied</p></div>
            </div>
            <div class="card-body">
                <?php if (count($eligible) > 0): ?>
                <div style="margin-bottom:12px; font-size:14px; color:var(--text-muted);">
                    Based on your completed courses, you are eligible for:
                </div>
                <?php foreach($eligible as $cc):
                    $pq = $conn->prepare("SELECT prereq_course_code FROM Prerequisite WHERE course_code=?");
                    $pq->bind_param('s', $cc);
                    $pq->execute();
                    $pres = $pq->get_result();
                    $prereq_list = [];
                    while($p=$pres->fetch_assoc()) $prereq_list[] = $p['prereq_course_code'];
                ?>
                <div style="border:1px solid var(--border); border-radius:10px; padding:14px 18px; margin-bottom:10px; display:flex; align-items:center; justify-content:space-between; background:#fff;">
                    <div>
                        <div class="fw-bold" style="font-size:15px;"><?= $cc ?></div>
                        <div class="text-muted" style="margin-top:4px; font-size:12px;">
                            Prerequisite satisfied: <?= implode(', ', $prereq_list) ?>
                        </div>
                    </div>
                    <a href="enrollment.php" class="btn btn-primary btn-sm">Enroll Now</a>
                </div>
                <?php endforeach; ?>
                <?php else: ?>
                <div class="empty-state"><div class="empty-icon">🎓</div><p>You've completed all available prerequisites or are already enrolled.</p></div>
                <?php endif; ?>
            </div>
        </div>

        <!-- Already completed -->
        <div class="card">
            <div class="card-header"><h2>📚 Your Completed Courses</h2></div>
            <div class="card-body">
                <?php if (count($done) > 0): ?>
                <?php foreach($done as $c): ?>
                <span class="course-chip chip-done"><?= $c ?></span>
                <?php endforeach; ?>
                <?php else: ?>
                <div class="text-muted">No completed courses found.</div>
                <?php endif; ?>
            </div>
        </div>

    </div>
</div>
</div>
</body>
</html>
