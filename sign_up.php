<?php
session_start();
include 'db.php';

$error = ""; $success = "";
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
  $name = trim($_POST['name']);
  $email = trim($_POST['email']);
  $password = trim($_POST['password']);

  if (!$name || !$email || !$password) {
    $error = "All fields are required.";
  } else {
    $check = $conn->prepare("SELECT id FROM users WHERE email=?");
    $check->bind_param("s", $email);
    $check->execute();
    $exists = $check->get_result()->num_rows > 0;

    if ($exists) {
      $error = "Email already registered. Please sign in.";
    } else {
      $hash = password_hash($password, PASSWORD_DEFAULT);
      $stmt = $conn->prepare("INSERT INTO users (name, email, password) VALUES (?,?,?)");
      $stmt->bind_param("sss", $name, $email, $hash);
    if ($stmt->execute()) {
    header("Location: sign_in.php");
    exit();
}
 else {
        $error = "Something went wrong. Try again.";
      }
    }
  }
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="UTF-8">
  <title>Sign Up</title>
  <link rel="stylesheet" href="styles.css">
  <link rel="stylesheet" href="sign_up.css">
</head>
<body>
  <div class="container">
  <img src="img/logo.png" alt="Logo" style="width: 140px; height: auto;">
  



    <h2>Create Account</h2>
    <?php if ($error) echo "<div class='error'>$error</div>"; ?>
    <?php if ($success) echo "<div class='success'>$success</div>"; ?>
    <form method="POST" action="">
      <input type="text" name="name" placeholder="Full Name" required>
      <input type="email" name="email" placeholder="Email" required>
      <input type="password" name="password" placeholder="Password" required>
      <button type="submit">Sign Up</button>
    </form>
    <a href="sign_in.php">Already have an account? Sign In</a>
  </div>
</body>
</html>


<!-- Add these scripts at the end of body in signup.php -->
<script src="https://accounts.google.com/gsi/client" async defer></script>
<script async defer crossorigin="anonymous" src="https://connect.facebook.net/en_US/sdk.js"></script>
<script src="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.0/js/all.min.js" defer></script>
<script src="scripts.js"></script>

