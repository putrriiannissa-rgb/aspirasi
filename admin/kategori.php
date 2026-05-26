<?php
session_start();
include '../config/koneksi.php';

/** @var mysqli $conn */

// TAMBAH KATEGORI
if (isset($_POST['tambah'])) {
    $nama = mysqli_real_escape_string($conn, $_POST['nama_kategori']);

    mysqli_query($conn, "INSERT INTO categories (nama_kategori) VALUES ('$nama')");

    header("Location: kategori.php?success=added");
    exit;
}

// HAPUS KATEGORI
if (isset($_GET['hapus'])) {
    $id = $_GET['hapus'];
    mysqli_query($conn, "DELETE FROM categories WHERE id = '$id'");
    header("Location: kategori.php?success=deleted");
    exit;
}

// EDIT KATEGORI
if (isset($_POST['edit'])) {
    $id   = $_POST['id'];
    $nama = mysqli_real_escape_string($conn, $_POST['nama_kategori']);
    mysqli_query($conn, "UPDATE categories SET nama_kategori = '$nama' WHERE id = '$id'");
    header("Location: kategori.php?success=updated");
    exit;
}

$data = mysqli_query($conn, "SELECT * FROM categories ORDER BY id DESC");
?>

<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Kelola Kategori - Sistem Aspirasi</title>

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

        /* ========== SIDEBAR (sama seperti halaman lain) ========== */
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

        /* Alert */
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

        /* Form Tambah */
        .form-tambah {
            padding: 24px 28px;
            border-bottom: 1px solid #edf2f7;
            background: #fafcff;
        }

        .form-group {
            margin-bottom: 16px;
        }

        .form-group label {
            font-size: 0.85rem;
            font-weight: 600;
            color: #334155;
            display: block;
            margin-bottom: 8px;
        }

        .form-group input {
            width: 100%;
            max-width: 400px;
            padding: 12px 16px;
            border: 1px solid #e2e8f0;
            border-radius: 16px;
            font-family: 'Inter', sans-serif;
            font-size: 0.9rem;
            transition: 0.2s;
        }

        .form-group input:focus {
            outline: none;
            border-color: #3b82f6;
            box-shadow: 0 0 0 3px rgba(59,130,246,0.1);
        }

        .btn-tambah {
            background: #3b82f6;
            color: white;
            border: none;
            padding: 10px 24px;
            border-radius: 40px;
            font-weight: 500;
            cursor: pointer;
            display: inline-flex;
            align-items: center;
            gap: 8px;
            transition: 0.2s;
        }

        .btn-tambah:hover {
            background: #2563eb;
            transform: translateY(-1px);
        }

        /* Table */
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
        }

        td {
            padding: 16px 12px;
            border-bottom: 1px solid #f0f2f5;
            vertical-align: middle;
        }

        tr:hover td {
            background: #fafcff;
        }

        .category-name {
            display: inline-flex;
            align-items: center;
            gap: 8px;
            background: #f1f5f9;
            padding: 6px 14px;
            border-radius: 40px;
            font-size: 0.85rem;
        }

        /* Edit Form dalam baris */
        .edit-form {
            display: flex;
            align-items: center;
            gap: 12px;
            flex-wrap: wrap;
        }

        .edit-input {
            padding: 8px 14px;
            border: 1px solid #e2e8f0;
            border-radius: 40px;
            font-family: 'Inter', sans-serif;
            font-size: 0.85rem;
            width: 240px;
        }

        .edit-input:focus {
            outline: none;
            border-color: #3b82f6;
        }

        .action-buttons {
            display: flex;
            gap: 8px;
        }

        .btn-save, .btn-hapus {
            padding: 6px 16px;
            border-radius: 40px;
            font-size: 0.7rem;
            font-weight: 500;
            text-decoration: none;
            display: inline-flex;
            align-items: center;
            gap: 6px;
            cursor: pointer;
            border: none;
            transition: 0.2s;
        }

        .btn-save {
            background: #eef2ff;
            color: #3b82f6;
        }

        .btn-save:hover {
            background: #3b82f6;
            color: white;
        }

        .btn-hapus {
            background: #fef2f2;
            color: #dc2626;
        }

        .btn-hapus:hover {
            background: #dc2626;
            color: white;
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

        /* Responsive */
        @media (max-width: 768px) {
            .main-content {
                margin-left: 0;
                padding: 20px;
            }
            .sidebar {
                transform: translateX(-100%);
            }
            .edit-form {
                flex-direction: column;
                align-items: flex-start;
            }
        }
    </style>
</head>
<body>

<!-- SIDEBAR -->
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
        <li class="nav-item"><a href="kategori.php" class="nav-link active"><i class="fas fa-tags"></i> Kategori</a></li>
        <li class="nav-item"><a href="request.php" class="nav-link"><i class="fas fa-chart-line"></i> Request Akun</a></li>
        <li class="nav-item" style="margin-top: 20px;"><a href="logout.php" class="nav-link" style="color: #ef4444;"><i class="fas fa-sign-out-alt"></i> Logout</a></li>
    </ul>
</aside>

<!-- MAIN CONTENT -->
<div class="main-content">
    <div class="card">
        <div class="card-header">
            <div style="display: flex; align-items: center; gap: 16px;">
                <h2><i class="fas fa-tags"></i> Kelola Kategori</h2>
                <div class="badge-count">
                    Total: <?= mysqli_num_rows($data) ?> kategori
                </div>
            </div>
        </div>

        <!-- Alert Notifikasi -->
        <?php if (isset($_GET['success'])): ?>
            <?php if ($_GET['success'] == 'added'): ?>
                <div class="alert alert-success" id="alertMessage">
                    <i class="fas fa-check-circle"></i> Kategori berhasil ditambahkan!
                </div>
            <?php elseif ($_GET['success'] == 'updated'): ?>
                <div class="alert alert-success" id="alertMessage">
                    <i class="fas fa-edit"></i> Kategori berhasil diperbarui!
                </div>
            <?php elseif ($_GET['success'] == 'deleted'): ?>
                <div class="alert alert-success" id="alertMessage">
                    <i class="fas fa-trash-alt"></i> Kategori berhasil dihapus!
                </div>
            <?php endif; ?>
            <script>
                setTimeout(() => {
                    const alert = document.getElementById('alertMessage');
                    if (alert) alert.style.display = 'none';
                }, 3000);
            </script>
        <?php endif; ?>

        <!-- FORM TAMBAH KATEGORI -->
        <div class="form-tambah">
            <form method="POST">
                <div class="form-group">
                    <label><i class="fas fa-plus-circle"></i> Nama Kategori Baru</label>
                    <input type="text" name="nama_kategori" placeholder="Contoh: Sarana Prasarana, KBM, Ekstrakurikuler" required>
                </div>
                <button type="submit" name="tambah" class="btn-tambah">
                    <i class="fas fa-save"></i> Tambah Kategori
                </button>
            </form>
        </div>

        <!-- TABEL DAFTAR KATEGORI -->
        <div class="table-wrapper">
            <?php if (mysqli_num_rows($data) > 0): ?>
                <table>
                    <thead>
                        <tr>
                            <th style="width: 80px;">No</th>
                            <th>Nama Kategori</th>
                            <th style="width: 400px;">Aksi</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php $no = 1; while ($row = mysqli_fetch_assoc($data)): ?>
                            <tr>
                                <td><?= str_pad($no++, 2, '0', STR_PAD_LEFT) ?></td>
                                <td>
                                    <span class="category-name">
                                        <i class="fas fa-folder-open"></i> <?= htmlspecialchars($row['nama_kategori']) ?>
                                    </span>
                                </td>
                                <td>
                                    <form method="POST" style="display: inline;">
                                        <div class="edit-form">
                                            <input type="hidden" name="id" value="<?= $row['id'] ?>">
                                            <input type="text" name="nama_kategori" value="<?= htmlspecialchars($row['nama_kategori']) ?>" class="edit-input" required>
                                            <div class="action-buttons">
                                                <button type="submit" name="edit" class="btn-save">
                                                    <i class="fas fa-check"></i> Simpan
                                                </button>
                                                <a href="?hapus=<?= $row['id'] ?>" class="btn-hapus" onclick="return confirm('Yakin ingin menghapus kategori “<?= htmlspecialchars($row['nama_kategori']) ?>”?')">
                                                    <i class="fas fa-trash"></i> Hapus
                                                </a>
                                            </div>
                                        </div>
                                    </form>
                                </td>
                            </tr>
                        <?php endwhile; ?>
                    </tbody>
                </table>
            <?php else: ?>
                <div class="empty-state">
                    <i class="fas fa-tags"></i>
                    <p>Belum ada kategori yang ditambahkan</p>
                    <p style="font-size: 0.85rem; margin-top: 8px;">Silakan tambahkan kategori melalui form di atas</p>
                </div>
            <?php endif; ?>
        </div>
    </div>
</div>

</body>
</html>