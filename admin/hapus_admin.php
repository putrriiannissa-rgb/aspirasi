<?php
session_start();
include '../config/koneksi.php';

/** @var mysqli $conn */

// Cek login admin
if (!isset($_SESSION['user']) || $_SESSION['user']['role'] != 'admin') {
    header("Location: ../auth/login.php");
    exit;
}

if (!isset($_GET['id']) || !is_numeric($_GET['id'])) {
    header("Location: admin_data.php?error=ID admin tidak valid");
    exit;
}

$id = (int)$_GET['id'];

// Jangan biarkan admin menghapus dirinya sendiri
if ($id == $_SESSION['user']['id']) {
    header("Location: admin_data.php?error=Tidak dapat menghapus akun sendiri");
    exit;
}

// Hapus admin (pastikan role = 'admin')
$query = "DELETE FROM users WHERE id = $id AND role = 'admin'";
if (mysqli_query($conn, $query)) {
    header("Location: admin_data.php?success=Admin berhasil dihapus");
} else {
    header("Location: admin_data.php?error=Gagal menghapus admin");
}
exit;
?>