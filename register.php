<?php
// register.php (MODULAR THEME APPLIED)
session_start();
include 'db.php'; // Database connection

// Redirect if already logged in
if (isset($_SESSION['user_id'])) {
    header("Location: home.php");
    exit;
}

$message = "";

// Subject List
// Fetch subjects from database
$stmt_subjects = $pdo->query("SELECT name FROM subjects ORDER BY name ASC");
$subjectList = $stmt_subjects->fetchAll(PDO::FETCH_COLUMN);

if ($_SERVER["REQUEST_METHOD"] == "POST") {
    $username = trim($_POST['username']);
    $email = trim($_POST['email']);
    $password = $_POST['password'];
    $role = $_POST['role'];
    
    // PROCESS SKILLS (Convert checkbox array to string)
    $skills = NULL;
    if ($role == 'tutor' && isset($_POST['skills']) && is_array($_POST['skills'])) {
        $skills = implode(", ", $_POST['skills']);
    }

    // Email check
    $check = $pdo->prepare("SELECT COUNT(*) FROM users WHERE email = ?");
    $check->execute([$email]);
    
    if ($check->fetchColumn() > 0) {
        $message = "<div class='error-msg'>This email is already registered!</div>";
    } else {
        $hashed_password = password_hash($password, PASSWORD_DEFAULT);
        
        $sql = "INSERT INTO users (userName, email, password, role, skills) VALUES (?, ?, ?, ?, ?)";
        $stmt = $pdo->prepare($sql);
        
        if ($stmt->execute([$username, $email, $hashed_password, $role, $skills])) {
            // Redirect to login if successful
            header("Location: login.php");
            exit;
        } else {
            $message = "<div class='error-msg'>Something went wrong!</div>";
        }
    }
}

// Include header (CSS and Navbar come from here)
include 'header.php'; 
?>

<style>
    /* Override global body styles to force light theme */
    body {
        background-color: #f4f7f6 !important;
        background-image: none !important;
        color: #333 !important;
    }
    
    /* Remove the dark overlay from header */
    body::before {
        display: none !important;
    }

    /* Center the form vertically */
    .form-wrapper {
        min-height: 90vh;
        display: flex;
        justify-content: center;
        align-items: center;
        padding-top: 80px;
        padding-bottom: 40px;
    }

    /* Make form slightly wider so checkboxes fit - Updated to White Card style */
    .register-card {
        background-color: #ffffff;
        width: 100%;
        max-width: 500px; 
        padding: 40px;
        border-radius: 20px;
        box-shadow: 0 10px 40px rgba(0,0,0,0.08);
        border: 1px solid #e0e6ed;
        text-align: center;
        position: relative;
        z-index: 10;
    }

    /* Card Title Style */
    .register-card h2 {
        color: #4361ee;
        font-weight: 700;
        margin-bottom: 30px;
        font-family: 'Poppins', sans-serif;
    }

    /* Input container style */
    .input-group {
        margin-bottom: 20px;
        text-align: left;
    }

    .input-group label {
        display: block;
        margin-bottom: 8px;
        font-weight: 600;
        color: #555; /* Darker text for visibility */
        font-size: 0.9em;
    }

    /* Select box should look like inputs - Updated colors for light theme */
    .input-group input, 
    select {
        width: 100%;
        padding: 12px 15px;
        border-radius: 10px;
        border: 2px solid #eef2f7;
        background-color: #f9fbfc;
        color: #333; /* Dark text */
        font-size: 1em;
        outline: none;
        transition: 0.3s;
    }

    /* Input focus effect */
    .input-group input:focus, 
    select:focus {
        border-color: #4361ee;
        background-color: #ffffff;
    }

    select option {
        background-color: #ffffff; /* White background options */
        color: #333;
    }

    /* Skills Area - Updated background */
    #skillsField {
        display: none; /* Hidden initially */
        margin-bottom: 20px;
        text-align: left;
        background: #f8f9fa;
        padding: 15px;
        border-radius: 10px;
        border: 1px solid #e9ecef;
    }
    
    .skills-label {
        font-size: 0.9em;
        margin-bottom: 10px;
        display: block;
        color: #4361ee;
        font-weight: bold;
    }
    
    .skills-container {
        display: grid;
        grid-template-columns: 1fr 1fr; /* Two columns */
        gap: 10px;
        max-height: 200px;
        overflow-y: auto;
    }
    
    .skills-container label {
        display: flex;
        align-items: center;
        font-size: 0.9em;
        cursor: pointer;
        color: #555; /* Dark text */
    }
    
    .skills-container input[type="checkbox"] {
        width: auto;
        margin-right: 8px;
        transform: scale(1.1); /* Make checkbox slightly larger */
    }
    
    /* Register Button Style */
    .btn-register-submit {
        background-color: #4361ee;
        color: white;
        padding: 12px;
        border-radius: 50px;
        border: none;
        width: 100%;
        font-weight: 600;
        font-size: 1.1em;
        cursor: pointer;
        box-shadow: 0 4px 10px rgba(67, 97, 238, 0.3);
        transition: 0.3s;
        margin-top: 10px;
    }
    
    .btn-register-submit:hover {
        background-color: #304ffe;
        transform: translateY(-2px);
    }

    /* Footer links */
    .form-footer { margin-top: 20px; font-size: 0.9em; color: #666; }
    .form-footer a { color: #4361ee; text-decoration: none; font-weight: bold; }

    /* Error message style */
    .error-msg {
        background: #ffe5e5;
        color: #d63031;
        padding: 10px;
        border-radius: 5px;
        margin-bottom: 15px;
        border: 1px solid #ff7675;
    }
</style>

<div class="form-wrapper">
    <div class="register-card">
        <h2>Create Account</h2>
        
        <?php echo $message; ?>

        <form method="POST" action="">
            
            <div class="input-group">
                <label>Full Name</label>
                <input type="text" name="username" placeholder="Enter your full name" required>
            </div>

            <div class="input-group">
                <label>Email Address</label>
                <input type="email" name="email" placeholder="Enter your email" required>
            </div>

            <div class="input-group">
                <label>Password</label>
                <input type="password" name="password" placeholder="Create a password" required>
            </div>
            
            <div class="input-group">
                <label>I am a...</label>
                <select name="role" id="roleSelect">
                    <option value="student">Student</option>
                    <option value="tutor">Tutor</option>
                </select>
            </div>

            <div id="skillsField">
                <span class="skills-label">Select Subjects you teach:</span>
                <div class="skills-container">
                    <?php foreach ($subjectList as $subject): ?>
                        <label>
                            <input type="checkbox" name="skills[]" value="<?php echo $subject; ?>"> 
                            <?php echo $subject; ?>
                        </label>
                    <?php endforeach; ?>
                </div>
            </div>

            <button type="submit" class="btn-register-submit">Register</button>
        </form>

        <div class="form-footer">
            Already have an account? <a href="login.php">Login here</a>
        </div>
    </div>
</div>

<script>
    const roleSelect = document.getElementById('roleSelect');
    const skillsField = document.getElementById('skillsField');

    // Check on page load
    if (roleSelect.value === 'tutor') {
        skillsField.style.display = 'block';
    }

    roleSelect.addEventListener('change', function() {
        if (this.value === 'tutor') {
            skillsField.style.display = 'block';
        } else {
            skillsField.style.display = 'none';
        }
    });
</script>

<?php
include 'footer.php'; // Include footer
?>