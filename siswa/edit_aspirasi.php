<?php
session_start();
include '../config/koneksi.php';

/** @var mysqli $conn */

if (!isset($_SESSION['user']) || $_SESSION['user']['role'] != 'siswa') {
    header("Location: ../auth/login.php");
    exit;
}

if (!isset($_GET['id']) || !is_numeric($_GET['id'])) {
    die("ID aspirasi tidak valid.");
}
$id = (int)$_GET['id'];

$stmt = mysqli_prepare($conn, "SELECT * FROM aspirations WHERE id = ?");
mysqli_stmt_bind_param($stmt, "i", $id);
mysqli_stmt_execute($stmt);
$result = mysqli_stmt_get_result($stmt);
$data = mysqli_fetch_assoc($result);
mysqli_stmt_close($stmt);

if (!$data) {
    die("Data aspirasi tidak ditemukan.");
}

if ($data['user_id'] != $_SESSION['user']['id']) {
    die("Akses ditolak!");
}

if (!in_array($data['status'], ['menunggu', 'pengecekan'])) {
    die("Aspirasi sudah diproses, tidak dapat diedit.");
}

$error = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $isi = trim($_POST['isi']);
    $lokasi = trim($_POST['lokasi']);
    $kategori_nama = trim($_POST['kategori']);
    $foto_lama = $data['foto'];
    $foto_baru = '';

    if (empty($isi) || empty($lokasi) || empty($kategori_nama)) {
        $error = "Semua field harus diisi.";
    } else {
        if (isset($_FILES['foto']) && $_FILES['foto']['error'] === 0) {
            $allowed = ['jpg', 'jpeg', 'png', 'gif'];
            $ext = strtolower(pathinfo($_FILES['foto']['name'], PATHINFO_EXTENSION));
            if (!in_array($ext, $allowed)) {
                $error = "Format foto tidak valid.";
            } elseif ($_FILES['foto']['size'] > 2 * 1024 * 1024) {
                $error = "Ukuran foto maksimal 2MB.";
            } else {
                $foto_baru = time() . '_' . basename($_FILES['foto']['name']);
                if (move_uploaded_file($_FILES['foto']['tmp_name'], "../uploads/" . $foto_baru)) {
                    if (!empty($foto_lama) && file_exists("../uploads/" . $foto_lama)) {
                        unlink("../uploads/" . $foto_lama);
                    }
                } else {
                    $error = "Gagal upload foto.";
                }
            }
        }

        if (empty($error)) {
            if (!empty($foto_baru)) {
                $update_stmt = mysqli_prepare($conn, "UPDATE aspirations SET isi = ?, lokasi = ?, kategori = ?, foto = ? WHERE id = ?");
                mysqli_stmt_bind_param($update_stmt, "ssssi", $isi, $lokasi, $kategori_nama, $foto_baru, $id);
            } else {
                $update_stmt = mysqli_prepare($conn, "UPDATE aspirations SET isi = ?, lokasi = ?, kategori = ? WHERE id = ?");
                mysqli_stmt_bind_param($update_stmt, "sssi", $isi, $lokasi, $kategori_nama, $id);
            }

            if (mysqli_stmt_execute($update_stmt)) {
                header("Location: dashboard.php?mode=saya&edit=success");
                exit;
            } else {
                $error = "Gagal menyimpan perubahan.";
            }
            mysqli_stmt_close($update_stmt);
        }
    }
}

// Ambil daftar kategori untuk dropdown
$categories = mysqli_query($conn, "SELECT * FROM categories ORDER BY nama_kategori");
?>
<!DOCTYPE html>
<html>
<head>
    <title>Edit Aspirasi | Sistem Aspirasi</title>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@300;400;500;600;700;800&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <style>
        * { margin: 0; padding: 0; box-sizing: border-box; }
        body { font-family: 'Inter', sans-serif; background: #f8fafc; color: #1e293b; }
        .container { max-width: 800px; margin: 40px auto; padding: 0 20px; }
        .card { background: white; border-radius: 24px; box-shadow: 0 4px 20px rgba(0,0,0,0.08); overflow: hidden; }
        .card-header { padding: 28px 32px; background: linear-gradient(135deg, #3b2e91 0%, #5226a4 100%); color: white; }
        .card-header h2 { font-size: 1.8rem; font-weight: 700; margin-bottom: 8px; display: flex; align-items: center; gap: 12px; }
        .card-header p { font-size: 0.9rem; opacity: 0.9; }
        .card-body { padding: 32px; }
        .form-group { margin-bottom: 24px; }
        .form-group label { display: block; margin-bottom: 8px; font-weight: 600; color: #334155; font-size: 0.85rem; }
        .form-group label i { margin-right: 6px; color: #3b2e91; }
        .form-group input, .form-group select, .form-group textarea { width: 100%; padding: 12px 16px; border: 1.5px solid #e2e8f0; border-radius: 12px; font-size: 0.9rem; font-family: 'Inter', sans-serif; transition: all 0.2s ease; }
        .form-group input:focus, .form-group select:focus, .form-group textarea:focus { outline: none; border-color: #3b2e91; box-shadow: 0 0 0 3px rgba(59,46,145,0.1); }
        .form-group textarea { resize: vertical; min-height: 120px; }
        .current-photo { margin: 16px 0; padding: 16px; background: #f1f5f9; border-radius: 16px; text-align: center; }
        .current-photo img { max-width: 200px; border-radius: 12px; }
        .btn-submit { background: #3b2e91; color: white; padding: 14px 24px; border: none; border-radius: 12px; font-size: 0.9rem; font-weight: 600; cursor: pointer; display: inline-flex; align-items: center; gap: 8px; transition: all 0.2s; }
        .btn-submit:hover { background: #5226a4; transform: translateY(-1px); box-shadow: 0 4px 12px rgba(59,46,145,0.3); }
        .btn-back { display: inline-flex; align-items: center; gap: 6px; background: #f1f5f9; color: #475569; padding: 10px 20px; border-radius: 12px; text-decoration: none; font-size: 0.85rem; font-weight: 500; margin-left: 16px; transition: all 0.2s; }
        .btn-back:hover { background: #e2e8f0; color: #1e293b; }
        .alert { padding: 14px 16px; border-radius: 12px; margin-bottom: 24px; display: flex; align-items: center; gap: 10px; font-size: 0.85rem; }
        .alert-error { background: #fef2f2; color: #dc2626; border: 1px solid #fee2e2; }
        .form-help { font-size: 0.7rem; color: #94a3b8; margin-top: 6px; }
        @media (max-width: 640px) { .container { padding: 16px; } .card-header { padding: 20px 24px; } .card-body { padding: 24px; } .card-header h2 { font-size: 1.4rem; } .btn-back { margin-left: 8px; padding: 8px 16px; } }
    </style>
</head>
<body>
<div class="container">
    <div class="card">
        <div class="card-header">
            <h2><i class="fas fa-edit"></i> Edit Aspirasi</h2>
            <p>Perbarui aspirasi Anda (masih dalam tahap menunggu/pengecekan)</p>
        </div>
        <div class="card-body">
            <?php if (!empty($error)): ?>
                <div class="alert alert-error"><i class="fas fa-exclamation-triangle"></i> <?= htmlspecialchars($error) ?></div>
            <?php endif; ?>
            <form action="" method="POST" enctype="multipart/form-data">
                <div class="form-group">
                    <label><i class="fas fa-comment"></i> Isi Aspirasi</label>
                    <textarea name="isi" required><?= htmlspecialchars($data['isi']) ?></textarea>
                </div>
                <div class="form-group">
                    <label><i class="fas fa-tag"></i> Kategori</label>
                    <select name="kategori" required>
                        <option value="">Pilih Kategori</option>
                        <?php while($cat = mysqli_fetch_assoc($categories)): ?>
                            <option value="<?= htmlspecialchars($cat['nama_kategori']) ?>" <?= ($data['kategori'] == $cat['nama_kategori']) ? 'selected' : '' ?>>
                                <?= htmlspecialchars($cat['nama_kategori']) ?>
                            </option>
                        <?php endwhile; ?>
                    </select>
                </div>
                <div class="form-group">
                    <label><i class="fas fa-location-dot"></i> Lokasi</label>
                    <select name="lokasi" required>
                        <option value="toilet" <?= $data['lokasi'] == 'toilet' ? 'selected' : '' ?>>🚽 Toilet</option>
                        <option value="kelas" <?= $data['lokasi'] == 'kelas' ? 'selected' : '' ?>>📚 Kelas</option>
                        <option value="kantin" <?= $data['lokasi'] == 'kantin' ? 'selected' : '' ?>>🍽️ Kantin</option>
                        <option value="lapangan" <?= $data['lokasi'] == 'lapangan' ? 'selected' : '' ?>>⚽ Lapangan</option>
                        <option value="aula" <?= $data['lokasi'] == 'aula' ? 'selected' : '' ?>>🏛️ Aula</option>
                        <option value="perpustakaan" <?= $data['lokasi'] == 'perpustakaan' ? 'selected' : '' ?>>📖 Perpustakaan</option>
                        <option value="taman" <?= $data['lokasi'] == 'taman' ? 'selected' : '' ?>>🌿 Taman</option>
                    </select>
                </div>
                <div class="current-photo">
                    <label style="font-weight:600; margin-bottom:8px; display:block;"><i class="fas fa-image"></i> Foto Saat Ini</label>
                    <img src="../uploads/<?= htmlspecialchars($data['foto']) ?>" alt="Foto Aspirasi" onerror="this.src='../uploads/default.png'">
                </div>
                <div class="form-group">
                    <label><i class="fas fa-upload"></i> Ganti Foto (Opsional)</label>
                    <input type="file" name="foto" accept="image/*">
                    <div class="form-help">Format: JPG, PNG, GIF (Max 2MB). Kosongkan jika tidak ingin mengganti.</div>
                </div>
                <div style="display: flex; justify-content: space-between; align-items: center; margin-top: 16px;">
                    <button type="submit" class="btn-submit"><i class="fas fa-save"></i> Simpan Perubahan</button>
                    <a href="dashboard.php?mode=saya" class="btn-back"><i class="fas fa-arrow-left"></i> Kembali ke Dashboard</a>
                </div>
            </form>
        </div>
    </div>
</div>
</body>
</html>