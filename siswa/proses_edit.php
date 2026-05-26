<?php
session_start();
include '../config/koneksi.php';

$id = $_POST['id'];
$user_id = $_SESSION['user']['id'];

// ambil data lama
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
    die("Tidak bisa diedit!");
}

// lanjut update
$judul = $_POST['judul'];
$isi = $_POST['isi'];
$lokasi = $_POST['lokasi'];

mysqli_query($conn, "UPDATE aspirations SET 
judul='$judul',
isi='$isi',
lokasi='$lokasi'
WHERE id='$id'");

header("Location: dashboard.php");