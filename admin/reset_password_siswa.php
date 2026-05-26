<?php
session_start();
include '../config/koneksi.php';

/** @var mysqli $conn */

if (!isset($_SESSION['user']) || $_SESSION['user']['role'] != 'admin') {
    header("Location: ../auth/login.php");
    exit;
}

$id = isset($_GET['id']) ? (int)$_GET['id'] : 0;
if ($id > 0) {
    $new_password = password_hash('12345678', PASSWORD_DEFAULT);
    mysqli_query($conn, "UPDATE users SET password = '$new_password' WHERE id = $id AND role = 'siswa'");
}
header("Location: siswa.php");
exit;
?>