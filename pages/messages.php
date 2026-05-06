<?php
require_once '../includes/auth.php';
requireLogin();
require_once '../includes/db.php';
$sid = getStudentId();

$msg_status = null;

if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['send_msg'])) {
    $receiver_id = trim($_POST['receiver_id']);
    $message_text = trim($_POST['message_text']);
    
    if (empty($receiver_id) || empty($message_text)) {
        $msg_status = ['type' => 'danger', 'text' => '❌ Please fill in all fields.'];
    } else {
        // Validate receiver
        $chk = $conn->prepare("SELECT Student_id FROM Student WHERE Student_id = ?");
        $chk->bind_param('s', $receiver_id);
        $chk->execute();
        if ($chk->get_result()->num_rows > 0) {
            $ins = $conn->prepare("INSERT INTO Messages (sender_id, receiver_id, message_text) VALUES (?, ?, ?)");
            $ins->bind_param('sss', $sid, $receiver_id, $message_text);
            if ($ins->execute()) {
                $msg_status = ['type' => 'success', 'text' => '✅ Message sent successfully!'];
            } else {
                $msg_status = ['type' => 'danger', 'text' => '❌ Failed to send message.'];
            }
        } else {
            $msg_status = ['type' => 'danger', 'text' => '❌ Receiver ID not found in the system.'];
        }
    }
}

// Mark messages as read when viewing the inbox
$upd = $conn->prepare("UPDATE Messages SET is_read=TRUE WHERE receiver_id=?");
$upd->bind_param('s', $sid);
$upd->execute();

// Fetch inbox messages
$inbox_q = $conn->prepare("
    SELECT m.*, s.Fname, s.Lname 
    FROM Messages m 
    JOIN Student s ON m.sender_id = s.Student_id 
    WHERE m.receiver_id = ? 
    ORDER BY m.sent_at DESC
");
$inbox_q->bind_param('s', $sid);
$inbox_q->execute();
$inbox = $inbox_q->get_result();
?>
<!DOCTYPE html>
<html lang="en">
<head>
<meta charset="UTF-8">
<meta name="viewport" content="width=device-width, initial-scale=1.0">
<title>Messages – BracU Portal</title>
<link rel="stylesheet" href="../assets/css/style.css">
</head>
<body>
<div class="layout">
<?php include '../includes/sidebar.php'; ?>
<div class="main">
    <div class="topbar">
        <div><h1>💬 Messages</h1><p>Communicate with your peers</p></div>
    </div>
    <div class="page-content">
        <?php if ($msg_status): ?>
            <div class="alert alert-<?= $msg_status['type'] ?>" style="margin-bottom:20px;"><?= $msg_status['text'] ?></div>
        <?php endif; ?>

        <div class="grid-2">
            <!-- Inbox -->
            <div class="card">
                <div class="card-header">
                    <h2>📥 Inbox</h2>
                </div>
                <div class="card-body">
                    <?php if ($inbox->num_rows > 0): ?>
                        <div style="display:flex; flex-direction:column; gap:15px;">
                        <?php while($m = $inbox->fetch_assoc()): ?>
                            <div style="background:var(--bg-color); border:1px solid var(--border-color); border-radius:8px; padding:15px;">
                                <div style="display:flex; justify-content:space-between; margin-bottom:8px;">
                                    <strong style="color:var(--primary);"><?= htmlspecialchars($m['Fname'].' '.$m['Lname']) ?> (<?= htmlspecialchars($m['sender_id']) ?>)</strong>
                                    <span style="font-size:12px; color:var(--text-muted);"><?= date('d M Y, h:i A', strtotime($m['sent_at'])) ?></span>
                                </div>
                                <div style="font-size:14px; line-height:1.5;">
                                    <?= nl2br(htmlspecialchars($m['message_text'])) ?>
                                </div>
                            </div>
                        <?php endwhile; ?>
                        </div>
                    <?php else: ?>
                        <div class="empty-state"><div class="empty-icon">📭</div><p>Your inbox is empty.</p></div>
                    <?php endif; ?>
                </div>
            </div>

            <!-- Send Message -->
            <div class="card" style="height:fit-content;">
                <div class="card-header">
                    <h2>📤 Send Message</h2>
                </div>
                <div class="card-body">
                    <form method="POST" action="">
                        <div class="form-group">
                            <label>Receiver Student ID</label>
                            <input type="text" name="receiver_id" class="form-control" placeholder="e.g. 21201001" required>
                        </div>
                        <div class="form-group" style="margin-top:15px;">
                            <label>Message</label>
                            <textarea name="message_text" class="form-control" rows="5" placeholder="Type your message here..." required></textarea>
                        </div>
                        <button type="submit" name="send_msg" class="btn btn-primary" style="margin-top:15px;">Send Message</button>
                    </form>
                </div>
            </div>
        </div>
    </div>
</div>
</div>
</body>
</html>
