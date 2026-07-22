<?php
// Handle user report submissions
session_start();
include 'db.php';

if ($_SERVER['REQUEST_METHOD'] == 'POST' && isset($_SESSION['user_id'])) {
    $reporterID = $_SESSION['user_id'];
    $requestID = $_POST['request_id'];
    $reason = trim($_POST['reason']);
    
    // Find the reported user based on the request ID
    // If the reporter is the student -> they are reporting the tutor.
    // If the reporter is the tutor -> they are reporting the student.
    
    $stmt = $pdo->prepare("SELECT studentID, tutorID FROM requests WHERE requestID = ?");
    $stmt->execute([$requestID]);
    $req = $stmt->fetch(PDO::FETCH_ASSOC);

    if ($req) {
        // Determine the ID of the user being reported
        $reportedID = ($reporterID == $req['studentID']) ? $req['tutorID'] : $req['studentID'];

        if (!empty($reason)) {
            $sql = "INSERT INTO reports (reporterID, reportedID, reason) VALUES (?, ?, ?)";
            $stmtInsert = $pdo->prepare($sql);
            $stmtInsert->execute([$reporterID, $reportedID, $reason]);
            
            // Redirect back to dashboard with success message
            header("Location: dashboard.php?msg=reported");
            exit;
        }
    }
}

// Redirect with error if something failed
header("Location: dashboard.php?err=1");
?>