<?php
session_start();
include '../config/koneksi.php';

/** @var mysqli $conn */

if (!isset($_SESSION['user']) || $_SESSION['user']['role'] != 'siswa') {
    header("Location: ../auth/login.php");
    exit;
}

if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    $user_id = $_SESSION['user']['id'];
    $isi = mysqli_real_escape_string($conn, $_POST['isi']);
    $lokasi = mysqli_real_escape_string($conn, $_POST['lokasi']);
    $category_id = (int)$_POST['category_id'];

    // Ambil nama kategori dari tabel categories berdasarkan ID
    $cat_query = mysqli_query($conn, "SELECT nama_kategori FROM categories WHERE id = $category_id");
    $cat = mysqli_fetch_assoc($cat_query);
    $kategori = $cat ? $cat['nama_kategori'] : '';

    // Upload foto
    $foto = $_FILES['foto']['name'];
    $tmp = $_FILES['foto']['tmp_name'];
    $foto_name = time() . '_' . basename($foto);
    move_uploaded_file($tmp, "../uploads/" . $foto_name);

    $query = "INSERT INTO aspirations (user_id, isi, lokasi, kategori, foto) 
              VALUES ('$user_id', '$isi', '$lokasi', '$kategori', '$foto_name')";
    
    if (mysqli_query($conn, $query)) {
        header("Location: dashboard.php?success=1");
    } else {
        header("Location: dashboard.php?error=1");
    }
}
?>