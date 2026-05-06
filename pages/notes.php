<?php
require_once '../includes/auth.php';
requireLogin();
require_once '../includes/db.php';
$sid = getStudentId();
$msg = '';

$upload_dir = '../uploads/';
if (!is_dir($upload_dir)) {
    mkdir($upload_dir, 0777, true);
}

// Handle Note Upload
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['upload'])) {
    $title = trim($_POST['title']);
    $date  = date('Y-m-d');
    
    if ($title && isset($_FILES['note_file']) && $_FILES['note_file']['error'] === UPLOAD_ERR_OK) {
        $file_tmp = $_FILES['note_file']['tmp_name'];
        $file_name_orig = $_FILES['note_file']['name'];
        $ext = strtolower(pathinfo($file_name_orig, PATHINFO_EXTENSION));
        
        $allowed_exts = ['pdf', 'jpg', 'jpeg', 'png'];
        if (in_array($ext, $allowed_exts)) {
            $file_type = ($ext === 'pdf') ? 'PDF' : 'Image';
            
            // Sanitize filename and prepend timestamp
            $safe_title = preg_replace('/[^A-Za-z0-9\-]/', '_', $title);
            $new_file_name = time() . '_' . $safe_title . '.' . $ext;
            $destination = $upload_dir . $new_file_name;
            
            if (move_uploaded_file($file_tmp, $destination)) {
                $ins = $conn->prepare("INSERT IGNORE INTO Note (Title, Rating, File_Type, Download, Upload_Date, Student_id, File_Path) VALUES (?,0,?,0,?,?,?)");
                $ins->bind_param('sssss', $title, $file_type, $date, $sid, $destination);
                
                if ($ins->execute() && $conn->affected_rows > 0) {
                    // Automated Reward System: Award 10 points for successful upload
                    $rp = $conn->prepare("INSERT INTO Reward_Points (Rank, Points_Awarded, Student_id) VALUES (0, 10, ?)");
                    $rp->bind_param('s', $sid);
                    $rp->execute();
                    
                    $msg = ['type' => 'success', 'text' => '✅ Note uploaded! +10 Reward Points added to your profile.'];
                } else {
                    $msg = ['type' => 'danger', 'text' => '❌ Failed to upload note. Title may already exist.'];
                }
            } else {
                $msg = ['type' => 'danger', 'text' => '❌ Failed to save the uploaded file.'];
            }
        } else {
            $msg = ['type' => 'danger', 'text' => '❌ Invalid file type. Only PDF, JPG, and PNG are allowed.'];
        }
    } else {
        $msg = ['type' => 'danger', 'text' => '❌ File upload error or missing title.'];
    }
}

// Download handler
if (isset($_GET['download'])) {
    $t = $_GET['download']; $u = $_GET['uploader'];
    $upd = $conn->prepare("UPDATE Note SET Download=Download+1 WHERE Title=? AND Student_id=?");
    $upd->bind_param('ss', $t, $u);
    $upd->execute();
    
    // Bonus rewards for high engagement
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

// Fetch user stats for the dashboard
$pts_q = $conn->prepare("SELECT SUM(Points_Awarded) as total FROM Reward_Points WHERE Student_id = ?");
$pts_q->bind_param('s', $sid);
$pts_q->execute();
$user_points = $pts_q->get_result()->fetch_assoc()['total'] ?? 0;

$notes_count_q = $conn->prepare("SELECT COUNT(*) as cnt FROM Note WHERE Student_id = ?");
$notes_count_q->bind_param('s', $sid);
$notes_count_q->execute();
$user_notes_count = $notes_count_q->get_result()->fetch_assoc()['cnt'] ?? 0;

$total_res_q = $conn->query("SELECT COUNT(*) as cnt FROM Note");
$total_resources = $total_res_q->fetch_assoc()['cnt'] ?? 0;

$notes_q = $conn->query("SELECT n.*, s.Fname, s.Lname FROM Note n JOIN Student s ON n.Student_id=s.Student_id ORDER BY n.Rating DESC, n.Download DESC");

$valid_notes = [];
while($n = $notes_q->fetch_assoc()) {
    if (!empty($n['File_Path']) && file_exists($n['File_Path'])) {
        $valid_notes[] = $n;
    }
}
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

        <!-- User Contribution Stats -->
        <div class="stats-grid mb-6">
            <div class="stat-card">
                <div class="stat-icon gold">🏆</div>
                <div class="stat-info"><div class="value"><?= $user_points ?></div><div class="label">Your Total Points</div></div>
            </div>
            <div class="stat-card">
                <div class="stat-icon blue">📚</div>
                <div class="stat-info"><div class="value"><?= $user_notes_count ?></div><div class="label">Notes Contributed</div></div>
            </div>
            <div class="stat-card">
                <div class="stat-icon green">✨</div>
                <div class="stat-info"><div class="value"><?= $total_resources ?></div><div class="label">Total Resources</div></div>
            </div>
        </div>

        <!-- Upload form -->
        <div class="card mb-6">
            <div class="card-header"><h2>📤 Upload a Note</h2><p>Share your notes and earn reward points!</p></div>
            <div class="card-body">
                <form method="POST" enctype="multipart/form-data" style="display:flex; gap:12px; flex-wrap:wrap; align-items:flex-end;">
                    <div class="form-group" style="flex:2; margin-bottom:0;">
                        <label>Note Title</label>
                        <input type="text" name="title" placeholder="e.g. CSE370 ER Diagram Notes" required>
                    </div>
                    <div class="form-group" style="flex:1; margin-bottom:0;">
                        <label>Upload File (PDF/Image)</label>
                        <input type="file" name="note_file" accept=".pdf, .jpg, .jpeg, .png" required style="padding: 10px; background: var(--bg-color); border: 1px solid var(--border-color); border-radius: 8px; width: 100%;">
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
        <?php if (count($valid_notes) > 0): ?>
        <div class="note-grid">
        <?php foreach($valid_notes as $n): 
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
            <?php if (!empty($n['File_Path'])): ?>
                <a href="<?= htmlspecialchars($n['File_Path']) ?>" target="_blank" class="btn btn-secondary btn-sm" style="margin-top:4px;">📥 View File</a>
            <?php else: ?>
                <a href="?download=<?= urlencode($n['Title']) ?>&uploader=<?= $n['Student_id'] ?>" class="btn btn-secondary btn-sm" style="margin-top:4px;">📥 Download</a>
            <?php endif; ?>
        </div>
        <?php endforeach; ?>
        </div>
        <?php else: ?>
        <div class="empty-state">
            <div class="empty-icon">📂</div>
            <p>No notes available yet. Be the first to upload one!</p>
        </div>
        <?php endif; ?>

    </div>
</div>
</div>
</body>
</html>

