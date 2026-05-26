<?php
session_start();

// Path koneksi yang benar (ke folder config di luar auth)
require_once __DIR__ . '/../config/koneksi.php';

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    header('Location: register.php');
    exit;
}

// Ambil data
$name     = trim($_POST['name'] ?? '');
$username = trim($_POST['username'] ?? '');
$email    = trim($_POST['email'] ?? '');
$nis      = trim($_POST['nis'] ?? '');
$password = $_POST['password'] ?? '';

$errors = [];

// Validasi sisi server
if (strlen($name) < 3) $errors[] = "Nama lengkap minimal 3 karakter.";
if (strlen($username) < 4) $errors[] = "Username minimal 4 karakter.";
if (!filter_var($email, FILTER_VALIDATE_EMAIL)) $errors[] = "Email tidak valid.";
if (!preg_match('/^\d{8}$/', $nis)) $errors[] = "NIS harus 8 digit angka.";
if (strlen($password) < 8) $errors[] = "Password minimal 8 karakter.";

// Cek duplikat di database (gunakan MySQLi)
if (empty($errors)) {
    // Cek username
    $stmt = mysqli_prepare($conn, "SELECT id FROM register_requests WHERE username = ?");
    mysqli_stmt_bind_param($stmt, "s", $username);
    mysqli_stmt_execute($stmt);
    mysqli_stmt_store_result($stmt);
    if (mysqli_stmt_num_rows($stmt) > 0) $errors[] = "Username sudah digunakan.";
    mysqli_stmt_close($stmt);

    // Cek email
    $stmt = mysqli_prepare($conn, "SELECT id FROM register_requests WHERE email = ?");
    mysqli_stmt_bind_param($stmt, "s", $email);
    mysqli_stmt_execute($stmt);
    mysqli_stmt_store_result($stmt);
    if (mysqli_stmt_num_rows($stmt) > 0) $errors[] = "Email sudah terdaftar.";
    mysqli_stmt_close($stmt);

    // Cek NIS
    $stmt = mysqli_prepare($conn, "SELECT id FROM register_requests WHERE nis = ?");
    mysqli_stmt_bind_param($stmt, "s", $nis);
    mysqli_stmt_execute($stmt);
    mysqli_stmt_store_result($stmt);
    if (mysqli_stmt_num_rows($stmt) > 0) $errors[] = "NIS sudah pernah melakukan request.";
    mysqli_stmt_close($stmt);
}

// Jika ada error, simpan ke session dan kembali
if (!empty($errors)) {
    $_SESSION['register_errors'] = $errors;
    $_SESSION['old_input'] = ['name' => $name, 'username' => $username, 'email' => $email, 'nis' => $nis];
    header('Location: register.php');
    exit;
}

// Hash password
$hashedPassword = password_hash($password, PASSWORD_DEFAULT);

// Insert ke tabel register_requests
$sql = "INSERT INTO register_requests (name, username, email, nis, password, status) VALUES (?, ?, ?, ?, ?, 'pending')";
$stmt = mysqli_prepare($conn, $sql);
mysqli_stmt_bind_param($stmt, "sssss", $name, $username, $email, $nis, $hashedPassword);

if (mysqli_stmt_execute($stmt)) {
    // Sukses - tampilkan pop up alert lalu redirect ke login.php
    mysqli_stmt_close($stmt);
    mysqli_close($conn);
    
    // Gunakan JavaScript alert + redirect (karena header tidak bisa setelah output)
    echo "<script>
            alert('✅ Oke! Berhasil request akun.\\nSilakan tunggu approval dari admin.');
            window.location.href = 'login.php';
          </script>";
    exit;
} else {
    // Gagal insert
    $_SESSION['register_errors'] = ["Gagal menyimpan data. Error: " . mysqli_error($conn)];
    $_SESSION['old_input'] = ['name' => $name, 'username' => $username, 'email' => $email, 'nis' => $nis];
    mysqli_stmt_close($stmt);
    mysqli_close($conn);
    header('Location: register.php');
    exit;
}
?>