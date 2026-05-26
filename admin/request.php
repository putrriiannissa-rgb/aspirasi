<?php
session_start();
include '../config/koneksi.php';

/** @var mysqli $conn */

// PROSES APPROVE
if (isset($_GET['approve'])) {
    $id = $_GET['approve'];

    // ambil data request
    $query = mysqli_query($conn, "SELECT * FROM register_requests WHERE id='$id'");
    $request = mysqli_fetch_assoc($query);

    if ($request) {
        $nis      = $request['nis'] ?? '';
        $name     = $request['name'];
        $username = $request['username'];
        $password = $request['password'];
        $role     = 'siswa';

        // cek username sudah ada atau belum
        $cek = mysqli_query($conn, "SELECT * FROM users WHERE username='$username'");
        if (mysqli_num_rows($cek) > 0) {
            echo "<script>alert('Username sudah digunakan!'); window.location='request.php';</script>";
            exit;
        }

        // insert user baru
        $insert = mysqli_query($conn, "
            INSERT INTO users (nis, name, username, password, role)
            VALUES ('$nis', '$name', '$username', '$password', '$role')
        ");

        if ($insert) {
            mysqli_query($conn, "DELETE FROM register_requests WHERE id='$id'");
            header('Location: request.php?success=approved');
            exit;
        }
    }
}

// PROSES REJECT
if (isset($_GET['reject'])) {
    $id = $_GET['reject'];
    mysqli_query($conn, "DELETE FROM register_requests WHERE id='$id'");
    header("Location: request.php?success=rejected");
    exit;
}

$data = mysqli_query($conn, "SELECT * FROM register_requests ORDER BY created_at DESC");
?>

<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Request Akun - Sistem Aspirasi</title>

    <!-- Google Fonts & Font Awesome -->
    <link href="https://fonts.googleapis.com/css2?family=Inter:opsz,wght@14..32,300;400;500;600;700;800&display=swap" rel="stylesheet">
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

        /* ========== SIDEBAR (konsisten dengan halaman lain) ========== */
        .sidebar {
            position: fixed;
            left: 0;
            top: 0;
            width: 280px;
            height: 100vh;
            background: #ffffff;
            box-shadow: 0 8px 32px rgba(0, 0, 0, 0.05);
            border-right: 1px solid #edf2f7;
            z-index: 100;
            overflow-y: auto;
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
            text-decoration: none;
            transition: all 0.2s;
        }

        .nav-link i {
            width: 24px;
            color: #94a3b8;
        }

        .nav-link:hover {
            background: #f1f5f9;
            color: #3b82f6;
        }

        .nav-link.active {
            background: #eef2ff;
            color: #3b82f6;
            font-weight: 600;
        }

        /* ========== MAIN CONTENT ========== */
        .main-content {
            margin-left: 280px;
            padding: 32px 40px;
            min-height: 100vh;
        }

        /* Card */
        .card {
            background: #ffffff;
            border-radius: 24px;
            box-shadow: 0 1px 3px rgba(0, 0, 0, 0.04);
            border: 1px solid #f0f2f5;
            overflow: hidden;
        }

        .card-header {
            padding: 20px 28px;
            border-bottom: 1px solid #edf2f7;
            display: flex;
            justify-content: space-between;
            align-items: center;
            flex-wrap: wrap;
            gap: 16px;
        }

        .card-header h2 {
            font-size: 1.4rem;
            font-weight: 600;
            color: #0f172a;
        }

        .badge-count {
            background: #f1f5f9;
            padding: 4px 12px;
            border-radius: 40px;
            font-size: 0.75rem;
            font-weight: 500;
            color: #475569;
        }

        /* Alert Notifikasi */
        .alert {
            margin: 20px 28px 0 28px;
            padding: 14px 18px;
            border-radius: 16px;
            display: flex;
            align-items: center;
            gap: 12px;
            font-size: 0.9rem;
            animation: fadeSlide 0.3s ease;
        }

        .alert-success {
            background: #ecfdf5;
            border: 1px solid #d1fae5;
            color: #059669;
        }

        .alert-warning {
            background: #fffbeb;
            border: 1px solid #fde68a;
            color: #d97706;
        }

        @keyframes fadeSlide {
            from {
                opacity: 0;
                transform: translateY(-8px);
            }
            to {
                opacity: 1;
                transform: translateY(0);
            }
        }

        /* Table Wrapper */
        .table-wrapper {
            overflow-x: auto;
            padding: 0 28px 28px 28px;
        }

        table {
            width: 100%;
            border-collapse: collapse;
        }

        th {
            text-align: left;
            padding: 14px 12px;
            background: #fafcff;
            font-weight: 600;
            color: #334155;
            border-bottom: 1px solid #e9edf2;
            font-size: 0.8rem;
            text-transform: uppercase;
            letter-spacing: 0.4px;
        }

        td {
            padding: 16px 12px;
            border-bottom: 1px solid #f0f2f5;
            vertical-align: middle;
        }

        tr:hover td {
            background: #fafcff;
        }

        /* User Avatar */
        .user-name {
            display: flex;
            align-items: center;
            gap: 12px;
        }

        .user-avatar {
            width: 38px;
            height: 38px;
            border-radius: 14px;
            background: #eef2ff;
            display: flex;
            align-items: center;
            justify-content: center;
            font-weight: 700;
            color: #3b82f6;
        }

        /* Informasi detail */
        .info-detail {
            display: flex;
            flex-direction: column;
            gap: 6px;
        }

        .info-nis, .info-username {
            font-size: 0.8rem;
            display: inline-flex;
            align-items: center;
            gap: 6px;
            background: #f8fafc;
            padding: 4px 12px;
            border-radius: 30px;
            width: fit-content;
        }

        .info-username {
            background: #f1f5f9;
            font-family: monospace;
        }

        /* Status Badge */
        .status-badge {
            display: inline-flex;
            align-items: center;
            gap: 6px;
            padding: 5px 14px;
            border-radius: 40px;
            font-size: 0.7rem;
            font-weight: 600;
            text-transform: uppercase;
            background: #fef9e3;
            color: #ca8a04;
        }

        /* Tombol Aksi */
        .action-buttons {
            display: flex;
            gap: 10px;
            flex-wrap: wrap;
        }

        .btn-approve, .btn-reject {
            padding: 6px 18px;
            border-radius: 40px;
            font-size: 0.7rem;
            font-weight: 600;
            text-decoration: none;
            display: inline-flex;
            align-items: center;
            gap: 8px;
            transition: 0.2s;
        }

        .btn-approve {
            background: #eef2ff;
            color: #3b82f6;
            border: 1px solid #dbeafe;
        }

        .btn-approve:hover {
            background: #3b82f6;
            color: white;
        }

        .btn-reject {
            background: #fef2f2;
            color: #dc2626;
            border: 1px solid #fee2e2;
        }

        .btn-reject:hover {
            background: #dc2626;
            color: white;
        }

        /* Empty State */
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

        /* Responsive */
        @media (max-width: 768px) {
            .main-content {
                margin-left: 0;
                padding: 20px;
            }
            .sidebar {
                transform: translateX(-100%);
            }
            .action-buttons {
                flex-direction: column;
            }
        }
    </style>
</head>
<body>

<!-- SIDEBAR (sama seperti halaman admin lain) -->
<aside class="sidebar">
    <div class="sidebar-header">
        <div class="logo">
            <i class="fas fa-graduation-cap"></i>
            <span>Sistem Aspirasi</span>
        </div>
        <p style="font-size: 0.7rem; color: #94a3b8; margin-top: 8px;">Admin Panel</p>
    </div>
    <ul class="nav-menu">
        <li class="nav-item"><a href="dashboard.php" class="nav-link"><i class="fas fa-tachometer-alt"></i> Dashboard</a></li>
        <li class="nav-item"><a href="users.php" class="nav-link"><i class="fas fa-users"></i> Kelola Pengguna</a></li>
        <li class="nav-item"><a href="kategori.php" class="nav-link"><i class="fas fa-tags"></i> Kategori</a></li>
        <li class="nav-item"><a href="request.php" class="nav-link active"><i class="fas fa-user-plus"></i> Request Akun</a></li>
        <li class="nav-item" style="margin-top: 20px;"><a href="index.php" class="nav-link" style="color: #ef4444;"><i class="fas fa-sign-out-alt"></i> Logout</a></li>
    </ul>
</aside>

<!-- MAIN CONTENT -->
<div class="main-content">
    <div class="card">
        <div class="card-header">
            <div style="display: flex; align-items: center; gap: 16px;">
                <h2><i class="fas fa-user-plus"></i> Request Akun</h2>
                <div class="badge-count">
                    Menunggu: <?= mysqli_num_rows($data) ?> request
                </div>
            </div>
        </div>

        <!-- Alert Notifikasi -->
        <?php if (isset($_GET['success'])): ?>
            <?php if ($_GET['success'] == 'approved'): ?>
                <div class="alert alert-success" id="alertMessage">
                    <i class="fas fa-check-circle"></i> Request berhasil di-approve! Akun siswa telah dibuat.
                </div>
            <?php elseif ($_GET['success'] == 'rejected'): ?>
                <div class="alert alert-warning" id="alertMessage">
                    <i class="fas fa-trash-alt"></i> Request berhasil ditolak.
                </div>
            <?php endif; ?>
            <script>
                setTimeout(() => {
                    const alert = document.getElementById('alertMessage');
                    if (alert) alert.style.display = 'none';
                }, 3000);
            </script>
        <?php endif; ?>

        <!-- Tabel Request -->
        <div class="table-wrapper">
            <?php if (mysqli_num_rows($data) > 0): ?>
                <table>
                    <thead>
                        <tr>
                            <th style="width: 70px;">No</th>
                            <th>Nama Pendaftar</th>
                            <th>Detail Akun</th>
                            <th>Status</th>
                            <th style="width: 240px;">Aksi</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php $no = 1; while ($row = mysqli_fetch_assoc($data)): ?>
                            <tr>
                                <td><?= str_pad($no++, 2, '0', STR_PAD_LEFT) ?></td>
                                <td>
                                    <div class="user-name">
                                        <div class="user-avatar">
                                            <?= htmlspecialchars(strtoupper(substr($row['name'], 0, 1))) ?>
                                        </div>
                                        <?= htmlspecialchars($row['name']) ?>
                                    </div>
                                </td>
                                <td>
                                    <div class="info-detail">
                                        <?php if (!empty($row['nis'])): ?>
                                            <span class="info-nis"><i class="fas fa-id-card"></i> NIS: <?= htmlspecialchars($row['nis']) ?></span>
                                        <?php endif; ?>
                                        <span class="info-username"><i class="fas fa-at"></i> <?= htmlspecialchars($row['username']) ?></span>
                                    </div>
                                </td>
                                <td>
                                    <span class="status-badge">
                                        <i class="fas fa-hourglass-half"></i> Pending
                                    </span>
                                </td>
                                <td>
                                    <div class="action-buttons">
                                        <a href="?approve=<?= $row['id'] ?>" class="btn-approve" onclick="return confirm('Approve request ini? Akun siswa akan langsung dibuat.')">
                                            <i class="fas fa-check"></i> Approve
                                        </a>
                                        <a href="?reject=<?= $row['id'] ?>" class="btn-reject" onclick="return confirm('Tolak request ini? Data akan dihapus.')">
                                            <i class="fas fa-times"></i> Reject
                                        </a>
                                    </div>
                                 </td>
                            </tr>
                        <?php endwhile; ?>
                    </tbody>
                </table>
            <?php else: ?>
                <div class="empty-state">
                    <i class="fas fa-inbox"></i>
                    <p>Tidak ada request akun yang menunggu</p>
                    <p style="font-size: 0.85rem; margin-top: 8px;">Semua request sudah diproses</p>
                </div>
            <?php endif; ?>
        </div>
    </div>
</div>

</body>
</html>