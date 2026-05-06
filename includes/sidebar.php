<?php
if (session_status() === PHP_SESSION_NONE) session_start();
$sid   = $_SESSION['student_id']   ?? '';
$sname = $_SESSION['student_name'] ?? 'Student';
$initial = strtoupper(substr($sname, 0, 1));
$current = basename($_SERVER['PHP_SELF'], '.php');
function navActive($page, $current) { return $page === $current ? 'active' : ''; }

$unread_msgs = 0;
if (isset($conn) && $sid) {
    $unread_q = $conn->prepare("SELECT COUNT(*) as cnt FROM Messages WHERE receiver_id=? AND is_read=FALSE");
    if ($unread_q) {
        $unread_q->bind_param('s', $sid);
        $unread_q->execute();
        $unread_msgs = $unread_q->get_result()->fetch_assoc()['cnt'] ?? 0;
    }
}
?>
<aside class="sidebar">
    <div class="sidebar-logo">
        <div class="s-icon">BU</div>
        <div>
            <h2>BracU Portal</h2>
            <p>Inspiring Excellence</p>
        </div>
    </div>
    <nav class="sidebar-nav">
        <div class="nav-section">Main</div>
        <a href="dashboard.php" class="nav-item <?= navActive('dashboard',$current) ?>">
            <span class="nav-icon">🏠</span> Dashboard
        </a>
        <a href="profile.php" class="nav-item <?= navActive('profile',$current) ?>">
            <span class="nav-icon">👤</span> My Profile
        </a>

        <div class="nav-section">Academics</div>
        <a href="enrollment.php" class="nav-item <?= navActive('enrollment',$current) ?>">
            <span class="nav-icon">📋</span> Course Enrollment
        </a>
        <a href="grades.php" class="nav-item <?= navActive('grades',$current) ?>">
            <span class="nav-icon">📊</span> Grade Sheet
        </a>
        <a href="routine.php" class="nav-item <?= navActive('routine',$current) ?>">
            <span class="nav-icon">📅</span> Academic Routine
        </a>
        <a href="exam_schedule.php" class="nav-item <?= navActive('exam_schedule',$current) ?>">
            <span class="nav-icon">📝</span> Exam Schedule
        </a>
        <a href="smart_recommendations.php" class="nav-item <?= navActive('smart_recommendations',$current) ?>">
            <span class="nav-icon">🤖</span> Smart Recommendations
        </a>
        <a href="analytics.php" class="nav-item <?= navActive('analytics',$current) ?>">
            <span class="nav-icon">📈</span> Analytics Dashboard
        </a>

        <div class="nav-section">Campus</div>
        <a href="lab_availability.php" class="nav-item <?= navActive('lab_availability',$current) ?>">
            <span class="nav-icon">🖥️</span> Lab Availability
        </a>
        <a href="complaint.php" class="nav-item <?= navActive('complaint',$current) ?>">
            <span class="nav-icon">🔔</span> Complaints
        </a>
        <a href="department_stats.php" class="nav-item <?= navActive('department_stats',$current) ?>">
            <span class="nav-icon">🏛️</span> Department Stats
        </a>

        <div class="nav-section">Community</div>
        <a href="notes.php" class="nav-item <?= navActive('notes',$current) ?>">
            <span class="nav-icon">📚</span> Notes Library
        </a>
        <a href="messages.php" class="nav-item <?= navActive('messages',$current) ?>">
            <span class="nav-icon">💬</span> Messages
            <?php if ($unread_msgs > 0): ?>
                <span class="badge" style="background:var(--danger);color:#fff;margin-left:auto;padding:2px 6px;font-size:11px;border-radius:10px;"><?= $unread_msgs ?></span>
            <?php endif; ?>
        </a>
        <a href="reward_points.php" class="nav-item <?= navActive('reward_points',$current) ?>">
            <span class="nav-icon">🏆</span> Reward Points
        </a>

        <div class="nav-section">Finance</div>
        <a href="fees.php" class="nav-item <?= navActive('fees',$current) ?>">
            <span class="nav-icon">💳</span> Semester Fees
        </a>
    </nav>
    <div class="sidebar-footer">
        <div class="user-chip">
            <div class="avatar"><?= htmlspecialchars($initial) ?></div>
            <div class="user-info">
                <strong><?= htmlspecialchars($sname) ?></strong>
                <span><?= htmlspecialchars($sid) ?></span>
            </div>
            <a href="../logout.php" title="Logout">⏻</a>
        </div>
    </div>
</aside>
