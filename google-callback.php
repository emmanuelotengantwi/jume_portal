<?php
require 'vendor/autoload.php';
require 'db.php';

session_start();

$client = new Google_Client();
$client->setClientId("YOUR_GOOGLE_CLIENT_ID");
$client->setClientSecret("YOUR_GOOGLE_CLIENT_SECRET");
$client->setRedirectUri("http://localhost/jume_portal/google-callback.php");

if (isset($_GET['code'])) {
    $token = $client->fetchAccessTokenWithAuthCode($_GET['code']);
    $client->setAccessToken($token);

    $oauth = new Google_Service_Oauth2($client);
    $profile = $oauth->userinfo->get();

    $email = $profile->email;
    $name = $profile->name;

    // Check if user exists
    $stmt = $conn->prepare("SELECT * FROM users WHERE email=?");
    $stmt->bind_param("s", $email);
    $stmt->execute();
    $result = $stmt->get_result();

    if ($result->num_rows == 0) {
        // Insert new user
        $stmt = $conn->prepare("INSERT INTO users (name, email, password) VALUES (?, ?, ?)");
        $stmt->bind_param("sss", $name, $email, $hash = password_hash('google-oauth', PASSWORD_DEFAULT));
        $stmt->execute();
    }

    $_SESSION['user'] = $email;
    header("Location: index.php");
    exit();
}
?>
