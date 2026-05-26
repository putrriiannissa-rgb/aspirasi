<?php
session_start();
include '../config/koneksi.php';

/** @var mysqli $conn */

if (!isset($_SESSION['user']) || $_SESSION['user']['role'] != 'admin') {
    header("Location: ../auth/login.php");
    exit;
}

$error = '';

if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    $name = mysqli_real_escape_string($conn, trim($_POST['name']));
    $username = mysqli_real_escape_string($conn, trim($_POST['username']));
    $password = $_POST['password'];
    
    // Validasi
    if (empty($name) || empty($username) || empty($password)) {
        $error = "Semua field harus diisi";
    } else {
        // Cek apakah username sudah ada
        $check = mysqli_query($conn, "SELECT id FROM users WHERE username = '$username'");
        if (mysqli_num_rows($check) > 0) {
            $error = "Username sudah digunakan, silakan pilih username lain";
        } else {
            $hashed = password_hash($password, PASSWORD_DEFAULT);
            $query = "INSERT INTO users (name, username, password, role) VALUES ('$name', '$username', '$hashed', 'admin')";
            if (mysqli_query($conn, $query)) {
                header("Location: admin_data.php?success=1");
                exit;
            } else {
                $error = "Gagal menambahkan admin: " . mysqli_error($conn);
            }
        }
    }
}
?>

<!DOCTYPE html>
<html>
<head>
    <title>Tambah Admin</title>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <link href="https://fonts.googleapis.com/css2?family=Inter:opsz,wght@14..32,300;400;500;600;700&display=swap" rel="stylesheet">
    <style>
        * { margin: 0; padding: 0; box-sizing: border-box; }
        body {
            font-family: 'Inter', -apple-system, BlinkMacSystemFont, 'Segoe UI', Roboto, sans-serif;
            background: #f8fafc;
            color: #1e293b;
        }
        .main-content {
            margin-left: 260px;
            padding: 32px 40px;
            min-height: 100vh;
        }
        .card {
            background: white;
            border-radius: 20px;
            padding: 32px;
            max-width: 600px;
            box-shadow: 0 1px 3px rgba(0,0,0,0.05);
        }
        .card h2 {
            font-size: 1.65rem;
            margin-bottom: 24px;
            background: linear-gradient(135deg, #1e293b 0%, #2d3a4f 100%);
            background-clip: text;
            -webkit-background-clip: text;
            -webkit-text-fill-color: transparent;
        }
        .form-group {
            margin-bottom: 20px;
        }
        label {
            display: block;
            margin-bottom: 8px;
            font-weight: 500;
            color: #475569;
        }
        input {
            width: 100%;
            padding: 12px 16px;
            border: 1px solid #e2e8f0;
            border-radius: 12px;
            font-size: 0.9rem;
            font-family: 'Inter', sans-serif;
            transition: all 0.2s;
        }
        input:focus {
            outline: none;
            border-color: #3b82f6;
            box-shadow: 0 0 0 3px rgba(59,130,246,0.1);
        }
        .btn-submit {
            background: #3b82f6;
            color: white;
            padding: 12px 24px;
            border: none;
            border-radius: 12px;
            font-size: 0.9rem;
            font-weight: 500;
            cursor: pointer;
            width: 100%;
        }
        .btn-submit:hover {
            background: #2563eb;
        }
        .btn-back {
            display: inline-block;
            margin-top: 16px;
            color: #64748b;
            text-decoration: none;
        }
        .alert {
            padding: 12px 16px;
            border-radius: 12px;
            margin-bottom: 20px;
        }
        .alert-error {
            background: #fef2f2;
            color: #dc2626;
            border: 1px solid #fee2e2;
        }
        @media (max-width: 768px) {
            .main-content {
                margin-left: 0;
                padding: 20px;
            }
        }
    </style>
</head>
<body>
<?php include 'partials/sidebar.php'; ?>

<div class="main-content">
    <div class="card">
        <h2>➕ Tambah Admin Baru</h2>
        
        <?php if (!empty($error)): ?>
            <div class="alert alert-error"><?= htmlspecialchars($error) ?></div>
        <?php endif; ?>
        
        <form method="POST">
            <div class="form-group">
                <label>Nama Lengkap</label>
                <input type="text" name="name" required placeholder="Masukkan nama lengkap">
            </div>
            
            <div class="form-group">
                <label>Username</label>
                <input type="text" name="username" required placeholder="Masukkan username" autocomplete="off">
            </div>
            
            <div class="form-group">
                <label>Password</label>
                <input type="password" name="password" required placeholder="Masukkan password">
            </div>
            
            <button type="submit" class="btn-submit">Simpan Admin</button>
            <a href="admin_data.php" class="btn-back">← Kembali</a>
        </form>
    </div>
</div>
</body>
</html>