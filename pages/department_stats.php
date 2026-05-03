<?php
require_once '../includes/auth.php';
requireLogin();
require_once '../includes/db.php';

$stats = $conn->query("SELECT d.dept_id, d.dept_name, COUNT(s.Student_id) as total, AVG(s.cgpa) as avg_cgpa, SUM(CASE WHEN s.Gender='Male' THEN 1 ELSE 0 END) as male, SUM(CASE WHEN s.Gender='Female' THEN 1 ELSE 0 END) as female FROM Department d LEFT JOIN Student s ON d.dept_id=s.dept_id GROUP BY d.dept_id, d.dept_name ORDER BY total DESC");
$rows = $stats->fetch_all(MYSQLI_ASSOC);
$max_total = max(array_column($rows, 'total') ?: [1]);
?>
<!DOCTYPE html>
<html lang="en">
<head>
<meta charset="UTF-8">
<meta name="viewport" content="width=device-width, initial-scale=1.0">
<title>Department Stats – BracU Portal</title>
<link rel="stylesheet" href="../assets/css/style.css">
</head>
<body>
<div class="layout">
<?php include '../includes/sidebar.php'; ?>
<div class="main">
    <div class="topbar">
        <div><h1>🏛️ Department Statistics</h1><p>Real-time student enrollment by department</p></div>
    </div>
    <div class="page-content">

        <div class="stats-grid mb-6">
            <div class="stat-card">
                <div class="stat-icon blue">🏛️</div>
                <div class="stat-info"><div class="value"><?= count($rows) ?></div><div class="label">Departments</div></div>
            </div>
            <div class="stat-card">
                <div class="stat-icon green">👨‍🎓</div>
                <div class="stat-info"><div class="value"><?= array_sum(array_column($rows,'total')) ?></div><div class="label">Total Students</div></div>
            </div>
        </div>

        <div class="card">
            <div class="card-header"><h2>📊 Department Breakdown</h2></div>
            <div class="table-wrapper">
                <table>
                    <thead><tr><th>Department</th><th>Code</th><th>Total Students</th><th>Avg CGPA</th><th>Male</th><th>Female</th><th>Enrollment Bar</th></tr></thead>
                    <tbody>
                    <?php foreach($rows as $r): $pct = $max_total > 0 ? round(($r['total']/$max_total)*100) : 0; ?>
                    <tr>
                        <td class="fw-bold"><?= htmlspecialchars($r['dept_name']) ?></td>
                        <td><span class="badge badge-primary"><?= $r['dept_id'] ?></span></td>
                        <td style="font-size:18px; font-weight:700;"><?= $r['total'] ?></td>
                        <td><?= $r['avg_cgpa'] ? number_format($r['avg_cgpa'],2) : '—' ?></td>
                        <td>👨 <?= $r['male'] ?></td>
                        <td>👩 <?= $r['female'] ?></td>
                        <td style="width:180px;">
                            <div class="progress-bar"><div class="fill" style="width:<?= $pct ?>%"></div></div>
                            <div style="font-size:11px; color:var(--text-muted); margin-top:2px;"><?= $pct ?>%</div>
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
