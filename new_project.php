<?php
session_start();
if (!isset($_SESSION['user'])) {
    header("Location: sign_in.php");
    exit();
}

// Database connection
$host = 'localhost';
$dbname = 'jume_portal';
$username = 'root';
$password = '';

try {
    $pdo = new PDO("mysql:host=$host;dbname=$dbname", $username, $password);
    $pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);

    // Get logged-in user
    $user_email = $_SESSION['user'];
    $stmt = $pdo->prepare("SELECT * FROM users WHERE email = :email");
    $stmt->bindParam(':email', $user_email);
    $stmt->execute();
    $user = $stmt->fetch(PDO::FETCH_ASSOC);
    
    if (!$user) {
        session_destroy();
        header("Location: sign_in.php");
        exit();
    }

} catch(PDOException $e) {
    die("Database error: " . $e->getMessage());
}

// Handle new project submission
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['create_project'])) {
    $project_name = trim($_POST['project_name']);
    $status = trim($_POST['status']);
    $progress = (int)$_POST['progress'];
    $deadline = $_POST['deadline'];

    if (!empty($project_name) && !empty($status)) {
        $stmt = $pdo->prepare("INSERT INTO projects (user_id, name, status, progress, deadline, created_at) VALUES (:user_id, :name, :status, :progress, :deadline, NOW())");
        $stmt->bindParam(':user_id', $user['id']);
        $stmt->bindParam(':name', $project_name);
        $stmt->bindParam(':status', $status);
        $stmt->bindParam(':progress', $progress);
        $stmt->bindParam(':deadline', $deadline);
        $stmt->execute();

        $success_message = "Project '$project_name' created successfully!";
    } else {
        $error_message = "Please fill in all required fields.";
    }
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>Create New Project - Jume IT Solution Consult</title>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <link rel="stylesheet" href="styles.css"> <!-- Reuse your existing styles -->
</head>
<body>
    <div class="container">
        <header>
            <div class="header-left">
                <div class="logo">
                    <div class="logo-img">J</div>
                    <span class="company-name">Jume IT Solution Consult</span>
                </div>
            </div>
            <div class="header-center">
                <nav class="navbar">
                    <ul>
                        <li><a href="dashboard.php"><i class="fas fa-home"></i> Dashboard</a></li>
                        <li><a href="new_project.php" class="active"><i class="fas fa-plus-circle"></i> New Project</a></li>
                        <li><a href="tickets.php"><i class="fas fa-ticket-alt"></i> Tickets</a></li>
                        <li><a href="profile.php"><i class="fas fa-user"></i> Profile</a></li>
                    </ul>
                </nav>
            </div>
            <div class="header-right">
                <div class="user-info">
                    <div class="user-avatar">
                        <?= !empty($user['avatar']) ? "<img src='{$user['avatar']}' alt='User Avatar'>" : strtoupper(substr($user['name'],0,1)) ?>
                    </div>
                    <span><?= htmlspecialchars($user['name']) ?></span>
                </div>
                <a href="sign_out.php" class="signout-btn"><i class="fas fa-sign-out-alt"></i></a>
            </div>
        </header>

        <div class="dashboard">
            <!-- Sidebar -->
            <div class="sidebar">
                <h3>Quick Links</h3>
                <ul class="quick-links">
                    <li><a href="dashboard.php"><i class="fas fa-tachometer-alt"></i> Dashboard</a></li>
                    <li><a href="new_project.php" class="active"><i class="fas fa-plus-circle"></i> New Project</a></li>
                    <li><a href="tickets.php"><i class="fas fa-ticket-alt"></i> Create Ticket</a></li>
                    <li><a href="settings.php"><i class="fas fa-cog"></i> Settings</a></li>
                </ul>
            </div>

            <!-- Main Content -->
            <div class="main-content">
                <div class="widget">
                    <h3 class="widget-title">Create New Project</h3>

                    <?php if (!empty($success_message)): ?>
                        <div class="alert alert-success"><?= $success_message ?></div>
                    <?php endif; ?>

                    <?php if (!empty($error_message)): ?>
                        <div class="alert alert-error"><?= $error_message ?></div>
                    <?php endif; ?>

                    <form method="POST" action="">
                        <div class="form-group">
                            <label for="project_name">Project Name</label>
                            <input type="text" id="project_name" name="project_name" required>
                        </div>

                        <div class="form-group">
                            <label for="status">Status</label>
                            <select id="status" name="status" required>
                                <option value="">Select Status</option>
                                <option value="Planning">Planning</option>
                                <option value="In Progress">In Progress</option>
                                <option value="Completed">Completed</option>
                            </select>
                        </div>

                        <div class="form-group">
                            <label for="progress">Progress (%)</label>
                            <input type="number" id="progress" name="progress" min="0" max="100" value="0" required>
                        </div>

                        <div class="form-group">
                            <label for="deadline">Deadline</label>
                            <input type="date" id="deadline" name="deadline" required>
                        </div>

                        <button type="submit" name="create_project" class="btn"><i class="fas fa-plus-circle"></i> Create Project</button>
                    </form>
                </div>
            </div>
        </div>

        <footer>
            <div class="footer-content">
                <div class="footer-logo">Jume IT Solution Consult</div>
                <div class="footer-links">
                    <a href="#">Privacy Policy</a>
                    <a href="#">Terms of Service</a>
                    <a href="#">Careers</a>
                    <a href="#">Contact Us</a>
                </div>
            </div>
            <div class="copyright">
                &copy; <?= date("Y") ?> Jume IT Solution Consult. All Rights Reserved.
            </div>
        </footer>
    </div>
</body>
</html>
