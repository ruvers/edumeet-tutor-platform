<?php
// Page for students to request a lesson
session_start();
include 'db.php';

date_default_timezone_set('Europe/Istanbul');

// Check if user is logged in and tutor ID is provided
if (!isset($_SESSION['user_id']) || !isset($_GET['tutorID'])) {
    header("Location: dashboard.php");
    exit;
}

$tutor_id = $_GET['tutorID'];
$student_id = $_SESSION['user_id'];
$message = "";

// Get tutor information
$stmt = $pdo->prepare("SELECT userName FROM users WHERE userID = ?");
$stmt->execute([$tutor_id]);
$tutor = $stmt->fetch(PDO::FETCH_ASSOC);

// Fetch busy time slots for this tutor
$busySql = "SELECT lesson_time FROM requests 
            WHERE tutorID = ? 
            AND status != 'rejected' 
            AND lesson_time > NOW() 
            ORDER BY lesson_time ASC";
$busyStmt = $pdo->prepare($busySql);
$busyStmt->execute([$tutor_id]);
$busy_slots = $busyStmt->fetchAll(PDO::FETCH_ASSOC);

// Handle form submission
if ($_SERVER["REQUEST_METHOD"] == "POST") {
    $subject = $_POST['subject'];
    $lesson_time = $_POST['lesson_time'];
    $current_time = date('Y-m-d H:i:s');

    if (empty($subject) || empty($lesson_time)) {
        $message = "<div class='msg-box error'>Please fill in all fields.</div>";
    } 
    elseif ($lesson_time < $current_time) {
        $message = "<div class='msg-box error'>You cannot request a lesson for a past date!</div>";
    } 
    else {
        // Check for scheduling conflicts
        $checkSql = "SELECT COUNT(*) FROM requests 
                     WHERE tutorID = ? 
                     AND status != 'rejected' 
                     AND ABS(TIMESTAMPDIFF(MINUTE, lesson_time, ?)) < 60";
        $checkStmt = $pdo->prepare($checkSql);
        $checkStmt->execute([$tutor_id, $lesson_time]);
        $conflict_count = $checkStmt->fetchColumn();

        if ($conflict_count > 0) {
            $message = "<div class='msg-box error'>This tutor is busy at that time! Check the list below.</div>";
        } else {
            // Insert the new request
            $sql = "INSERT INTO requests (studentID, tutorID, subject, lesson_time, status) VALUES (?, ?, ?, ?, 'pending')";
            $stmt = $pdo->prepare($sql);
            try {
                $stmt->execute([$student_id, $tutor_id, $subject, $lesson_time]);
                $message = "<div class='msg-box success'>Request sent successfully! Redirecting...</div>";
                header("Refresh: 2; url=dashboard.php");
            } catch (PDOException $e) {
                $message = "<div class='msg-box error'>Error: " . $e->getMessage() . "</div>";
            }
        }
    }
}

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

    /* Centered Layout */
    .request-wrapper { 
        display: flex; justify-content: center; align-items: center; 
        min-height: 80vh; padding-top: 100px; padding-bottom: 50px; 
    }
    
    /* White Card Container */
    .request-card { 
        background: #ffffff; 
        border: 1px solid #e0e6ed; 
        padding: 40px; border-radius: 20px; 
        width: 100%; max-width: 600px; 
        box-shadow: 0 10px 40px rgba(0,0,0,0.08); 
        color: #333; position: relative; z-index: 10;
    }
    
    .request-card h2 { 
        text-align: center; margin-bottom: 5px; font-size: 2em; 
        color: #2c3e50; font-weight: 700;
    }
    .tutor-info { 
        text-align: center; margin-bottom: 30px; font-size: 1.1em; color: #666; 
    }
    .tutor-name { color: #4361ee; font-weight: bold; }

    /* Busy Slots Section */
    .busy-container { 
        background: #fff5f5; border: 1px solid #ffe3e3; 
        border-radius: 12px; padding: 20px; margin-bottom: 30px; 
    }
    .busy-title { color: #e03131; font-weight: bold; margin-bottom: 10px; }
    
    .busy-list { 
        list-style: none; max-height: 150px; overflow-y: auto; padding-right: 5px; 
    }
    .busy-list::-webkit-scrollbar { width: 6px; }
    .busy-list::-webkit-scrollbar-thumb { background: #ccc; border-radius: 5px; }
    
    .busy-item { 
        background: #ffffff; border: 1px solid #ffc9c9;
        padding: 8px 12px; margin-bottom: 5px; border-radius: 8px; 
        font-size: 0.9em; display: flex; justify-content: space-between; align-items: center; 
        color: #555;
    }
    .tag-busy { 
        background: #ffe3e3; color: #e03131; padding: 2px 8px; 
        border-radius: 4px; font-size: 0.8em; font-weight: 600;
    }

    /* Review Section */
    .reviews-container { margin-top: 30px; margin-bottom: 30px; }
    
    /* Scrollable review list - Adjusted height to show approx 3 items */
    .reviews-list-scroll {
        max-height: 320px; /* Reduced height for ~3 items */
        overflow-y: auto; 
        padding-right: 5px;
    }
    .reviews-list-scroll::-webkit-scrollbar { width: 6px; }
    .reviews-list-scroll::-webkit-scrollbar-thumb { background: #ccc; border-radius: 4px; }

    .review-item { 
        background: #f8f9fa; padding: 15px; border-radius: 10px; 
        margin-bottom: 10px; border-left: 4px solid #f39c12; 
        border: 1px solid #e9ecef; 
    }

    /* Form Elements */
    .form-group { margin-bottom: 20px; }
    .form-group label { display: block; margin-bottom: 8px; font-weight: 600; font-size: 0.9em; color: #555; }
    .form-control { 
        width: 100%; padding: 12px 15px; border-radius: 10px; 
        border: 2px solid #eef2f7; background: #f9fbfc; 
        color: #333; font-size: 1em; outline: none; transition: 0.3s; 
    }
    .form-control:focus { background: #fff; border-color: #4361ee; }
    input[type="datetime-local"] { color-scheme: light; }

    /* Buttons */
    .btn-submit { 
        width: 100%; padding: 14px; background: #4361ee; 
        border: none; border-radius: 30px; color: white; 
        font-weight: bold; font-size: 1.1em; cursor: pointer; 
        transition: 0.3s; box-shadow: 0 4px 10px rgba(67, 97, 238, 0.3); 
    }
    .btn-submit:hover { transform: translateY(-2px); background: #304ffe; }
    .btn-cancel { 
        display: block; text-align: center; margin-top: 15px; 
        color: #888; text-decoration: none; font-size: 0.9em; transition: 0.3s; 
    }
    .btn-cancel:hover { color: #4361ee; text-decoration: underline; }

    /* Messages */
    .msg-box { padding: 15px; border-radius: 10px; text-align: center; margin-bottom: 20px; font-weight: bold; }
    .msg-box.error { background: #ffe5e5; color: #d63031; border: 1px solid #ff7675; }
    .msg-box.success { background: #d4edda; color: #155724; border: 1px solid #c3e6cb; }
</style>

<div class="request-wrapper">
    <div class="request-card">
        
        <h2>Request Lesson</h2>
        <div class="tutor-info">
            Tutor: <span class="tutor-name"><?php echo htmlspecialchars($tutor['userName']); ?></span>
        </div>
        
        <?php echo $message; ?>

        <div class="busy-container">
            <div class="busy-title">⛔ Busy Times (Occupied)</div>
            <?php if (count($busy_slots) > 0): ?>
                <p style="font-size:0.85em; color:#666; margin-bottom:10px;">This tutor is busy during these 1-hour slots:</p>
                <ul class="busy-list">
                    <?php foreach ($busy_slots as $slot): ?>
                        <?php 
                            $start = strtotime($slot['lesson_time']);
                            $end = $start + 3600; 
                            $dateStr = date("d.m.Y", $start);
                            $timeStr = date("H:i", $start) . " - " . date("H:i", $end);
                        ?>
                        <li class="busy-item">
                            <span>📅 <?php echo $dateStr; ?> &nbsp;|&nbsp; 🕒 <?php echo $timeStr; ?></span>
                            <span class="tag-busy">Busy</span>
                        </li>
                    <?php endforeach; ?>
                </ul>
            <?php else: ?>
                <p style="color:#2ecc71; margin:0; font-weight:bold; text-align:center;">✅ This tutor is currently completely free!</p>
            <?php endif; ?>
        </div>

        <div class="reviews-container">
            <h3 style="color:#f39c12; border-bottom:1px solid #eee; padding-bottom:10px; margin-bottom:15px; display:flex; justify-content:space-between; align-items:center;">
                <span>⭐ Ratings & Reviews</span>
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
                    <p style="color:#999; font-style:italic; text-align:center;">No ratings yet. Be the first to review!</p>
                <?php endif; ?>
            </div>
        </div>

        <form method="POST" action="">
            <div class="form-group">
                <label>Subject / Topic</label>
                <input type="text" name="subject" class="form-control" placeholder="e.g. Calculus - Derivatives" required>
            </div>
            <div class="form-group">
                <label>Desired Date and Time</label>
                <input type="datetime-local" name="lesson_time" class="form-control" min="<?php echo date('Y-m-d\TH:i'); ?>" required>
            </div>
            <button type="submit" class="btn-submit">Send Request</button>
            <a href="dashboard.php" class="btn-cancel">Cancel</a>
        </form>

    </div>
</div>

<?php include 'footer.php'; ?>