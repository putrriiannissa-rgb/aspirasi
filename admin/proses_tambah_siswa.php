<?php
include '../config/koneksi.php';

$name = $_POST['name'];
$username = $_POST['username'];
$email = $_POST['email'];
$nis = $_POST['nis'];
$password = password_hash($_POST['password'], PASSWORD_DEFAULT);

// ✅ VALIDASI EMAIL
if (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
    die("Format email tidak valid!");
}

// ✅ CEK DUPLIKAT
$cek = mysqli_query($conn, "
SELECT * FROM users 
WHERE email='$email' OR username='$username'
");

if (mysqli_num_rows($cek) > 0) {
    die("Email atau Username sudah digunakan!");
}

// simpan
mysqli_query($conn, "
INSERT INTO users (name,username,email,password,role,nis)
VALUES ('$name','$username','$email','$password','siswa','$nis')
");

header("Location: dashboard.php");