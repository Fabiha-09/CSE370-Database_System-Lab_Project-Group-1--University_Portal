<?php
session_start();
if (isset($_SESSION['student_id'])) {
    header('Location: pages/dashboard.php');
    exit;
}

require_once 'includes/db.php';
$error = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['login'])) {
    $sid = trim($_POST['student_id']);
    $pw  = trim($_POST['password']);

    if (empty($sid) || empty($pw)) {
        $error = 'Please enter your Student ID and password.';
    } else {
        $stmt = $conn->prepare("SELECT Student_id, Fname, Lname, Email FROM Student WHERE Student_id = ?");
        $stmt->bind_param('s', $sid);
        $stmt->execute();
        $res = $stmt->get_result();
        if ($row = $res->fetch_assoc()) {
            // Demo: password = student_id (change to password_verify() in production)
            if ($pw === $sid || password_verify($pw, $pw)) {
                $_SESSION['student_id']   = $row['Student_id'];
                $_SESSION['student_name'] = $row['Fname'] . ' ' . $row['Lname'];
                $_SESSION['student_email']= $row['Email'];
                header('Location: pages/dashboard.php');
                exit;
            } else {
                $error = 'Invalid password. (Hint: use your Student ID as password for demo)';
            }
        } else {
            $error = 'Student ID not found. Please check your credentials.';
        }
    }
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
<meta charset="UTF-8">
<meta name="viewport" content="width=device-width, initial-scale=1.0">
<title>BracU Portal – Login</title>
<link rel="stylesheet" href="assets/css/style.css">
</head>
<body>
<div class="auth-wrapper">
    <div class="auth-card">
        <div class="auth-logo">
            <div class="logo-circle"><span>BU</span></div>
            <h1>BracU Central Portal</h1>
            <p>Inspiring Excellence</p>
        </div>

        <h2>Sign in to your account</h2>

        <?php if ($error): ?>
            <div class="alert alert-danger"><?= htmlspecialchars($error) ?></div>
        <?php endif; ?>

        <form method="POST">
            <div class="form-group">
                <label>Student ID or Email</label>
                <input type="text" name="student_id" placeholder="e.g. 24101352" required autocomplete="username">
            </div>
            <div class="form-group">
                <label>Password</label>
                <input type="password" name="password" placeholder="Enter your password" required autocomplete="current-password">
            </div>
            <div style="text-align:right; margin-bottom:16px;">
                <a href="#" style="font-size:13px; color:var(--primary-light); text-decoration:none; font-weight:600;">Forgot Password?</a>
            </div>
            <button type="submit" name="login" class="btn btn-primary">Sign In</button>
        </form>

        <div class="divider">or</div>

        <div class="auth-links">
            Don't have an account? <a href="register.php">Register here</a>
        </div>

        <div style="margin-top:20px; padding:12px; background:var(--surface2); border-radius:8px; font-size:12px; color:var(--text-muted); text-align:center;">
            <strong>Demo:</strong> Use any Student ID as both username &amp; password<br>
            e.g. <code>24101352</code> / <code>24101352</code>
        </div>
    </div>
</div>
</body>
</html>
