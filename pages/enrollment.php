<?php
require_once '../includes/auth.php';
requireLogin();
require_once '../includes/db.php';
$sid = getStudentId();
$msg = '';

$today         = date('Y-m-d');
$is_simulated  = false;

// ── 1. Global advising period flag ──────────────────────────────────────────
$advising_period = true;

// ── 2. Create Wishlist table if it does not yet exist ───────────────────────
$conn->query("CREATE TABLE IF NOT EXISTS Wishlist (
    student_id   VARCHAR(10) NOT NULL,
    course_code  VARCHAR(10) NOT NULL,
    added_date   DATE        NOT NULL,
    target_semester VARCHAR(10) DEFAULT 'FALL26',
    PRIMARY KEY (student_id, course_code),
    KEY idx_student (student_id)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci");

// ── 3. Advising-period guard for ALL POST actions ───────────────────────────
if ($_SERVER['REQUEST_METHOD'] === 'POST' && !$advising_period &&
    (isset($_POST['enroll']) || isset($_POST['drop']) ||
     isset($_POST['wishlist_add']) || isset($_POST['wishlist_remove']))) {
    $msg = ['type' => 'warning',
            'text' => 'Advising period is over. You cannot add or drop courses between semesters. Please contact the registrar for assistance.'];
}

// ── 4. POST: Enroll (only when advising period is open) ─────────────────────
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['enroll']) && $advising_period) {
    $cc = $_POST['course_code'];

    // Check already enrolled
    $chk = $conn->prepare("SELECT 1 FROM Enrolled_In WHERE Student_id=? AND course_code=?");
    $chk->bind_param('ss', $sid, $cc);
    $chk->execute();
    $chk_res = $chk->get_result();
    $already = $chk_res->num_rows > 0;
    $chk_res->free();
    $chk->close();

    if ($already) {
        $msg = ['type' => 'warning', 'text' => 'You are already enrolled in ' . htmlspecialchars($cc)];
    } else {
        // Feature 08: Credit limit check
        $cred = $conn->prepare("SELECT SUM(c.credit_hours) as total FROM Enrolled_In e JOIN Course c ON e.course_code=c.course_code WHERE e.Student_id=?");
        $cred->bind_param('s', $sid);
        $cred->execute();
        $cred_res = $cred->get_result();
        $cur_credits_for_check = $cred_res->fetch_assoc()['total'] ?? 0;
        $cred_res->free();
        $cred->close();

        $new_cred = $conn->prepare("SELECT credit_hours, max_capacity, room_no FROM Course WHERE course_code=?");
        $new_cred->bind_param('s', $cc);
        $new_cred->execute();
        $new_cred_res = $new_cred->get_result();
        $course_info = $new_cred_res->fetch_assoc();
        $new_cred_res->free();
        $new_cred->close();

        if (($cur_credits_for_check + $course_info['credit_hours']) > 15) {
            $msg = ['type' => 'danger',
                    'text' => "⚠️ Credit Limit Exceeded — You cannot enroll in " . htmlspecialchars($cc) .
                              ". Maximum allowed: 15 credits per semester. Current: {$cur_credits_for_check} credits."];
        } else {
            // Feature 10: Classroom capacity check
            $cap_q = $conn->prepare("SELECT COUNT(*) as enrolled FROM Enrolled_In WHERE course_code=?");
            $cap_q->bind_param('s', $cc);
            $cap_q->execute();
            $cap_res = $cap_q->get_result();
            $enrolled_in_course = $cap_res->fetch_assoc()['enrolled'];
            $cap_res->free();
            $cap_q->close();

            if ($enrolled_in_course >= $course_info['max_capacity']) {
                $msg = ['type' => 'danger',
                        'text' => "🚫 Class Full — Cannot Enroll in " . htmlspecialchars($cc) .
                                  ". Maximum capacity ({$course_info['max_capacity']} students) reached."];
            } else {
                $ins = $conn->prepare("INSERT INTO Enrolled_In (Student_id, course_code) VALUES (?,?)");
                $ins->bind_param('ss', $sid, $cc);
                $ins->execute();
                $ins->close();
                $msg = ['type' => 'success', 'text' => "✅ Successfully enrolled in " . htmlspecialchars($cc) . "!"];
            }
        }
    }
}

// ── 5. POST: Drop (only when advising period is open) ───────────────────────
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['drop']) && $advising_period) {
    $cc = $_POST['drop_code'];
    $del = $conn->prepare("DELETE FROM Enrolled_In WHERE Student_id=? AND course_code=?");
    $del->bind_param('ss', $sid, $cc);
    $del->execute();
    $del->close();
    $msg = ['type' => 'success', 'text' => "Course " . htmlspecialchars($cc) . " dropped successfully."];
}

// ── 6. POST: Wishlist add (only when advising period is open) ────────────────
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['wishlist_add']) && $advising_period) {
    $wc = $_POST['wishlist_course'] ?? '';
    if ($wc !== '') {
        $wi = $conn->prepare("INSERT IGNORE INTO Wishlist (student_id, course_code, added_date) VALUES (?,?,CURDATE())");
        $wi->bind_param('ss', $sid, $wc);
        $wi->execute();
        $wi->close();
        $msg = ['type' => 'success', 'text' => "✅ " . htmlspecialchars($wc) . " added to your Future Semester Wishlist!"];
    }
}

// ── 7. POST: Wishlist remove (only when advising period is open) ─────────────
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['wishlist_remove']) && $advising_period) {
    $wc = $_POST['wishlist_code'] ?? '';
    if ($wc !== '') {
        $wr = $conn->prepare("DELETE FROM Wishlist WHERE student_id=? AND course_code=?");
        $wr->bind_param('ss', $sid, $wc);
        $wr->execute();
        $wr->close();
        $msg = ['type' => 'success', 'text' => "Course removed from Wishlist."];
    }
}

// ── 8. Wishlist date-window ──────────────────────────────────────────────────
$today         = date('Y-m-d');
$wishlist_open = true;

// ── 9. Current credit total ──────────────────────────────────────────────────
$cred_q = $conn->prepare("SELECT SUM(c.credit_hours) as total FROM Enrolled_In e JOIN Course c ON e.course_code=c.course_code WHERE e.Student_id=?");
$cred_q->bind_param('s', $sid);
$cred_q->execute();
$cred_q_res  = $cred_q->get_result();
$cur_credits = $cred_q_res->fetch_assoc()['total'] ?? 0;
$cred_q_res->free();
$cred_q->close();

// ── 10. My enrolled courses ──────────────────────────────────────────────────
$my_q = $conn->prepare(
    "SELECT e.course_code, c.credit_hours, c.Semester_id, c.room_no, c.dept_id,
            (SELECT COUNT(*) FROM Enrolled_In WHERE course_code=e.course_code) as enrolled_count,
            c.max_capacity
     FROM Enrolled_In e
     JOIN Course c ON e.course_code=c.course_code
     WHERE e.Student_id=?
     ORDER BY e.course_code");
$my_q->bind_param('s', $sid);
$my_q->execute();
$my_courses = $my_q->get_result();

// ── 11. All available courses ────────────────────────────────────────────────
$all_q = $conn->prepare(
    "SELECT c.course_code, c.credit_hours, c.dept_id, c.max_capacity, d.dept_name,
            (SELECT COUNT(*) FROM Enrolled_In WHERE course_code=c.course_code) as enrolled_count,
            (SELECT 1 FROM Enrolled_In WHERE Student_id=? AND course_code=c.course_code) as already_enrolled
     FROM Course c
     JOIN Department d ON c.dept_id=d.dept_id
     ORDER BY c.course_code");
$all_q->bind_param('s', $sid);
$all_q->execute();
$all_courses = $all_q->get_result();

// ── 12. Wishlist: my current wishlist ────────────────────────────────────────
$wl_q = $conn->prepare(
    "SELECT w.course_code, w.added_date, w.target_semester, c.credit_hours, c.dept_id
     FROM Wishlist w
     LEFT JOIN Course c ON w.course_code=c.course_code
     WHERE w.student_id=?
     ORDER BY w.course_code");
$wl_q->bind_param('s', $sid);
$wl_q->execute();
$wl_res      = $wl_q->get_result();
$my_wishlist = [];
while ($r = $wl_res->fetch_assoc()) {
    $my_wishlist[] = $r;
}
$wl_res->free();
$wl_q->close();

// ── 13. Wishlist: courses not already wishlisted or enrolled ─────────────────
$avail_q = $conn->prepare(
    "SELECT c.course_code, c.credit_hours, c.dept_id
     FROM Course c
     WHERE c.course_code NOT IN (SELECT course_code FROM Wishlist      WHERE student_id=?)
       AND c.course_code NOT IN (SELECT course_code FROM Enrolled_In   WHERE Student_id=?)
     ORDER BY c.course_code");
$avail_q->bind_param('ss', $sid, $sid);
$avail_q->execute();
$avail_wish            = $avail_q->get_result();
$avail_wishlist_courses = [];
while ($r = $avail_wish->fetch_assoc()) {
    $avail_wishlist_courses[] = $r;
}
$avail_wish->free();
$avail_q->close();
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
                    <div class="fill" style="width:<?= min(($cur_credits / 15) * 100, 100) ?>%; background: <?= $cur_credits >= 15 ? 'var(--danger)' : ($cur_credits >= 12 ? 'var(--accent)' : '') ?>"></div>
                </div>
                <?php if ($cur_credits >= 15): ?>
                <div class="alert alert-danger" style="margin-top:12px; margin-bottom:0;">⚠️ Credit limit reached. You cannot enroll in more courses this semester.</div>
                <?php endif; ?>
            </div>
        </div>

        <!-- ── My Enrolled Courses ─────────────────────────────────────────── -->
        <div class="card mb-6">
            <div class="card-header">
                <div><h2>📋 My Enrolled Courses</h2><p>Currently enrolled this semester</p></div>
            </div>
            <div class="table-wrapper">
                <?php if ($my_courses->num_rows > 0): ?>
                <table>
                    <thead>
                        <tr>
                            <th>Course Code</th>
                            <th>Credits</th>
                            <th>Dept</th>
                            <th>Room</th>
                            <th>Enrolled</th>
                            <th>Action</th>
                        </tr>
                    </thead>
                    <tbody>
                    <?php while ($r = $my_courses->fetch_assoc()): ?>
                    <tr>
                        <td class="fw-bold"><?= htmlspecialchars($r['course_code']) ?></td>
                        <td><?= $r['credit_hours'] ?> cr</td>
                        <td><?= htmlspecialchars($r['dept_id']) ?></td>
                        <td><?= htmlspecialchars($r['room_no']) ?></td>
                        <td><?= $r['enrolled_count'] ?>/<?= $r['max_capacity'] ?></td>
                        <td>
                            <form method="POST" style="display:inline;margin:0;">
                                <input type="hidden" name="drop_code" value="<?= htmlspecialchars($r['course_code']) ?>">
                                <button type="submit" name="drop" class="btn btn-danger btn-sm">Drop</button>
                            </form>
                        </td>
                    </tr>
                    <?php endwhile; ?>
                    </tbody>
                </table>
                <?php else: ?>
                <div class="empty-state">
                    <div class="empty-icon">📭</div>
                    <p>No courses enrolled yet.</p>
                </div>
                <?php endif; ?>
            </div>
        </div>
        <?php
        $my_courses->free();
        $my_q->close();
        ?>

        <!-- ── Available Courses ──────────────────────────────────────────── -->
        <div class="card">
            <div class="card-header">
                <div><h2>📚 Available Courses</h2><p>Spring 2026 course catalog</p></div>
            </div>
            <div class="table-wrapper">
                <table>
                    <thead>
                        <tr>
                            <th>Course Code</th>
                            <th>Credits</th>
                            <th>Department</th>
                            <th>Enrolled/Capacity</th>
                            <th>Status</th>
                            <th>Action</th>
                        </tr>
                    </thead>
                    <tbody>
                    <?php while ($r = $all_courses->fetch_assoc()):
                        $full = $r['enrolled_count'] >= $r['max_capacity'];
                    ?>
                    <tr>
                        <td class="fw-bold"><?= htmlspecialchars($r['course_code']) ?></td>
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
                                    <input type="hidden" name="course_code" value="<?= htmlspecialchars($r['course_code']) ?>">
                                    <button type="submit" name="enroll" class="btn btn-primary btn-sm">Enroll</button>
                                </form>
                            <?php elseif ($r['already_enrolled']): ?>
                                <button class="btn btn-secondary btn-sm" disabled>Enrolled</button>
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
        <?php
        $all_courses->free();
        $all_q->close();
        ?>

        <!-- ── Future Semester Wishlist ───────────────────────────────────── -->
        <div class="card mt-6" style="margin-top:24px;">
            <div class="card-header" style="background:#f0f9ff; border-bottom:2px solid #7dd3fc;">
                <div>
                    <h2 style="color:#0369a1;">🗓️ Future Semester Wishlist</h2>
                    <p>Plan ahead — add courses you want to take next semester.</p>
                </div>
                <div style="display:flex;align-items:center;gap:10px;">
                    <span class="badge badge-success">✅ Wishlist Available</span>
                </div>
            </div>
            <div class="card-body">

                <!-- Current wishlist chips -->
                <?php if (!empty($my_wishlist)): ?>
                <h3 style="font-size:14px;font-weight:700;margin-bottom:12px;color:var(--text-muted);text-transform:uppercase;letter-spacing:.5px;">
                    My Wishlist (<?= count($my_wishlist) ?> course<?= count($my_wishlist) !== 1 ? 's' : '' ?>)
                </h3>
                <div style="display:flex;flex-wrap:wrap;gap:10px;margin-bottom:20px;">
                    <?php foreach ($my_wishlist as $wc): ?>
                    <div style="display:flex;align-items:center;gap:8px;background:#e0f2fe;border:1px solid #7dd3fc;border-radius:10px;padding:8px 14px;">
                        <span style="font-weight:700;color:#0369a1;"><?= htmlspecialchars($wc['course_code']) ?></span>
                        <span style="font-size:11px;color:#0369a1;"><?= $wc['credit_hours'] ?> cr · <?= htmlspecialchars($wc['dept_id']) ?></span>
                        <?php if ($wishlist_open): ?>
                        <form method="POST" style="display:inline;margin:0;">
                            <input type="hidden" name="wishlist_code" value="<?= htmlspecialchars($wc['course_code']) ?>">
                            <button type="submit" name="wishlist_remove"
                                    style="background:none;border:none;cursor:pointer;color:#c0392b;font-size:14px;padding:0;line-height:1;"
                                    title="Remove">✕</button>
                        </form>
                        <?php endif; ?>
                    </div>
                    <?php endforeach; ?>
                </div>
                <?php else: ?>
                <p class="text-muted" style="margin-bottom:20px;">Your wishlist is empty. Add courses below.</p>
                <?php endif; ?>

                <!-- Add to Wishlist grid -->
                <?php if ($wishlist_open): ?>
                <h3 style="font-size:14px;font-weight:700;margin-bottom:12px;color:var(--text-muted);text-transform:uppercase;letter-spacing:.5px;">Add Courses to Wishlist</h3>
                <div style="display:grid;grid-template-columns:repeat(auto-fill,minmax(180px,1fr));gap:10px;max-height:320px;overflow-y:auto;">
                    <?php foreach ($avail_wishlist_courses as $ac): ?>
                    <div style="border:1px solid var(--border);border-radius:8px;padding:10px 12px;background:#fff;display:flex;align-items:center;justify-content:space-between;gap:8px;">
                        <div>
                            <div class="fw-bold" style="font-size:13px;"><?= htmlspecialchars($ac['course_code']) ?></div>
                            <div style="font-size:11px;color:var(--text-muted);"><?= $ac['credit_hours'] ?> cr · <?= htmlspecialchars($ac['dept_id']) ?></div>
                        </div>
                        <form method="POST" style="display:inline;margin:0;">
                            <input type="hidden" name="wishlist_course" value="<?= htmlspecialchars($ac['course_code']) ?>">
                            <button type="submit" name="wishlist_add" class="btn btn-sm"
                                    style="background:#0369a1;color:#fff;padding:4px 10px;font-size:11px;"
                                    title="Add to Wishlist">+ Add</button>
                        </form>
                    </div>
                    <?php endforeach; ?>
                </div>
                <?php endif; ?>

            </div>
        </div>

    </div><!-- /.page-content -->
</div><!-- /.main -->
</div><!-- /.layout -->
</body>
</html>
