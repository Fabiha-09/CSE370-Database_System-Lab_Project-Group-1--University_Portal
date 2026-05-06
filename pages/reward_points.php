<?php
require_once '../includes/auth.php';
requireLogin();
require_once '../includes/db.php';
$sid = getStudentId();

$leaderboard = $conn->query("SELECT s.Student_id, s.Fname, s.Lname, s.dept_id, SUM(r.Points_Awarded) as total_points, (SELECT COUNT(*) FROM Note n WHERE n.Student_id=s.Student_id) as contributions FROM Reward_Points r JOIN Student s ON r.Student_id=s.Student_id GROUP BY s.Student_id, s.Fname, s.Lname, s.dept_id ORDER BY total_points DESC LIMIT 20");

$my_pts = $conn->prepare("SELECT SUM(Points_Awarded) as total FROM Reward_Points WHERE Student_id=?");
$my_pts->bind_param('s', $sid);
$my_pts->execute();
$my_total = $my_pts->get_result()->fetch_assoc()['total'] ?? 0;
?>
<!DOCTYPE html>
<html lang="en">
<head>
<meta charset="UTF-8">
<meta name="viewport" content="width=device-width, initial-scale=1.0">
<title>Reward Points – BracU Portal</title>
<link rel="stylesheet" href="../assets/css/style.css">
</head>
<body>
<div class="layout">
<?php include '../includes/sidebar.php'; ?>
<div class="main">
    <div class="topbar">
        <div><h1>🏆 Reward Points Leaderboard</h1><p>Top contributors to the Notes Library</p></div>
        <div class="topbar-right">
            <span class="badge badge-gold">⭐ Your Points: <?= $my_total ?></span>
        </div>
    </div>
    <div class="page-content">

        <div class="card mb-6" style="background:linear-gradient(135deg,#1a3a6b,#2651a0); color:#fff; border:none;">
            <div class="card-body" style="text-align:center;">
                <div style="font-size:48px; margin-bottom:8px;">🏆</div>
                <div style="font-family:'Playfair Display',serif; font-size:22px; margin-bottom:4px;">Your Score</div>
                <div style="font-size:48px; font-weight:700;"><?= $my_total ?></div>
                <div style="opacity:0.7; font-size:14px;">Reward Points</div>
                <div style="margin-top:16px; font-size:13px; opacity:0.8;">
                    Upload notes · Get ratings · Earn downloads = More points!
                </div>
            </div>
        </div>

        <div class="card">
            <div class="card-header"><h2>🏅 Top Contributors</h2></div>
            <div class="table-wrapper">
                <table>
                    <thead><tr><th>Rank</th><th>Student</th><th>Department</th><th>Contributions</th><th>Total Points</th><th>Achievement</th></tr></thead>
                    <tbody>
                    <?php $rank=1; while($r = $leaderboard->fetch_assoc()): 
                        $is_me = $r['Student_id'] === $sid;
                    ?>
                    <tr style="<?= $is_me ? 'background:#fff8e6;' : '' ?>">
                        <td>
                            <?php if ($rank <= 3): ?>
                            <span class="rank-badge rank-<?= $rank ?>"><?= $rank ?></span>
                            <?php else: ?>
                            <span class="rank-badge rank-n"><?= $rank ?></span>
                            <?php endif; ?>
                        </td>
                        <td>
                            <div class="fw-bold"><?= htmlspecialchars($r['Fname'].' '.$r['Lname']) ?></div>
                            <?php if ($is_me): ?><div style="font-size:11px; color:var(--accent);">← You</div><?php endif; ?>
                        </td>
                        <td><?= $r['dept_id'] ?></td>
                        <td style="font-weight:600;"><?= $r['contributions'] ?></td>
                        <td style="font-size:18px; font-weight:700; color:var(--primary);"><?= $r['total_points'] ?></td>
                        <td>
                            <?php if ($rank === 1): ?><span class="badge badge-gold">🥇 Gold</span>
                            <?php elseif ($rank === 2): ?><span class="badge" style="background:#e8e8e8;color:#555;">🥈 Silver</span>
                            <?php elseif ($rank === 3): ?><span class="badge" style="background:#f5e6d0;color:#8b5e2a;">🥉 Bronze</span>
                            <?php elseif ($r['total_points'] >= 50): ?><span class="badge badge-success">⭐ Star</span>
                            <?php else: ?><span class="badge badge-primary">📚 Contributor</span>
                            <?php endif; ?>
                        </td>
                    </tr>
                    <?php $rank++; endwhile; ?>
                    </tbody>
                </table>
            </div>
        </div>

        <div class="card" style="margin-top:20px;">
            <div class="card-header"><h2>📋 How to Earn Points</h2></div>
            <div class="card-body">
                <table>
                    <thead><tr><th>Action</th><th>Points</th></tr></thead>
                    <tbody>
                        <tr><td>📤 Upload a note to the library</td><td><span class="badge badge-success">+10 pts</span></td></tr>
                        <tr><td>⭐ Note receives average rating ≥ 4.5 stars</td><td><span class="badge badge-success">+5 pts</span></td></tr>
                        <tr><td>🔥 Note download count exceeds 50</td><td><span class="badge badge-success">+20 pts</span></td></tr>
                    </tbody>
                </table>
            </div>
        </div>

    </div>
</div>
</div>
</body>
</html>
