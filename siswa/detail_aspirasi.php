<?php
session_start();
include '../config/koneksi.php';

if (!isset($_SESSION['user']) || $_SESSION['user']['role'] != 'siswa') {
    header("Location: ../auth/login.php");
    exit;
}

$id = $_GET['id'];
$user_id = $_SESSION['user']['id'];

// ambil data aspirasi + user
$data = mysqli_fetch_assoc(mysqli_query($conn, "
SELECT a.*, u.name 
FROM aspirations a 
JOIN users u ON a.user_id = u.id
WHERE a.id='$id'
"));

// ambil feedback
$feedback = mysqli_query($conn, "
SELECT f.*, u.name as admin_name
FROM feedbacks f
JOIN users u ON f.admin_id = u.id
WHERE f.aspiration_id='$id'
ORDER BY f.id DESC
");

// ambil progress
$progress = mysqli_query($conn, "
SELECT * FROM progress 
WHERE aspiration_id='$id'
ORDER BY id ASC
");

// ambil status terbaru dari progress
$latestProgress = mysqli_fetch_assoc(mysqli_query($conn, "
    SELECT status FROM progress 
    WHERE aspiration_id='$id' 
    ORDER BY id DESC LIMIT 1
"));
$currentStatus = $latestProgress ? $latestProgress['status'] : $data['status'];
?>

<!DOCTYPE html>
<html>
<head>
    <title>Detail Aspirasi | Sistem Aspirasi</title>
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@300;400;500;600;700;800&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="../assets/siswa_detail.css">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
</head>
<body>

<div class="container">
    <!-- Header -->
    <div class="header">
        <div class="header-left">
            <div class="logo">
                <i class="fas fa-file-alt"></i>
                <span>Detail Aspirasi</span>
            </div>
            <h1>Informasi Lengkap Aspirasi</h1>
            <p class="breadcrumb">
                <i class="fas fa-home"></i> Dashboard / Detail Aspirasi
            </p>
        </div>
        <a href="dashboard.php" class="back-btn">
            <i class="fas fa-arrow-left"></i> Kembali ke Dashboard
        </a>
    </div>

    <!-- Informasi Aspirasi -->
    <div class="card">
        <div class="card-header">
            <h2><i class="fas fa-info-circle"></i> Informasi Aspirasi</h2>
            <span class="status-badge status-<?= $currentStatus; ?>">
                <?php 
                $icon = '';
                if($currentStatus == 'menunggu') $icon = '⏳';
                elseif($currentStatus == 'pengecekan') $icon = '🔍';
                elseif($currentStatus == 'proses') $icon = '⚙️';
                elseif($currentStatus == 'selesai') $icon = '✅';
                echo $icon . ' ' . strtoupper($currentStatus);
                ?>
            </span>
        </div>
        
        <div class="info-grid">
            <div class="info-item">
                <div class="info-icon"><i class="fas fa-user"></i></div>
                <div class="info-content">
                    <div class="info-label">Pengirim</div>
                    <div class="info-value"><?= ($data['user_id'] == $user_id) ? '👤 Saya' : '🔒 Siswa'; ?></div>
                </div>
            </div>
            <div class="info-item">
                <div class="info-icon"><i class="fas fa-location-dot"></i></div>
                <div class="info-content">
                    <div class="info-label">Lokasi</div>
                    <div class="info-value"><?= ucfirst(htmlspecialchars($data['lokasi'])); ?></div>
                </div>
            </div>
            <div class="info-item">
                <div class="info-icon"><i class="fas fa-tag"></i></div>
                <div class="info-content">
                    <div class="info-label">Kategori</div>
                    <div class="info-value"><?= ucfirst(htmlspecialchars($data['kategori'])); ?></div>
                </div>
            </div>
            <div class="info-item">
                <div class="info-icon"><i class="fas fa-calendar"></i></div>
                <div class="info-content">
                    <div class="info-label">Tanggal Dibuat</div>
                    <div class="info-value"><?= date('d F Y, H:i', strtotime($data['created_at'])); ?></div>
                </div>
            </div>
        </div>

        <div class="aspirasi-content">
            <div class="section-title">
                <i class="fas fa-message"></i>
                <h3>Isi Aspirasi</h3>
            </div>
            <div class="aspirasi-text">
                <?= nl2br(htmlspecialchars($data['isi'])); ?>
            </div>
        </div>

        <?php if($data['foto']) { ?>
            <div class="aspirasi-image">
                <div class="section-title">
                    <i class="fas fa-image"></i>
                    <h3>Foto Pendukung</h3>
                </div>
                <div class="image-wrapper">
                    <img src="../uploads/<?= $data['foto']; ?>" alt="Foto Aspirasi" onclick="openModal(this.src)">
                    <div class="image-caption">Klik gambar untuk memperbesar</div>
                </div>
            </div>
        <?php } ?>
    </div>

    <!-- Riwayat Progress -->
    <?php if(mysqli_num_rows($progress) > 0) { ?>
    <div class="card">
        <div class="card-header">
            <h2><i class="fas fa-chart-line"></i> Riwayat Progress</h2>
        </div>
        <div class="progress-timeline">
            <?php while($p = mysqli_fetch_assoc($progress)) { ?>
                <div class="progress-item <?= $p['status'] ?>">
                    <div class="progress-icon">
                        <?php 
                        if($p['status'] == 'menunggu') echo '⏳';
                        elseif($p['status'] == 'pengecekan') echo '🔍';
                        elseif($p['status'] == 'proses') echo '⚙️';
                        elseif($p['status'] == 'selesai') echo '✅';
                        ?>
                    </div>
                    <div class="progress-content">
                        <div class="progress-status">
                            <?= strtoupper($p['status']); ?>
                        </div>
                        <div class="progress-date">
                            <i class="fas fa-clock"></i> <?= date('d F Y, H:i:s', strtotime($p['tanggal'])); ?>
                        </div>
                    </div>
                </div>
            <?php } ?>
        </div>
    </div>
    <?php } ?>

    <!-- Feedback Admin -->
    <div class="card">
        <div class="card-header">
            <h2><i class="fas fa-comments"></i> Feedback Admin</h2>
        </div>
        <?php if(mysqli_num_rows($feedback) > 0): ?>
            <div class="feedback-list">
                <?php while($f = mysqli_fetch_assoc($feedback)) { ?>
                    <div class="feedback-item">
                        <div class="feedback-header">
                            <div class="feedback-author">
                                <i class="fas fa-user-shield"></i>
                                <?= htmlspecialchars($f['admin_name']); ?>
                                <span class="admin-badge">Admin</span>
                            </div>
                            <div class="feedback-date">
                                <i class="fas fa-calendar-alt"></i>
                                <?= date('d F Y, H:i', strtotime($f['created_at'])); ?>
                            </div>
                        </div>
                        <div class="feedback-content">
                            <i class="fas fa-quote-left"></i>
                            <?= nl2br(htmlspecialchars($f['isi_feedback'])); ?>
                        </div>
                        <?php if($f['foto']) { ?>
                            <div class="feedback-image">
                                <img src="../uploads/<?= $f['foto']; ?>" alt="Bukti Feedback" onclick="openModal(this.src)">
                            </div>
                        <?php } ?>
                    </div>
                <?php } ?>
            </div>
        <?php else: ?>
            <div class="empty-feedback">
                <i class="fas fa-inbox"></i>
                <p>Belum ada feedback dari admin</p>
                <small>Feedback akan muncul setelah admin merespons</small>
            </div>
        <?php endif; ?>
    </div>
</div>

<!-- Modal untuk preview gambar -->
<div id="imageModal" class="modal" onclick="closeModal()">
    <span class="modal-close">&times;</span>
    <img class="modal-content" id="modalImage">
</div>

<!-- Scroll to Top Button -->
<button id="scrollTopBtn" class="scroll-top-btn" onclick="scrollToTop()">
    <i class="fas fa-arrow-up"></i>
</button>

<script>
// Image modal
function openModal(src) {
    const modal = document.getElementById('imageModal');
    const modalImg = document.getElementById('modalImage');
    modal.style.display = "flex";
    modalImg.src = src;
}

function closeModal() {
    document.getElementById('imageModal').style.display = "none";
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

// Close modal with escape key
document.addEventListener('keydown', function(e) {
    if (e.key === 'Escape') {
        closeModal();
    }
});
</script>

</body>
</html>