<?php
// Start session only if not already active
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>EduMeet - Peer Tutoring Platform</title>
    
    <link href="https://fonts.googleapis.com/css2?family=Poppins:wght@300;400;600;700&display=swap" rel="stylesheet">
    
    <link rel="stylesheet" href="style.css?v=<?php echo time(); ?>"> 
    
    <script src="script.js" defer></script>
</head>
<body>

    <header class="navbar">
        <a href="index.php" class="logo">EduMeet.</a>
        
        <div class="nav-buttons">
            <?php if(isset($_SESSION['user_id'])): ?>
                
                <div class="user-menu">
                    <button onclick="toggleMenu()" class="user-btn">
                        <span>👤</span> 
                        <?php echo htmlspecialchars($_SESSION['username'] ?? 'User'); ?> 
                        <span>▼</span>
                    </button>
                    <div id="myDropdown" class="dropdown-content">
                        <a href="home.php">🏠 Dashboard</a>
                        <a href="profile.php">⚙️ Edit Profile</a>
                        <a href="logout.php" style="color:#dc3545;">🚪 Logout</a>
                    </div>
                </div>

            <?php else: ?>
                <a href="login.php" class="btn btn-login">Login</a>
                <a href="register.php" class="btn btn-register">Register</a>
            <?php endif; ?>
        </div>
    </header>
    ```

