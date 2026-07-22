<?php
// login.php 
session_start();
include 'db.php';

// Redirect to home if the user is already logged in
if (isset($_SESSION['user_id'])) {
    header("Location: home.php");
    exit;
}

$message = "";

if ($_SERVER["REQUEST_METHOD"] == "POST") {
    $email = trim($_POST['email']);
    $password = $_POST['password'];

    // Check if the user exists in the database
    $sql = "SELECT * FROM users WHERE email = ?";
    $stmt = $pdo->prepare($sql);
    $stmt->execute([$email]);
    $user = $stmt->fetch(PDO::FETCH_ASSOC);

    // Verify password and start session
    if ($user && password_verify($password, $user['password'])) {
        $_SESSION['user_id'] = $user['userID'];    
        $_SESSION['username'] = $user['userName']; 
        $_SESSION['role'] = $user['role'];
        
        header("Location: home.php");
        exit;
    } else {
        // Show error message for invalid credentials
        $message = "<div class='error-msg'>Invalid email or password!</div>";
    }
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Login - EduMeet</title>
    <link rel="stylesheet" href="style.css?v=<?php echo time(); ?>">
    <link href="https://fonts.googleapis.com/css2?family=Poppins:wght@300;400;600;700&display=swap" rel="stylesheet">
</head>
<body>

    <header class="navbar">
        <a href="index.php" class="logo">EduMeet.</a>
        <div class="nav-buttons">
            <a href="register.php" class="btn btn-register">Register</a>
        </div>
    </header>

    <div class="form-wrapper">
        <div class="login-card">
            <h2>Welcome Back</h2>
            
            <?php echo $message; ?>

            <form method="POST" action="">
                <div class="input-group">
                    <label>Email Address</label>
                    <input type="email" name="email" placeholder="Enter your email" required>
                </div>
                
                <div class="input-group">
                    <label>Password</label>
                    <input type="password" name="password" placeholder="Enter your password" required>
                </div>

                <button type="submit" class="btn btn-login-submit">Login</button>
            </form>

            <div class="form-footer">
                Don't have an account? <a href="register.php">Register here</a>
            </div>
        </div>
    </div>

    <footer>
        <a href="#" class="footer-logo">EduMeet</a>
        <div class="footer-links">
            <a href="#">About Us</a>
            <a href="#">Contact</a>
            <a href="#">Privacy Policy</a>
            <a href="#">Terms of Service</a>
        </div>
        <div class="copyright">
            &copy; 2025 EduMeet System. All rights reserved.
        </div>
    </footer>

</body>
</html>