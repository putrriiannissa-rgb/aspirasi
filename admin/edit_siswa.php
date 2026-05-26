<?php
session_start();
include '../config/koneksi.php';

/** @var mysqli $conn */

$id = $_GET['id'];
$data = mysqli_query($conn, "SELECT * FROM users WHERE id='$id' AND role='siswa'");
$siswa = mysqli_fetch_assoc($data);

if (!$siswa) {
    header("Location: siswa.php");
    exit();
}

if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    $name = mysqli_real_escape_string($conn, $_POST['name']);
    $username = mysqli_real_escape_string($conn, $_POST['username']);
    
    $query = "UPDATE users SET name='$name', username='$username' WHERE id='$id'";
    
    if (!empty($_POST['password'])) {
        $password = password_hash($_POST['password'], PASSWORD_DEFAULT);
        $query = "UPDATE users SET name='$name', username='$username', password='$password' WHERE id='$id'";
    }
    
    if (mysqli_query($conn, $query)) {
        header("Location: edit_siswa.php?success=1");
        exit();
    } else {
        $error = "Gagal mengupdate admin";
    }
}
?>

<!DOCTYPE html>
<html>
<head>
    <title>Edit Siswa</title>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <style>
        /* Gunakan style yang sama dengan tambah_admin.php */
        * { margin: 0; padding: 0; box-sizing: border-box; }
        body {
            font-family: 'Inter', -apple-system, BlinkMacSystemFont, 'Segoe UI', Roboto, sans-serif;
            background: #f8fafc;
        }
        @import url('https://fonts.googleapis.com/css2?family=Inter:opsz,wght@14..32,300;14..32,400;14..32,500;14..32,600;14..32,700&display=swap');
        
        .main-content { margin-left: 260px; padding: 32px 40px; }
        .card { background: white; border-radius: 20px; padding: 32px; max-width: 600px; }
        .card h2 { font-size: 1.65rem; margin-bottom: 24px; }
        .form-group { margin-bottom: 20px; }
        label { display: block; margin-bottom: 8px; font-weight: 500; }
        input { width: 100%; padding: 12px 16px; border: 1px solid #e2e8f0; border-radius: 12px; }
        .btn-submit { background: #3b82f6; color: white; padding: 12px 24px; border: none; border-radius: 12px; width: 100%; cursor: pointer; }
        .btn-back { display: inline-block; margin-top: 16px; color: #64748b; text-decoration: none; }
        
        @media (max-width: 768px) { .main-content { margin-left: 0; padding: 20px; } }
    </style>
</head>
<body>
<?php include 'partials/sidebar.php'; ?>

<div class="main-content">
    <div class="card">
        <h2>✏️ Edit Siswa</h2>
        
        <form method="POST">
            <div class="form-group">
                <label>Nama Lengkap</label>
                <input type="text" name="name" value="<?= htmlspecialchars($siswa['name']) ?>" required>
            </div>
            
            <div class="form-group">
                <label>Username</label>
                <input type="text" name="username" value="<?= htmlspecialchars($siswa['username']) ?>" required>
            </div>
            
            <div class="form-group">
                <label>Password Baru (kosongkan jika tidak diubah)</label>
                <input type="password" name="password" placeholder="Masukkan password baru">
            </div>
            
            <button type="submit" class="btn-submit">Update Siswa</button>
            <a href="siswa.php" class="btn-back">← Kembali</a>
        </form>
    </div>
</div>
</body>
</html>