<?php
// User dashboard page
session_start();
include 'db.php';
date_default_timezone_set('Europe/Istanbul');

// Security checks
if (!isset($_SESSION['user_id'])) {
    header("Location: index.php");
    exit;
}

// Redirect admins to admin panel
if ($_SESSION['role'] == 'admin') {
    header("Location: admin.php");
    exit;
}

$current_role = $_SESSION['role'];
$current_user_id = $_SESSION['user_id'];
$username = $_SESSION['username'];
$message = "";

// Fetch subject list from database
try {
    $stmt_subjects = $pdo->query("SELECT name FROM subjects ORDER BY name ASC");
    $subjectList = $stmt_subjects->fetchAll(PDO::FETCH_COLUMN);
} catch (PDOException $e) {
    // Fallback list if database table is missing
    $subjectList = ["Mathematics", "Physics", "English", "Coding"];
}

// Success messages
if (isset($_GET['msg']) && $_GET['msg'] == 'rated') {
    $message = "<div class='success-msg'>Thank you! Your feedback has been saved. ⭐</div>";
}
if (isset($_GET['msg']) && $_GET['msg'] == 'reported') {
    $message = "<div class='success-msg' style='background:#f8d7da; color:#721c24; border-color:#f5c6cb;'>Report submitted successfully. Admins will review it.</div>";
}

// Include header file
include 'header.php';
?>

<style>
    /* Ensure light background */
    body {
        background-color: #f0f4f8 !important;
        background-image: none !important;
        color: #333 !important;
    }

    /* Main container */
    .dashboard-container { 
        width: 100%; max-width: 1200px; 
        margin: 100px auto 50px auto; 
        padding: 0 20px; 
    }

    /* Header section */
    .page-header {
        display: flex; justify-content: space-between; align-items: center; margin-bottom: 30px;
        background: #ffffff; padding: 30px;
        border-radius: 20px; 
        border: 1px solid #e0e6ed; 
        box-shadow: 0 10px 30px rgba(0,0,0,0.05);
    }
    .page-header h2 { margin: 0; font-size: 1.8em; color: #2c3e50; font-weight: 700; }

    /* Back button */
    .btn-back {
        text-decoration: none; color: #4361ee; 
        background: transparent; padding: 10px 25px;
        border-radius: 30px; font-weight: 600; transition: 0.3s; 
        display: flex; align-items: center; gap: 8px;
        border: 2px solid #4361ee;
    }
    .btn-back:hover { background: #4361ee; color: white; transform: translateX(-3px); }

    /* Search box styles */
    .search-glass {
        background: #ffffff; padding: 25px; border-radius: 20px;
        border: 1px solid #e0e6ed; display: flex; gap: 15px; 
        flex-wrap: wrap; margin-bottom: 40px; align-items: center;
        box-shadow: 0 10px 30px rgba(0,0,0,0.03);
    }
    .search-glass input, .search-glass select {
        flex: 1; padding: 14px 20px; border-radius: 12px; 
        border: 2px solid #eef2f7;
        background: #f9fbfc; color: #333; 
        font-family: 'Poppins', sans-serif; outline: none; transition: 0.3s;
    }
    .search-glass input:focus, .search-glass select:focus { 
        background: #fff; border-color: #4361ee; 
    }
    .search-glass select option { background: #fff; color: #333; }
    
    .btn-filter {
        background: #4361ee; color: white; border: none; padding: 14px 35px;
        border-radius: 50px; font-weight: bold; cursor: pointer; transition: 0.3s; 
        box-shadow: 0 4px 15px rgba(67, 97, 238, 0.3);
    }
    .btn-filter:hover { background: #304ffe; transform: translateY(-2px); }

    /* Tutor grid layout */
    .tutor-grid { display: grid; grid-template-columns: repeat(auto-fill, minmax(300px, 1fr)); gap: 30px; }
    
    /* Tutor Card */
    .tutor-card {
        background: #ffffff; border: 1px solid #e0e6ed;
        border-top: 4px solid #e0e6ed; border-radius: 20px; 
        padding: 30px; text-align: center;
        transition: 0.3s; display: flex; flex-direction: column; 
        align-items: center; position: relative; overflow: hidden;
        box-shadow: 0 5px 15px rgba(0,0,0,0.03);
    }
    .tutor-card:hover { 
        transform: translateY(-10px); 
        box-shadow: 0 20px 40px rgba(67, 97, 238, 0.1); 
        border-top-color: #4361ee; 
    }
    
    .tutor-avatar {
        width: 85px; height: 85px; 
        background: linear-gradient(135deg, #4361ee, #304ffe); color: white;
        font-size: 2.2em; font-weight: bold; display: flex; 
        justify-content: center; align-items: center;
        border-radius: 50%; margin-bottom: 20px; 
        box-shadow: 0 10px 20px rgba(67, 97, 238, 0.2); 
        border: 4px solid #f0f4f8;
    }
    .tutor-name { font-size: 1.4em; font-weight: 700; color: #2c3e50; margin-bottom: 5px; }
    .tutor-skills { color: #666; font-size: 0.95em; margin-bottom: 15px; font-weight: 400; }
    
    /* Snippet for latest review on card */
    .latest-review-snippet {
        background: #f8f9fa; padding: 10px; border-radius: 10px; 
        font-size: 0.85em; color: #555; font-style: italic; 
        margin-bottom: 20px; width: 100%; border: 1px solid #e9ecef;
    }

    .rating-badge { 
        background: #fff8e1; color: #f39c12; padding: 5px 15px; 
        border-radius: 20px; font-weight: bold; font-size: 0.9em; 
        margin-bottom: 20px; border: 1px solid #ffe0b2; 
    }
    
    /* Buttons */
    .btn-request { 
        background: #4361ee; color: white; padding: 12px 30px; 
        border-radius: 30px; text-decoration: none; font-weight: 600; 
        transition: 0.3s; width: 100%; border: none; 
        box-shadow: 0 5px 15px rgba(67, 97, 238, 0.3);
    }
    .btn-request:hover { background: #304ffe; transform: scale(1.05); }

    /* Button for Tutors to view others */
    .btn-view-profile {
        background: #fff; color: #4361ee; border: 2px solid #4361ee;
        padding: 12px 30px; border-radius: 30px; text-decoration: none; 
        font-weight: 600; transition: 0.3s; width: 100%; display:block;
    }
    .btn-view-profile:hover { background: #4361ee; color: white; }

    /* Request list items */
    .request-list-item { 
        background: #ffffff; border-left: 5px solid #ccc; 
        padding: 25px; margin-bottom: 15px; border-radius: 12px; 
        display: flex; justify-content: space-between; align-items: center; 
        transition: 0.3s; border: 1px solid #e0e6ed; border-left-width: 5px;
        box-shadow: 0 2px 5px rgba(0,0,0,0.02);
    }
    .request-list-item:hover { transform: translateX(5px); box-shadow: 0 5px 15px rgba(0,0,0,0.05); }
    
    .border-pending { border-left-color: #f39c12; }
    .border-accepted { border-left-color: #2ecc71; }
    .border-rejected { border-left-color: #e74c3c; }
    
    .status-badge { padding: 6px 15px; border-radius: 30px; font-weight: bold; font-size: 0.85em; text-transform: uppercase; letter-spacing: 1px; }
    .status-pending { background: #fff3cd; color: #856404; }
    .status-accepted { background: #d4edda; color: #155724; }
    .status-rejected { background: #f8d7da; color: #721c24; }
    
    .success-msg { background: #d4edda; padding: 15px; border-radius: 10px; text-align: center; color: #155724; margin-bottom: 20px; font-weight: bold; border: 1px solid #c3e6cb; }

    /* Tutor Own Stats Box */
    .stats-card {
        background: linear-gradient(135deg, #4361ee, #304ffe);
        color: white; padding: 25px; border-radius: 20px;
        margin-bottom: 40px; display: flex; flex-direction:column; gap: 20px;
        box-shadow: 0 10px 30px rgba(67, 97, 238, 0.3);
    }
    .stats-header { display: flex; justify-content: space-between; width: 100%; align-items: center; }
    .stats-info h3 { margin: 0 0 5px 0; font-size: 1.5em; }
    .stats-info p { margin: 0; opacity: 0.9; }
    .stats-score { 
        font-size: 2.5em; font-weight: 800; background: rgba(255,255,255,0.2);
        padding: 10px 20px; border-radius: 15px; backdrop-filter: blur(5px);
    }

    /* Scrollable Reviews in Stats */
    .my-reviews-scroll {
        background: rgba(255,255,255,0.1); border-radius: 15px; padding: 15px; 
        width: 100%; max-height: 200px; overflow-y: auto;
    }
    .my-review-item {
        background: rgba(255,255,255,0.9); color: #333; padding: 10px; 
        border-radius: 10px; margin-bottom: 10px; font-size: 0.9em;
    }
    .my-reviews-scroll::-webkit-scrollbar { width: 5px; }
    .my-reviews-scroll::-webkit-scrollbar-thumb { background: rgba(255,255,255,0.5); border-radius: 5px; }

    @media (max-width: 768px) {
        .request-list-item { flex-direction: column; align-items: flex-start; gap: 15px; }
        .page-header { flex-direction: column; gap: 15px; text-align: center; }
    }
</style>

<div class="dashboard-container">
    
    <div class="page-header">
        <h2>Dashboard</h2>
        <a href="home.php" class="btn-back">⬅ Back to Home</a>
    </div>

    <?php echo $message; ?>

    <?php if ($current_role == 'tutor'): ?>
        
        <?php
        // Calculate tutor's own rating
        $myStatsSql = "SELECT AVG(rating) as avg_rating, COUNT(*) as total_reviews FROM requests WHERE tutorID = ? AND rating > 0";
        $myStatsStmt = $pdo->prepare($myStatsSql);
        $myStatsStmt->execute([$current_user_id]);
        $myStats = $myStatsStmt->fetch(PDO::FETCH_ASSOC);
        $myScore = $myStats['avg_rating'] ? number_format($myStats['avg_rating'], 1) : "0.0";

        // Fetch My Reviews (New addition)
        $myReviewsSql = "SELECT review, rating FROM requests WHERE tutorID = ? AND review != '' ORDER BY requestID DESC";
        $myReviewsStmt = $pdo->prepare($myReviewsSql);
        $myReviewsStmt->execute([$current_user_id]);
        $myReviews = $myReviewsStmt->fetchAll(PDO::FETCH_ASSOC);
        ?>

        <div class="stats-card">
            <div class="stats-header">
                <div class="stats-info">
                    <h3 style=" color:aliceblue;">My Performance</h3>
                    <p>Based on <?php echo $myStats['total_reviews']; ?> student reviews.</p>
                </div>
                <div class="stats-score">
                    ⭐ <?php echo $myScore; ?>
                </div>
            </div>

            <?php if(count($myReviews) > 0): ?>
                <div class="my-reviews-scroll">
                    <strong style="display:block; margin-bottom:10px; color:white;">Latest Feedback:</strong>
                    <?php foreach($myReviews as $rev): ?>
                        <div class="my-review-item">
                            <span style="color:#f39c12; font-weight:bold;"><?php echo str_repeat('★', $rev['rating']); ?></span>
                            "<?php echo htmlspecialchars($rev['review']); ?>"
                        </div>
                    <?php endforeach; ?>
                </div>
            <?php endif; ?>
        </div>

        <h3 style="color:#2c3e50; margin-bottom:20px; font-weight:600; border-left:4px solid #38ef7d; padding-left:15px;">Incoming Lesson Requests</h3>
        
        <div class="request-list" style="margin-bottom: 60px;">
            <?php
            $sql = "SELECT r.*, u.userName as studentName, u.email as studentEmail FROM requests r JOIN users u ON r.studentID = u.userID WHERE r.tutorID = ? ORDER BY r.lesson_time DESC";
            $stmt = $pdo->prepare($sql);
            $stmt->execute([$current_user_id]);

            if ($stmt->rowCount() > 0) {
                while ($row = $stmt->fetch(PDO::FETCH_ASSOC)) {
                    $borderClass = 'border-' . $row['status'];

                    echo '<div class="request-list-item ' . $borderClass . '">';
                    echo '<div>';
                    echo '<div style="font-size:1.1em; color:#2c3e50; font-weight:bold;">' . htmlspecialchars($row['studentName']) . '</div>';
                    echo '<div style="color:#666;">Request: ' . htmlspecialchars($row['subject']) . '</div>';
                    
                    if (!empty($row['lesson_time'])) {
                        echo '<div style="color:#27ae60; font-size:0.9em; margin-top:5px; font-weight:600;">🕒 ' . date("d.m.Y H:i", strtotime($row['lesson_time'])) . '</div>';
                    }
                    if($row['status'] == 'accepted') {
                        echo '<div style="color:#4361ee; font-size:0.85em; margin-top:5px; font-weight:500;">📧 ' . $row['studentEmail'] . '</div>';
                    }
                    echo '</div>';

                    echo '<div>';
                    if ($row['status'] == 'pending') {
                        echo '<a href="update_request.php?id=' . $row['requestID'] . '&status=accepted" style="background:#2ecc71; color:white; padding:8px 15px; text-decoration:none; border-radius:30px; margin-right:5px; font-weight:600; box-shadow:0 4px 10px rgba(46,204,113,0.3);">Accept</a>';
                        echo '<a href="update_request.php?id=' . $row['requestID'] . '&status=rejected" style="background:#e74c3c; color:white; padding:8px 15px; text-decoration:none; border-radius:30px; font-weight:600; box-shadow:0 4px 10px rgba(231,76,60,0.3);">Reject</a>';
                    } elseif ($row['status'] == 'accepted') {
                        echo '<span class="status-badge status-accepted">Approved</span>';
                    } else {
                        echo '<span class="status-badge status-rejected">Rejected</span>';
                    }
                    echo '</div>';
                    echo '</div>';
                }
            } else {
                echo "<p style='color:#666;'>No incoming lesson requests yet.</p>";
            }
            ?>
        </div>
        
        <hr style="border:0; border-top:1px solid #e0e6ed; margin: 40px 0;">
        <h3 style="color:#2c3e50; margin-bottom:20px; font-weight:600; border-left:4px solid #4361ee; padding-left:15px;">Other Tutors & Reviews</h3>

    <?php endif; ?>


    <?php if ($current_role == 'student'): ?>
        <h3 style="color:#2c3e50; margin-bottom:20px; font-weight:600; border-left:4px solid #f39c12; padding-left:15px;">My Requests Status</h3>
        
        <div class="request-list" style="margin-bottom: 50px;">
            <?php
            $reqSql = "SELECT r.*, u.userName as tutorName, u.email as tutorEmail FROM requests r JOIN users u ON r.tutorID = u.userID WHERE r.studentID = ? ORDER BY r.lesson_time DESC";
            $reqStmt = $pdo->prepare($reqSql);
            $reqStmt->execute([$current_user_id]);

            if ($reqStmt->rowCount() > 0) {
                while ($req = $reqStmt->fetch(PDO::FETCH_ASSOC)) {
                    $borderClass = 'border-' . $req['status'];
                    
                    echo '<div class="request-list-item ' . $borderClass . '">';
                    echo '<div>';
                    echo '<div style="font-size:1.1em; color:#2c3e50; font-weight:bold;">' . $req['tutorName'] . '</div>';
                    echo '<div style="color:#666;">Subject: ' . htmlspecialchars($req['subject']) . '</div>';
                    if (!empty($req['lesson_time'])) {
                        echo '<div style="color:#27ae60; font-size:0.9em; margin-top:5px; font-weight:600;">🕒 ' . date("d.m.Y H:i", strtotime($req['lesson_time'])) . '</div>';
                    }
                    echo '</div>';

                    echo '<div style="text-align:right;">';
                    if($req['status'] == 'pending') echo '<span class="status-badge status-pending">Pending</span>';
                    elseif($req['status'] == 'accepted') echo '<span class="status-badge status-accepted">Accepted</span>';
                    else echo '<span class="status-badge status-rejected">Rejected</span>';

                    if($req['status'] == 'pending') {
                         echo '<br><a href="delete_request.php?id='.$req['requestID'].'" style="color:#e74c3c; font-size:0.85em; margin-top:10px; display:inline-block; text-decoration:none; font-weight:bold;">✖ Cancel</a>';
                    }

                    // Rating Logic (Same as before)
                    $is_past = (!empty($req['lesson_time']) && strtotime($req['lesson_time']) < time());
                    $status_clean = strtolower(trim($req['status']));

                    if ($status_clean == 'accepted' && $is_past) {
                        echo '<div style="background:#f9f9f9; padding:15px; border-radius:10px; margin-top:15px; border:1px solid #eee;">';
                        
                        if (empty($req['rating']) || $req['rating'] == 0) {
                            echo '<form class="rating-form">';
                            echo '<input type="hidden" name="request_id" value="' . $req['requestID'] . '">';
                            echo '<div style="margin-bottom:10px; font-weight:bold; color:#f39c12; font-size:0.9em;">Rate & Review:</div>';
                            echo '<select name="rating" style="width:100%; padding:10px; border-radius:5px; background:#fff; color:#333; border:1px solid #ccc; margin-bottom:10px;">
                                    <option value="5">⭐⭐⭐⭐⭐ - Excellent</option>
                                    <option value="4">⭐⭐⭐⭐ - Good</option>
                                    <option value="3">⭐⭐⭐ - Average</option>
                                    <option value="2">⭐⭐ - Poor</option>
                                    <option value="1">⭐ - Terrible</option>
                                  </select>';
                            echo '<textarea name="review" placeholder="Write your review here..." rows="2" style="width:100%; padding:10px; border-radius:5px; background:#fff; color:#333; border:1px solid #ccc; font-family:Poppins; resize:none; margin-bottom:10px; font-size:0.9em;"></textarea>';
                            echo '<button type="submit" style="background:#f39c12; color:white; border:none; padding:8px 20px; border-radius:30px; cursor:pointer; width:100%; font-weight:bold;">Submit Review</button>';
                            echo '</form>';
                        } else {
                            echo '<div style="color:#f39c12; font-weight:bold;">You rated: ' . str_repeat('★', $req['rating']) . '</div>';
                            if(!empty($req['review'])) {
                                echo '<div style="color:#666; font-size:0.9em; font-style:italic; margin-top:5px; border-left:2px solid #ccc; padding-left:10px;">"' . htmlspecialchars($req['review']) . '"</div>';
                            }
                        }
                        
                        echo '<hr style="border:0; border-top:1px solid #eee; margin:15px 0;">';
                        echo '<div style="text-align:right;">';
                        echo '<button onclick="document.getElementById(\'report-form-'.$req['requestID'].'\').style.display=\'block\'" style="background:transparent; border:none; color:#e74c3c; font-size:0.85em; cursor:pointer; text-decoration:underline;">🚩 Report an Issue</button>';
                        echo '</div>';

                        // Hidden Report Form
                        echo '<form id="report-form-'.$req['requestID'].'" action="report_action.php" method="POST" style="display:none; margin-top:10px; animation:fadeIn 0.5s;">';
                        echo '<input type="hidden" name="request_id" value="' . $req['requestID'] . '">';
                        echo '<textarea name="reason" placeholder="Why are you reporting this tutor?" required style="width:100%; padding:10px; border-radius:5px; background:#fff; color:#333; border:1px solid #e74c3c; font-family:Poppins; resize:none; margin-bottom:5px; font-size:0.9em;"></textarea>';
                        echo '<button type="submit" style="background:#e74c3c; color:white; border:none; padding:6px 15px; border-radius:5px; cursor:pointer; font-size:0.9em;">Send Report</button>';
                        echo ' <button type="button" onclick="this.parentElement.style.display=\'none\'" style="background:transparent; color:#888; border:none; cursor:pointer; font-size:0.9em;">Cancel</button>';
                        echo '</form>';

                        echo '</div>';
                    }
                    echo '</div>';
                    echo '</div>';
                }
            } else {
                echo "<p style='color:#666;'>You have no requests yet.</p>";
            }
            ?>
        </div>
        
        <h3 style="color:#2c3e50; margin-bottom:20px; font-weight:600; border-left:4px solid #4361ee; padding-left:15px;">Find a Tutor</h3>
    <?php endif; ?>


    <form method="GET" action="" class="search-glass">
        <input type="text" name="q" placeholder="Search by tutor name..." 
               value="<?php echo isset($_GET['q']) ? htmlspecialchars($_GET['q']) : ''; ?>">
        
        <select name="subject_filter">
            <option value="">All Subjects</option>
            <?php 
                $selected_subject = isset($_GET['subject_filter']) ? $_GET['subject_filter'] : '';
                foreach ($subjectList as $subj) {
                    $sel = ($selected_subject == $subj) ? 'selected' : '';
                    echo "<option value='$subj' $sel>$subj</option>";
                }
            ?>
        </select>

        <select name="sort">
            <option value="">Sort By...</option>
            <option value="rating_desc" <?php if(isset($_GET['sort']) && $_GET['sort']=='rating_desc') echo 'selected'; ?>>Highest Rated ⭐</option>
        </select>

        <button type="submit" class="btn-filter">Search</button>
        <?php if(isset($_GET['q']) || isset($_GET['sort']) || isset($_GET['subject_filter'])): ?>
            <a href="dashboard.php" style="color:#666; font-size:0.9em; margin-left:10px; text-decoration:underline;">Reset</a>
        <?php endif; ?>
    </form>

    <div class="tutor-grid">
        <?php
        // Updated SQL to get Avg Rating AND Latest Review
        $sql = "SELECT u.userID, u.userName, u.skills, 
                       AVG(r.rating) as avg_rating,
                       (SELECT review FROM requests WHERE tutorID = u.userID AND review != '' ORDER BY requestID DESC LIMIT 1) as latest_review
                FROM users u 
                LEFT JOIN requests r ON u.userID = r.tutorID 
                WHERE u.role = 'tutor'";
        $params = [];

        if (isset($_GET['q']) && !empty($_GET['q'])) {
            $sql .= " AND u.userName LIKE ?";
            $params[] = "%" . $_GET['q'] . "%";
        }
        if (isset($_GET['subject_filter']) && !empty($_GET['subject_filter'])) {
            $sql .= " AND u.skills LIKE ?";
            $params[] = "%" . $_GET['subject_filter'] . "%";
        }

        // Exclude self from the grid if user is a tutor
        if ($current_role == 'tutor') {
            $sql .= " AND u.userID != ?";
            $params[] = $current_user_id;
        }

        $sql .= " GROUP BY u.userID";

        if (isset($_GET['sort']) && $_GET['sort'] == 'rating_desc') {
            $sql .= " ORDER BY avg_rating DESC";
        } else {
            $sql .= " ORDER BY u.userID ASC"; 
        }

        $stmt = $pdo->prepare($sql);
        $stmt->execute($params);
        
        if ($stmt->rowCount() > 0) {
            while ($row = $stmt->fetch(PDO::FETCH_ASSOC)) {
                $avg_score = $row['avg_rating'] ? number_format($row['avg_rating'], 1) : 0;
                $initials = strtoupper(substr($row['userName'], 0, 1));
                
                echo '<div class="tutor-card">';
                echo '<div class="tutor-avatar">' . $initials . '</div>';
                echo '<div class="tutor-name">' . htmlspecialchars($row['userName']) . '</div>';
                echo '<div class="tutor-skills">' . htmlspecialchars($row['skills']) . '</div>';
                
                // Show Rating
                if ($avg_score > 0) {
                    echo '<div class="rating-badge">⭐ ' . $avg_score . ' / 5.0</div>';
                } else {
                    echo '<div class="rating-badge" style="opacity:0.7; background:#f1f3f5; color:#888; border:none;">New Tutor</div>';
                }

                // Show Latest Review Snippet
                if (!empty($row['latest_review'])) {
                    // Truncate if too long
                    $reviewSnippet = strlen($row['latest_review']) > 50 ? substr($row['latest_review'], 0, 50) . "..." : $row['latest_review'];
                    echo '<div class="latest-review-snippet">"'.htmlspecialchars($reviewSnippet).'"</div>';
                } else {
                    echo '<div class="latest-review-snippet" style="opacity:0.5;">No reviews yet</div>';
                }
                
                // Action Button Logic
                if ($current_role == 'student') {
                    // Students can request
                    echo '<a href="make_request.php?tutorID=' . $row['userID'] . '" class="btn-request">Request Lesson</a>';
                } else {
                    // Tutors can view profile to see reviews but not request
                    echo '<a href="view_tutor.php?tutorID=' . $row['userID'] . '" class="btn-view-profile">View Profile</a>';
                }
                
                echo '</div>';
            }
        } else {
            echo "<p style='color:#666; grid-column: 1/-1; text-align:center;'>No tutors found.</p>";
        }
        ?>
    </div>

</div>

<?php include 'footer.php'; ?>