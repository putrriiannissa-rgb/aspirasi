<?php
session_start();
include '../config/koneksi.php';

if (!isset($_SESSION['user'])) {
    header("Location: ../auth/login.php");
    exit;
}

$user_id = $_SESSION['user']['id'];
$name = mysqli_real_escape_string($conn, $_POST['name']);
$username = mysqli_real_escape_string($conn, $_POST['username']);
$email = mysqli_real_escape_string($conn, $_POST['email']);
$password = $_POST['password'];

// Update query
if (!empty($password)) {
    $hashed_password = password_hash($password, PASSWORD_DEFAULT);
    $query = "UPDATE users SET name='$name', username='$username', email='$email', password='$hashed_password' WHERE id='$user_id'";
} else {
    $query = "UPDATE users SET name='$name', username='$username', email='$email' WHERE id='$user_id'";
}

if (mysqli_query($conn, $query)) {
    // Update session
    $_SESSION['user']['name'] = $name;
    $_SESSION['user']['username'] = $username;
    $_SESSION['user']['email'] = $email;
    
    header("Location: profil.php?success=1");
} else {
    header("Location: profil.php?error=1");
}
?>