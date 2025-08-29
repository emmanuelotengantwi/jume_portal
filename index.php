<?php
session_start();
if (!isset($_SESSION['user'])) {
  header("Location: sign_in.php");
  exit();
}

// Database connection
$host = 'localhost';
$dbname = 'jume_portal';
$username = 'root'; // Change as needed
$password = ''; // Change as needed

try {
    $pdo = new PDO("mysql:host=$host;dbname=$dbname", $username, $password);
    $pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
    
    // Get user data
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
    
    // Get user projects
    $stmt = $pdo->prepare("SELECT * FROM projects WHERE user_id = :user_id ORDER BY created_at DESC LIMIT 3");
    $stmt->bindParam(':user_id', $user['id']);
    $stmt->execute();
    $projects = $stmt->fetchAll(PDO::FETCH_ASSOC);
    
    // Get user tickets
    $stmt = $pdo->prepare("SELECT * FROM tickets WHERE user_id = :user_id ORDER BY created_at DESC LIMIT 5");
    $stmt->bindParam(':user_id', $user['id']);
    $stmt->execute();
    $tickets = $stmt->fetchAll(PDO::FETCH_ASSOC);
    
} catch(PDOException $e) {
    // For demo purposes, we'll use sample data if DB connection fails
    $user = [
        'id' => 1,
        'name' => 'Demo User',
        'email' => $_SESSION['user'],
        'avatar' => null,
        'provider' => 'local'
    ];
    
    $projects = [
        ['id' => 1, 'name' => 'Website Redesign', 'status' => 'In Progress', 'progress' => 65, 'deadline' => '2023-12-15'],
        ['id' => 2, 'name' => 'E-commerce Platform', 'status' => 'Planning', 'progress' => 20, 'deadline' => '2024-02-28'],
        ['id' => 3, 'name' => 'Mobile App Development', 'status' => 'Completed', 'progress' => 100, 'deadline' => '2023-10-10']
    ];
    
    $tickets = [
        ['id' => 1, 'issue' => 'Website loading slowly', 'status' => 'Open', 'priority' => 'High', 'created_at' => '2023-11-01'],
        ['id' => 2, 'issue' => 'Email configuration issue', 'status' => 'In Progress', 'priority' => 'Medium', 'created_at' => '2023-11-05'],
        ['id' => 3, 'issue' => 'Database connection error', 'status' => 'Resolved', 'priority' => 'High', 'created_at' => '2023-10-28']
    ];
}

// Handle profile update
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['update_profile'])) {
    $name = $_POST['name'];
    $email = $_POST['email'];
    
    try {
        $stmt = $pdo->prepare("UPDATE users SET name = :name, email = :email WHERE id = :id");
        $stmt->bindParam(':name', $name);
        $stmt->bindParam(':email', $email);
        $stmt->bindParam(':id', $user['id']);
        $stmt->execute();
        
        // Update session if email changed
        if ($email !== $_SESSION['user']) {
            $_SESSION['user'] = $email;
        }
        
        // Refresh user data
        $stmt = $pdo->prepare("SELECT * FROM users WHERE id = :id");
        $stmt->bindParam(':id', $user['id']);
        $stmt->execute();
        $user = $stmt->fetch(PDO::FETCH_ASSOC);
        
        $success_message = "Profile updated successfully!";
    } catch(PDOException $e) {
        $error_message = "Error updating profile: " . $e->getMessage();
    }
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width, initial-scale=1.0">
  <title>Jume IT Solution Consult - Dashboard</title>
  <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
  <style>
    /* ===================== GLOBAL STYLES ===================== */
    * {
      margin: 0;
      padding: 0;
      box-sizing: border-box;
      font-family: 'Segoe UI', Tahoma, Geneva, Verdana, sans-serif;
    }

    body {
      background: linear-gradient(135deg, #f5f7fa 0%, #e4e8f0 100%);
      color: #333;
      line-height: 1.6;
      min-height: 100vh;
      display: flex;
      flex-direction: column;
      align-items: center;
      padding: 20px;
    }

    .container {
      width: 100%;
      max-width: 1200px;
    }

    /* ===================== HEADER ===================== */
    header {
      background: linear-gradient(135deg, #004080 0%, #0066cc 100%);
      color: #fff;
      padding: 15px 30px;
      display: flex;
      justify-content: space-between;
      align-items: center;
      width: 100%;
      border-radius: 10px 10px 0 0;
      box-shadow: 0 4px 12px rgba(0, 0, 0, 0.1);
      margin-bottom: 2px;
    }

    /* Split into 3 sections */
    .header-left, .header-center, .header-right {
      flex: 1;
      display: flex;
      align-items: center;
    }

    .header-center {
      justify-content: center;
    }

    .header-right {
      justify-content: flex-end;
    }

    /* Left: Logo + Name */
    .logo {
      display: flex;
      align-items: center;
      gap: 12px;
    }

    .logo-img {
      height: 50px;
      width: 50px;
      background: #fff;
      border-radius: 50%;
      display: flex;
      align-items: center;
      justify-content: center;
      font-weight: bold;
      color: #004080;
      font-size: 20px;
      box-shadow: 0 0 10px rgba(0, 0, 0, 0.2);
    }

    .company-name {
      font-size: 22px;
      font-weight: bold;
      white-space: nowrap;
    }

    /* Middle: Navigation */
    .navbar ul {
      list-style: none;
      display: flex;
      gap: 30px;
      margin: 0;
      padding: 0;
    }

    .navbar ul li a {
      color: #fff;
      font-weight: 500;
      text-decoration: none;
      padding: 8px 15px;
      border-radius: 6px;
      transition: all 0.3s ease;
    }

    .navbar ul li a:hover, .navbar ul li a.active {
      background-color: rgba(255, 255, 255, 0.15);
      transform: translateY(-2px);
    }

    /* Right: User info + Sign-out */
    .user-info {
      display: flex;
      align-items: center;
      gap: 10px;
      margin-right: 15px;
    }

    .user-avatar {
      width: 40px;
      height: 40px;
      border-radius: 50%;
      background: rgba(255, 255, 255, 0.2);
      display: flex;
      align-items: center;
      justify-content: center;
      font-weight: bold;
      overflow: hidden;
    }

    .user-avatar img {
      width: 100%;
      height: 100%;
      object-fit: cover;
    }

    .signout-btn {
      color: #fff;
      font-size: 22px;
      transition: all 0.3s ease;
      background: rgba(255, 255, 255, 0.1);
      border: none;
      border-radius: 50%;
      width: 45px;
      height: 45px;
      display: flex;
      align-items: center;
      justify-content: center;
      cursor: pointer;
      text-decoration: none;
    }

    .signout-btn:hover {
      color: #ff6b6b;
      background: rgba(255, 255, 255, 0.2);
      transform: scale(1.1);
    }

    /* ===================== DASHBOARD LAYOUT ===================== */
    .dashboard {
      display: grid;
      grid-template-columns: 1fr 3fr;
      gap: 25px;
      padding: 30px;
      background: #fff;
      border-radius: 0 0 10px 10px;
      box-shadow: 0 4px 12px rgba(0, 0, 0, 0.08);
      width: 100%;
    }

    /* Sidebar */
    .sidebar {
      background: #f9f9f9;
      padding: 25px;
      border-radius: 10px;
      box-shadow: 0 2px 8px rgba(0,0,0,0.06);
    }

    .sidebar h3 {
      color: #004080;
      margin-top: 0;
      margin-bottom: 20px;
      padding-bottom: 10px;
      border-bottom: 2px solid #004080;
    }

    .quick-links {
      list-style: none;
      margin-bottom: 30px;
    }

    .quick-links li {
      margin-bottom: 12px;
    }

    .quick-links a {
      color: #004080;
      text-decoration: none;
      display: block;
      padding: 10px 15px;
      background: #fff;
      border-radius: 6px;
      transition: all 0.3s ease;
      box-shadow: 0 2px 4px rgba(0,0,0,0.05);
    }

    .quick-links a:hover, .quick-links a.active {
      background: #004080;
      color: #fff;
      transform: translateX(5px);
    }

    .quick-links a i {
      margin-right: 10px;
      color: #0066cc;
    }

    .quick-links a:hover i, .quick-links a.active i {
      color: #fff;
    }

    /* Stats Widget */
    .stats-widget {
      background: linear-gradient(135deg, #004080, #0066cc);
      color: #fff;
      padding: 20px;
      border-radius: 10px;
      text-align: center;
    }

    .stat {
      margin: 15px 0;
    }

    .stat-value {
      font-size: 2rem;
      font-weight: bold;
      display: block;
    }

    .stat-label {
      font-size: 0.9rem;
      opacity: 0.8;
    }

    /* Main Content */
    .main-content {
      display: flex;
      flex-direction: column;
      gap: 25px;
    }

    /* Welcome Banner */
    .welcome-banner {
      background: linear-gradient(135deg, #007bff, #00c6ff);
      color: #fff;
      padding: 25px;
      border-radius: 10px;
      text-align: center;
    }

    .welcome-banner h1 {
      font-size: 2.2rem;
      margin-bottom: 10px;
    }

    .welcome-banner p {
      font-size: 1.1rem;
      max-width: 700px;
      margin: 0 auto;
    }

    /* Dashboard Widgets */
    .widget {
      background: #f9f9f9;
      padding: 25px;
      border-radius: 10px;
      box-shadow: 0 2px 8px rgba(0,0,0,0.06);
    }

    .widget-header {
      display: flex;
      justify-content: space-between;
      align-items: center;
      margin-bottom: 20px;
    }

    .widget-title {
      color: #004080;
      margin: 0;
      padding-bottom: 10px;
      border-bottom: 2px solid #004080;
    }

    .view-all {
      color: #0066cc;
      text-decoration: none;
      font-size: 0.9rem;
    }

    .view-all:hover {
      text-decoration: underline;
    }

    /* Tables */
    .data-table {
      width: 100%;
      border-collapse: collapse;
    }

    .data-table th, .data-table td {
      padding: 12px 15px;
      text-align: left;
      border-bottom: 1px solid #e0e0e0;
    }

    .data-table th {
      background-color: #f0f5ff;
      color: #004080;
      font-weight: 600;
    }

    .data-table tr:hover {
      background-color: #f5f8ff;
    }

    /* Status badges */
    .status-badge {
      display: inline-block;
      padding: 5px 10px;
      border-radius: 20px;
      font-size: 0.8rem;
      font-weight: 500;
    }

    .status-active, .status-completed, .status-resolved {
      background: #e6f7ee;
      color: #00a651;
    }

    .status-in-progress {
      background: #fff4e6;
      color: #ff8c1a;
    }

    .status-planning {
      background: #e6f0ff;
      color: #0066cc;
    }

    .status-open {
      background: #ffe6e6;
      color: #ff4d4d;
    }

    .priority-high {
      background: #ffe6e6;
      color: #ff4d4d;
      padding: 3px 8px;
      border-radius: 4px;
      font-size: 0.75rem;
    }

    .priority-medium {
      background: #fff4e6;
      color: #ff8c1a;
      padding: 3px 8px;
      border-radius: 4px;
      font-size: 0.75rem;
    }

    .priority-low {
      background: #e6f0ff;
      color: #0066cc;
      padding: 3px 8px;
      border-radius: 4px;
      font-size: 0.75rem;
    }

    /* Progress bars */
    .progress-bar {
      height: 10px;
      background: #e0e0e0;
      border-radius: 5px;
      overflow: hidden;
    }

    .progress-fill {
      height: 100%;
      background: linear-gradient(135deg, #007bff, #00c6ff);
      border-radius: 5px;
    }

    /* Forms */
    .form-group {
      margin-bottom: 20px;
    }

    .form-group label {
      display: block;
      margin-bottom: 8px;
      font-weight: 500;
      color: #004080;
    }

    .form-group input, .form-group textarea, .form-group select {
      width: 100%;
      padding: 12px 15px;
      border: 1px solid #ddd;
      border-radius: 6px;
      font-size: 1rem;
      transition: border-color 0.3s;
    }

    .form-group input:focus, .form-group textarea:focus, .form-group select:focus {
      border-color: #007bff;
      outline: none;
    }

    .btn {
      padding: 12px 20px;
      background: #004080;
      color: #fff;
      border: none;
      border-radius: 6px;
      cursor: pointer;
      font-size: 1rem;
      font-weight: 500;
      transition: background 0.3s;
    }

    .btn:hover {
      background: #0066cc;
    }

    .alert {
      padding: 15px;
      border-radius: 6px;
      margin-bottom: 20px;
    }

    .alert-success {
      background: #e6f7ee;
      color: #00a651;
      border: 1px solid #00a651;
    }

    .alert-error {
      background: #ffe6e6;
      color: #ff4d4d;
      border: 1px solid #ff4d4d;
    }

    /* ===================== FOOTER ===================== */
    footer {
      background: #004080;
      color: #fff;
      text-align: center;
      padding: 20px;
      margin-top: 30px;
      border-radius: 0 0 10px 10px;
      width: 100%;
    }

    .footer-content {
      display: flex;
      justify-content: space-between;
      align-items: center;
      max-width: 900px;
      margin: 0 auto;
    }

    .footer-logo {
      font-size: 1.2rem;
      font-weight: bold;
    }

    .footer-links a {
      color: #fff;
      margin: 0 12px;
      text-decoration: none;
      transition: color 0.3s;
    }

    .footer-links a:hover {
      color: #00c6ff;
      text-decoration: underline;
    }

    .social-icons {
      display: flex;
      gap: 15px;
    }

    .social-icons a {
      color: #fff;
      font-size: 1.2rem;
      transition: color 0.3s;
    }

    .social-icons a:hover {
      color: #00c6ff;
    }

    .copyright {
      margin-top: 20px;
      padding-top: 20px;
      border-top: 1px solid rgba(255, 255, 255, 0.2);
    }

    /* ===================== RESPONSIVE DESIGN ===================== */
    @media (max-width: 900px) {
      .dashboard {
        grid-template-columns: 1fr;
      }
      
      .navbar ul {
        gap: 15px;
      }
    }

    @media (max-width: 768px) {
      header {
        flex-direction: column;
        gap: 20px;
        padding: 20px;
      }
      
      .header-left, .header-center, .header-right {
        width: 100%;
        justify-content: center;
      }
      
      .navbar ul {
        flex-wrap: wrap;
        justify-content: center;
        gap: 10px;
      }
      
      .footer-content {
        flex-direction: column;
        gap: 15px;
      }
      
      .welcome-banner h1 {
        font-size: 1.8rem;
      }
    }

    @media (max-width: 480px) {
      body {
        padding: 10px;
      }
      
      .welcome-banner {
        padding: 20px 15px;
      }
      
      .welcome-banner h1 {
        font-size: 1.5rem;
      }
      
      .widget {
        padding: 20px;
      }
      
      .navbar ul {
        flex-direction: column;
        align-items: center;
        gap: 10px;
      }
      
      .navbar ul li {
        width: 100%;
        text-align: center;
      }
      
      .company-name {
        font-size: 18px;
      }
      
      .data-table {
        font-size: 0.85rem;
      }
      
      .data-table th, .data-table td {
        padding: 8px 10px;
      }
    }
  </style>
</head>
<body>
  <div class="container">
    <!-- HEADER -->
    <header>
      <div class="header-left">
        <div class="logo">
         <div class="logo-img">
  <img src="img/logo.png" alt="Logo">
</div>

          <span class="company-name">Jume IT Solution Consult</span>
        </div>
      </div>

      <div class="header-center">
        <nav class="navbar">
          <ul>
            <li><a href="#" class="active"><i class="fas fa-home"></i> Dashboard</a></li>
            <li><a href="new_project.php"><i class="fas fa-tasks"></i> Projects</a></li>
            <li><a href="#"><i class="fas fa-ticket-alt"></i> Tickets</a></li>
            <li><a href="#"><i class="fas fa-calendar"></i> Events</a></li>
            <li><a href="#"><i class="fas fa-user"></i> Profile</a></li>
          </ul>
        </nav>
      </div>

      <div class="header-right">
        <div class="user-info">
          <div class="user-avatar">
            <?php if (!empty($user['avatar'])): ?>
              <img src="<?= htmlspecialchars($user['avatar']) ?>" alt="User Avatar">
            <?php else: ?>
              <?= strtoupper(substr($user['name'], 0, 1)) ?>
            <?php endif; ?>
          </div>
          <span><?= htmlspecialchars($user['name']) ?></span>
        </div>
        <a href="sign_out.php" class="signout-btn" title="Sign Out">
          <i class="fas fa-sign-out-alt"></i>
        </a>
      </div>
    </header>

    <!-- DASHBOARD -->
    <div class="dashboard">
      <!-- Sidebar -->
      <div class="sidebar">
        <h3>Quick Links</h3>
        <ul class="quick-links">
          <li><a href="#" class="active"><i class="fas fa-tachometer-alt"></i> Dashboard</a></li>
          <li><a href="#"><i class="fas fa-plus-circle"></i> New Project</a></li>
          <li><a href="#"><i class="fas fa-ticket-alt"></i> Create Ticket</a></li>
          <li><a href="#"><i class="fas fa-cog"></i> Settings</a></li>
          <li><a href="#"><i class="fas fa-question-circle"></i> Help & Support</a></li>
        </ul>

        <div class="stats-widget">
          <h3>Your Stats</h3>
          <div class="stat">
            <span class="stat-value"><?= count($projects) ?></span>
            <span class="stat-label">Active Projects</span>
          </div>
          <div class="stat">
            <span class="stat-value"><?= count($tickets) ?></span>
            <span class="stat-label">Support Tickets</span>
          </div>
          <div class="stat">
            <span class="stat-value"><?= $user['provider'] ?? 'Local' ?></span>
            <span class="stat-label">Login Method</span>
          </div>
        </div>
      </div>

      <!-- Main Content -->
      <div class="main-content">
        <!-- Welcome Banner -->
        <div class="welcome-banner">
          <h1>Welcome to Jume IT Solution Consult</h1>
          <p>Hello, <?= htmlspecialchars($user['name']) ?> 👋</p>
          <p>Here's what's happening with your projects and support tickets.</p>
        </div>

        <!-- Profile Update Form -->
        <div class="widget">
          <h3 class="widget-title">Your Profile</h3>
          <?php if (isset($success_message)): ?>
            <div class="alert alert-success"><?= $success_message ?></div>
          <?php endif; ?>
          <?php if (isset($error_message)): ?>
            <div class="alert alert-error"><?= $error_message ?></div>
          <?php endif; ?>
          <form method="POST" action="">
            <div class="form-group">
              <label for="name">Full Name</label>
              <input type="text" id="name" name="name" value="<?= htmlspecialchars($user['name']) ?>" required>
            </div>
            <div class="form-group">
              <label for="email">Email Address</label>
              <input type="email" id="email" name="email" value="<?= htmlspecialchars($user['email']) ?>" required>
            </div>
            <button type="submit" name="update_profile" class="btn">Update Profile</button>
          </form>
        </div>

        <!-- Recent Projects Widget -->
        <div class="widget">
          <div class="widget-header">
            <h3 class="widget-title">Your Projects</h3>
            <a href="#" class="view-all">View All</a>
          </div>
          <?php if (count($projects) > 0): ?>
            <table class="data-table">
              <thead>
                <tr>
                  <th>Project Name</th>
                  <th>Status</th>
                  <th>Progress</th>
                  <th>Deadline</th>
                </tr>
              </thead>
              <tbody>
                <?php foreach ($projects as $project): ?>
                <tr>
                  <td><?= htmlspecialchars($project['name']) ?></td>
                  <td>
                    <span class="status-badge status-<?= strtolower(str_replace(' ', '-', $project['status'])) ?>">
                      <?= $project['status'] ?>
                    </span>
                  </td>
                  <td>
                    <div class="progress-bar">
                      <div class="progress-fill" style="width: <?= $project['progress'] ?>%"></div>
                    </div>
                    <small><?= $project['progress'] ?>% complete</small>
                  </td>
                  <td><?= $project['deadline'] ?></td>
                </tr>
                <?php endforeach; ?>
              </tbody>
            </table>
          <?php else: ?>
            <p>You don't have any projects yet.</p>
          <?php endif; ?>
        </div>

        <!-- Support Tickets Widget -->
        <div class="widget">
          <div class="widget-header">
            <h3 class="widget-title">Your Support Tickets</h3>
            <a href="#" class="view-all">View All</a>
          </div>
          <?php if (count($tickets) > 0): ?>
            <table class="data-table">
              <thead>
                <tr>
                  <th>Ticket ID</th>
                  <th>Issue</th>
                  <th>Status</th>
                  <th>Priority</th>
                  <th>Created</th>
                </tr>
              </thead>
              <tbody>
                <?php foreach ($tickets as $ticket): ?>
                <tr>
                  <td>#<?= $ticket['id'] ?></td>
                  <td><?= htmlspecialchars($ticket['issue']) ?></td>
                  <td>
                    <span class="status-badge status-<?= strtolower(str_replace(' ', '-', $ticket['status'])) ?>">
                      <?= $ticket['status'] ?>
                    </span>
                  </td>
                  <td>
                    <span class="priority-<?= strtolower($ticket['priority']) ?>">
                      <?= $ticket['priority'] ?>
                    </span>
                  </td>
                  <td><?= $ticket['created_at'] ?></td>
                </tr>
                <?php endforeach; ?>
              </tbody>
            </table>
          <?php else: ?>
            <p>You don't have any support tickets yet.</p>
          <?php endif; ?>
        </div>
      </div>
    </div>

    <!-- FOOTER -->
    <footer>
      <div class="footer-content">
        <div class="footer-logo">Jume IT Solution Consult</div>
        <div class="footer-links">
          <a href="#">Privacy Policy</a>
          <a href="#">Terms of Service</a>
          <a href="#">Careers</a>
          <a href="#">Contact Us</a>
        </div>
        <div class="social-icons">
          <a href="#"><i class="fab fa-facebook"></i></a>
          <a href="#"><i class="fab fa-twitter"></i></a>
          <a href="#"><i class="fab fa-linkedin"></i></a>
          <a href="#"><i class="fab fa-instagram"></i></a>
        </div>
      </div>
      <div class="copyright">
        &copy; <?= date("Y") ?> Jume IT Solution Consult. All Rights Reserved.
      </div>
    </footer>
  </div>

  <script>
    // Simple JavaScript for interactive elements
    document.addEventListener('DOMContentLoaded', function() {
      // Add active class to clicked nav items
      const navItems = document.querySelectorAll('.navbar a');
      navItems.forEach(item => {
        item.addEventListener('click', function(e) {
          e.preventDefault();
          navItems.forEach(i => i.classList.remove('active'));
          this.classList.add('active');
        });
      });
      
      // Animate progress bars
      const progressBars = document.querySelectorAll('.progress-fill');
      progressBars.forEach(bar => {
        const width = bar.style.width;
        bar.style.width = '0';
        setTimeout(() => {
          bar.style.transition = 'width 1s ease-in-out';
          bar.style.width = width;
        }, 300);
      });
    });
  </script>
</body>
</html>