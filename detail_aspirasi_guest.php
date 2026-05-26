<?php
include 'config/koneksi.php';

$id = $_GET['id'];

$data = mysqli_fetch_assoc(mysqli_query($conn,"
SELECT a.*,
COALESCE(
    (SELECT p.status FROM progress p WHERE p.aspiration_id = a.id ORDER BY p.id DESC LIMIT 1),
    a.status
) as status_terbaru
FROM aspirations a
WHERE a.id='$id'
"));

$status = $data['status_terbaru'] ?? $data['status'];

// Ambil progress history
$progress = mysqli_query($conn, "
SELECT * FROM progress 
WHERE aspiration_id='$id'
ORDER BY id ASC
");

// Ambil feedback
$feedback = mysqli_query($conn, "
SELECT f.*, u.name as admin_name
FROM feedbacks f
JOIN users u ON f.admin_id = u.id
WHERE f.aspiration_id='$id'
ORDER BY f.id DESC
");
?>

<!DOCTYPE html>
<html>
<head>
    <title>Detail Aspirasi | Sistem Aspirasi</title>
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

        .container {
            position: relative;
            z-index: 1;
            max-width: 900px;
            margin: 0 auto;
        }

        /* ==================== HEADER ==================== */
        .header {
            background: white;
            border-radius: 24px;
            padding: 20px 28px;
            margin-bottom: 24px;
            display: flex;
            justify-content: space-between;
            align-items: center;
            flex-wrap: wrap;
            gap: 16px;
            box-shadow: 0 1px 3px rgba(0, 0, 0, 0.04);
            border: 1px solid #f1f5f9;
            animation: fadeInDown 0.4s ease;
        }

        @keyframes fadeInDown {
            from {
                opacity: 0;
                transform: translateY(-20px);
            }
            to {
                opacity: 1;
                transform: translateY(0);
            }
        }

        .header-left .logo {
            display: flex;
            align-items: center;
            gap: 10px;
            margin-bottom: 6px;
        }

        .header-left .logo i {
            font-size: 24px;
            background: linear-gradient(135deg, #6366f1, #8b5cf6);
            -webkit-background-clip: text;
            background-clip: text;
            color: transparent;
        }

        .header-left .logo span {
            font-size: 16px;
            font-weight: 700;
            background: linear-gradient(135deg, #6366f1, #8b5cf6);
            -webkit-background-clip: text;
            background-clip: text;
            color: transparent;
        }

        .header-left h1 {
            font-size: 20px;
            font-weight: 600;
            color: #1e293b;
            margin-top: 4px;
        }

        .breadcrumb {
            font-size: 12px;
            color: #64748b;
            margin-top: 4px;
        }

        .breadcrumb i {
            margin-right: 4px;
            font-size: 10px;
            color: #6366f1;
        }

        .back-btn {
            background: #f8fafc;
            color: #475569;
            padding: 10px 20px;
            border-radius: 12px;
            text-decoration: none;
            font-size: 13px;
            font-weight: 500;
            transition: all 0.2s ease;
            display: inline-flex;
            align-items: center;
            gap: 8px;
            border: 1px solid #e2e8f0;
        }

        .back-btn:hover {
            background: white;
            transform: translateY(-1px);
            box-shadow: 0 2px 8px rgba(0, 0, 0, 0.04);
            border-color: #cbd5e1;
        }

        /* ==================== CARD ==================== */
        .card {
            background: white;
            border-radius: 24px;
            margin-bottom: 24px;
            box-shadow: 0 1px 3px rgba(0, 0, 0, 0.04);
            border: 1px solid #f1f5f9;
            overflow: hidden;
            animation: fadeInUp 0.4s ease;
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

        .card-header {
            background: #f8fafc;
            padding: 16px 24px;
            border-bottom: 1px solid #e2e8f0;
            display: flex;
            justify-content: space-between;
            align-items: center;
            flex-wrap: wrap;
            gap: 12px;
        }

        .card-header h2 {
            font-size: 18px;
            font-weight: 600;
            color: #1e293b;
            display: flex;
            align-items: center;
            gap: 10px;
        }

        .card-header h2 i {
            color: #6366f1;
        }

        .card-body {
            padding: 24px;
        }

        /* ==================== STATUS BADGE ==================== */
        .status-badge {
            display: inline-flex;
            align-items: center;
            gap: 6px;
            padding: 6px 14px;
            border-radius: 20px;
            font-size: 12px;
            font-weight: 600;
        }

        .status-menunggu {
            background: #fffbeb;
            color: #d97706;
            border: 1px solid #fde68a;
        }

        .status-pengecekan {
            background: #eef2ff;
            color: #4f46e5;
            border: 1px solid #c7d2fe;
        }

        .status-proses {
            background: #eff6ff;
            color: #2563eb;
            border: 1px solid #bfdbfe;
        }

        .status-selesai {
            background: #ecfdf5;
            color: #059669;
            border: 1px solid #a7f3d0;
        }

        /* ==================== INFO GRID ==================== */
        .info-grid {
            display: grid;
            grid-template-columns: repeat(auto-fit, minmax(250px, 1fr));
            gap: 16px;
            margin-bottom: 24px;
        }

        .info-item {
            display: flex;
            align-items: center;
            gap: 12px;
            padding: 12px 16px;
            background: #f8fafc;
            border-radius: 14px;
        }

        .info-icon i {
            font-size: 20px;
            color: #6366f1;
        }

        .info-content {
            flex: 1;
        }

        .info-label {
            font-size: 11px;
            font-weight: 600;
            color: #64748b;
            text-transform: uppercase;
            letter-spacing: 0.5px;
            margin-bottom: 4px;
        }

        .info-value {
            font-size: 14px;
            font-weight: 500;
            color: #1e293b;
        }

        /* ==================== ASPIRASI CONTENT ==================== */
        .aspirasi-text {
            background: #f8fafc;
            padding: 20px;
            border-radius: 16px;
            font-size: 14px;
            line-height: 1.8;
            color: #334155;
            border-left: 3px solid #6366f1;
            margin-bottom: 20px;
        }

        /* ==================== IMAGE ==================== */
        .aspirasi-image {
            text-align: center;
        }

        .aspirasi-image img {
            max-width: 100%;
            max-height: 350px;
            border-radius: 16px;
            box-shadow: 0 4px 12px rgba(0, 0, 0, 0.08);
            cursor: pointer;
            transition: all 0.2s ease;
            border: 1px solid #e2e8f0;
        }

        .aspirasi-image img:hover {
            transform: scale(1.02);
            box-shadow: 0 8px 24px rgba(0, 0, 0, 0.12);
        }

        .image-caption {
            margin-top: 8px;
            font-size: 11px;
            color: #94a3b8;
        }

        /* ==================== PROGRESS TIMELINE ==================== */
        .timeline {
            position: relative;
        }

        .timeline-item {
            display: flex;
            gap: 16px;
            margin-bottom: 20px;
            position: relative;
        }

        .timeline-item:last-child {
            margin-bottom: 0;
        }

        .timeline-item:not(:last-child)::before {
            content: '';
            position: absolute;
            left: 17px;
            top: 32px;
            bottom: -20px;
            width: 2px;
            background: #e2e8f0;
        }

        .timeline-icon {
            width: 36px;
            height: 36px;
            background: white;
            border: 2px solid #6366f1;
            border-radius: 50%;
            display: flex;
            align-items: center;
            justify-content: center;
            font-size: 16px;
            background: white;
            z-index: 1;
        }

        .timeline-content {
            flex: 1;
            background: #f8fafc;
            padding: 12px 16px;
            border-radius: 14px;
        }

        .timeline-status {
            font-weight: 600;
            font-size: 13px;
            color: #1e293b;
            margin-bottom: 4px;
        }

        .timeline-date {
            font-size: 11px;
            color: #94a3b8;
            display: flex;
            align-items: center;
            gap: 5px;
        }

        /* ==================== FEEDBACK LIST ==================== */
        .feedback-list {
            display: flex;
            flex-direction: column;
            gap: 16px;
        }

        .feedback-item {
            background: #f8fafc;
            border-radius: 16px;
            padding: 18px;
            border-left: 3px solid #6366f1;
            transition: all 0.2s ease;
        }

        .feedback-item:hover {
            background: #f1f5f9;
            transform: translateX(4px);
        }

        .feedback-header {
            display: flex;
            justify-content: space-between;
            align-items: center;
            margin-bottom: 10px;
            flex-wrap: wrap;
            gap: 10px;
        }

        .feedback-author {
            font-weight: 600;
            font-size: 13px;
            color: #1e293b;
            display: flex;
            align-items: center;
            gap: 8px;
        }

        .feedback-author i {
            color: #6366f1;
        }

        .admin-badge {
            background: #eef2ff;
            color: #4f46e5;
            padding: 2px 8px;
            border-radius: 12px;
            font-size: 10px;
            font-weight: 600;
        }

        .feedback-date {
            font-size: 11px;
            color: #94a3b8;
            display: flex;
            align-items: center;
            gap: 5px;
        }

        .feedback-content {
            font-size: 14px;
            line-height: 1.7;
            color: #334155;
            margin-bottom: 12px;
        }

        .feedback-content i {
            color: #94a3b8;
            font-size: 12px;
            margin-right: 6px;
            opacity: 0.5;
        }

        .feedback-image img {
            max-width: 200px;
            border-radius: 12px;
            cursor: pointer;
            transition: all 0.2s ease;
            border: 1px solid #e2e8f0;
            margin-top: 10px;
        }

        .feedback-image img:hover {
            transform: scale(1.02);
            box-shadow: 0 4px 12px rgba(0, 0, 0, 0.1);
        }

        /* ==================== EMPTY STATE ==================== */
        .empty-state {
            text-align: center;
            padding: 48px 24px;
            color: #94a3b8;
        }

        .empty-state i {
            font-size: 48px;
            margin-bottom: 16px;
            display: block;
            opacity: 0.5;
        }

        .empty-state p {
            font-size: 14px;
            margin-bottom: 8px;
            color: #64748b;
        }

        .empty-state small {
            font-size: 12px;
        }

        /* ==================== MODAL IMAGE ==================== */
        .modal {
            display: none;
            position: fixed;
            z-index: 2000;
            left: 0;
            top: 0;
            width: 100%;
            height: 100%;
            background-color: rgba(0, 0, 0, 0.9);
            align-items: center;
            justify-content: center;
            cursor: pointer;
        }

        .modal-content {
            max-width: 90%;
            max-height: 90%;
            border-radius: 12px;
            animation: zoomIn 0.3s ease;
        }

        @keyframes zoomIn {
            from {
                opacity: 0;
                transform: scale(0.95);
            }
            to {
                opacity: 1;
                transform: scale(1);
            }
        }

        .modal-close {
            position: absolute;
            top: 20px;
            right: 35px;
            color: #f1f1f1;
            font-size: 40px;
            font-weight: bold;
            cursor: pointer;
            transition: all 0.2s;
        }

        .modal-close:hover {
            color: #f94144;
        }

        /* ==================== SCROLL TO TOP ==================== */
        .scroll-top-btn {
            position: fixed;
            bottom: 30px;
            right: 30px;
            width: 48px;
            height: 48px;
            background: linear-gradient(135deg, #6366f1, #8b5cf6);
            color: white;
            border: none;
            border-radius: 50%;
            cursor: pointer;
            font-size: 18px;
            display: none;
            align-items: center;
            justify-content: center;
            transition: all 0.2s ease;
            z-index: 1000;
            box-shadow: 0 4px 12px rgba(99, 102, 241, 0.3);
        }

        .scroll-top-btn:hover {
            transform: translateY(-3px);
            box-shadow: 0 6px 20px rgba(99, 102, 241, 0.4);
        }

        .scroll-top-btn.show {
            display: flex;
            animation: fadeInUp 0.3s ease;
        }

        /* ==================== CUSTOM SCROLLBAR ==================== */
        ::-webkit-scrollbar {
            width: 8px;
            height: 8px;
        }

        ::-webkit-scrollbar-track {
            background: #f1f5f9;
            border-radius: 10px;
        }

        ::-webkit-scrollbar-thumb {
            background: #cbd5e1;
            border-radius: 10px;
        }

        ::-webkit-scrollbar-thumb:hover {
            background: #94a3b8;
        }

        /* ==================== RESPONSIVE ==================== */
        @media (max-width: 768px) {
            body {
                padding: 16px;
            }

            .header {
                flex-direction: column;
                text-align: center;
                padding: 16px 20px;
            }

            .header-left h1 {
                font-size: 18px;
            }

            .info-grid {
                grid-template-columns: 1fr;
                gap: 12px;
            }

            .timeline-item {
                flex-direction: column;
                gap: 8px;
            }

            .timeline-item:not(:last-child)::before {
                left: 17px;
                top: 36px;
            }

            .feedback-header {
                flex-direction: column;
                align-items: flex-start;
            }

            .scroll-top-btn {
                bottom: 20px;
                right: 20px;
                width: 42px;
                height: 42px;
                font-size: 16px;
            }

            .modal-close {
                top: 15px;
                right: 20px;
                font-size: 30px;
            }
        }
    </style>
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
            <div class="breadcrumb">
                <i class="fas fa-home"></i> Beranda / Detail Aspirasi
            </div>
        </div>
        <a href="index.php" class="back-btn">
            <i class="fas fa-arrow-left"></i> Kembali ke Beranda
        </a>
    </div>

    <!-- Detail Aspirasi -->
    <div class="card">
        <div class="card-header">
            <h2><i class="fas fa-info-circle"></i> Informasi Aspirasi</h2>
            <span class="status-badge status-<?= $status ?>">
                <?php 
                $icon = '';
                if($status == 'menunggu') $icon = '⏳';
                elseif($status == 'pengecekan') $icon = '🔍';
                elseif($status == 'proses') $icon = '⚙️';
                elseif($status == 'selesai') $icon = '✅';
                echo $icon . ' ' . strtoupper($status);
                ?>
            </span>
        </div>
        <div class="card-body">
            <div class="info-grid">
                <div class="info-item">
                    <div class="info-icon"><i class="fas fa-map-marker-alt"></i></div>
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

            <div class="aspirasi-text">
                <?= nl2br(htmlspecialchars($data['isi'])); ?>
            </div>

            <?php if($data['foto']): ?>
            <div class="aspirasi-image">
                <img src="uploads/<?= $data['foto']; ?>" alt="Foto Aspirasi" onclick="openModal(this.src)">
                <div class="image-caption">Klik gambar untuk memperbesar</div>
            </div>
            <?php endif; ?>
        </div>
    </div>

    <!-- Progress Timeline -->
    <?php if(mysqli_num_rows($progress) > 0): ?>
    <div class="card">
        <div class="card-header">
            <h2><i class="fas fa-chart-line"></i> Riwayat Progress</h2>
        </div>
        <div class="card-body">
            <div class="timeline">
                <?php while($p = mysqli_fetch_assoc($progress)): ?>
                <div class="timeline-item">
                    <div class="timeline-icon">
                        <?php 
                        if($p['status'] == 'menunggu') echo '⏳';
                        elseif($p['status'] == 'pengecekan') echo '🔍';
                        elseif($p['status'] == 'proses') echo '⚙️';
                        elseif($p['status'] == 'selesai') echo '✅';
                        ?>
                    </div>
                    <div class="timeline-content">
                        <div class="timeline-status"><?= strtoupper($p['status']); ?></div>
                        <div class="timeline-date">
                            <i class="fas fa-clock"></i> <?= date('d F Y, H:i:s', strtotime($p['tanggal'])); ?>
                        </div>
                    </div>
                </div>
                <?php endwhile; ?>
            </div>
        </div>
    </div>
    <?php endif; ?>

    <!-- Feedback Admin -->
    <div class="card">
        <div class="card-header">
            <h2><i class="fas fa-comments"></i> Feedback Admin</h2>
        </div>
        <div class="card-body">
            <?php if(mysqli_num_rows($feedback) > 0): ?>
                <div class="feedback-list">
                    <?php while($f = mysqli_fetch_assoc($feedback)): ?>
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
                            <?= nl2br(htmlspecialchars($f['feedback'])); ?>
                        </div>
                        <?php if($f['foto']): ?>
                        <div class="feedback-image">
                            <img src="uploads/<?= $f['foto']; ?>" alt="Bukti Feedback" onclick="openModal(this.src)">
                        </div>
                        <?php endif; ?>
                    </div>
                    <?php endwhile; ?>
                </div>
            <?php else: ?>
                <div class="empty-state">
                    <i class="fas fa-inbox"></i>
                    <p>Belum ada feedback dari admin</p>
                    <small>Feedback akan muncul setelah admin merespons</small>
                </div>
            <?php endif; ?>
        </div>
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