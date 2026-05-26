<?php
session_start();
include '../config/koneksi.php';

/** @var mysqli $conn */

// Statistik
$total_admin = mysqli_fetch_assoc(mysqli_query($conn, "SELECT COUNT(*) as total FROM users WHERE role='admin'"))['total'];
$total_siswa = mysqli_fetch_assoc(mysqli_query($conn, "SELECT COUNT(*) as total FROM users WHERE role='siswa'"))['total'];
$total_aspirasi = mysqli_fetch_assoc(mysqli_query($conn, "SELECT COUNT(*) as total FROM aspirations"))['total'];

// Ambil 5 aspirasi terbaru dengan status terbaru dari progress
$recent_aspirations = mysqli_query($conn, "
    SELECT a.*,
        COALESCE(
            (SELECT p.status FROM progress p WHERE p.aspiration_id = a.id ORDER BY p.id DESC LIMIT 1),
            a.status
        ) as status_terbaru
    FROM aspirations a
    ORDER BY a.created_at DESC
    LIMIT 5
");
?>

<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Dashboard Admin - Sistem Aspirasi</title>
    
    <!-- Google Fonts & Font Awesome -->
    <link href="https://fonts.googleapis.com/css2?family=Inter:opsz,wght@14..32,300;14..32,400;14..32,500;14..32,600;14..32,700;14..32,800&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    
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

        /* ========== SIDEBAR MODERN ========== */
        .sidebar {
            position: fixed;
            left: 0;
            top: 0;
            width: 280px;
            height: 100vh;
            background: linear-gradient(145deg, #ffffff 0%, #fefefe 100%);
            backdrop-filter: blur(0px);
            box-shadow: 0 8px 32px rgba(0, 0, 0, 0.05), 1px 0 0 rgba(0, 0, 0, 0.02);
            z-index: 100;
            transition: all 0.3s cubic-bezier(0.4, 0, 0.2, 1);
            overflow-y: auto;
            border-right: 1px solid rgba(203, 213, 225, 0.4);
        }

        .sidebar-header {
            padding: 28px 24px;
            border-bottom: 1px solid #edf2f7;
            margin-bottom: 20px;
        }

        .logo {
            display: flex;
            align-items: center;
            gap: 12px;
            font-size: 1.3rem;
            font-weight: 700;
            background: linear-gradient(135deg, #3b82f6, #8b5cf6);
            -webkit-background-clip: text;
            background-clip: text;
            color: transparent;
        }

        .logo i {
            background: none;
            -webkit-background-clip: unset;
            background-clip: unset;
            color: #3b82f6;
            font-size: 1.6rem;
        }

        .nav-menu {
            list-style: none;
            padding: 0 16px;
        }

        .nav-item {
            margin-bottom: 8px;
        }

        .nav-link {
            display: flex;
            align-items: center;
            gap: 14px;
            padding: 12px 18px;
            border-radius: 14px;
            color: #475569;
            font-weight: 500;
            transition: all 0.2s ease;
            text-decoration: none;
            font-size: 0.95rem;
        }

        .nav-link i {
            width: 24px;
            font-size: 1.2rem;
            color: #94a3b8;
            transition: color 0.2s;
        }

        .nav-link:hover {
            background: #f1f5f9;
            color: #3b82f6;
        }

        .nav-link:hover i {
            color: #3b82f6;
        }

        .nav-link.active {
            background: linear-gradient(135deg, #eef2ff, #ffffff);
            color: #3b82f6;
            font-weight: 600;
            box-shadow: 0 2px 6px rgba(59,130,246,0.08);
        }

        .nav-link.active i {
            color: #3b82f6;
        }

        /* ========== MAIN CONTENT ========== */
        .main-content {
            margin-left: 280px;
            padding: 32px 40px;
            min-height: 100vh;
        }

        /* Page Header */
        .page-header {
            margin-bottom: 32px;
        }

        .page-header h1 {
            font-size: 2rem;
            font-weight: 700;
            letter-spacing: -0.3px;
            background: linear-gradient(135deg, #1e293b 0%, #2d3a4f 100%);
            background-clip: text;
            -webkit-background-clip: text;
            -webkit-text-fill-color: transparent;
            margin-bottom: 8px;
        }

        .page-header p {
            color: #64748b;
            font-size: 0.9rem;
        }

        /* Cards Grid */
        .stats-grid {
            display: grid;
            grid-template-columns: repeat(auto-fit, minmax(280px, 1fr));
            gap: 24px;
            margin-bottom: 40px;
        }

        .stat-card {
            background: #ffffff;
            border-radius: 24px;
            padding: 24px;
            box-shadow: 0 1px 3px rgba(0, 0, 0, 0.04), 0 1px 2px rgba(0, 0, 0, 0.02);
            transition: all 0.25s ease;
            border: 1px solid #f0f2f5;
            position: relative;
            overflow: hidden;
        }

        .stat-card:hover {
            transform: translateY(-3px);
            box-shadow: 0 12px 24px -12px rgba(0, 0, 0, 0.12);
        }

        .card-icon {
            width: 54px;
            height: 54px;
            border-radius: 18px;
            display: flex;
            align-items: center;
            justify-content: center;
            margin-bottom: 20px;
            font-size: 26px;
        }

        .card-icon.admin {
            background: #eef2ff;
            color: #3b82f6;
        }

        .card-icon.siswa {
            background: #e0f2fe;
            color: #0ea5e9;
        }

        .card-icon.aspirasi {
            background: #fef3c7;
            color: #f59e0b;
        }

        .stat-card h3 {
            font-size: 0.85rem;
            font-weight: 600;
            text-transform: uppercase;
            letter-spacing: 0.5px;
            color: #64748b;
            margin-bottom: 10px;
        }

        .stat-card .number {
            font-size: 2.8rem;
            font-weight: 800;
            color: #0f172a;
            letter-spacing: -1px;
            line-height: 1.2;
        }

        .card-footer {
            margin-top: 18px;
            padding-top: 12px;
            border-top: 1px solid #f0f2f5;
            font-size: 0.75rem;
            color: #94a3b8;
        }

        /* Recent Section */
        .recent-section {
            background: #ffffff;
            border-radius: 24px;
            box-shadow: 0 1px 3px rgba(0, 0, 0, 0.04);
            overflow: hidden;
            border: 1px solid #f0f2f5;
        }

        .recent-header {
            padding: 20px 28px;
            border-bottom: 1px solid #edf2f7;
            display: flex;
            justify-content: space-between;
            align-items: center;
            flex-wrap: wrap;
            gap: 12px;
        }

        .recent-header h2 {
            font-size: 1.25rem;
            font-weight: 600;
            color: #0f172a;
        }

        .view-all {
            color: #3b82f6;
            text-decoration: none;
            font-size: 0.85rem;
            font-weight: 500;
            transition: all 0.2s;
            background: #f8fafc;
            padding: 6px 14px;
            border-radius: 40px;
        }

        .view-all:hover {
            background: #eef2ff;
            color: #2563eb;
        }

        /* Table Styling */
        .table-wrapper {
            overflow-x: auto;
            padding: 0 20px 24px 20px;
        }

        table {
            width: 100%;
            border-collapse: separate;
            border-spacing: 0;
            font-size: 0.9rem;
        }

        th {
            text-align: left;
            padding: 16px 12px;
            background-color: #fafcff;
            font-weight: 600;
            color: #334155;
            border-bottom: 1.5px solid #e9edf2;
            font-size: 0.8rem;
            text-transform: uppercase;
            letter-spacing: 0.4px;
        }

        td {
            padding: 16px 12px;
            border-bottom: 1px solid #f0f2f5;
            vertical-align: middle;
            color: #1e293b;
        }

        tr:hover td {
            background-color: #fafcff;
        }

        /* Badge Status (sama seperti index.php) */
        .badge {
            display: inline-flex;
            align-items: center;
            gap: 6px;
            padding: 5px 12px;
            border-radius: 40px;
            font-size: 0.7rem;
            font-weight: 600;
            text-transform: uppercase;
            letter-spacing: 0.3px;
        }

        .badge.menunggu {
            background: #fef9e3;
            color: #ca8a04;
        }
        .badge.pengecekan {
            background: #ffedd5;
            color: #ea580c;
        }
        .badge.proses {
            background: #e0f2fe;
            color: #0284c7;
        }
        .badge.selesai {
            background: #dcfce7;
            color: #15803d;
        }

        .btn-detail {
            display: inline-flex;
            align-items: center;
            gap: 6px;
            background: #ffffff;
            color: #3b82f6;
            padding: 6px 16px;
            text-decoration: none;
            border-radius: 30px;
            font-size: 0.75rem;
            font-weight: 500;
            border: 1px solid #dbeafe;
            transition: all 0.2s;
        }

        .btn-detail:hover {
            background: #3b82f6;
            color: white;
            border-color: #3b82f6;
        }

        .empty-state {
            text-align: center;
            padding: 56px 24px;
            color: #94a3b8;
        }

        .empty-state i {
            font-size: 3rem;
            margin-bottom: 16px;
            opacity: 0.5;
        }

        /* Animasi */
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

        .stat-card, .recent-section {
            animation: fadeInUp 0.4s ease-out forwards;
        }

        /* Responsive */
        @media (max-width: 992px) {
            .sidebar {
                transform: translateX(-100%);
                box-shadow: none;
            }
            .main-content {
                margin-left: 0;
                padding: 24px 20px;
            }
        }

        /* Scrollbar kustom */
        ::-webkit-scrollbar {
            width: 6px;
            height: 6px;
        }
        ::-webkit-scrollbar-track {
            background: #f1f5f9;
        }
        ::-webkit-scrollbar-thumb {
            background: #cbd5e1;
            border-radius: 8px;
        }
    </style>
</head>
<body>

<!-- SIDEBAR MODERN -->
<aside class="sidebar">
    <div class="sidebar-header">
        <div class="logo">
            <i class="fas fa-graduation-cap"></i>
            <span>Sistem Aspirasi</span>
        </div>
        <p style="font-size: 0.7rem; color: #94a3b8; margin-top: 8px;">Admin Panel</p>
    </div>
    <ul class="nav-menu">
        <li class="nav-item">
            <a href="dashboard.php" class="nav-link active">
                <i class="fas fa-tachometer-alt"></i>
                <span>Dashboard</span>
            </a>
        </li>
        <li class="nav-item">
            <a href="users.php" class="nav-link">
                <i class="fas fa-users"></i>
                <span>Kelola Pengguna</span>
            </a>
        </li>
        <li class="nav-item">
            <a href="kategori.php" class="nav-link">
                <i class="fas fa-tags"></i>
                <span>Kategori</span>
            </a>
        </li>
        <li class="nav-item">
            <a href="request.php" class="nav-link">
                <i class="fas fa-chart-line"></i>
                <span>Request Akun</span>
            </a>
        </li>
        <li class="nav-item" style="margin-top: 20px;">
            <a href="logout.php" class="nav-link" style="color: #ef4444;">
                <i class="fas fa-sign-out-alt"></i>
                <span>Logout</span>
            </a>
        </li>
    </ul>
</aside>

<!-- MAIN CONTENT -->
<div class="main-content">
    <div class="page-header">
        <h1>Dashboard</h1>
        <p>Selamat datang kembali, Admin. Pantau statistik dan aspirasi terbaru di sini.</p>
    </div>

    <!-- Statistik Cards -->
    <div class="stats-grid">
        <div class="stat-card">
            <div class="card-icon admin"><i class="fas fa-user-shield"></i></div>
            <h3>Total Administrator</h3>
            <div class="number"><?= number_format($total_admin) ?></div>
            <div class="card-footer">Pengelola sistem aktif</div>
        </div>

        <div class="stat-card">
            <div class="card-icon siswa"><i class="fas fa-user-graduate"></i></div>
            <h3>Total Siswa Terdaftar</h3>
            <div class="number"><?= number_format($total_siswa) ?></div>
            <div class="card-footer">Pengguna dengan akses terbatas</div>
        </div>

        <div class="stat-card">
            <div class="card-icon aspirasi"><i class="fas fa-comment-dots"></i></div>
            <h3>Total Aspirasi Masuk</h3>
            <div class="number"><?= number_format($total_aspirasi) ?></div>
            <div class="card-footer">Keseluruhan aspirasi dari siswa</div>
        </div>
    </div>

    <!-- Tabel Aspirasi Terbaru -->
    <div class="recent-section">
        <div class="recent-header">
            <h2><i class="fas fa-clock"></i> Aspirasi Terbaru</h2>
        </div>
        <div class="table-wrapper">
            <?php if(mysqli_num_rows($recent_aspirations) > 0): ?>
                <table>
                    <thead>
                        <tr>
                            <th>No</th>
                            <th>Tanggal</th>
                            <th>Isi Aspirasi</th>
                            <th>Kategori</th>
                            <th>Lokasi</th>
                            <th>Status</th>
                            <th>Aksi</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php $no = 1; while($asp = mysqli_fetch_assoc($recent_aspirations)): 
                            $status = $asp['status_terbaru'] ?? $asp['status'];
                            $iconStatus = '';
                            if($status == 'menunggu') $iconStatus = '⏳';
                            elseif($status == 'pengecekan') $iconStatus = '🔍';
                            elseif($status == 'proses') $iconStatus = '⚙️';
                            elseif($status == 'selesai') $iconStatus = '✅';
                        ?>
                        <tr>
                            <td style="width: 50px; font-weight: 500;"><?= str_pad($no++, 2, '0', STR_PAD_LEFT) ?></td>
                            <td style="white-space: nowrap;"><?= date('d/m/Y', strtotime($asp['created_at'])) ?></td>
                            <td class="aspiration-preview" style="max-width: 320px; word-break: break-word;">
                                <?= htmlspecialchars(substr($asp['isi'], 0, 90)) ?>
                                <?= strlen($asp['isi']) > 90 ? '...' : '' ?>
                            </td>
                            <td><?= htmlspecialchars(ucfirst($asp['kategori'])) ?></td>
                            <td><?= htmlspecialchars(ucfirst($asp['lokasi'])) ?></td>
                            <td><span class="badge <?= $status ?>"><?= $iconStatus ?> <?= strtoupper($status) ?></span></td>
                            <td><a href="detail_aspirasi.php?id=<?= $asp['id'] ?>" class="btn-detail"><i class="fas fa-eye"></i> Detail</a></td>
                        </tr>
                        <?php endwhile; ?>
                    </tbody>
                </table>
            <?php else: ?>
                <div class="empty-state">
                    <i class="fas fa-inbox"></i>
                    <h4>Belum Ada Aspirasi</h4>
                    <p>Belum ada aspirasi yang disampaikan oleh siswa.</p>
                </div>
            <?php endif; ?>
        </div>
    </div>
</div>

</body>
</html>