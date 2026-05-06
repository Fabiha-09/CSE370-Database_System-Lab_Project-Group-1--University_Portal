<?php
require_once '../includes/auth.php';
requireLogin();
require_once '../includes/db.php';

$analytics = $conn->query("SELECT * FROM AnalyticsDashboard ORDER BY Course_Section");
$dept_stats = $conn->query("SELECT d.dept_name, d.dept_id, COUNT(s.Student_id) as total, AVG(s.cgpa) as avg_cgpa FROM Department d LEFT JOIN Student s ON d.dept_id=s.dept_id GROUP BY d.dept_id, d.dept_name ORDER BY total DESC");
?>
<!DOCTYPE html>
<html lang="en">
<head>
<meta charset="UTF-8">
<meta name="viewport" content="width=device-width, initial-scale=1.0">
<title>Analytics Dashboard – BracU Portal</title>
<link rel="stylesheet" href="../assets/css/style.css">
</head>
<body>
<div class="layout">
<?php include '../includes/sidebar.php'; ?>
<div class="main">
    <div class="topbar">
        <div><h1>📈 Analytics Dashboard</h1><p>Course performance & class-wide statistics</p></div>
    </div>
    <div class="page-content">

        <!-- Course Performance -->
        <div class="card mb-6">
            <div class="card-header"><div><h2>📊 Course Performance Analytics</h2><p>Faculty view — class-wide grade statistics</p></div></div>
            <div class="table-wrapper">
                <table>
                    <thead><tr><th>Course / Section</th><th>Highest Grade</th><th>Lowest Grade</th><th>Class Performance</th></tr></thead>
                    <tbody>
                    <?php while($r = $analytics->fetch_assoc()): ?>
                    <tr>
                        <td class="fw-bold"><?= htmlspecialchars($r['Course_Section']) ?></td>
                        <td><span class="grade-pill"><?= $r['Highest_Grade'] ?></span></td>
                        <td><span class="grade-pill" style="background:var(--danger-bg);color:var(--danger);"><?= $r['Lowest_Grade'] ?></span></td>
                        <td style="font-size:13px; color:var(--text-muted);"><?= htmlspecialchars($r['Class_performance']) ?></td>
                    </tr>
                    <?php endwhile; ?>
                    </tbody>
                </table>
            </div>
        </div>

        <!-- Feature 03: Department Statistics -->
        <div class="card">
            <div class="card-header"><div><h2>🏛️ Department Statistics</h2><p>Real-time enrollment counts by department</p></div></div>
            <div class="table-wrapper">
                <table>
                    <thead><tr><th>Department</th><th>Code</th><th>Total Students</th><th>Avg CGPA</th><th>Distribution</th></tr></thead>
                    <tbody>
                    <?php
                    $max_s = 1;
                    $rows = [];
                    while($r = $dept_stats->fetch_assoc()) { $rows[] = $r; if($r['total']>$max_s) $max_s=$r['total']; }
                    foreach($rows as $r):
                        $pct = $max_s > 0 ? round(($r['total']/$max_s)*100) : 0;
                    ?>
                    <tr>
                        <td class="fw-bold"><?= htmlspecialchars($r['dept_name']) ?></td>
                        <td><span class="badge badge-primary"><?= $r['dept_id'] ?></span></td>
                        <td><?= $r['total'] ?></td>
                        <td><?= $r['avg_cgpa'] ? number_format($r['avg_cgpa'],2) : '—' ?></td>
                        <td style="width:200px;">
                            <div class="progress-bar">
                                <div class="fill" style="width:<?= $pct ?>%"></div>
                            </div>
                        </td>
                    </tr>
                    <?php endforeach; ?>
                    </tbody>
                </table>
            </div>
        </div>

    </div>
</div>
</div>
</body>
</html>
