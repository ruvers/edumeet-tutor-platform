<?php
// Admin dashboard main file
session_start();
include 'db.php';

// Security check: Only admins allowed
if (!isset($_SESSION['user_id']) || $_SESSION['role'] != 'admin') {
    die("Access Denied! You are not authorized.");
}

// Delete user logic
if (isset($_GET['action']) && $_GET['action'] == 'delete_user' && isset($_GET['id'])) {
    // Prevent admin from deleting themselves
    if ($_GET['id'] != $_SESSION['user_id']) {
        $pdo->prepare("DELETE FROM users WHERE userID = ?")->execute([$_GET['id']]);
        header("Location: admin.php?msg=user_deleted");
        exit;
    }
}

// Delete report logic
if (isset($_GET['action']) && $_GET['action'] == 'delete_report' && isset($_GET['id'])) {
    $pdo->prepare("DELETE FROM reports WHERE reportID = ?")->execute([$_GET['id']]);
    header("Location: admin.php?msg=report_deleted");
    exit;
}

// Delete subject logic
if (isset($_GET['action']) && $_GET['action'] == 'delete_subject' && isset($_GET['id'])) {
    $pdo->prepare("DELETE FROM subjects WHERE id = ?")->execute([$_GET['id']]);
    header("Location: admin.php?msg=subject_deleted");
    exit;
}

// Add new subject logic
if ($_SERVER['REQUEST_METHOD'] == 'POST' && isset($_POST['add_subject'])) {
    $subName = trim($_POST['subject_name']);
    if (!empty($subName)) {
        try {
            $stmt = $pdo->prepare("INSERT INTO subjects (name) VALUES (?)");
            $stmt->execute([$subName]);
            header("Location: admin.php?msg=subject_added");
            exit;
        } catch (PDOException $e) {
            // Ignore if duplicate subject exists
        }
    }
}

include 'header.php';
?>

<style>
    /* Admin layout styles */
    .admin-container { width: 100%; max-width: 1200px; margin: 100px auto 50px; padding: 0 20px; color: white; }
    
    .admin-header {
        display: flex; justify-content: space-between; align-items: center; margin-bottom: 40px;
        background: rgba(0, 0, 0, 0.4); padding: 20px 30px; border-radius: 15px;
        border: 1px solid rgba(255, 255, 255, 0.1); backdrop-filter: blur(10px);
    }
    .admin-header h1 { margin: 0; font-size: 2em; }

    /* Grid system */
    .admin-grid { display: grid; grid-template-columns: 2fr 1fr; gap: 30px; }
    @media (max-width: 900px) { .admin-grid { grid-template-columns: 1fr; } }

    /* Panel cards */
    .glass-panel {
        background: rgba(255, 255, 255, 0.05); backdrop-filter: blur(15px);
        border-radius: 15px; border: 1px solid rgba(255, 255, 255, 0.1);
        padding: 25px; margin-bottom: 30px; overflow-x: auto;
    }
    
    .panel-title { font-size: 1.4em; font-weight: bold; margin-bottom: 20px; border-left: 4px solid #4c6ef5; padding-left: 10px; color: white; }
    .title-red { border-color: #e74c3c; }
    .title-green { border-color: #2ecc71; }

    /* Data tables */
    table { width: 100%; border-collapse: collapse; min-width: 600px; }
    th { text-align: left; padding: 12px; color: #aaa; font-size: 0.9em; border-bottom: 1px solid rgba(255,255,255,0.1); }
    td { padding: 12px; border-bottom: 1px solid rgba(255,255,255,0.05); vertical-align: middle; color: #eee; }
    tr:hover td { background: rgba(255,255,255,0.05); }

    /* Form elements */
    .admin-input { background: rgba(0,0,0,0.3); border: 1px solid rgba(255,255,255,0.2); padding: 10px; border-radius: 5px; color: white; width: 70%; }
    .admin-btn-add { background: #2ecc71; color: white; border: none; padding: 10px 15px; border-radius: 5px; cursor: pointer; font-weight: bold; }
    
    /* Buttons */
    .btn-small { padding: 5px 10px; border-radius: 5px; text-decoration: none; font-size: 0.8em; font-weight: bold; display: inline-block; }
    .btn-red { background: rgba(231, 76, 60, 0.2); color: #e74c3c; border: 1px solid #e74c3c; }
    .btn-red:hover { background: #e74c3c; color: white; }
    
    /* Subject tags */
    .subject-list { display: flex; flex-wrap: wrap; gap: 10px; }
    .subject-tag { background: rgba(76, 110, 245, 0.2); padding: 5px 12px; border-radius: 20px; border: 1px solid #4c6ef5; display: flex; align-items: center; gap: 8px; font-size: 0.9em; }
    .subject-delete { color: #ff6b6b; text-decoration: none; font-weight: bold; font-size: 1.1em; cursor: pointer; }
    .subject-delete:hover { color: white; }

    /* Messages */
    .msg-success { background: rgba(46, 204, 113, 0.2); border: 1px solid #2ecc71; color: #2ecc71; padding: 15px; border-radius: 10px; margin-bottom: 20px; text-align: center; }
</style>

<div class="admin-container">
    
    <div class="admin-header">
        <h1>🛠️ Admin Control Panel</h1>
        <a href="logout.php" class="btn-small btn-red" style="padding:10px 20px;">🚪 Logout</a>
    </div>

    <?php if(isset($_GET['msg'])): ?>
        <div class="msg-success">
            <?php 
                if($_GET['msg'] == 'subject_added') echo "New subject added successfully!";
                elseif($_GET['msg'] == 'user_deleted') echo "User deleted successfully!";
                elseif($_GET['msg'] == 'report_deleted') echo "Report marked as resolved/deleted!";
                elseif($_GET['msg'] == 'subject_deleted') echo "Subject deleted!";
            ?>
        </div>
    <?php endif; ?>

    <div class="admin-grid">
        
        <div class="left-col">
            
            <div class="glass-panel">
                <div class="panel-title title-red">🚩 User Reports</div>
                <table>
                    <thead>
                        <tr>
                            <th>From</th>
                            <th>Reported User</th>
                            <th>Reason</th>
                            <th>Date</th>
                            <th>Action</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php
                        $repSql = "SELECT r.reportID, r.reason, r.created_at, 
                                   u1.userName as reporterName, u2.userName as reportedName, u2.userID as reportedID
                                   FROM reports r
                                   JOIN users u1 ON r.reporterID = u1.userID
                                   JOIN users u2 ON r.reportedID = u2.userID
                                   ORDER BY r.created_at DESC";
                        $repStmt = $pdo->query($repSql);

                        if ($repStmt->rowCount() > 0) {
                            while ($rep = $repStmt->fetch(PDO::FETCH_ASSOC)) {
                                echo "<tr>";
                                echo "<td>" . htmlspecialchars($rep['reporterName']) . "</td>";
                                echo "<td style='color:#e74c3c; font-weight:bold;'>" . htmlspecialchars($rep['reportedName']) . "</td>";
                                echo "<td>" . htmlspecialchars($rep['reason']) . "</td>";
                                echo "<td style='font-size:0.8em; opacity:0.7;'>" . date("d.m.Y", strtotime($rep['created_at'])) . "</td>";
                                echo "<td>
                                        <a href='admin.php?action=delete_user&id=" . $rep['reportedID'] . "' class='btn-small btn-red' onclick='return confirm(\"Ban this user?\");'>Ban User</a>
                                        <a href='admin.php?action=delete_report&id=" . $rep['reportID'] . "' style='color:#aaa; font-size:0.8em; margin-left:5px;'>Dismiss</a>
                                      </td>";
                                echo "</tr>";
                            }
                        } else {
                            echo "<tr><td colspan='5' style='text-align:center; color:#999; padding:20px;'>No pending reports. Great!</td></tr>";
                        }
                        ?>
                    </tbody>
                </table>
            </div>

            <div class="glass-panel">
                <div class="panel-title">👥 All Users List</div>
                <table>
                    <thead>
                        <tr>
                            <th>ID</th>
                            <th>Name</th>
                            <th>Role</th>
                            <th>Email</th>
                            <th>Action</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php
                        // Fetch latest 20 users
                        $users = $pdo->query("SELECT * FROM users ORDER BY userID DESC LIMIT 20");
                        while ($u = $users->fetch(PDO::FETCH_ASSOC)) {
                            $roleColor = ($u['role'] == 'admin') ? '#f39c12' : (($u['role'] == 'tutor') ? '#4c6ef5' : '#2ecc71');
                            echo "<tr>";
                            echo "<td>#" . $u['userID'] . "</td>";
                            echo "<td>" . htmlspecialchars($u['userName']) . "</td>";
                            echo "<td style='color:$roleColor; font-weight:bold; text-transform:uppercase; font-size:0.8em;'>" . $u['role'] . "</td>";
                            echo "<td>" . htmlspecialchars($u['email']) . "</td>";
                            echo "<td>";
                            if ($u['role'] != 'admin') {
                                echo "<a href='admin.php?action=delete_user&id=" . $u['userID'] . "' class='btn-small btn-red' onclick='return confirm(\"Delete this user?\");'>Delete</a>";
                            }
                            echo "</td>";
                            echo "</tr>";
                        }
                        ?>
                    </tbody>
                </table>
                <div style="text-align:center; font-size:0.8em; opacity:0.5; margin-top:10px;">Showing last 20 registered users</div>
            </div>

        </div>

        <div class="right-col">
            
            <div class="glass-panel">
                <div class="panel-title title-green">📚 Manage Subjects</div>
                
                <form method="POST" action="" style="display:flex; gap:5px; margin-bottom:20px;">
                    <input type="text" name="subject_name" class="admin-input" placeholder="New Subject Name" required>
                    <button type="submit" name="add_subject" class="admin-btn-add">Add</button>
                </form>

                <div class="subject-list">
                    <?php
                    $subStmt = $pdo->query("SELECT * FROM subjects ORDER BY name ASC");
                    while ($sub = $subStmt->fetch(PDO::FETCH_ASSOC)) {
                        echo "<div class='subject-tag'>";
                        echo htmlspecialchars($sub['name']);
                        echo "<a href='admin.php?action=delete_subject&id=" . $sub['id'] . "' class='subject-delete' onclick='return confirm(\"Delete this subject?\");'>&times;</a>";
                        echo "</div>";
                    }
                    ?>
                </div>
            </div>

            <div class="glass-panel">
                <div class="panel-title">📊 Quick Stats</div>
                <div style="display:flex; flex-direction:column; gap:15px;">
                    <?php
                    $stats = [
                        'Total Users' => $pdo->query("SELECT COUNT(*) FROM users")->fetchColumn(),
                        'Total Lessons' => $pdo->query("SELECT COUNT(*) FROM requests")->fetchColumn(),
                        'Active Reports' => $pdo->query("SELECT COUNT(*) FROM reports")->fetchColumn()
                    ];
                    foreach($stats as $label => $val) {
                        echo "<div style='display:flex; justify-content:space-between; border-bottom:1px solid rgba(255,255,255,0.1); padding-bottom:10px;'>";
                        echo "<span>$label</span> <strong style='color:#4c6ef5; font-size:1.2em;'>$val</strong>";
                        echo "</div>";
                    }
                    ?>
                </div>
            </div>

        </div>
    </div>

</div>

<?php include 'footer.php'; ?>