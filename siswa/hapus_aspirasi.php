<?php
session_start();
include '../config/koneksi.php';

if (!isset($_SESSION['user'])) {
    header("Location: ../auth/login.php");
    exit;
}

$id = $_GET['id'];
$user_id = $_SESSION['user']['id'];

// ambil data
$data = mysqli_fetch_assoc(mysqli_query($conn, 
    "SELECT * FROM aspirations WHERE id='$id'"));

if (!$data) {
    die("Data tidak ditemukan");
}

// 🔒 CEK PEMILIK
if ($data['user_id'] != $user_id) {
    die("Akses ditolak!");
}

// 🔒 CEK STATUS
if ($data['status'] != 'menunggu') {
    die("Tidak bisa dihapus, sudah diproses!");
}

// hapus
mysqli_query($conn, "DELETE FROM aspirations WHERE id='$id'");

header("Location: dashboard.php");