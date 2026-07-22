<?php
// Admin student details page
session_start();
include 'db.php';

// Ensure user has admin privileges
if (!isset($_SESSION['user_id']) || $_SESSION['role'] != 'admin') {
    die("Access Denied!");
}

// Check if a student ID is provided
if (!isset($_GET['studentID'])) {
    die("Error: No student selected.");
}

$student_id = $_GET['studentID'];

// Handle request deletion logic
if (isset($_GET['action']) && $_GET['action'] == 'delete_request' && isset($_GET['req_id'])) {
    $req_id = $_GET['req_id'];
    $delStmt = $pdo->prepare("DELETE FROM requests WHERE requestID = ?");
    $delStmt->execute([$req_id]);
    
    // Reload page after deletion
    header("Location: admin_student_details.php?studentID=$student_id&msg=deleted");
    exit;
}

// Fetch student profile information
$stmt = $pdo->prepare("SELECT * FROM users WHERE userID = ?");
$stmt->execute([$student_id]);
$student = $stmt->fetch(PDO::FETCH_ASSOC);

// Get usage statistics for the student
$stats = $pdo->prepare("SELECT COUNT(*) as total_requests, 
                        (SELECT COUNT(*) FROM requests WHERE studentID = ? AND status='accepted') as accepted_requests 
                        FROM requests WHERE studentID = ?");
$stats->execute([$student_id, $student_id]);
$statData = $stats->fetch(PDO::FETCH_ASSOC);

// Fetch lesson history combining request and tutor data
$historySql = "SELECT r.*, u.userName as tutorName, u.email as tutorEmail 
               FROM requests r 
               JOIN users u ON r.tutorID = u.userID 
               WHERE r.studentID = ? 
               ORDER BY r.lesson_time DESC";
$historyStmt = $pdo->prepare($historySql);
$historyStmt->execute([$student_id]);
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>Student Details</title>
    <link rel="stylesheet" href="style.css?v=<?php echo time(); ?>">
    <style>
        body { background: #f4f6f9; color: #333; }
        .container { width: 80%; max-width: 900px; margin: 30px auto; }
        
        .profile-header { background: #fff; padding: 20px; border: 1px solid #ddd; border-radius: 8px; margin-bottom: 20px; display: flex; justify-content: space-between; align-items: center; }
        .profile-info h2 { margin: 0; color: #333; }
        .stat-badge { background: #28a745; color: white; padding: 5px 10px; border-radius: 4px; font-weight: bold; }
        
        .review-card { background: #fff; border: 1px solid #eee; padding: 15px; margin-bottom: 10px; border-radius: 6px; }
        .review-header { display: flex; justify-content: space-between; font-size: 0.9em; color: #666; border-bottom: 1px solid #f9f9f9; padding-bottom: 5px; margin-bottom: 5px; }
        
        .btn-gray { background: #6c757d; color: white; padding: 8px 12px; text-decoration: none; border-radius: 4px; display:inline-block; }
        .btn-sm-red { background: #dc3545; color: white; padding: 4px 8px; text-decoration: none; border-radius: 4px; font-size: 0.8em; float: right; margin-top: -5px;}
        .btn-sm-red:hover { background: #c82333; }
    </style>
</head>
<body>

<div class="container">
    <a href="admin.php" class="btn-gray" style="margin-bottom:15px;">&larr; Back to Dashboard</a>

    <div class="profile-header">
        <div class="profile-info">
            <h2>🎓 Student: <?php echo htmlspecialchars($student['userName']); ?></h2>
            <p><strong>Email:</strong> <?php echo htmlspecialchars($student['email']); ?></p>
        </div>
        <div class="profile-stats">
            <p>Total Requests: <span class="stat-badge"><?php echo $statData['total_requests']; ?></span></p>
            <p>Lessons Taken: <span class="stat-badge" style="background:#17a2b8;"><?php echo $statData['accepted_requests']; ?></span></p>
        </div>
    </div>

    <h3>📚 Lesson History (Requests Made)</h3>
    
    <?php if(isset($_GET['msg']) && $_GET['msg']=='deleted'): ?>
        <p style="color: green; background: #d4edda; padding: 10px; border-radius: 5px;">Record deleted successfully!</p>
    <?php endif; ?>
    
    <?php 
    if ($historyStmt->rowCount() > 0):
        while($row = $historyStmt->fetch(PDO::FETCH_ASSOC)): 
    ?>
        <div class="review-card">
            <div class="review-header">
                <span>Tutor: <strong><?php echo htmlspecialchars($row['tutorName']); ?></strong></span>
                <span><?php echo $row['lesson_time']; ?></span>
            </div>
            
            <a href="admin_student_details.php?studentID=<?php echo $student_id; ?>&action=delete_request&req_id=<?php echo $row['requestID']; ?>" 
               class="btn-sm-red" 
               onclick="return confirm('Are you sure you want to delete this lesson record?');">
               🗑️ Delete Record
            </a>
            
            <div>
                <strong>Subject:</strong> <?php echo htmlspecialchars($row['subject']); ?> 
                <span style="font-size:0.8em; padding:2px 6px; border-radius:3px; background:<?php echo $row['status']=='accepted'?'#d4edda': ($row['status']=='rejected'?'#f8d7da':'#fff3cd'); ?>">
                    <?php echo strtoupper($row['status']); ?>
                </span>
            </div>
            
            <?php if($row['status'] == 'accepted'): ?>
                <div style="font-size:0.85em; color:#666; margin-top:5px;">
                    Tutor Email: <?php echo $row['tutorEmail']; ?>
                </div>
            <?php endif; ?>
            
            <?php if($row['rating']): ?>
                <div style="margin-top:5px; color:#f39c12;">
                    Rating Given: <strong><?php echo $row['rating']; ?> ★</strong>
                </div>
            <?php endif; ?>

        </div>
    <?php 
        endwhile; 
    else:
        echo "<p style='color:#666;'>No lesson requests found for this student.</p>";
    endif;
    ?>

</div>

</body>
</html>