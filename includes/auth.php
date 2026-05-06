<?php
function requireLogin() {
    if (session_status() === PHP_SESSION_NONE) session_start();
    if (!isset($_SESSION['student_id'])) {
        header('Location: ../index.php');
        exit;
    }
}

function getStudentId() {
    return $_SESSION['student_id'] ?? null;
}

function getStudentName() {
    return $_SESSION['student_name'] ?? 'Student';
}
?>
