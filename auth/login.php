<?php session_start(); ?>
<!DOCTYPE html>
<html lang="id">
<head>
    <title>Login | Sistem Aspirasi Sekolah</title>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@300;400;500;600;700;800&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <style>
        /* =========================================
           Reset & Base Styles
           ========================================= */
        * {
            margin: 0;
            padding: 0;
            box-sizing: border-box;
        }

        body {
            font-family: 'Inter', sans-serif;
            background: #f0f4f8;
            min-height: 100vh;
            display: flex;
            align-items: center;
            justify-content: center;
            padding: 24px;
            position: relative;
        }

        /* Subtle background pattern */
        body::before {
            content: '';
            position: fixed;
            top: 0;
            left: 0;
            right: 0;
            bottom: 0;
            background-image: 
                radial-gradient(circle at 20% 80%, rgba(99, 102, 241, 0.03) 0%, transparent 50%),
                radial-gradient(circle at 80% 20%, rgba(139, 92, 246, 0.03) 0%, transparent 50%);
            pointer-events: none;
        }

        /* =========================================
           Layout & Containers
           ========================================= */
        .login-container {
            position: relative;
            z-index: 1;
            width: 100%;
            max-width: 480px;
        }

        .login-card {
            background: white;
            border-radius: 28px;
            padding: 40px;
            box-shadow: 0 8px 32px rgba(0, 0, 0, 0.08);
            border: 1px solid #f1f5f9;
            transition: transform 0.3s ease;
        }

        .login-card:hover {
            transform: translateY(-4px);
        }

        /* =========================================
           Header Section
           ========================================= */
        .login-header {
            text-align: center;
            margin-bottom: 32px;
        }

        .logo {
            width: 72px;
            height: 72px;
            background: linear-gradient(135deg, #6366f1, #8b5cf6);
            border-radius: 24px;
            display: flex;
            align-items: center;
            justify-content: center;
            margin: 0 auto 20px;
        }

        .logo i {
            font-size: 36px;
            color: white;
        }

        .login-header h2 {
            font-size: 28px;
            font-weight: 700;
            color: #1e293b;
            margin-bottom: 8px;
        }

        .login-header p {
            color: #64748b;
            font-size: 14px;
        }

        /* =========================================
           Role Tabs
           ========================================= */
        .role-tabs {
            display: flex;
            gap: 12px;
            margin-bottom: 32px;
            background: #f1f5f9;
            padding: 6px;
            border-radius: 16px;
        }

        .role-tab {
            flex: 1;
            text-align: center;
            padding: 12px;
            background: transparent;
            border: none;
            border-radius: 12px;
            font-size: 14px;
            font-weight: 600;
            cursor: pointer;
            transition: all 0.2s ease;
            font-family: 'Inter', sans-serif;
            color: #64748b;
            display: flex;
            align-items: center;
            justify-content: center;
            gap: 8px;
        }

        .role-tab i {
            font-size: 16px;
        }

        .role-tab.active {
            background: white;
            color: #6366f1;
            box-shadow: 0 2px 8px rgba(0, 0, 0, 0.04);
        }

        .role-tab.admin.active {
            color: #6366f1;
        }

        .role-tab.siswa.active {
            color: #10b981;
        }

        /* =========================================
           Form Elements
           ========================================= */
        .form-group {
            margin-bottom: 24px;
            transition: all 0.3s ease;
        }

        .form-group label {
            display: block;
            margin-bottom: 8px;
            font-weight: 500;
            color: #475569;
            font-size: 13px;
        }

        .form-group label i {
            margin-right: 8px;
            color: #6366f1;
        }

        .input-wrapper {
            position: relative;
        }

        .form-group input {
            width: 100%;
            padding: 12px 16px;
            border: 1.5px solid #e2e8f0;
            border-radius: 14px;
            font-size: 14px;
            font-family: 'Inter', sans-serif;
            transition: all 0.2s ease;
            background: white;
            outline: none;
        }

        .form-group input:focus {
            border-color: #6366f1;
            box-shadow: 0 0 0 3px rgba(99, 102, 241, 0.08);
        }

        .form-group input.error {
            border-color: #ef4444;
            background: #fef2f2;
        }

        .form-group input.success {
            border-color: #10b981;
            background: #f0fdf4;
        }

        .password-toggle {
            position: absolute;
            right: 14px;
            top: 50%;
            transform: translateY(-50%);
            cursor: pointer;
            background: none;
            border: none;
            color: #94a3b8;
            font-size: 16px;
            transition: color 0.2s;
        }

        .password-toggle:hover {
            color: #6366f1;
        }

        .error-message {
            display: block;
            margin-top: 6px;
            font-size: 11px;
            color: #ef4444;
        }

        .info-text {
            font-size: 11px;
            color: #94a3b8;
            margin-top: 4px;
        }

        /* =========================================
           Buttons & Footer
           ========================================= */
        .btn-login {
            width: 100%;
            padding: 12px;
            background: linear-gradient(135deg, #6366f1, #8b5cf6);
            color: white;
            border: none;
            border-radius: 14px;
            font-size: 14px;
            font-weight: 600;
            cursor: pointer;
            transition: all 0.2s ease;
            font-family: 'Inter', sans-serif;
            display: flex;
            align-items: center;
            justify-content: center;
            gap: 8px;
            margin-top: 8px;
        }

        .btn-login:hover {
            transform: translateY(-1px);
            box-shadow: 0 4px 12px rgba(99, 102, 241, 0.3);
        }

        .login-footer {
            margin-top: 24px;
            text-align: center;
        }

        .login-footer p {
            color: #64748b;
            font-size: 14px;
            margin-bottom: 12px;
        }

        .login-footer div {
            display: flex;
            flex-direction: column;
            align-items: center;
            gap: 10px;
        }

        .login-footer a {
            color: #6366f1;
            text-decoration: none;
            font-size: 14px;
            font-weight: 600;
            transition: color 0.2s;
            display: flex;
            align-items: center;
            justify-content: center;
            gap: 6px;
        }

        .login-footer a:hover {
            color: #4f46e5;
            text-decoration: underline;
        }

        .back-btn {
            background: white;
            padding: 10px 16px;
            border-radius: 14px;
            box-shadow: 0 4px 12px rgba(0,0,0,0.08);
            width: 100%; /* Membuat tombol selebar container-nya */
            max-width: 250px; /* Opsional: membatasi lebar maksimal */
        }

        /* =========================================
           Alerts & Notifications
           ========================================= */
        .error-alert {
            background: #fef2f2;
            border: 1px solid #fecaca;
            border-radius: 14px;
            padding: 12px 16px;
            margin-bottom: 24px;
            display: flex;
            align-items: center;
            gap: 12px;
        }

        .error-alert i {
            font-size: 18px;
            color: #dc2626;
        }

        .error-alert p {
            color: #dc2626;
            font-size: 13px;
            font-weight: 500;
        }

        /* =========================================
           Animations
           ========================================= */
        .fade-in {
            animation: fadeIn 0.3s ease;
        }

        @keyframes fadeIn {
            from {
                opacity: 0;
                transform: translateY(-10px);
            }
            to {
                opacity: 1;
                transform: translateY(0);
            }
        }

        /* =========================================
           Responsive Design
           ========================================= */
        @media (max-width: 768px) {
            body {
                padding: 16px;
            }
            
            .login-card {
                padding: 28px 24px;
            }

            .login-header h2 {
                font-size: 24px;
            }

            .logo {
                width: 60px;
                height: 60px;
                border-radius: 20px;
            }

            .logo i {
                font-size: 28px;
            }

            .role-tab {
                padding: 10px;
                font-size: 13px;
            }
        }
    </style>
</head>
<body>

<div class="login-container">
    <div class="login-card">
        
        <div class="login-header">
            <div class="logo">
                <i class="fas fa-graduation-cap"></i>
            </div>
            <h2>Selamat Datang</h2>
            <p>Silakan login untuk melanjutkan</p>
        </div>

        <?php if (isset($_GET['error'])): ?>
            <div class="error-alert">
                <i class="fas fa-exclamation-circle"></i>
                <p>Login gagal! Periksa kembali NIS/Username dan password Anda.</p>
            </div>
        <?php endif; ?>

        <div class="role-tabs">
            <button type="button" class="role-tab admin active" onclick="setRole('admin')">
                <i class="fas fa-user-shield"></i> Admin
            </button>
            <button type="button" class="role-tab siswa" onclick="setRole('siswa')">
                <i class="fas fa-user-graduate"></i> Siswa
            </button>
        </div>

        <form id="loginForm" action="proses_login.php" method="POST" onsubmit="return validateForm()">
            <input type="hidden" name="role" id="roleInput" value="admin">
            
            <div id="adminFields" class="fade-in">
                <div class="form-group">
                    <label><i class="fas fa-user"></i> Username</label>
                    <input type="text" id="username" name="username" placeholder="Masukkan username admin" autocomplete="off">
                    <small class="error-message" id="usernameError"></small>
                </div>
            </div>

            <div id="siswaFields" class="fade-in" style="display: none;">
                <div class="form-group">
                    <label><i class="fas fa-id-card"></i> NIS (8 Angka)</label>
                    <input type="text" id="nis" name="nis" placeholder="Masukkan NIS (8 digit)" maxlength="8" autocomplete="off">
                    <small class="error-message" id="nisError"></small>
                    <div class="info-text">Masukkan NIS sebanyak 8 digit angka</div>
                </div>
            </div>

            <div class="form-group">
                <label><i class="fas fa-lock"></i> Password</label>
                <div class="input-wrapper">
                    <input type="password" name="password" id="password" placeholder="Masukkan password" required>
                    <button type="button" class="password-toggle" onclick="togglePassword()">
                        <i class="far fa-eye"></i>
                    </button>
                </div>
            </div>

            <button type="submit" class="btn-login" id="loginBtn">
                <i class="fas fa-arrow-right"></i> Login sebagai Admin
            </button>

            <div class="login-footer">
                <p>Belum punya akun siswa?</p>
                <div>
                    <a href="register.php">
                        <i class="fas fa-user-plus"></i> Request Akun Siswa
                    </a>
                    <a href="../index.php" class="back-btn">
                        <i class="fas fa-arrow-left"></i> Kembali ke Beranda
                    </a>
                </div>
            </div>
        </form>

    </div>
</div>

<script>
    // Inisialisasi Elemen
    const roleInput = document.getElementById("roleInput");
    const loginBtn = document.getElementById("loginBtn");
    const adminTab = document.querySelector('.role-tab.admin');
    const siswaTab = document.querySelector('.role-tab.siswa');
    const adminFields = document.getElementById("adminFields");
    const siswaFields = document.getElementById("siswaFields");
    const usernameInput = document.getElementById("username");
    const nisInput = document.getElementById("nis");
    const usernameError = document.getElementById("usernameError");
    const nisError = document.getElementById("nisError");

    // Fungsi Pengaturan Role
    function setRole(role) {
        roleInput.value = role;
        
        // Reset tampilan tab
        adminTab.classList.remove('active');
        siswaTab.classList.remove('active');
        
        if (role === 'admin') {
            adminTab.classList.add('active');
            loginBtn.innerHTML = '<i class="fas fa-arrow-right"></i> Login sebagai Admin';
            loginBtn.style.background = 'linear-gradient(135deg, #6366f1, #8b5cf6)';
            
            // Atur visibilitas field
            adminFields.style.display = 'block';
            siswaFields.style.display = 'none';
            
            // Atur atribut wajib
            usernameInput.required = true;
            nisInput.required = false;
            
            // Bersihkan field NIS
            nisInput.value = '';
            nisError.textContent = '';
            nisInput.classList.remove('error', 'success');
            
        } else {
            siswaTab.classList.add('active');
            loginBtn.innerHTML = '<i class="fas fa-arrow-right"></i> Login sebagai Siswa';
            loginBtn.style.background = 'linear-gradient(135deg, #10b981, #059669)';
            
            // Atur visibilitas field
            adminFields.style.display = 'none';
            siswaFields.style.display = 'block';
            
            // Atur atribut wajib
            usernameInput.required = false;
            nisInput.required = true;
            
            // Bersihkan field Username
            usernameInput.value = '';
            usernameError.textContent = '';
            usernameInput.classList.remove('error', 'success');
        }
    }

    // Validasi Live NIS (8 Digit)
    nisInput.addEventListener("input", function() {
        nisInput.value = nisInput.value.replace(/[^0-9]/g, ''); // Hanya angka
        
        if (nisInput.value.length !== 8 && nisInput.value.length > 0) {
            nisInput.classList.add("error");
            nisInput.classList.remove("success");
            nisError.textContent = "NIS harus 8 digit angka!";
        } else if (nisInput.value.length === 8) {
            nisInput.classList.remove("error");
            nisInput.classList.add("success");
            nisError.textContent = "";
        } else {
            nisInput.classList.remove("error", "success");
            nisError.textContent = "";
        }
    });

    // Validasi Live Username
    usernameInput.addEventListener("input", function() {
        if (usernameInput.value.trim() === "") {
            usernameInput.classList.add("error");
            usernameError.textContent = "Username tidak boleh kosong!";
        } else if (usernameInput.value.length < 3) {
            usernameInput.classList.add("error");
            usernameError.textContent = "Username minimal 3 karakter!";
        } else {
            usernameInput.classList.remove("error");
            usernameInput.classList.add("success");
            usernameError.textContent = "";
        }
    });

    // Validasi Final saat Submit
    function validateForm() {
        const role = roleInput.value;
        let isValid = true;
        
        if (role === 'admin') {
            const username = usernameInput.value.trim();
            if (username === "") {
                usernameInput.classList.add("error");
                usernameError.textContent = "Username tidak boleh kosong!";
                usernameInput.scrollIntoView({ behavior: 'smooth', block: 'center' });
                isValid = false;
            } else if (username.length < 3) {
                usernameInput.classList.add("error");
                usernameError.textContent = "Username minimal 3 karakter!";
                usernameInput.scrollIntoView({ behavior: 'smooth', block: 'center' });
                isValid = false;
            }
        } else {
            const nisValue = nisInput.value;
            if (nisValue === "") {
                nisInput.classList.add("error");
                nisError.textContent = "NIS tidak boleh kosong!";
                nisInput.scrollIntoView({ behavior: 'smooth', block: 'center' });
                isValid = false;
            } else if (nisValue.length !== 8) {
                nisInput.classList.add("error");
                nisError.textContent = "NIS harus 8 digit angka!";
                nisInput.scrollIntoView({ behavior: 'smooth', block: 'center' });
                isValid = false;
            }
        }
        
        return isValid;
    }

    // Toggle Tampilkan/Sembunyikan Password
    function togglePassword() {
        const passwordInput = document.getElementById("password");
        const toggleBtn = document.querySelector(".password-toggle i");
        
        if (passwordInput.type === "password") {
            passwordInput.type = "text";
            toggleBtn.classList.remove("fa-eye");
            toggleBtn.classList.add("fa-eye-slash");
        } else {
            passwordInput.type = "password";
            toggleBtn.classList.remove("fa-eye-slash");
            toggleBtn.classList.add("fa-eye");
        }
    }

    // Set role default saat halaman dimuat
    setRole('admin');
</script>

</body>
</html>