<?php
session_start();
include '../config/koneksi.php';  // path diperbaiki

/** @var mysqli $conn */

// Cek apakah user sudah login dan sebagai admin
if (!isset($_SESSION['user']) || $_SESSION['user']['role'] != 'admin') {
    header("Location: ../auth/login.php");
    exit();
}

$error = '';

if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    $nis = trim($_POST['nis']);
    $nama_lengkap = trim($_POST['nama_lengkap']);
    $username = trim($_POST['username']);
    $email = trim($_POST['email']);
    $password = $_POST['password'];
    
    // Validasi input
    if (empty($nis) || empty($nama_lengkap) || empty($username) || empty($email) || empty($password)) {
        $error = "Semua field harus diisi!";
    } elseif (strlen($password) < 6) {
        $error = "Password minimal 6 karakter!";
    } elseif (!preg_match('/^\d{8}$/', $nis)) {
        $error = "NIS harus berupa 8 digit angka!";
    } elseif (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
        $error = "Format email tidak valid!";
    } else {
        // Escape untuk keamanan
        $nis_esc = mysqli_real_escape_string($conn, $nis);
        $nama_lengkap_esc = mysqli_real_escape_string($conn, $nama_lengkap);
        $username_esc = mysqli_real_escape_string($conn, $username);
        $email_esc = mysqli_real_escape_string($conn, $email);
        
        // Cek apakah NIS sudah terdaftar
        $check_nis = mysqli_query($conn, "SELECT id FROM users WHERE nis = '$nis_esc'");
        if (mysqli_num_rows($check_nis) > 0) {
            $error = "NIS sudah terdaftar!";
        }
        
        // Cek apakah username sudah terdaftar
        $check_username = mysqli_query($conn, "SELECT id FROM users WHERE username = '$username_esc'");
        if (mysqli_num_rows($check_username) > 0) {
            $error = "Username sudah terdaftar!";
        }
        
        // Cek apakah email sudah terdaftar
        $check_email = mysqli_query($conn, "SELECT id FROM users WHERE email = '$email_esc'");
        if (mysqli_num_rows($check_email) > 0) {
            $error = "Email sudah terdaftar!";
        }
        
        if (empty($error)) {
            $hashed_password = password_hash($password, PASSWORD_DEFAULT);
            $role = 'siswa';
            $query = "INSERT INTO users (nis, name, username, email, password, role) 
                      VALUES ('$nis_esc', '$nama_lengkap_esc', '$username_esc', '$email_esc', '$hashed_password', '$role')";
            
            if (mysqli_query($conn, $query)) {
                header("Location: siswa.php?success=added");
                exit();
            } else {
                $error = "Gagal menambahkan siswa: " . mysqli_error($conn);
            }
        }
    }
}
?>

<!DOCTYPE html>
<html>
<head>
    <title>Tambah Siswa - Aspirasi System</title>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <style>
        * {
            margin: 0;
            padding: 0;
            box-sizing: border-box;
        }

        body {
            font-family: 'Inter', -apple-system, BlinkMacSystemFont, 'Segoe UI', Roboto, sans-serif;
            background: #f8fafc;
            color: #1e293b;
            line-height: 1.5;
        }

        @import url('https://fonts.googleapis.com/css2?family=Inter:opsz,wght@14..32,300;14..32,400;14..32,500;14..32,600;14..32,700&display=swap');

        .main-content {
            margin-left: 260px;
            padding: 32px 40px;
            min-height: 100vh;
            display: flex;
            justify-content: center;
            align-items: center;
        }

        .card {
            background: white;
            border-radius: 24px;
            box-shadow: 0 4px 20px rgba(0, 0, 0, 0.08);
            max-width: 600px;
            width: 100%;
            overflow: hidden;
            animation: fadeInUp 0.4s ease-out;
        }

        .card-header {
            padding: 28px 32px 20px 32px;
            background: linear-gradient(135deg, #059669 0%, #10b981 100%);
            color: white;
        }

        .card-header h2 {
            font-size: 1.75rem;
            font-weight: 600;
            margin-bottom: 6px;
            display: flex;
            align-items: center;
            gap: 10px;
        }

        .card-header p {
            font-size: 0.9rem;
            opacity: 0.9;
        }

        .card-body {
            padding: 32px;
        }

        .form-group {
            margin-bottom: 24px;
        }

        .form-group label {
            display: block;
            margin-bottom: 8px;
            font-weight: 600;
            color: #334155;
            font-size: 0.85rem;
        }

        .form-group label span {
            color: #ef4444;
            margin-left: 4px;
        }

        .form-group input {
            width: 100%;
            padding: 12px 16px;
            border: 1.5px solid #e2e8f0;
            border-radius: 12px;
            font-size: 0.9rem;
            font-family: 'Inter', sans-serif;
            transition: all 0.2s ease;
        }

        .form-group input:focus {
            outline: none;
            border-color: #10b981;
            box-shadow: 0 0 0 3px rgba(16, 185, 129, 0.1);
        }

        .form-group input::placeholder {
            color: #cbd5e1;
        }

        .form-group small.error-message {
            color: #ef4444;
            font-size: 0.7rem;
            margin-top: 4px;
            display: block;
        }

        .form-row {
            display: grid;
            grid-template-columns: 1fr 1fr;
            gap: 20px;
        }

        .alert {
            padding: 14px 16px;
            border-radius: 12px;
            margin-bottom: 24px;
            display: flex;
            align-items: center;
            gap: 10px;
            font-size: 0.85rem;
        }

        .alert-error {
            background: #fef2f2;
            color: #dc2626;
            border: 1px solid #fee2e2;
        }

        .alert-success {
            background: #ecfdf5;
            color: #059669;
            border: 1px solid #d1fae5;
        }

        .btn-submit {
            background: #10b981;
            color: white;
            padding: 14px 28px;
            border: none;
            border-radius: 12px;
            font-size: 0.9rem;
            font-weight: 600;
            cursor: pointer;
            width: 100%;
            transition: all 0.2s ease;
            display: flex;
            align-items: center;
            justify-content: center;
            gap: 8px;
        }

        .btn-submit:hover {
            background: #059669;
            transform: translateY(-1px);
            box-shadow: 0 4px 12px rgba(16, 185, 129, 0.3);
        }

        .btn-back {
            display: inline-flex;
            align-items: center;
            gap: 6px;
            margin-top: 20px;
            color: #64748b;
            text-decoration: none;
            font-size: 0.85rem;
            transition: color 0.2s;
        }

        .btn-back:hover {
            color: #059669;
        }

        .password-hint {
            font-size: 0.7rem;
            color: #94a3b8;
            margin-top: 6px;
        }

        @keyframes fadeInUp {
            from {
                opacity: 0;
                transform: translateY(20px);
            }
            to {
                opacity: 1;
                transform: translateY(0);
            }
        }

        @media (max-width: 768px) {
            .main-content {
                margin-left: 0;
                padding: 20px;
            }
            
            .form-row {
                grid-template-columns: 1fr;
                gap: 0;
            }
            
            .card-header {
                padding: 24px;
            }
            
            .card-body {
                padding: 24px;
            }
            
            .card-header h2 {
                font-size: 1.5rem;
            }
        }
    </style>
</head>
<body>

<?php include 'partials/sidebar.php'; ?>

<div class="main-content">
    <div class="card">
        <div class="card-header">
            <h2>🎓 Tambah Siswa Baru</h2>
            <p>Isi formulir berikut untuk mendaftarkan siswa</p>
        </div>
        
        <div class="card-body">
            <?php if (!empty($error)): ?>
                <div class="alert alert-error">
                    <span>⚠️</span> <?= htmlspecialchars($error) ?>
                </div>
            <?php endif; ?>
            
            <form method="POST" onsubmit="return validateForm()">
                <div class="form-group">
                    <label>Nomor Induk Siswa (NIS) <span>*</span></label>
                    <input 
                        type="text" 
                        name="nis" 
                        id="nis"
                        maxlength="8"
                        required 
                        placeholder="8 digit angka"
                        pattern="\d{8}"
                        title="Harus 8 digit angka"
                        value="<?= isset($_POST['nis']) ? htmlspecialchars($_POST['nis']) : '' ?>"
                        oninput="this.value = this.value.replace(/[^0-9]/g, '').slice(0,8)"
                    >
                    <small class="error-message" id="nisError"></small>
                    <div class="password-hint">NIS terdiri dari 8 digit angka</div>
                </div>
                
                <div class="form-group">
                    <label>Nama Lengkap <span>*</span></label>
                    <input 
                        type="text" 
                        name="nama_lengkap" 
                        required 
                        placeholder="Contoh: Ahmad Fathir Rahman"
                        value="<?= isset($_POST['nama_lengkap']) ? htmlspecialchars($_POST['nama_lengkap']) : '' ?>"
                    >
                </div>

                <div class="form-group">
                    <label>Email <span>*</span></label>
                    <input 
                        type="email" 
                        name="email" 
                        id="email"
                        required 
                        placeholder="contoh@sekolah.com"
                        value="<?= isset($_POST['email']) ? htmlspecialchars($_POST['email']) : '' ?>"
                    >
                    <small class="error-message" id="emailError"></small>
                </div>
                
                <div class="form-row">
                    <div class="form-group">
                        <label>Username <span>*</span></label>
                        <input 
                            type="text" 
                            name="username" 
                            required 
                            placeholder="Username untuk login"
                            value="<?= isset($_POST['username']) ? htmlspecialchars($_POST['username']) : '' ?>"
                        >
                    </div>
                    
                    <div class="form-group">
                        <label>Password <span>*</span></label>
                        <input 
                            type="password" 
                            name="password" 
                            id="password"
                            required 
                            placeholder="Minimal 6 karakter"
                        >
                        <div class="password-hint">
                            🔒 Password minimal 6 karakter
                        </div>
                    </div>
                </div>
                
                <button type="submit" class="btn-submit">
                    💾 Simpan Data Siswa
                </button>
                
                <a href="siswa.php" class="btn-back">
                    ← Kembali ke Kelola Siswa
                </a>
            </form>
        </div>
    </div>
</div>

<script>
    // NIS validation
    const nisInput = document.getElementById('nis');
    const nisError = document.getElementById('nisError');
    
    nisInput.addEventListener('input', function() {
        let value = this.value;
        if (value.length !== 8 && value.length > 0) {
            nisError.textContent = 'NIS harus 8 digit angka!';
            this.classList.add('error');
        } else if (value.length === 8) {
            nisError.textContent = '';
            this.classList.remove('error');
        } else {
            nisError.textContent = '';
            this.classList.remove('error');
        }
    });
    
    // Email validation
    const emailInput = document.getElementById('email');
    const emailError = document.getElementById('emailError');
    const emailPattern = /^[^\s@]+@[^\s@]+\.[^\s@]+$/;
    
    emailInput.addEventListener('input', function() {
        const emailValue = this.value;
        if (emailValue !== "" && !emailPattern.test(emailValue)) {
            emailError.textContent = 'Format email tidak valid! Contoh: nama@domain.com';
            this.classList.add('error');
        } else if (emailPattern.test(emailValue)) {
            emailError.textContent = '';
            this.classList.remove('error');
        } else {
            emailError.textContent = '';
            this.classList.remove('error');
        }
    });
    
    function validateForm() {
        let isValid = true;
        let nis = nisInput.value;
        let email = emailInput.value;
        
        if (nis.length !== 8) {
            nisError.textContent = 'NIS harus 8 digit angka!';
            nisInput.classList.add('error');
            isValid = false;
        }
        
        if (!emailPattern.test(email)) {
            emailError.textContent = 'Format email tidak valid!';
            emailInput.classList.add('error');
            isValid = false;
        }
        
        return isValid;
    }
</script>

</body>
</html>