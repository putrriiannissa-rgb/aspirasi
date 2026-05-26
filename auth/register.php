<?php session_start(); ?>
<!DOCTYPE html>
<html>
<head>
    <title>Request Akun | Sistem Aspirasi Sekolah</title>
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@300;400;500;600;700;800&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <style>
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

        .register-container {
            position: relative;
            z-index: 1;
            width: 100%;
            max-width: 480px;
        }

        .register-card {
            background: white;
            border-radius: 28px;
            padding: 40px;
            box-shadow: 0 8px 32px rgba(0, 0, 0, 0.08);
            border: 1px solid #f1f5f9;
            transition: transform 0.3s ease;
        }

        .register-card:hover {
            transform: translateY(-4px);
        }

        .register-header {
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

        .register-header h2 {
            font-size: 28px;
            font-weight: 700;
            color: #1e293b;
            margin-bottom: 8px;
        }

        .register-header p {
            color: #64748b;
            font-size: 14px;
        }

        .form-group {
            margin-bottom: 20px;
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

        .password-wrapper {
            position: relative;
        }

        .password-wrapper input {
            padding-right: 45px;
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

        .btn-register {
            width: 100%;
            padding: 12px;
            background: linear-gradient(135deg, #10b981, #059669);
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

        .btn-register:hover {
            transform: translateY(-1px);
            box-shadow: 0 4px 12px rgba(16, 185, 129, 0.3);
        }

        .success-alert {
            background: #ecfdf5;
            border: 1px solid #a7f3d0;
            border-radius: 14px;
            padding: 12px 16px;
            margin-bottom: 24px;
            display: flex;
            align-items: center;
            gap: 12px;
        }

        .success-alert i {
            font-size: 18px;
            color: #059669;
        }

        .success-alert p {
            color: #059669;
            font-size: 13px;
            font-weight: 500;
        }

        .error-alert {
            background: #fef2f2;
            border: 1px solid #fecaca;
            border-radius: 14px;
            padding: 12px 16px;
            margin-bottom: 20px;
        }

        .error-alert i {
            color: #ef4444;
            margin-right: 8px;
        }

        .error-alert ul {
            color: #b91c1c;
            font-size: 13px;
            margin: 0;
            padding-left: 20px;
        }

        .register-footer {
            text-align: center;
            margin-top: 28px;
            padding-top: 24px;
            border-top: 1px solid #e2e8f0;
        }

        .register-footer p {
            color: #64748b;
            font-size: 13px;
            margin-bottom: 12px;
        }

        .register-footer a {
            color: #6366f1;
            text-decoration: none;
            font-size: 14px;
            font-weight: 600;
            transition: color 0.2s;
            display: inline-flex;
            align-items: center;
            gap: 6px;
        }

        .register-footer a:hover {
            color: #8b5cf6;
        }

        .info-text {
            font-size: 11px;
            color: #94a3b8;
            margin-top: 4px;
        }

        @media (max-width: 768px) {
            body {
                padding: 16px;
            }
            .register-card {
                padding: 28px 24px;
            }
            .register-header h2 {
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
        }
    </style>
</head>
<body>

<div class="register-container">
    <div class="register-card">
        <div class="register-header">
            <div class="logo">
                <i class="fas fa-user-plus"></i>
            </div>
            <h2>Request Akun Siswa</h2>
            <p>Isi form berikut untuk mengajukan akun</p>
        </div>

        <!-- Tampilkan error jika ada -->
        <?php if (isset($_SESSION['register_errors']) && !empty($_SESSION['register_errors'])): ?>
            <div class="error-alert">
                <i class="fas fa-exclamation-triangle"></i>
                <ul>
                    <?php foreach ($_SESSION['register_errors'] as $err): ?>
                        <li><?php echo htmlspecialchars($err); ?></li>
                    <?php endforeach; ?>
                </ul>
            </div>
            <?php unset($_SESSION['register_errors']); ?>
        <?php endif; ?>

        <!-- Tampilkan sukses jika ada -->
        <?php if (isset($_SESSION['register_success'])): ?>
            <div class="success-alert">
                <i class="fas fa-check-circle"></i>
                <p><?php echo htmlspecialchars($_SESSION['register_success']); ?></p>
            </div>
            <?php unset($_SESSION['register_success']); ?>
        <?php endif; ?>

        <form action="proses_register.php" method="POST" onsubmit="return validasiForm()">
            <div class="form-group">
                <label><i class="fas fa-user"></i> Nama Lengkap</label>
                <input type="text" name="name" id="name" 
                       value="<?php echo htmlspecialchars($_SESSION['old_input']['name'] ?? ''); ?>" 
                       placeholder="Masukkan nama lengkap" required>
            </div>

            <div class="form-group">
                <label><i class="fas fa-at"></i> Username</label>
                <input type="text" name="username" id="username" 
                       value="<?php echo htmlspecialchars($_SESSION['old_input']['username'] ?? ''); ?>" 
                       placeholder="Masukkan username" required>
            </div>

            <div class="form-group">
                <label><i class="fas fa-envelope"></i> Email</label>
                <input type="email" name="email" id="email" 
                       value="<?php echo htmlspecialchars($_SESSION['old_input']['email'] ?? ''); ?>" 
                       placeholder="contoh@sekolah.com" required>
                <small class="error-message" id="emailError"></small>
            </div>

            <div class="form-group">
                <label><i class="fas fa-id-card"></i> NIS (8 angka)</label>
                <input type="text" name="nis" id="nis" 
                       value="<?php echo htmlspecialchars($_SESSION['old_input']['nis'] ?? ''); ?>" 
                       placeholder="Masukkan NIS 8 digit" maxlength="8" required>
                <small class="error-message" id="nisError"></small>
                <div class="info-text">NIS terdiri dari 8 digit angka</div>
            </div>

            <div class="form-group">
                <label><i class="fas fa-lock"></i> Password</label>
                <div class="password-wrapper">
                    <input type="password" name="password" id="password" placeholder="Minimal 8 karakter" required>
                    <button type="button" class="password-toggle" onclick="togglePassword()">
                        <i class="far fa-eye"></i>
                    </button>
                </div>
            </div>

            <button type="submit" class="btn-register">
                <i class="fas fa-paper-plane"></i> Kirim Request
            </button>

            <div class="register-footer">
                <p>Sudah punya akun?</p>
                <a href="login.php">
                    <i class="fas fa-sign-in-alt"></i> Login Sekarang
                </a>
            </div>
        </form>
    </div>
</div>

<script>
// Email validation
const email = document.getElementById("email");
const emailError = document.getElementById("emailError");
const emailPattern = /^[^\s@]+@[^\s@]+\.[^\s@]+$/;

email.addEventListener("input", function() {
    const emailValue = email.value;
    
    if (emailValue !== "" && !emailPattern.test(emailValue)) {
        email.classList.add("error");
        email.classList.remove("success");
        emailError.textContent = "Format email tidak valid! Contoh: nama@domain.com";
    } else if (emailPattern.test(emailValue)) {
        email.classList.remove("error");
        email.classList.add("success");
        emailError.textContent = "";
    } else {
        email.classList.remove("error", "success");
        emailError.textContent = "";
    }
});

// NIS validation (8 digits)
const nis = document.getElementById("nis");
const nisError = document.getElementById("nisError");

nis.addEventListener("input", function() {
    nis.value = nis.value.replace(/[^0-9]/g, '');
    
    if (nis.value.length !== 8 && nis.value.length > 0) {
        nis.classList.add("error");
        nis.classList.remove("success");
        nisError.textContent = "NIS harus 8 digit angka!";
    } else if (nis.value.length === 8) {
        nis.classList.remove("error");
        nis.classList.add("success");
        nisError.textContent = "";
    } else {
        nis.classList.remove("error", "success");
        nisError.textContent = "";
    }
});

function validasiForm() {
    let isValid = true;
    
    if (!emailPattern.test(email.value)) {
        email.classList.add("error");
        emailError.textContent = "Email tidak valid!";
        isValid = false;
    }
    
    if (nis.value.length !== 8) {
        nis.classList.add("error");
        nisError.textContent = "NIS harus 8 digit angka!";
        isValid = false;
    }
    
    return isValid;
}

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

// Hapus session old_input setelah ditampilkan (opsional, tapi biarkan karena hanya sekali)
<?php if (isset($_SESSION['old_input'])) unset($_SESSION['old_input']); ?>
</script>

</body>
</html>