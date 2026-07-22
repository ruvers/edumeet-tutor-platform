<?php
// Process lesson request status updates (accept/reject)
session_start();
include 'db.php';

// Check if user is logged in
if (!isset($_SESSION['user_id'])) {
    header("Location: login.php");
    exit;
}

$current_user_id = $_SESSION['user_id'];

// Check if request ID and status are provided
if (isset($_GET['id']) && isset($_GET['status'])) {
    $request_id = $_GET['id'];
    $status = $_GET['status'];

    // Allow only accepted or rejected statuses
    $allowed_statuses = ['accepted', 'rejected'];

    if (in_array($status, $allowed_statuses)) {
        
        // Update status only if the request belongs to the current tutor
        $sql = "UPDATE requests SET status = ? WHERE requestID = ? AND tutorID = ?";
        $stmt = $pdo->prepare($sql);
        $stmt->execute([$status, $request_id, $current_user_id]);
    }
}

// Redirect back to dashboard
header("Location: dashboard.php");
exit;
?>