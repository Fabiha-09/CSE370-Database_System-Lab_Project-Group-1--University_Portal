<?php
require_once '../includes/auth.php';
requireLogin();
require_once '../includes/db.php';
$sid = getStudentId();
$msg = '';

// Handle enroll
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['enroll'])) {
    $cc = $_POST['course_code'];

    // Check already enrolled
    $chk = $conn->prepare("SELECT 1 FROM Enrolled_In WHERE Student_id=? AND course_code=?");
    $chk->bind_param('ss', $sid, $cc);
    $chk->execute();
    if ($chk->get_result()->num_rows > 0) {
        $msg = ['type'=>'warning', 'text'=>'You are already enrolled in ' . $cc];
    } else {
        // Feature 08: Credit limit check
        $cred = $conn->prepare("SELECT SUM(c.credit_hours) as total FROM Enrolled_In e JOIN Course c ON e.course_code=c.course_code WHERE e.Student_id=?");
        $cred->bind_param('s', $sid);
        $cred->execute();
        $cur_credits = $cred->get_result()->fetch_assoc()['total'] ?? 0;

        $new_cred = $conn->prepare("SELECT credit_hours, max_capacity, room_no FROM Course WHERE course_code=?");
        $new_cred->bind_param('s', $cc);
        $new_cred->execute();
        $course_info = $new_cred->get_result()->fetch_assoc();

        if (($cur_credits + $course_info['credit_hours']) > 15) {
            $msg = ['type'=>'danger', 'text'=>"⚠️ Credit Limit Exceeded — You cannot enroll in $cc. Maximum allowed: 15 credits per semester. Current: {$cur_credits} credits."];
        } else {
            // Feature 10: Classroom capacity check
            $cap_q = $conn->prepare("SELECT COUNT(*) as enrolled FROM Enrolled_In WHERE course_code=?");
            $cap_q->bind_param('s', $cc);
            $cap_q->execute();
            $enrolled_in_course = $cap_q->get_result()->fetch_assoc()['enrolled'];
            if ($enrolled_in_course >= $course_info['max_capacity']) {
                $msg = ['type'=>'danger', 'text'=>"🚫 Class Full — Cannot Enroll in $cc. Maximum capacity ({$course_info['max_capacity']} students) reached."];
            } else {
                $ins = $conn->prepare("INSERT INTO Enrolled_In (Student_id, course_code) VALUES (?,?)");
                $ins->bind_param('ss', $sid, $cc);
                $ins->execute();
                $msg = ['type'=>'success', 'text'=>"✅ Successfully enrolled in $cc!"];
            }
        }
    }
}

// Handle drop
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['drop'])) {
    $cc = $_POST['drop_code'];
    $del = $conn->prepare("DELETE FROM Enrolled_In WHERE Student_id=? AND course_code=?");
    $del->bind_param('ss', $sid, $cc);
    $del->execute();
    $msg = ['type'=>'success', 'text'=>"Course $cc dropped successfully."];
}

// Current credits
$cred_q = $conn->prepare("SELECT SUM(c.credit_hours) as total FROM Enrolled_In e JOIN Course c ON e.course_code=c.course_code WHERE e.Student_id=?");
$cred_q->bind_param('s', $sid);
$cred_q->execute();
$cur_credits = $cred_q->get_result()->fetch_assoc()['total'] ?? 0;

// My enrolled courses
$my_q = $conn->prepare("SELECT e.course_code, c.credit_hours, c.Semester_id, c.room_no, c.dept_id, (SELECT COUNT(*) FROM Enrolled_In WHERE course_code=e.course_code) as enrolled_count, c.max_capacity FROM Enrolled_In e JOIN Course c ON e.course_code=c.course_code WHERE e.Student_id=? ORDER BY e.course_code");
$my_q->bind_param('s', $sid);
$my_q->execute();
$my_courses = $my_q->get_result();

// All available courses
$all_q = $conn->prepare("SELECT c.course_code, c.credit_hours, c.dept_id, c.max_capacity, d.dept_name, (SELECT COUNT(*) FROM Enrolled_In WHERE course_code=c.course_code) as enrolled_count, (SELECT 1 FROM Enrolled_In WHERE Student_id=? AND course_code=c.course_code) as already_enrolled FROM Course c JOIN Department d ON c.dept_id=d.dept_id ORDER BY c.course_code");
$all_q->bind_param('s', $sid);
$all_q->execute();
$all_courses = $all_q->get_result();
?>
<!DOCTYPE html>
<html lang="en">
<head>
<meta charset="UTF-8">
<meta name="viewport" content="width=device-width, initial-scale=1.0">
<title>Course Enrollment – BracU Portal</title>
<link rel="stylesheet" href="../assets/css/style.css">
</head>
<body>
<div class="layout">
<?php include '../includes/sidebar.php'; ?>
<div class="main">
    <div class="topbar">
        <div>
            <h1>Course Enrollment</h1>
            <p>Add or drop courses for Spring 2026</p>
        </div>
        <div class="topbar-right">
            <span class="badge <?= $cur_credits >= 15 ? 'badge-danger' : ($cur_credits >= 12 ? 'badge-warning' : 'badge-success') ?>">
                📊 <?= $cur_credits ?>/15 Credits
            </span>
        </div>
    </div>

    <div class="page-content">
        <?php if ($msg): ?>
        <div class="alert alert-<?= $msg['type'] ?> mb-4"><?= $msg['text'] ?></div>
        <?php endif; ?>

        <!-- Credit bar -->
        <div class="card mb-6">
            <div class="card-body">
                <div style="display:flex; justify-content:space-between; margin-bottom:8px;">
                    <span class="fw-bold">Credit Hours Used</span>
                    <span><?= $cur_credits ?> / 15</span>
                </div>
                <div class="progress-bar">
                    <div class="fill" style="width:<?= min(($cur_credits/15)*100, 100) ?>%; background: <?= $cur_credits >= 15 ? 'var(--danger)' : ($cur_credits >= 12 ? 'var(--accent)' : '') ?>"></div>
                </div>
                <?php if ($cur_credits >= 15): ?>
                <div class="alert alert-danger" style="margin-top:12px; margin-bottom:0;">⚠️ Credit limit reached. You cannot enroll in more courses this semester.</div>
                <?php endif; ?>
            </div>
        </div>

        <!-- My Courses -->
        <div class="card mb-6">
            <div class="card-header">
                <div><h2>📋 My Enrolled Courses</h2><p>Currently enrolled this semester</p></div>
            </div>
            <div class="table-wrapper">
                <?php if ($my_courses->num_rows > 0): ?>
                <table>
                    <thead><tr><th>Course Code</th><th>Credits</th><th>Dept</th><th>Room</th><th>Enrolled</th><th>Action</th></tr></thead>
                    <tbody>
                    <?php while($r = $my_courses->fetch_assoc()): ?>
                    <tr>
                        <td class="fw-bold"><?= $r['course_code'] ?></td>
                        <td><?= $r['credit_hours'] ?> cr</td>
                        <td><?= $r['dept_id'] ?></td>
                        <td><?= $r['room_no'] ?></td>
                        <td><?= $r['enrolled_count'] ?>/<?= $r['max_capacity'] ?></td>
                        <td>
                            <form method="POST" style="display:inline;" onsubmit="return confirm('Drop <?= $r['course_code'] ?>?')">
                                <input type="hidden" name="drop_code" value="<?= $r['course_code'] ?>">
                                <button type="submit" name="drop" class="btn btn-danger btn-sm">Drop</button>
                            </form>
                        </td>
                    </tr>
                    <?php endwhile; ?>
                    </tbody>
                </table>
                <?php else: ?>
                <div class="empty-state"><div class="empty-icon">📭</div><p>No courses enrolled yet.</p></div>
                <?php endif; ?>
            </div>
        </div>

        <!-- Available Courses -->
        <div class="card">
            <div class="card-header">
                <div><h2>📚 Available Courses</h2><p>Spring 2026 course catalog</p></div>
            </div>
            <div class="table-wrapper">
                <table>
                    <thead><tr><th>Course Code</th><th>Credits</th><th>Department</th><th>Enrolled/Capacity</th><th>Status</th><th>Action</th></tr></thead>
                    <tbody>
                    <?php while($r = $all_courses->fetch_assoc()): 
                        $full = $r['enrolled_count'] >= $r['max_capacity'];
                    ?>
                    <tr>
                        <td class="fw-bold"><?= $r['course_code'] ?></td>
                        <td><?= $r['credit_hours'] ?> cr</td>
                        <td><?= htmlspecialchars($r['dept_name']) ?></td>
                        <td><?= $r['enrolled_count'] ?>/<?= $r['max_capacity'] ?></td>
                        <td>
                            <?php if ($r['already_enrolled']): ?>
                                <span class="badge badge-success">Enrolled</span>
                            <?php elseif ($full): ?>
                                <span class="badge badge-danger">Full</span>
                            <?php else: ?>
                                <span class="badge badge-primary">Open</span>
                            <?php endif; ?>
                        </td>
                        <td>
                            <?php if (!$r['already_enrolled'] && !$full): ?>
                            <form method="POST" style="display:inline;">
                                <input type="hidden" name="course_code" value="<?= $r['course_code'] ?>">
                                <button type="submit" name="enroll" class="btn btn-primary btn-sm">Enroll</button>
                            </form>
                            <?php elseif ($r['already_enrolled']): ?>
                            <form method="POST" style="display:inline;" onsubmit="return confirm('Drop this course?')">
                                <input type="hidden" name="drop_code" value="<?= $r['course_code'] ?>">
                                <button type="submit" name="drop" class="btn btn-danger btn-sm">Drop</button>
                            </form>
                            <?php else: ?>
                            <button class="btn btn-secondary btn-sm" disabled>Full</button>
                            <?php endif; ?>
                        </td>
                    </tr>
                    <?php endwhile; ?>
                    </tbody>
                </table>
            </div>
        </div>
    </div>
</div>
</div>
</body>
</html>
