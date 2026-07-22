<?php
// User profile settings page
session_start();
include 'db.php';

// Check if user is logged in
if (!isset($_SESSION['user_id'])) {
    header("Location: login.php");
    exit;
}

$user_id = $_SESSION['user_id'];
$message = "";

// Fetch current user information
$stmt = $pdo->prepare("SELECT * FROM users WHERE userID = ?");
$stmt->execute([$user_id]);
$user = $stmt->fetch(PDO::FETCH_ASSOC);

// Handle form submission
if ($_SERVER["REQUEST_METHOD"] == "POST") {
    
    // Update username logic
    if (isset($_POST['username'])) {
        $new_username = trim($_POST['username']);
        
        // Check if username has changed
        if ($new_username != $user['userName']) {
            // Ensure username is unique
            $checkName = $pdo->prepare("SELECT COUNT(*) FROM users WHERE userName = ? AND userID != ?");
            $checkName->execute([$new_username, $user_id]);
            
            if ($checkName->fetchColumn() > 0) {
                $message = "<div class='error-msg'>This username is already taken!</div>";
            } else {
                $updateName = $pdo->prepare("UPDATE users SET userName = ? WHERE userID = ?");
                $updateName->execute([$new_username, $user_id]);
                
                // Update session variable to reflect change immediately
                $_SESSION['username'] = $new_username;
                $user['userName'] = $new_username;
                $message = "<div class='success-msg'>Username updated!</div>";
            }
        }
    }

    // Update email logic
    if (isset($_POST['email']) && strpos($message, 'error') === false) {
        $new_email = trim($_POST['email']);
        
        // Check if email has changed
        if ($new_email != $user['email']) {
            // Ensure email is unique
            $checkEmail = $pdo->prepare("SELECT COUNT(*) FROM users WHERE email = ? AND userID != ?");
            $checkEmail->execute([$new_email, $user_id]);

            if ($checkEmail->fetchColumn() > 0) {
                $message = "<div class='error-msg'>This email is already in use!</div>";
            } else {
                $updateEmail = $pdo->prepare("UPDATE users SET email = ? WHERE userID = ?");
                $updateEmail->execute([$new_email, $user_id]);
                $user['email'] = $new_email;
                $message = "<div class='success-msg'>Profile info updated!</div>";
            }
        }
    }

    // Update skills (only for tutors)
    if ($user['role'] == 'tutor' && isset($_POST['skills'])) {
        $skills = trim($_POST['skills']);
        $updateSkill = $pdo->prepare("UPDATE users SET skills = ? WHERE userID = ?");
        $updateSkill->execute([$skills, $user_id]);
    }

    // Change password logic
    if (!empty($_POST['new_password'])) {
        $old_password = $_POST['old_password'];
        $new_password = $_POST['new_password'];

        if (empty($old_password)) {
            $message = "<div class='error-msg'>You must enter your old password to change it!</div>";
        } 
        elseif (!password_verify($old_password, $user['password'])) {
            $message = "<div class='error-msg'>Old password is wrong!</div>";
        } 
        else {
            // Hash new password before saving
            $new_password_hash = password_hash($new_password, PASSWORD_DEFAULT);
            $updatePass = $pdo->prepare("UPDATE users SET password = ? WHERE userID = ?");
            $updatePass->execute([$new_password_hash, $user_id]);
            $message = "<div class='success-msg'>Password changed successfully!</div>";
        }
    }

    // Reload page to show updated info
    if (strpos($message, 'error') === false && !empty($message)) {
        header("Refresh: 1");
    }
}

// Include header (CSS and Navbar)
include 'header.php'; 
?>

<style>
    /* Force light background */
    body {
        background-color: #f0f4f8 !important;
        background-image: none !important;
        color: #333 !important;
    }

    /* Remove dark overlay if present */
    body::before { display: none !important; }

    /* Centering wrapper */
    .form-wrapper {
        min-height: 90vh;
        display: flex;
        justify-content: center;
        align-items: center;
        padding-top: 100px;
        padding-bottom: 50px;
    }

    /* White Profile Card Style */
    .profile-card {
        background: #ffffff;
        width: 100%;
        max-width: 550px; /* Slightly wider for profile settings */
        padding: 40px;
        border-radius: 20px;
        box-shadow: 0 10px 40px rgba(0,0,0,0.08);
        border: 1px solid #e0e6ed;
        position: relative;
        z-index: 10;
    }

    .profile-card h2 {
        text-align: center;
        color: #2c3e50;
        margin-bottom: 30px;
        font-weight: 700;
        border-bottom: 2px solid #f0f4f8;
        padding-bottom: 15px;
    }

    /* Input Groups */
    .input-group {
        margin-bottom: 20px;
        text-align: left;
    }

    .input-group label {
        font-weight: 600;
        display: block;
        margin-bottom: 8px;
        color: #555;
    }

    .input-group input {
        width: 100%;
        padding: 12px 15px;
        border: 2px solid #eef2f7;
        background-color: #f9fbfc;
        border-radius: 10px;
        color: #333;
        font-size: 1em;
        outline: none;
        transition: 0.3s;
    }

    .input-group input:focus {
        border-color: #4361ee;
        background-color: #fff;
    }

    /* Password Change Section Styling */
    .password-section { 
        background: #f8f9fa; 
        padding: 20px; 
        border: 1px solid #e9ecef; 
        border-radius: 12px; 
        margin-top: 30px; 
        margin-bottom: 20px;
    }
    
    .password-section h3 { 
        margin-top: 0; 
        font-size: 1.1em; 
        color: #4361ee; 
        margin-bottom: 10px;
    }

    /* Save Button */
    .btn-save {
        width: 100%;
        padding: 14px;
        background: #4361ee; 
        color: white;
        border: none;
        border-radius: 50px;
        cursor: pointer;
        font-size: 1.1em;
        font-weight: 600;
        margin-top: 10px;
        transition: 0.3s;
        box-shadow: 0 4px 10px rgba(67, 97, 238, 0.3);
    }
    .btn-save:hover { background: #304ffe; transform: translateY(-2px); }

    /* Message Boxes */
    .error-msg {
        background: #ffe5e5; color: #d63031; padding: 10px; border-radius: 8px; margin-bottom: 20px; border: 1px solid #ff7675; text-align: center;
    }
    .success-msg {
        background: #d4edda; color: #155724; padding: 10px; border-radius: 8px; margin-bottom: 20px; border: 1px solid #c3e6cb; text-align: center;
    }

    /* Back Link */
    .back-link-wrapper { text-align: center; margin-top: 20px; }
    .back-link { color: #666; text-decoration: none; font-size: 0.9em; transition: 0.3s; }
    .back-link:hover { color: #4361ee; text-decoration: underline; }
</style>

<div class="form-wrapper">
    <div class="profile-card">
        <h2>Edit Profile</h2>
        <?php echo $message; ?>
        
        <form method="POST" action="">
            <div class="input-group">
                <label>Username</label>
                <input type="text" name="username" value="<?php echo htmlspecialchars($user['userName']); ?>" required>
            </div>

            <div class="input-group">
                <label>Email Address</label>
                <input type="email" name="email" value="<?php echo htmlspecialchars($user['email']); ?>" required>
            </div>

            <?php if ($user['role'] == 'tutor'): ?>
                <div class="input-group">
                    <label>Subjects / Skills you teach</label>
                    <input type="text" name="skills" value="<?php echo htmlspecialchars($user['skills']); ?>">
                </div>
            <?php endif; ?>

            <div class="password-section">
                <h3>🔒 Change Password</h3>
                <small style="color:#666; display:block; margin-bottom:15px;">Fill these only if you want to change your password.</small>
                
                <div class="input-group">
                    <label>Current (Old) Password</label>
                    <input type="password" name="old_password" placeholder="Enter current password">
                </div>

                <div class="input-group">
                    <label>New Password</label>
                    <input type="password" name="new_password" placeholder="Enter new password">
                </div>
            </div>

            <button type="submit" class="btn-save">Save Changes</button>
        </form>
        
        <div class="back-link-wrapper">
            <a href="home.php" class="back-link">&larr; Back to Dashboard</a>
        </div>
    </div>
</div>

<?php 
// Include footer 
include 'footer.php'; 
?>