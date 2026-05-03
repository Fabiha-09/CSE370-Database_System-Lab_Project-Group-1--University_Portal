<?php
require_once '../includes/auth.php';
requireLogin();
require_once '../includes/db.php';
$sid = getStudentId();
$msg = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['upload'])) {
    $title     = trim($_POST['title']);
    $file_type = $_POST['file_type'];
    $date      = date('Y-m-d');
    if ($title && $file_type) {
        $ins = $conn->prepare("INSERT IGNORE INTO Note (Title, Rating, File_Type, Download, Upload_Date, Student_id) VALUES (?,0,?,0,?,?)");
        $ins->bind_param('ssss', $title, $file_type, $date, $sid);
        if ($ins->execute()) {
            // Award 10 points for upload
            $rp = $conn->prepare("INSERT INTO Reward_Points (Rank, Points_Awarded, Student_id) VALUES (0,10,?)");
            $rp->bind_param('s', $sid);
            $rp->execute();
            $msg = ['type'=>'success', 'text'=>'✅ Note uploaded! +10 Reward Points earned.'];
        }
    }
}

// Download handler
if (isset($_GET['download'])) {
    $t = $_GET['download']; $u = $_GET['uploader'];
    $upd = $conn->prepare("UPDATE Note SET Download=Download+1 WHERE Title=? AND Student_id=?");
    $upd->bind_param('ss', $t, $u);
    $upd->execute();
    // Check if downloads > 50, award bonus
    $chk = $conn->prepare("SELECT Download FROM Note WHERE Title=? AND Student_id=?");
    $chk->bind_param('ss', $t, $u);
    $chk->execute();
    $dlcount = $chk->get_result()->fetch_assoc()['Download'];
    if ($dlcount == 51) {
        $rp = $conn->prepare("INSERT INTO Reward_Points (Rank, Points_Awarded, Student_id) VALUES (0,20,?)");
        $rp->bind_param('s', $u);
        $rp->execute();
    }
    $msg = ['type'=>'success', 'text'=>"📥 Downloading: ".htmlspecialchars($t)];
}

$notes_q = $conn->query("SELECT n.*, s.Fname, s.Lname FROM Note n JOIN Student s ON n.Student_id=s.Student_id ORDER BY n.Rating DESC, n.Download DESC");
?>
<!DOCTYPE html>
<html lang="en">
<head>
<meta charset="UTF-8">
<meta name="viewport" content="width=device-width, initial-scale=1.0">
<title>Notes Library – BracU Portal</title>
<link rel="stylesheet" href="../assets/css/style.css">
</head>
<body>
<div class="layout">
<?php include '../includes/sidebar.php'; ?>
<div class="main">
    <div class="topbar">
        <div><h1>📚 Student Notes Library</h1><p>Upload and download peer academic resources</p></div>
        <div class="topbar-right">
            <a href="reward_points.php" class="badge badge-gold" style="text-decoration:none;">🏆 Leaderboard</a>
        </div>
    </div>
    <div class="page-content">

        <?php if ($msg): ?>
        <div class="alert alert-<?= $msg['type'] ?> mb-4"><?= $msg['text'] ?></div>
        <?php endif; ?>

        <!-- Upload form -->
        <div class="card mb-6">
            <div class="card-header"><h2>📤 Upload a Note</h2><p>Share your notes and earn reward points!</p></div>
            <div class="card-body">
                <form method="POST" style="display:flex; gap:12px; flex-wrap:wrap; align-items:flex-end;">
                    <div class="form-group" style="flex:2; margin-bottom:0;">
                        <label>Note Title</label>
                        <input type="text" name="title" placeholder="e.g. CSE370 ER Diagram Notes" required>
                    </div>
                    <div class="form-group" style="flex:1; margin-bottom:0;">
                        <label>File Type</label>
                        <select name="file_type" required>
                            <option value="PDF">PDF</option>
                            <option value="Image">Image</option>
                            <option value="DOCX">DOCX</option>
                        </select>
                    </div>
                    <div style="margin-bottom:0;">
                        <button type="submit" name="upload" class="btn btn-accent">📤 Upload (+10 pts)</button>
                    </div>
                </form>
                <div class="text-muted" style="margin-top:10px; font-size:12px;">
                    🏆 <strong>Earn Points:</strong> +10 for upload · +5 if rated ≥4.5★ · +20 if 50+ downloads
                </div>
            </div>
        </div>

        <!-- Notes grid -->
        <div class="note-grid">
        <?php while($n = $notes_q->fetch_assoc()): 
            $stars = round($n['Rating']);
            $star_str = str_repeat('★', $stars) . str_repeat('☆', 5-$stars);
        ?>
        <div class="note-card">
            <div style="display:flex; justify-content:space-between; align-items:start;">
                <div class="note-title"><?= htmlspecialchars($n['Title']) ?></div>
                <span class="badge badge-primary"><?= $n['File_Type'] ?></span>
            </div>
            <div class="stars"><?= $star_str ?> <?= number_format($n['Rating'],1) ?></div>
            <div class="note-meta">
                <span>👤 <?= htmlspecialchars($n['Fname'].' '.$n['Lname']) ?></span>
                <span>📥 <?= $n['Download'] ?> downloads</span>
            </div>
            <div class="note-meta">
                <span>📅 <?= $n['Upload_Date'] ?></span>
                <?php if($n['Download'] >= 50): ?><span class="badge badge-gold">🔥 Popular</span><?php endif; ?>
                <?php if($n['Rating'] >= 4.5): ?><span class="badge badge-success">⭐ Top Rated</span><?php endif; ?>
            </div>
            <a href="?download=<?= urlencode($n['Title']) ?>&uploader=<?= $n['Student_id'] ?>" class="btn btn-secondary btn-sm" style="margin-top:4px;">📥 Download</a>
        </div>
        <?php endwhile; ?>
        </div>

    </div>
</div>
</div>
</body>
</html>
