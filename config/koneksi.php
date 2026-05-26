<?php
$conn = mysqli_connect("mysql.railway.internal", "root", "ynBTsKSgjKwUmPlWlTNUizquDpFhgvzX", "railway");

if (!$conn) {
    die("Koneksi gagal: " . mysqli_connect_error());
}