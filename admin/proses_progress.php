<?php
session_start();
include '../config/koneksi.php';

/** @var mysqli $conn */

// Cek login admin
if (!isset($_SESSION['user']) || $_SESSION['user']['role'] != 'admin') {
    header("Location: ../auth/login.php");
    exit;
}

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    header("Location: dashboard.php");
    exit;
}

$aspiration_id = isset($_POST['aspiration_id']) ? (int)$_POST['aspiration_id'] : 0;
$status = isset($_POST['status']) ? mysqli_real_escape_string($conn, $_POST['status']) : '';

if ($aspiration_id <= 0 || empty($status)) {
    header("Location: detail_aspirasi.php?id=$aspiration_id&error=Status tidak valid");
    exit;
}

// Validasi status yang diizinkan
$allowed = ['menunggu', 'pengecekan', 'proses', 'selesai'];
if (!in_array($status, $allowed)) {
    header("Location: detail_aspirasi.php?id=$aspiration_id&error=Status tidak dikenal");
    exit;
}

// Simpan progress ke tabel progress
$query = "INSERT INTO progress (aspiration_id, status, tanggal) VALUES ($aspiration_id, '$status', NOW())";
if (mysqli_query($conn, $query)) {
    // Update juga kolom status di tabel aspirations (opsional, untuk kompatibilitas)
    mysqli_query($conn, "UPDATE aspirations SET status='$status' WHERE id=$aspiration_id");
    header("Location: detail_aspirasi.php?id=$aspiration_id&success=Status berhasil diupdate");
} else {
    header("Location: detail_aspirasi.php?id=$aspiration_id&error=Gagal update status");
}
exit;
?>