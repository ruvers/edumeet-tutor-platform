<?php
// Handles AJAX requests to rate and review a lesson
session_start();
include 'db.php';

// Set response header to JSON
header('Content-Type: application/json');

// Check request method and session
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_SESSION['user_id'])) {
    
    $request_id = $_POST['request_id'];
    $rating = (int)$_POST['rating'];
    
    // Get review text and sanitize it to prevent XSS
    $review = isset($_POST['review']) ? htmlspecialchars(trim($_POST['review'])) : ''; 
    $student_id = $_SESSION['user_id'];

    // Verify that this request belongs to the currently logged-in student
    $checkSql = "SELECT requestID FROM requests WHERE requestID = ? AND studentID = ?";
    $stmt = $pdo->prepare($checkSql);
    $stmt->execute([$request_id, $student_id]);

    if ($stmt->rowCount() > 0) {
        // Update the record with the rating and review
        $updateSql = "UPDATE requests SET rating = ?, review = ? WHERE requestID = ?";
        $updateStmt = $pdo->prepare($updateSql);
        
        if ($updateStmt->execute([$rating, $review, $request_id])) {
            echo json_encode(['success' => true, 'message' => 'Feedback saved!']);
        } else {
            echo json_encode(['success' => false, 'message' => 'Database error.']);
        }
    } else {
        echo json_encode(['success' => false, 'message' => 'Unauthorized request.']);
    }
} else {
    echo json_encode(['success' => false, 'message' => 'Invalid request.']);
}
?>