<?php
session_start();

if (!isset($_SESSION['user'])) {
    header("Location: ../auth/login.php");
    exit;
}

$user = $_SESSION['user'];
?>

<!DOCTYPE html>
<html>
<head>
    <title>Profil Siswa | Sistem Aspirasi</title>
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@300;400;500;600;700;800&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="../assets/profil_siswa.css">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
</head>
<body>

<div class="container">
    <!-- Header -->
    <div class="header">
        <h1>
            <i class="fas fa-user-graduate"></i> Profil Siswa
        </h1>
        <a href="dashboard.php" class="back-btn">
            <i class="fas fa-arrow-left"></i> Kembali ke Dashboard
        </a>
    </div>

    <!-- Alert Messages -->
    <?php if(isset($_GET['success'])): ?>
        <div class="alert alert-success">
            <i class="fas fa-check-circle"></i>
            Profil berhasil diperbarui!
        </div>
    <?php endif; ?>

    <?php if(isset($_GET['error'])): ?>
        <div class="alert alert-error">
            <i class="fas fa-exclamation-circle"></i>
            Gagal memperbarui profil. Silakan coba lagi.
        </div>
    <?php endif; ?>

    <!-- Profile Card -->
    <div class="profile-card">
        <div class="profile-header">
            <div class="profile-avatar">
                <?php
                $inisial = strtoupper(substr($user['name'], 0, 2));
                echo $inisial;
                ?>
            </div>
            <div class="profile-title">
                <h2><?= htmlspecialchars($user['name']); ?></h2>
                <span class="profile-badge">
                    <i class="fas fa-crown"></i> <?= ucfirst($user['role']); ?>
                </span>
            </div>
        </div>

        <div class="profile-info">
            <div class="info-row">
                <div class="info-icon">
                    <i class="fas fa-envelope"></i>
                </div>
                <div class="info-content">
                    <div class="info-label">Email</div>
                    <div class="info-value"><?= htmlspecialchars($user['email']); ?></div>
                </div>
            </div>

            <div class="info-row">
                <div class="info-icon">
                    <i class="fas fa-user"></i>
                </div>
                <div class="info-content">
                    <div class="info-label">Username</div>
                    <div class="info-value"><?= htmlspecialchars($user['username']); ?></div>
                </div>
            </div>

            <?php if(isset($user['nis'])): ?>
            <div class="info-row">
                <div class="info-icon">
                    <i class="fas fa-id-card"></i>
                </div>
                <div class="info-content">
                    <div class="info-label">NIS</div>
                    <div class="info-value"><?= htmlspecialchars($user['nis']); ?></div>
                </div>
            </div>
            <?php endif; ?>

            <div class="info-row">
                <div class="info-icon">
                    <i class="fas fa-calendar-alt"></i>
                </div>
                <div class="info-content">
                    <div class="info-label">Bergabung Sejak</div>
                    <div class="info-value"><?= date('d F Y', strtotime($user['created_at'])); ?></div>
                </div>
            </div>
        </div>

        <div class="profile-actions">
            <button onclick="toggleEditForm()" class="btn btn-primary">
                <i class="fas fa-edit"></i> Edit Profil
            </button>
            <a href="../auth/logout.php" onclick="return confirm('Yakin ingin logout?')" class="btn btn-danger">
                <i class="fas fa-sign-out-alt"></i> Logout
            </a>
        </div>

        <!-- Edit Form -->
        <div id="editForm" class="edit-form">
            <h3>
                <i class="fas fa-pencil-alt"></i> Edit Profil
            </h3>
            <form action="proses_edit_profil.php" method="POST">
                <div class="form-group">
                    <label><i class="fas fa-user"></i> Nama Lengkap</label>
                    <input type="text" name="name" value="<?= htmlspecialchars($user['name']); ?>" required>
                </div>

                <div class="form-group">
                    <label><i class="fas fa-at"></i> Username</label>
                    <input type="text" name="username" value="<?= htmlspecialchars($user['username']); ?>" required>
                </div>

                <div class="form-group">
                    <label><i class="fas fa-envelope"></i> Email</label>
                    <input type="email" name="email" value="<?= htmlspecialchars($user['email']); ?>" required>
                </div>

                <div class="form-group">
                    <label><i class="fas fa-lock"></i> Password Baru</label>
                    <input type="password" name="password" placeholder="Kosongkan jika tidak ingin mengubah password">
                </div>

                <div class="form-actions">
                    <button type="submit" class="btn btn-save">
                        <i class="fas fa-save"></i> Simpan Perubahan
                    </button>
                    <button type="button" onclick="toggleEditForm()" class="btn btn-cancel">
                        <i class="fas fa-times"></i> Batal
                    </button>
                </div>
            </form>
        </div>
    </div>
</div>

<!-- Scroll to Top Button -->
<button id="scrollTopBtn" class="scroll-top-btn" onclick="scrollToTop()">
    <i class="fas fa-arrow-up"></i>
</button>

<script>
function toggleEditForm() {
    let form = document.getElementById("editForm");
    form.classList.toggle("show");
}

// Scroll to Top
const scrollTopBtn = document.getElementById("scrollTopBtn");

window.addEventListener('scroll', function() {
    if (document.body.scrollTop > 200 || document.documentElement.scrollTop > 200) {
        scrollTopBtn.classList.add("show");
    } else {
        scrollTopBtn.classList.remove("show");
    }
});

function scrollToTop() {
    window.scrollTo({ top: 0, behavior: "smooth" });
}
</script>

</body>
</html>