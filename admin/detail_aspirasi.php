<?php
session_start();
include '../config/koneksi.php';

/** @var mysqli $conn */

// Cek login admin
if (!isset($_SESSION['user']) || $_SESSION['user']['role'] != 'admin') {
    header("Location: ../auth/login.php");
    exit;
}

// Cek parameter id
if (!isset($_GET['id']) || !is_numeric($_GET['id'])) {
    header("Location: dashboard.php?error=ID aspirasi tidak valid");
    exit;
}

$id = (int)$_GET['id'];

// Ambil data aspirasi + user
$query = mysqli_query($conn, "
    SELECT a.*, u.name, u.nis 
    FROM aspirations a 
    JOIN users u ON a.user_id = u.id
    WHERE a.id = $id
");

if (mysqli_num_rows($query) == 0) {
    header("Location: dashboard.php?error=Aspirasi tidak ditemukan");
    exit;
}

$data = mysqli_fetch_assoc($query);

// Status terbaru dari progress
$latest = mysqli_fetch_assoc(mysqli_query($conn, "
    SELECT status FROM progress 
    WHERE aspiration_id = $id 
    ORDER BY id DESC LIMIT 1
"));
$currentStatus = $latest ? $latest['status'] : $data['status'];

// Feedback (dari admin)
$feedback = mysqli_query($conn, "
    SELECT f.*, u.name as admin_name
    FROM feedbacks f
    JOIN users u ON f.admin_id = u.id
    WHERE f.aspiration_id = $id
    ORDER BY f.id DESC
");

// Riwayat progress
$progress = mysqli_query($conn, "
    SELECT * FROM progress 
    WHERE aspiration_id = $id
    ORDER BY id ASC
");
?>
<!DOCTYPE html>
<html>
<head>
    <title>Detail Aspirasi - Admin</title>
    <link rel="stylesheet" href="../assets/admin_detail.css">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <style>
        .alert {
            padding: 12px 20px;
            border-radius: 8px;
            margin-bottom: 20px;
            font-weight: 500;
        }
        .alert-success {
            background-color: #d1fae5;
            color: #065f46;
            border-left: 4px solid #10b981;
        }
        .alert-error {
            background-color: #fee2e2;
            color: #991b1b;
            border-left: 4px solid #ef4444;
        }
    </style>
</head>
<body>
<div class="container">
    <div class="header">
        <h1><i class="fas fa-file-alt"></i> Detail Aspirasi</h1>
        <a href="dashboard.php" class="back-btn"><i class="fas fa-arrow-left"></i> Kembali ke Dashboard</a>
    </div>

    <!-- Pesan sukses/error -->
    <?php if (isset($_GET['success'])): ?>
        <div class="alert alert-success"><?= htmlspecialchars($_GET['success']) ?></div>
    <?php elseif (isset($_GET['error'])): ?>
        <div class="alert alert-error"><?= htmlspecialchars($_GET['error']) ?></div>
    <?php endif; ?>

    <!-- Informasi Aspirasi -->
    <div class="card">
        <h2><i class="fas fa-info-circle"></i> Informasi Aspirasi</h2>
        <div class="info-grid">
            <div class="info-item">
                <div class="info-label"><i class="fas fa-user"></i> Nama Siswa</div>
                <div class="info-value"><?= htmlspecialchars($data['name']) ?></div>
            </div>
            <div class="info-item">
                <div class="info-label"><i class="fas fa-id-card"></i> NIS</div>
                <div class="info-value"><?= htmlspecialchars($data['nis']) ?></div>
            </div>
            <div class="info-item">
                <div class="info-label"><i class="fas fa-location-dot"></i> Lokasi</div>
                <div class="info-value"><?= htmlspecialchars($data['lokasi']) ?></div>
            </div>
            <div class="info-item">
                <div class="info-label"><i class="fas fa-tag"></i> Kategori</div>
                <div class="info-value"><?= htmlspecialchars($data['kategori']) ?></div>
            </div>
            <div class="info-item">
                <div class="info-label"><i class="fas fa-flag"></i> Status</div>
                <div class="info-value">
                    <span class="status-badge status-<?= $currentStatus ?>">
                        <?php
                        $icon = match($currentStatus) {
                            'menunggu' => '⏳',
                            'pengecekan' => '🔍',
                            'proses' => '⚙️',
                            'selesai' => '✅',
                            default => ''
                        };
                        echo $icon . ' ' . strtoupper($currentStatus);
                        ?>
                    </span>
                </div>
            </div>
            <div class="info-item">
                <div class="info-label"><i class="fas fa-calendar"></i> Tanggal Dibuat</div>
                <div class="info-value"><?= date('d/m/Y H:i', strtotime($data['created_at'])) ?></div>
            </div>
        </div>
        <div class="info-item full-width">
            <div class="info-label"><i class="fas fa-message"></i> Isi Aspirasi</div>
            <div class="info-value"><?= nl2br(htmlspecialchars($data['isi'])) ?></div>
        </div>
        <?php if (!empty($data['foto'])): ?>
            <div class="aspirasi-image">
                <div class="info-label"><i class="fas fa-image"></i> Foto Pendukung</div>
                <img src="../uploads/<?= $data['foto'] ?>" alt="Foto Aspirasi">
            </div>
        <?php endif; ?>
    </div>

    <!-- Update Status -->
    <div class="card">
        <h2><i class="fas fa-sync-alt"></i> Update Status</h2>
        <form action="proses_progress.php" method="POST">
            <input type="hidden" name="aspiration_id" value="<?= $id ?>">
            <div class="form-group">
                <label><i class="fas fa-tasks"></i> Pilih Status</label>
                <select name="status" <?= $currentStatus == 'selesai' ? 'disabled' : '' ?> required>
                    <option value="pengecekan" <?= $currentStatus == 'pengecekan' ? 'selected' : '' ?>>🔍 Pengecekan</option>
                    <option value="menunggu" <?= $currentStatus == 'menunggu' ? 'selected' : '' ?>>⏳ Menunggu</option>
                    <option value="proses" <?= $currentStatus == 'proses' ? 'selected' : '' ?>>⚙️ Proses</option>
                    <option value="selesai" <?= $currentStatus == 'selesai' ? 'selected' : '' ?>>✅ Selesai</option>
                </select>
            </div>
            <?php if ($currentStatus != 'selesai'): ?>
                <button type="submit" class="btn-submit"><i class="fas fa-save"></i> Update Status</button>
            <?php else: ?>
                <button type="button" class="btn-submit disabled" disabled><i class="fas fa-check-circle"></i> Status Sudah Selesai</button>
            <?php endif; ?>
        </form>
    </div>

    <!-- Form Beri Feedback -->
    <div class="card">
        <h2><i class="fas fa-comment-dots"></i> Beri Feedback</h2>
        <form action="proses_feedback.php" method="POST" enctype="multipart/form-data">
            <input type="hidden" name="aspiration_id" value="<?= $id ?>">
            <div class="form-group">
                <label><i class="fas fa-comment"></i> Feedback</label>
                <textarea name="feedback" placeholder="Tulis feedback atau tanggapan Anda..." required></textarea>
            </div>
            <div class="form-group">
                <label><i class="fas fa-image"></i> Lampiran (opsional)</label>
                <input type="file" name="foto" accept="image/*">
                <small class="form-help">Format: JPG, PNG, GIF (Max 2MB)</small>
            </div>
            <button type="submit" class="btn-submit"><i class="fas fa-paper-plane"></i> Kirim Feedback</button>
        </form>
    </div>

    <!-- Riwayat Progress -->
    <?php if (mysqli_num_rows($progress) > 0): ?>
    <div class="card">
        <h2><i class="fas fa-history"></i> Riwayat Progress</h2>
        <div class="progress-timeline">
            <?php while ($p = mysqli_fetch_assoc($progress)): ?>
                <div class="progress-item">
                    <div class="progress-status">
                        <?php
                        $ic = match($p['status']) {
                            'menunggu' => '⏳',
                            'pengecekan' => '🔍',
                            'proses' => '⚙️',
                            'selesai' => '✅',
                            default => ''
                        };
                        echo $ic . ' ' . strtoupper($p['status']);
                        ?>
                    </div>
                    <div class="progress-date"><i class="fas fa-calendar-alt"></i> <?= date('d/m/Y H:i:s', strtotime($p['tanggal'])) ?></div>
                </div>
            <?php endwhile; ?>
        </div>
    </div>
    <?php endif; ?>

    <!-- Daftar Feedback Admin -->
    <?php if (mysqli_num_rows($feedback) > 0): ?>
    <div class="card">
        <h2><i class="fas fa-comments"></i> Feedback dari Admin</h2>
        <div class="feedback-list">
            <?php while ($f = mysqli_fetch_assoc($feedback)): ?>
                <div class="feedback-item">
                    <div class="feedback-header">
                        <div class="feedback-author">
                            <i class="fas fa-user-circle"></i> <?= htmlspecialchars($f['admin_name']) ?>
                            <span class="feedback-badge">Admin</span>
                        </div>
                        <div class="feedback-date"><i class="fas fa-clock"></i> <?= date('d/m/Y H:i', strtotime($f['created_at'])) ?></div>
                    </div>
                    <div class="feedback-content"><i class="fas fa-quote-left"></i> <?= nl2br(htmlspecialchars($f['isi_feedback'])) ?></div>
                    <?php if (!empty($f['foto'])): ?>
                        <div class="feedback-image">
                            <a href="../uploads/<?= $f['foto'] ?>" target="_blank"><img src="../uploads/<?= $f['foto'] ?>" alt="Lampiran"></a>
                        </div>
                    <?php endif; ?>
                </div>
            <?php endwhile; ?>
        </div>
    </div>
    <?php endif; ?>
</div>
</body>
</html>