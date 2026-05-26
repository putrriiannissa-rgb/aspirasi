<?php
include 'config/koneksi.php';

$password_baru = password_hash("admin1", PASSWORD_DEFAULT);

mysqli_query($conn, "UPDATE users SET password='$password_baru' WHERE username='nisa'");

echo "Password berhasil direset!";