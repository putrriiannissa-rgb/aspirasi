<?php
session_start();
include '../config/koneksi.php';

/** @var mysqli $conn */

$data = mysqli_query($conn, "SELECT * FROM users WHERE role='siswa' ORDER BY created_at DESC");
?>

<!DOCTYPE html>
<html>
<head>
    <title>Kelola Siswa - Aspirasi System</title>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
</head>
<body>

<?php include 'partials/sidebar.php'; ?>

<div class="main-content">
    <div class="card">
        <div class="card-header">
            <?php if(isset($_GET['success']) && $_GET['success'] == 'added'): ?>
    <div style="margin: 0 28px 20px 28px; padding: 12px 16px; background: #ecfdf5; border: 1px solid #d1fae5; border-radius: 12px; color: #059669; display: flex; align-items: center; gap: 10px;">
        <span>✅</span> Siswa berhasil ditambahkan!
    </div>
<?php endif; ?>
            <div style="display: flex; align-items: center; gap: 16px;">
                <h2>🎓 Kelola Siswa</h2>
                <div class="badge-count">
                    Total: <?= mysqli_num_rows($data) ?> siswa
                </div>
            </div>
            <a href="tambah_siswa.php" class="btn-add">
                + Tambah Siswa
            </a>
        </div>

        <!-- Search Box -->
        <div class="search-container">
            <div class="search-box">
                <span>🔍</span>
                <input type="text" id="searchInput" placeholder="Cari nama atau username siswa..." onkeyup="searchTable()">
            </div>
        </div>
        
        <div class="table-container">
            <?php if(mysqli_num_rows($data) > 0): ?>
                <table id="siswaTable">
                    <thead>
                        <tr>
                            <th>No</th>
                            <th>Nama Siswa</th>
                            <th>Username</th>
                            <th>Status</th>
                            <th style="width: 240px;">Aksi</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php $no=1; while($row = mysqli_fetch_assoc($data)): ?>
                        <tr>
                            <td class="no-column"><?= str_pad($no++, 2, '0', STR_PAD_LEFT) ?></td>
                            <td>
                                <div class="siswa-name">
                                    <div class="siswa-avatar">
                                        <?= htmlspecialchars(strtoupper(substr($row['name'], 0, 1))) ?>
                                    </div>
                                    <?= htmlspecialchars($row['name']) ?>
                                </div>
                             </td>
                            <td>
                                <span class="username">@<?= htmlspecialchars($row['username']) ?></span>
                            </td>
                            <td>
                                <?php 
                                // Contoh status (bisa disesuaikan dengan field status di database)
                                // Jika tidak ada field status, bisa ditampilkan sebagai "Aktif" semua
                                $status = $row['status'] ?? 'active';
                                ?>
                                <span class="status-badge <?= $status == 'active' ? 'status-active' : 'status-inactive' ?>">
                                    <?= $status == 'active' ? '🟢 Aktif' : '🔴 Nonaktif' ?>
                                </span>
                            </td>
                            <td>
                                <div class="action-buttons">
                                    <a href="edit_siswa.php?id=<?= $row['id'] ?>" class="btn-edit">
                                        ✏️ Edit
                                    </a>
                                    <button onclick="confirmReset('<?= $row['id'] ?>', '<?= htmlspecialchars($row['username']) ?>')" class="btn-reset">
                                        🔄 Reset Password
                                    </button>
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
                    <p>Belum ada siswa yang terdaftar</p>
                    <p style="font-size: 0.85rem; margin-top: 8px;">Klik tombol "+ Tambah Siswa" untuk menambahkan</p>
                </div>
            <?php endif; ?>
        </div>
    </div>
</div>

<!-- Modal Konfirmasi Hapus -->
<div id="deleteModal" class="modal">
    <div class="modal-content">
        <h3>Hapus Siswa</h3>
        <p>Apakah Anda yakin ingin menghapus siswa ini?<br>Tindakan ini tidak dapat dibatalkan.</p>
        <div class="modal-buttons">
            <button class="modal-cancel" onclick="closeModal()">Batal</button>
            <button class="modal-confirm" onclick="deleteSiswa()">Hapus</button>
        </div>
    </div>
</div>

<!-- Modal Konfirmasi Reset Password -->
<div id="resetModal" class="modal">
    <div class="modal-content">
        <h3>Reset Password</h3>
        <p id="resetMessage">Apakah Anda yakin ingin mereset password siswa ini?</p>
        <div class="modal-buttons">
            <button class="modal-cancel" onclick="closeResetModal()">Batal</button>
            <button class="modal-confirm" onclick="resetPassword()">Reset</button>
        </div>
    </div>
</div>

<script>
    let selectedSiswaId = null;
    let selectedSiswaUsername = null;

    // Fungsi untuk search
    function searchTable() {
        let input = document.getElementById('searchInput');
        let filter = input.value.toLowerCase();
        let table = document.getElementById('siswaTable');
        let tr = table.getElementsByTagName('tr');

        for (let i = 1; i < tr.length; i++) {
            let tdName = tr[i].getElementsByTagName('td')[1];
            let tdUsername = tr[i].getElementsByTagName('td')[2];
            if (tdName || tdUsername) {
                let nameValue = tdName.textContent || tdName.innerText;
                let usernameValue = tdUsername.textContent || tdUsername.innerText;
                if (nameValue.toLowerCase().indexOf(filter) > -1 || usernameValue.toLowerCase().indexOf(filter) > -1) {
                    tr[i].style.display = '';
                } else {
                    tr[i].style.display = 'none';
                }
            }
        }
    }

    // Fungsi untuk hapus
    function confirmDelete(id) {
        selectedSiswaId = id;
        document.getElementById('deleteModal').style.display = 'flex';
    }

    function closeModal() {
        document.getElementById('deleteModal').style.display = 'none';
        selectedSiswaId = null;
    }

    function deleteSiswa() {
        if (selectedSiswaId) {
            window.location.href = 'hapus_siswa.php?id=' + selectedSiswaId;
        }
    }

    // Fungsi untuk reset password
    function confirmReset(id, username) {
        selectedSiswaId = id;
        selectedSiswaUsername = username;
        document.getElementById('resetMessage').innerHTML = `Apakah Anda yakin ingin mereset password untuk siswa <strong>${username}</strong>?<br>Password akan direset menjadi <strong>12345678</strong>.`;
        document.getElementById('resetModal').style.display = 'flex';
    }

    function closeResetModal() {
        document.getElementById('resetModal').style.display = 'none';
        selectedSiswaId = null;
        selectedSiswaUsername = null;
    }

    function resetPassword() {
        if (selectedSiswaId) {
            window.location.href = 'reset_password_siswa.php?id=' + selectedSiswaId;
        }
    }

    // Tutup modal jika klik di luar konten
    window.onclick = function(event) {
        const deleteModal = document.getElementById('deleteModal');
        const resetModal = document.getElementById('resetModal');
        if (event.target === deleteModal) {
            closeModal();
        }
        if (event.target === resetModal) {
            closeResetModal();
        }
    }
</script>

</body>
</html>