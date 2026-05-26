<?php
session_start();
include '../config/koneksi.php';

/** @var mysqli $conn */

$data = mysqli_query($conn, "SELECT * FROM users WHERE role='admin' ORDER BY created_at DESC");
?>

<!DOCTYPE html>
<html>
<head>
    <title>Kelola Admin - Aspirasi System</title>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
</head>
<body>

<?php include 'partials/sidebar.php'; ?>

<div class="main-content">
    <div class="card">
        <div class="card-header">
            <div style="display: flex; align-items: center; gap: 16px;">
                <h2>👥 Kelola Admin</h2>
                <div class="badge-count">
                    Total: <?= mysqli_num_rows($data) ?> admin
                </div>
            </div>
            <a href="tambah_admin.php" class="btn-add">
                + Tambah Admin
            </a>
        </div>
        
        <div class="table-container">
            <?php if(mysqli_num_rows($data) > 0): ?>
                <table>
                    <thead>
                        <tr>
                            <th>No</th>
                            <th>Nama Admin</th>
                            <th>Username</th>
                            <th style="width: 180px;">Aksi</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php $no=1; while($row = mysqli_fetch_assoc($data)): ?>
                        <tr>
                            <td class="no-column"><?= str_pad($no++, 2, '0', STR_PAD_LEFT) ?></td>
                            <td>
                                <div class="admin-name">
                                    <div class="admin-avatar">
                                        <?= substr($row['name'], 0, 1) ?>
                                    </div>
                                    <?= htmlspecialchars($row['name']) ?>
                                </div>
                             </td>
                            <td>
                                <span class="username">@<?= htmlspecialchars($row['username']) ?></span>
                            </td>
                            <td>
                                <div class="action-buttons">
                                    <a href="edit_admin.php?id=<?= $row['id'] ?>" class="btn-edit">
                                        ✏️ Edit
                                    </a>
                                    <button onclick="confirmDelete(<?= $row['id'] ?>)" class="btn-delete">
                                        🗑️ Hapus
                                    </button>
                                </div>
                            </td>
                        </tr>
                        <?php endwhile; ?>
                    </tbody>
                </table>
            <?php else: ?>
                <div class="empty-state">
                    <svg width="64" height="64" viewBox="0 0 24 24" fill="none" xmlns="http://www.w3.org/2000/svg">
                        <path d="M12 4V20M20 12H4" stroke="#cbd5e1" stroke-width="1.5" stroke-linecap="round"/>
                        <circle cx="12" cy="12" r="9" stroke="#cbd5e1" stroke-width="1.5"/>
                    </svg>
                    <p>Belum ada admin yang terdaftar</p>
                    <p style="font-size: 0.85rem; margin-top: 8px;">Klik tombol "+ Tambah Admin" untuk menambahkan</p>
                </div>
            <?php endif; ?>
        </div>
    </div>
</div>

<!-- Modal Konfirmasi Hapus -->
<div id="deleteModal" class="modal">
    <div class="modal-content">
        <h3>Hapus Admin</h3>
        <p>Apakah Anda yakin ingin menghapus admin ini?<br>Tindakan ini tidak dapat dibatalkan.</p>
        <div class="modal-buttons">
            <button class="modal-cancel" onclick="closeModal()">Batal</button>
            <button class="modal-confirm" onclick="deleteAdmin()">Hapus</button>
        </div>
    </div>
</div>

<script>
    let selectedAdminId = null;

    function confirmDelete(id) {
        selectedAdminId = id;
        document.getElementById('deleteModal').style.display = 'flex';
    }

    function closeModal() {
        document.getElementById('deleteModal').style.display = 'none';
        selectedAdminId = null;
    }

    function deleteAdmin() {
        if (selectedAdminId) {
            window.location.href = 'hapus_admin.php?id=' + selectedAdminId;
        }
    }

    // Tutup modal jika klik di luar konten
    window.onclick = function(event) {
        const modal = document.getElementById('deleteModal');
        if (event.target === modal) {
            closeModal();
        }
    }
</script>

</body>
</html>