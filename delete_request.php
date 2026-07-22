<?php
// Delete pending lesson request
session_start();
include 'db.php';

// Check if user is logged in and request ID is provided
if (!isset($_SESSION['user_id']) || !isset($_GET['id'])) {
    header("Location: dashboard.php");
    exit;
}

$request_id = $_GET['id'];
$student_id = $_SESSION['user_id'];

// Delete request only if it belongs to the current user and status is pending
$sql = "DELETE FROM requests WHERE requestID = ? AND studentID = ? AND status = 'pending'";
$stmt = $pdo->prepare($sql);
$stmt->execute([$request_id, $student_id]);

// Redirect back to dashboard
header("Location: dashboard.php");
exit;
?>