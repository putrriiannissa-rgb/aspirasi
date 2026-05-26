<?php
session_start();
include '../config/koneksi.php';

/** @var mysqli $conn */

// Ambil data dari form
$role = isset($_POST['role']) ? mysqli_real_escape_string($conn, $_POST['role']) : '';
$password = isset($_POST['password']) ? $_POST['password'] : '';

// Variabel untuk menyimpan data user
$user = null;

if ($role == 'admin') {
    // Login sebagai Admin menggunakan Username
    $username = isset($_POST['username']) ? mysqli_real_escape_string($conn, $_POST['username']) : '';
    
    if (empty($username)) {
        header("Location: login.php?error=Username tidak boleh kosong");
        exit;
    }
    
    // Cek di database
    $query = "SELECT * FROM users WHERE username = '$username' AND role = 'admin'";
    $result = mysqli_query($conn, $query);
    
    if (mysqli_num_rows($result) == 1) {
        $user = mysqli_fetch_assoc($result);
        
        // Verifikasi password
        if (password_verify($password, $user['password'])) {
            $_SESSION['user'] = $user;
            header("Location: ../admin/dashboard.php");
            exit;
        } else {
            header("Location: login.php?error=Password salah");
            exit;
        }
    } else {
        header("Location: login.php?error=Username tidak ditemukan");
        exit;
    }
    
} elseif ($role == 'siswa') {
    // Login sebagai Siswa menggunakan NIS
    $nis = isset($_POST['nis']) ? mysqli_real_escape_string($conn, $_POST['nis']) : '';
    
    if (empty($nis)) {
        header("Location: login.php?error=NIS tidak boleh kosong");
        exit;
    }
    
    // Cek di database
    $query = "SELECT * FROM users WHERE nis = '$nis' AND role = 'siswa'";
    $result = mysqli_query($conn, $query);
    
    if (mysqli_num_rows($result) == 1) {
        $user = mysqli_fetch_assoc($result);
        
        // Verifikasi password
        if (password_verify($password, $user['password'])) {
            $_SESSION['user'] = $user;
            header("Location: ../siswa/dashboard.php");
            exit;
        } else {
            header("Location: login.php?error=Password salah");
            exit;
        }
    } else {
        header("Location: login.php?error=NIS tidak ditemukan atau akun belum disetujui");
        exit;
    }
    
} else {
    header("Location: login.php?error=Role tidak valid");
    exit;
}
?>