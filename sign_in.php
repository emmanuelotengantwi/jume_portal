<?php
session_start();
include 'db.php'; // make sure this only contains DB connection code, no output

$error = "";

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $email = trim($_POST['email']);
    $password = trim($_POST['password']);

    if ($email && $password) {
        $stmt = $conn->prepare("SELECT * FROM users WHERE email = ?");
        $stmt->bind_param("s", $email);
        $stmt->execute();
        $result = $stmt->get_result();
        $user = $result->fetch_assoc();

        if ($user && password_verify($password, $user['password'])) {
            // Store email and ID in session
            $_SESSION['user'] = $user['email'];
            $_SESSION['user_id'] = $user['id'];

            // Redirect to dashboard
            header("Location: index.php");
            exit();
        } else {
            $error = "Invalid email or password.";
        }
    } else {
        $error = "Please fill in all fields.";
    }
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>Sign In</title>
    <link rel="stylesheet" href="styles.css">
    <link rel="stylesheet" href="sign_in.css">
</head>
<body>
    <div class="container">
        <img src="img/logo.png" alt="Logo" style="width: 140px; height: auto;">

        <h2>Sign In</h2>
        <?php if ($error): ?>
            <div class="error"><?= htmlspecialchars($error) ?></div>
        <?php endif; ?>
        <form method="POST" action="">
            <input type="email" name="email" placeholder="Email" required>
            <input type="password" name="password" placeholder="Password" required>
            <button type="submit">Sign In</button>
        </form>
        <p>Don't have an account? <a href="sign_up.php">Sign Up</a></p>
    </div>
</body>
</html>
