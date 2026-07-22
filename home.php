<?php
// Home dashboard page
session_start();
include 'db.php';

// Check if user is logged in
if (!isset($_SESSION['user_id'])) {
    header("Location: index.php");
    exit;
}

// Redirect admins to the admin panel
if ($_SESSION['role'] == 'admin') {
    header("Location: admin.php");
    exit;
}

$username = $_SESSION['username'];
$role = ucfirst($_SESSION['role']); 

// Include header file
include 'header.php';
?>

<style>
    /* Ensure light background matches the new theme */
    body {
        background-color: #f0f4f8 !important;
        background-image: none !important;
        color: #333 !important;
    }
    
    /* Main container centered on screen */
    .home-container {
        width: 90%;
        max-width: 1200px; /* Increased width for better fit */
        margin: 80px auto 50px auto; 
        padding: 20px;
        text-align: center;
    }

    /* Welcome text style */
    .welcome-section {
        margin-bottom: 50px;
    }
    
    .welcome-section h1 {
        font-size: 3em;
        margin-bottom: 10px;
        font-weight: 800;
        color: #2c3e50; /* Dark blue text */
    }
    
    .welcome-section p {
        font-size: 1.2em;
        color: #666;
        font-weight: 400;
        max-width: 700px;
        margin: 0 auto;
        line-height: 1.6;
    }

    /* Grid layout for action cards */
    .action-grid {
        display: grid;
        /* Flexible columns that wrap nicely */
        grid-template-columns: repeat(auto-fit, minmax(280px, 1fr));
        gap: 30px;
        justify-content: center;
        align-items: stretch; /* Cards stretch to same height */
    }

    /* White Card Design */
    .dashboard-card {
        background: #ffffff;
        padding: 40px 30px;
        border-radius: 20px;
        text-decoration: none;
        color: #333;
        transition: transform 0.3s, box-shadow 0.3s; 
        display: flex;
        flex-direction: column;
        align-items: center;
        justify-content: center;
        box-shadow: 0 10px 30px rgba(0,0,0,0.05);
        border: 1px solid #e0e6ed;
        min-height: 250px; /* Consistent height */
    }

    /* Hover effect for cards */
    .dashboard-card:hover {
        transform: translateY(-8px);
        box-shadow: 0 20px 50px rgba(67, 97, 238, 0.15);
        border-color: #4361ee;
    }

    .card-icon {
        font-size: 3.5em;
        margin-bottom: 20px;
        display: block;
    }

    .card-title {
        font-size: 1.4em;
        font-weight: 700;
        margin-bottom: 10px;
        color: #4361ee; /* Brand color */
    }

    .card-desc {
        font-size: 0.95em;
        opacity: 0.8;
        line-height: 1.5;
        color: #666;
    }
    
    /* Logout card specific style */
    .logout-card {
        border-top: 4px solid #dc3545;
    }
    .logout-card .card-title {
        color: #dc3545;
    }
    .logout-card:hover {
        box-shadow: 0 20px 50px rgba(220, 53, 69, 0.15);
        border-color: #dc3545;
    }
</style>

<div class="home-container">
    
    <div class="welcome-section">
        <h1>Welcome Back, <?php echo htmlspecialchars($username); ?>!</h1>
        <p>
            You are logged in as a <strong><?php echo $role; ?></strong>. 
            <br>Access your dashboard below to manage your lessons and profile.
        </p>
    </div>

    <div class="action-grid">
        
        <?php if ($_SESSION['role'] == 'student'): ?>
            
            <a href="dashboard.php" class="dashboard-card">
                <span class="card-icon">🔍</span>
                <div class="card-title">Find a Tutor</div>
                <div class="card-desc">Browse our expert tutors and schedule your next lesson today.</div>
            </a>

            <a href="profile.php" class="dashboard-card">
                <span class="card-icon">👤</span>
                <div class="card-title">My Profile</div>
                <div class="card-desc">Update your personal information and change your password.</div>
            </a>

        <?php else: ?>
            
            <a href="dashboard.php" class="dashboard-card">
                <span class="card-icon">📬</span>
                <div class="card-title">Lesson Requests</div>
                <div class="card-desc">View incoming lesson requests from students and manage your schedule.</div>
            </a>

            <a href="profile.php" class="dashboard-card">
                <span class="card-icon">⚙️</span>
                <div class="card-title">Tutor Profile</div>
                <div class="card-desc">Manage your expertise areas and account settings.</div>
            </a>

        <?php endif; ?>

        <a href="logout.php" class="dashboard-card logout-card">
            <span class="card-icon">🚪</span>
            <div class="card-title">Logout</div>
            <div class="card-desc">End your session safely. See you next time!</div>
        </a>

    </div>

</div>

<?php include 'footer.php'; ?>