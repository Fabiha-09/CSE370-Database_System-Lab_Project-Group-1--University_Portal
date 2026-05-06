<?php
session_start();
require_once 'includes/db.php';
$error = ''; $success = '';

$allowedStudents = [
    '24101352' => ['Mohammad Faiyaz', 'Mazumder'],
    '23201493' => ['Fabiha', 'Tarannum'],
    '23201558' => ['Shuvo', 'Das'],
];

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $sid    = trim($_POST['student_id']);
    $fname  = trim($_POST['fname']);
    $lname  = trim($_POST['lname']);
    $email  = trim($_POST['email']);
    $dob    = $_POST['dob'];
    $gender = $_POST['gender'];
    $dept   = $_POST['dept_id'];
    $phone  = trim($_POST['phone']);

    if (!$sid || !$fname || !$lname || !$email || !$dept) {
        $error = 'Please fill in all required fields.';
    } elseif (!array_key_exists($sid, $allowedStudents)) {
        $error = 'Registration is open only for the following IDs: 24101352, 23201493, 23201558.';
    } elseif (strcasecmp($fname, $allowedStudents[$sid][0]) !== 0 || strcasecmp($lname, $allowedStudents[$sid][1]) !== 0) {
        $error = 'Please use the registered name for this ID: ' . $allowedStudents[$sid][0] . ' ' . $allowedStudents[$sid][1] . '.';
    } else {
        // Check duplicate
        $chk = $conn->prepare("SELECT Student_id FROM Student WHERE Student_id=? OR Email=?");
        $chk->bind_param('ss', $sid, $email);
        $chk->execute();
        if ($chk->get_result()->num_rows > 0) {
            $error = 'Student ID or Email already exists.';
        } else {
            $ins = $conn->prepare("INSERT INTO Student (Student_id,DOB,Email,Fname,Lname,Gender,dept_id,Student_type) VALUES (?,?,?,?,?,?,?,'Undergrad')");
            $ins->bind_param('sssssss', $sid, $dob, $email, $fname, $lname, $gender, $dept);
            if ($ins->execute()) {
                if ($phone) {
                    $ip = $conn->prepare("INSERT INTO Student_Phone VALUES (?,?)");
                    $ip->bind_param('ss', $phone, $sid);
                    $ip->execute();
                }
                $success = 'Registration successful! You can now <a href="index.php">login</a>.';
            } else {
                $error = 'Registration failed: ' . $conn->error;
            }
        }
    }
}

$depts = $conn->query("SELECT dept_id, dept_name FROM Department ORDER BY dept_name");
?>
<!DOCTYPE html>
<html lang="en">
<head>
<meta charset="UTF-8">
<meta name="viewport" content="width=device-width, initial-scale=1.0">
<title>BracU Portal – Register</title>
<link rel="stylesheet" href="assets/css/style.css">
</head>
<body>
<div class="auth-wrapper">
    <div class="auth-card" style="max-width:520px;">
        <div class="auth-logo">
            <div class="logo-circle"><span>BU</span></div>
            <h1>BracU Central Portal</h1>
            <p>Create your account</p>
        </div>

        <h2>Student Registration</h2>
        <div class="alert alert-info">Registration is currently allowed only for these IDs: 24101352, 23201493, 23201558.</div>

        <?php if ($error): ?><div class="alert alert-danger"><?= $error ?></div><?php endif; ?>
        <?php if ($success): ?><div class="alert alert-success"><?= $success ?></div><?php endif; ?>

        <form method="POST">
            <div class="grid-2">
                <div class="form-group">
                    <label>First Name *</label>
                    <input type="text" name="fname" required>
                </div>
                <div class="form-group">
                    <label>Last Name *</label>
                    <input type="text" name="lname" required>
                </div>
            </div>
            <div class="form-group">
                <label>Student ID *</label>
                <input type="text" name="student_id" placeholder="e.g. 24101352" required>
            </div>
            <div class="form-group">
                <label>University Email *</label>
                <input type="email" name="email" placeholder="name@g.bracu.ac.bd" required>
            </div>
            <div class="grid-2">
                <div class="form-group">
                    <label>Date of Birth</label>
                    <input type="date" name="dob">
                </div>
                <div class="form-group">
                    <label>Gender</label>
                    <select name="gender">
                        <option value="">Select</option>
                        <option>Male</option>
                        <option>Female</option>
                        <option>Other</option>
                    </select>
                </div>
            </div>
            <div class="form-group">
                <label>Department *</label>
                <select name="dept_id" required>
                    <option value="">Select Department</option>
                    <?php while($d = $depts->fetch_assoc()): ?>
                    <option value="<?= $d['dept_id'] ?>"><?= htmlspecialchars($d['dept_name']) ?></option>
                    <?php endwhile; ?>
                </select>
            </div>
            <div class="form-group">
                <label>Phone Number</label>
                <input type="text" name="phone" placeholder="01XXXXXXXXX">
            </div>
            <button type="submit" class="btn btn-primary">Create Account</button>
        </form>

        <div class="auth-links">
            Already have an account? <a href="index.php">Sign In</a>
        </div>
    </div>
</div>
</body>
</html>
