<?php
include '../config/koneksi.php';

/** @var mysqli $conn */

$id = $_POST['id'];
$status = $_POST['status'];

// simpan ke tabel progress
mysqli_query($conn, "
INSERT INTO progress (aspiration_id, status)
VALUES ('$id', '$status')
");

header("Location: " . $_SERVER['HTTP_REFERER']);