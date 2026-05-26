<?php
session_start();
include '../config/koneksi.php';

if (!isset($_SESSION['user']) || $_SESSION['user']['role'] != 'siswa') {
    header("Location: ../auth/login.php");
    exit;
}
?>

<!DOCTYPE html>
<html>
<head>
    <title>Tambah Aspirasi</title>
</head>
<body>

<h2>Tambah Aspirasi</h2>

<form action="proses_tambah.php" method="POST" enctype="multipart/form-data">
    <input type="text" name="judul" placeholder="Judul" required><br><br>

    <textarea name="isi" placeholder="Isi aspirasi" required></textarea><br><br>

    <select name="lokasi" required>
        <option value="">-- Pilih Lokasi --</option>
        <option value="toilet">Toilet</option>
        <option value="kelas">Kelas</option>
        <option value="kantin">Kantin</option>
        <option value="lapangan">Lapangan</option>
        <option value="aula">Aula</option>
        <option value="perpustakaan">Perpustakaan</option>
        <option value="taman">Taman</option>
    </select><br><br>

    <input type="file" name="foto" required><br><br>

    <button type="submit">Kirim</button>
</form>

<a href="dashboard.php">Kembali</a>

</body>
</html>