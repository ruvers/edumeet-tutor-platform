<?php
// view_tutor.php - Read-only profile view for Tutors
session_start();
include 'db.php';

// Security: Check if logged in
if (!isset($_SESSION['user_id']) || !isset($_GET['tutorID'])) {
    header("Location: dashboard.php");
    exit;
}

$tutor_id = $_GET['tutorID'];
$viewer_id = $_SESSION['user_id'];

// Get tutor information
$stmt = $pdo->prepare("SELECT userName, email FROM users WHERE userID = ?");
$stmt->execute([$tutor_id]);
$tutor = $stmt->fetch(PDO::FETCH_ASSOC);

// Fetch stats (Total Reviews & Average Rating)
$statsSql = "SELECT COUNT(*) as total, AVG(rating) as average 
             FROM requests 
             WHERE tutorID = ? AND rating > 0 AND review IS NOT NULL AND review != ''";
$statsStmt = $pdo->prepare($statsSql);
$statsStmt->execute([$tutor_id]);
$stats = $statsStmt->fetch(PDO::FETCH_ASSOC);

$totalReviews = $stats['total'];
$averageRating = $stats['average'] ? number_format($stats['average'], 1) : "0.0";

// Fetch ALL reviews list
$allReviewsSql = "SELECT r.rating, r.review, r.lesson_time, u.userName 
                  FROM requests r 
                  JOIN users u ON r.studentID = u.userID 
                  WHERE r.tutorID = ? AND r.rating > 0 AND r.review IS NOT NULL AND r.review != ''
                  ORDER BY r.requestID DESC"; 
$allReviewsStmt = $pdo->prepare($allReviewsSql);
$allReviewsStmt->execute([$tutor_id]);
$allReviews = $allReviewsStmt->fetchAll(PDO::FETCH_ASSOC);

include 'header.php';
?>

<style>
    body {
        background-color: #f0f4f8 !important;
        background-image: none !important;
        color: #333 !important;
    }
    body::before { display: none !important; }

    .view-wrapper { 
        display: flex; justify-content: center; align-items: center; 
        min-height: 80vh; padding-top: 100px; padding-bottom: 50px; 
    }
    
    .profile-card { 
        background: #ffffff; 
        border: 1px solid #e0e6ed; 
        padding: 40px; border-radius: 20px; 
        width: 100%; max-width: 600px; 
        box-shadow: 0 10px 40px rgba(0,0,0,0.08); 
        color: #333; position: relative; z-index: 10;
        text-align: center;
    }
    
    .profile-card h2 { margin-bottom: 5px; font-size: 2em; color: #2c3e50; font-weight: 700; }
    .tutor-role { font-size: 1.1em; color: #666; margin-bottom: 30px; }
    .tutor-name { color: #4361ee; font-weight: bold; }

    /* Review Section */
    .reviews-container { margin-top: 30px; margin-bottom: 30px; text-align: left; }
    
    .reviews-list-scroll {
        max-height: 500px; /* Taller scroll area since there is no form */
        overflow-y: auto; padding-right: 5px;
    }
    .reviews-list-scroll::-webkit-scrollbar { width: 6px; }
    .reviews-list-scroll::-webkit-scrollbar-thumb { background: #ccc; border-radius: 5px; }

    .review-item { 
        background: #f8f9fa; padding: 15px; border-radius: 10px; 
        margin-bottom: 10px; border-left: 4px solid #f39c12; 
        border: 1px solid #e9ecef; 
    }

    .btn-back { 
        display: inline-block; text-align: center; margin-top: 20px; 
        background: #4361ee; color: white; padding: 12px 30px; 
        border-radius: 30px; text-decoration: none; font-weight: 600; 
        transition: 0.3s; box-shadow: 0 4px 10px rgba(67, 97, 238, 0.3);
    }
    .btn-back:hover { transform: translateY(-2px); background: #304ffe; }
</style>

<div class="view-wrapper">
    <div class="profile-card">
        
        <h2>Tutor Profile</h2>
        <div class="tutor-role">
            Viewing: <span class="tutor-name"><?php echo htmlspecialchars($tutor['userName']); ?></span>
        </div>
        
        <div class="reviews-container">
            <h3 style="color:#f39c12; border-bottom:1px solid #eee; padding-bottom:10px; margin-bottom:15px; display:flex; justify-content:space-between; align-items:center;">
                <span>⭐ Performance Reviews</span>
                <?php if ($totalReviews > 0): ?>
                    <span style="font-size:0.8em; background:#fff3cd; padding:5px 12px; border-radius:12px; color:#856404; border:1px solid #ffeeba;">
                        Avg: <strong><?php echo $averageRating; ?></strong> / 5.0 (<?php echo $totalReviews; ?>)
                    </span>
                <?php endif; ?>
            </h3>

            <div class="reviews-list-scroll">
                <?php if (count($allReviews) > 0): ?>
                    <?php foreach ($allReviews as $rev): ?>
                        <div class="review-item">
                            <div style="display:flex; justify-content:space-between; margin-bottom:5px;">
                                <strong style="color:#333; font-size:0.95em;"><?php echo htmlspecialchars($rev['userName']); ?></strong>
                                <span style="color:#f39c12; font-size:0.9em; font-weight:bold;">
                                    <?php echo str_repeat('★', $rev['rating']); ?> 
                                    <span style="color:#aaa; font-weight:normal; font-size:0.8em;">(<?php echo $rev['rating']; ?>/5)</span>
                                </span>
                            </div>
                            <div style="color:#555; font-size:0.95em; line-height:1.4;">"<?php echo htmlspecialchars($rev['review']); ?>"</div>
                            <div style="font-size:0.8em; color:#999; margin-top:5px; text-align:right;">
                                <?php echo date("d.m.Y", strtotime($rev['lesson_time'])); ?>
                            </div>
                        </div>
                    <?php endforeach; ?>
                <?php else: ?>
                    <p style="color:#999; font-style:italic; text-align:center; padding: 20px;">
                        No reviews found for this colleague yet.
                    </p>
                <?php endif; ?>
            </div>
        </div>

        <a href="dashboard.php" class="btn-back">⬅ Back to Dashboard</a>

    </div>
</div>

<?php include 'footer.php'; ?>