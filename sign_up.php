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
<div class="social-login">
  <div id="g-btn-wrap"></div>
  <button type="button" class="social-btn facebook" onclick="handleFacebookLogin()">
    <i class="fa-brands fa-facebook-f"></i> Facebook
  </button>
</div>

<!-- Add these scripts at the end of body in signup.php -->
<script src="https://accounts.google.com/gsi/client" async defer></script>
<script async defer crossorigin="anonymous" src="https://connect.facebook.net/en_US/sdk.js"></script>
<script src="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.0/js/all.min.js" defer></script>
<script src="scripts.js"></script>
<script>
  // Google init
  window.onload = function() {
    const GOOGLE_CLIENT_ID = "YOUR_GOOGLE_CLIENT_ID.apps.googleusercontent.com";
    google.accounts.id.initialize({
      client_id: GOOGLE_CLIENT_ID,
      callback: handleGoogleCredentialResponse
    });
    google.accounts.id.renderButton(
      document.getElementById("g-btn-wrap"),
      { theme: "filled_blue", size: "large", text: "signin_with", shape: "pill", width: "100%" }
    );
  };

  // Facebook init
  window.fbAsyncInit = function() {
    FB.init({
      appId      : 'YOUR_FACEBOOK_APP_ID',
      cookie     : true,
      xfbml      : true,
      version    : 'v19.0'
    });
  };
</script>
