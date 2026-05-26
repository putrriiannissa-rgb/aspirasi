<?php
session_start();
include '../config/koneksi.php';

/** @var mysqli $conn */

// Ambil data admin
$admins = mysqli_query($conn, "SELECT * FROM users WHERE role='admin' ORDER BY created_at DESC");

// Ambil data siswa
$students = mysqli_query($conn, "SELECT * FROM users WHERE role='siswa' ORDER BY created_at DESC");

// Variabel untuk tab aktif (default admin)
$active_tab = isset($_GET['tab']) ? $_GET['tab'] : 'admin';
?>

<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Kelola Pengguna - Sistem Aspirasi</title>
    
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

        /* ========== SIDEBAR STYLE (sama seperti dashboard) ========== */
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
            box-shadow: 0 1px 3px rgba(0,0,0,0.04);
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

        .btn-add {
            background: #3b82f6;
            color: white;
            border: none;
            padding: 8px 20px;
            border-radius: 40px;
            font-weight: 500;
            text-decoration: none;
            font-size: 0.85rem;
            transition: 0.2s;
            display: inline-flex;
            align-items: center;
            gap: 8px;
        }

        .btn-add:hover {
            background: #2563eb;
            transform: translateY(-1px);
        }

        /* Tabs */
        .tabs {
            display: flex;
            gap: 8px;
            padding: 0 28px;
            border-bottom: 1px solid #edf2f7;
            background: #ffffff;
        }

        .tab-btn {
            padding: 12px 24px;
            font-weight: 600;
            font-size: 0.9rem;
            border: none;
            background: transparent;
            color: #64748b;
            cursor: pointer;
            transition: 0.2s;
            border-bottom: 2px solid transparent;
        }

        .tab-btn.active {
            color: #3b82f6;
            border-bottom-color: #3b82f6;
        }

        .tab-content {
            display: none;
            padding: 24px 28px;
        }

        .tab-content.active {
            display: block;
            animation: fade 0.3s ease;
        }

        @keyframes fade {
            from { opacity: 0; transform: translateY(5px); }
            to { opacity: 1; transform: translateY(0); }
        }

        /* Search Box */
        .search-container {
            margin-bottom: 24px;
        }

        .search-box {
            display: flex;
            align-items: center;
            background: #f8fafc;
            border: 1px solid #e2e8f0;
            border-radius: 48px;
            padding: 8px 18px;
            max-width: 320px;
        }

        .search-box span {
            color: #94a3b8;
            margin-right: 10px;
        }

        .search-box input {
            border: none;
            background: transparent;
            width: 100%;
            font-family: 'Inter', sans-serif;
            outline: none;
        }

        /* Table */
        .table-wrapper {
            overflow-x: auto;
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

        /* Avatar & Nama */
        .admin-avatar, .siswa-avatar {
            width: 36px;
            height: 36px;
            border-radius: 12px;
            background: #eef2ff;
            display: flex;
            align-items: center;
            justify-content: center;
            font-weight: 700;
            color: #3b82f6;
        }

        .admin-name, .siswa-name {
            display: flex;
            align-items: center;
            gap: 12px;
        }

        .username {
            font-family: monospace;
            background: #f1f5f9;
            padding: 4px 10px;
            border-radius: 20px;
            font-size: 0.8rem;
        }

        /* Status Badge */
        .status-badge {
            display: inline-flex;
            align-items: center;
            gap: 6px;
            padding: 4px 12px;
            border-radius: 40px;
            font-size: 0.7rem;
            font-weight: 600;
        }
        .status-active {
            background: #dcfce7;
            color: #15803d;
        }
        .status-inactive {
            background: #fee2e2;
            color: #b91c1c;
        }

        /* Action Buttons */
        .action-buttons {
            display: flex;
            gap: 8px;
            flex-wrap: wrap;
        }

        .btn-edit, .btn-reset, .btn-delete {
            padding: 6px 14px;
            border-radius: 30px;
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

        .btn-edit {
            background: #eef2ff;
            color: #3b82f6;
        }
        .btn-edit:hover {
            background: #3b82f6;
            color: white;
        }

        .btn-reset {
            background: #fff7ed;
            color: #ea580c;
        }
        .btn-reset:hover {
            background: #ea580c;
            color: white;
        }

        .btn-delete {
            background: #fef2f2;
            color: #dc2626;
        }
        .btn-delete:hover {
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

        /* Modal */
        .modal {
            display: none;
            position: fixed;
            top: 0;
            left: 0;
            width: 100%;
            height: 100%;
            background: rgba(0,0,0,0.5);
            align-items: center;
            justify-content: center;
            z-index: 1000;
        }
        .modal-content {
            background: white;
            padding: 28px;
            border-radius: 24px;
            max-width: 400px;
            width: 90%;
            text-align: center;
        }
        .modal-buttons {
            display: flex;
            gap: 12px;
            justify-content: center;
            margin-top: 24px;
        }
        .modal-cancel, .modal-confirm {
            padding: 8px 20px;
            border-radius: 40px;
            border: none;
            font-weight: 500;
            cursor: pointer;
        }
        .modal-cancel {
            background: #e2e8f0;
            color: #1e293b;
        }
        .modal-confirm {
            background: #dc2626;
            color: white;
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
        <li class="nav-item"><a href="users.php" class="nav-link active"><i class="fas fa-users"></i> Kelola Pengguna</a></li>
        <li class="nav-item"><a href="kategori.php" class="nav-link"><i class="fas fa-tags"></i> Kategori</a></li>
        <li class="nav-item"><a href="request.php" class="nav-link"><i class="fas fa-chart-line"></i> Request Akun</a></li>
        <li class="nav-item" style="margin-top: 20px;"><a href="logout.php" class="nav-link" style="color: #ef4444;"><i class="fas fa-sign-out-alt"></i> Logout</a></li>
    </ul>
</aside>

<!-- MAIN CONTENT -->
<div class="main-content">
    <div class="card">
        <div class="card-header">
            <div style="display: flex; align-items: center; gap: 16px;">
                <h2><i class="fas fa-users"></i> Kelola Pengguna</h2>
                <div class="badge-count">
                    Admin: <?= mysqli_num_rows($admins) ?> | Siswa: <?= mysqli_num_rows($students) ?>
                </div>
            </div>
            <a href="<?= $active_tab === 'admin' ? 'tambah_admin.php' : 'tambah_siswa.php' ?>" class="btn-add" id="dynamicAddBtn">
                + Tambah <?= $active_tab === 'admin' ? 'Admin' : 'Siswa' ?>
            </a>
        </div>

        <!-- TABS -->
        <div class="tabs">
            <button class="tab-btn <?= $active_tab == 'admin' ? 'active' : '' ?>" data-tab="admin">👥 Data Admin</button>
            <button class="tab-btn <?= $active_tab == 'siswa' ? 'active' : '' ?>" data-tab="siswa">🎓 Data Siswa</button>
        </div>

        <!-- TAB ADMIN -->
        <div id="adminTab" class="tab-content <?= $active_tab == 'admin' ? 'active' : '' ?>">
            <div class="table-wrapper">
                <?php if(mysqli_num_rows($admins) > 0): ?>
                    <table>
                        <thead>
                            <tr><th>No</th><th>Nama Admin</th><th>Username</th><th>Aksi</th></tr>
                        </thead>
                        <tbody>
                            <?php $no=1; while($admin = mysqli_fetch_assoc($admins)): ?>
                            <tr>
                                <td style="width: 70px;"><?= str_pad($no++, 2, '0', STR_PAD_LEFT) ?></td>
                                <td>
                                    <div class="admin-name">
                                        <div class="admin-avatar"><?= strtoupper(substr($admin['name'], 0, 1)) ?></div>
                                        <?= htmlspecialchars($admin['name']) ?>
                                    </div>
                                 </td>
                                <td><span class="username">@<?= htmlspecialchars($admin['username']) ?></span></td>
                                <td>
                                    <div class="action-buttons">
                                        <a href="edit_admin.php?id=<?= $admin['id'] ?>" class="btn-edit"><i class="fas fa-edit"></i> Edit</a>
                                        <button onclick="confirmDeleteAdmin(<?= $admin['id'] ?>)" class="btn-delete"><i class="fas fa-trash"></i> Hapus</button>
                                    </div>
                                 </td>
                             </tr>
                            <?php endwhile; ?>
                        </tbody>
                    </table>
                <?php else: ?>
                    <div class="empty-state"><i class="fas fa-user-slash"></i><p>Belum ada admin terdaftar</p></div>
                <?php endif; ?>
            </div>
        </div>

        <!-- TAB SISWA -->
        <div id="siswaTab" class="tab-content <?= $active_tab == 'siswa' ? 'active' : '' ?>">
            <div class="search-container">
                <div class="search-box">
                    <span><i class="fas fa-search"></i></span>
                    <input type="text" id="searchSiswa" placeholder="Cari nama atau username siswa..." onkeyup="searchSiswaTable()">
                </div>
            </div>
            <div class="table-wrapper">
                <?php if(mysqli_num_rows($students) > 0): ?>
                    <table id="siswaTable">
                        <thead>
                            <tr><th>No</th><th>Nama Siswa</th><th>Username</th><th>Status</th><th>Aksi</th></tr>
                        </thead>
                        <tbody>
                            <?php $no=1; while($siswa = mysqli_fetch_assoc($students)): 
                                $status = $siswa['status'] ?? 'active';
                            ?>
                            <tr>
                                <td><?= str_pad($no++, 2, '0', STR_PAD_LEFT) ?></td>
                                <td>
                                    <div class="siswa-name">
                                        <div class="siswa-avatar"><?= strtoupper(substr($siswa['name'], 0, 1)) ?></div>
                                        <?= htmlspecialchars($siswa['name']) ?>
                                    </div>
                                 </td>
                                <td><span class="username">@<?= htmlspecialchars($siswa['username']) ?></span></td>
                                <td>
                                    <span class="status-badge <?= $status == 'active' ? 'status-active' : 'status-inactive' ?>">
                                        <?= $status == 'active' ? '🟢 Aktif' : '🔴 Nonaktif' ?>
                                    </span>
                                 </td>
                                <td>
                                    <div class="action-buttons">
                                        <a href="edit_siswa.php?id=<?= $siswa['id'] ?>" class="btn-edit"><i class="fas fa-edit"></i> Edit</a>
                                        <button onclick="confirmReset(<?= $siswa['id'] ?>, '<?= htmlspecialchars($siswa['username']) ?>')" class="btn-reset"><i class="fas fa-key"></i> Reset PW</button>
                                        <button onclick="confirmDeleteSiswa(<?= $siswa['id'] ?>)" class="btn-delete"><i class="fas fa-trash"></i> Hapus</button>
                                    </div>
                                 </td>
                             </tr>
                            <?php endwhile; ?>
                        </tbody>
                    </table>
                <?php else: ?>
                    <div class="empty-state"><i class="fas fa-user-graduate"></i><p>Belum ada siswa terdaftar</p></div>
                <?php endif; ?>
            </div>
        </div>
    </div>
</div>

<!-- MODAL HAPUS ADMIN -->
<div id="deleteAdminModal" class="modal">
    <div class="modal-content">
        <h3>Hapus Admin</h3>
        <p>Apakah Anda yakin ingin menghapus admin ini?<br>Tindakan ini tidak dapat dibatalkan.</p>
        <div class="modal-buttons">
            <button class="modal-cancel" onclick="closeDeleteAdminModal()">Batal</button>
            <button class="modal-confirm" onclick="deleteAdmin()">Hapus</button>
        </div>
    </div>
</div>

<!-- MODAL HAPUS SISWA -->
<div id="deleteSiswaModal" class="modal">
    <div class="modal-content">
        <h3>Hapus Siswa</h3>
        <p>Apakah Anda yakin ingin menghapus siswa ini?<br>Tindakan ini tidak dapat dibatalkan.</p>
        <div class="modal-buttons">
            <button class="modal-cancel" onclick="closeDeleteSiswaModal()">Batal</button>
            <button class="modal-confirm" onclick="deleteSiswa()">Hapus</button>
        </div>
    </div>
</div>

<!-- MODAL RESET PASSWORD SISWA -->
<div id="resetModal" class="modal">
    <div class="modal-content">
        <h3>Reset Password</h3>
        <p id="resetMessage">Apakah Anda yakin ingin mereset password siswa ini?<br>Password akan direset menjadi <strong>12345678</strong>.</p>
        <div class="modal-buttons">
            <button class="modal-cancel" onclick="closeResetModal()">Batal</button>
            <button class="modal-confirm" onclick="resetPassword()">Reset</button>
        </div>
    </div>
</div>

<script>
    // ========== TAB LOGIC ==========
    const tabs = document.querySelectorAll('.tab-btn');
    const contents = document.querySelectorAll('.tab-content');
    const dynamicAddBtn = document.getElementById('dynamicAddBtn');

    tabs.forEach(btn => {
        btn.addEventListener('click', () => {
            const tabId = btn.getAttribute('data-tab');
            // Update URL parameter tanpa reload
            const url = new URL(window.location.href);
            url.searchParams.set('tab', tabId);
            window.history.pushState({}, '', url);
            
            // Update active class on buttons
            tabs.forEach(b => b.classList.remove('active'));
            btn.classList.add('active');
            
            // Show active content
            contents.forEach(content => content.classList.remove('active'));
            document.getElementById(tabId + 'Tab').classList.add('active');
            
            // Ubah tombol tambah sesuai tab
            if(tabId === 'admin') {
                dynamicAddBtn.href = 'tambah_admin.php';
                dynamicAddBtn.innerHTML = '+ Tambah Admin';
            } else {
                dynamicAddBtn.href = 'tambah_siswa.php';
                dynamicAddBtn.innerHTML = '+ Tambah Siswa';
            }
        });
    });

    // ========== ADMIN DELETE ==========
    let selectedAdminId = null;
    function confirmDeleteAdmin(id) {
        selectedAdminId = id;
        document.getElementById('deleteAdminModal').style.display = 'flex';
    }
    function closeDeleteAdminModal() {
        document.getElementById('deleteAdminModal').style.display = 'none';
        selectedAdminId = null;
    }
    function deleteAdmin() {
        if(selectedAdminId) window.location.href = 'hapus_admin.php?id=' + selectedAdminId;
    }

    // ========== SISWA DELETE ==========
    let selectedSiswaId = null;
    function confirmDeleteSiswa(id) {
        selectedSiswaId = id;
        document.getElementById('deleteSiswaModal').style.display = 'flex';
    }
    function closeDeleteSiswaModal() {
        document.getElementById('deleteSiswaModal').style.display = 'none';
        selectedSiswaId = null;
    }
    function deleteSiswa() {
        if(selectedSiswaId) window.location.href = 'hapus_siswa.php?id=' + selectedSiswaId;
    }

    // ========== RESET PASSWORD SISWA ==========
    let resetSiswaId = null;
    function confirmReset(id, username) {
        resetSiswaId = id;
        document.getElementById('resetMessage').innerHTML = `Apakah Anda yakin ingin mereset password untuk siswa <strong>${username}</strong>?<br>Password akan direset menjadi <strong>12345678</strong>.`;
        document.getElementById('resetModal').style.display = 'flex';
    }
    function closeResetModal() {
        document.getElementById('resetModal').style.display = 'none';
        resetSiswaId = null;
    }
    function resetPassword() {
        if(resetSiswaId) window.location.href = 'reset_password_siswa.php?id=' + resetSiswaId;
    }

    // ========== SEARCH SISWA ==========
    function searchSiswaTable() {
        let input = document.getElementById('searchSiswa');
        let filter = input.value.toLowerCase();
        let table = document.getElementById('siswaTable');
        if(!table) return;
        let rows = table.getElementsByTagName('tr');
        for(let i=1; i<rows.length; i++) {
            let nameCell = rows[i].getElementsByTagName('td')[1];
            let userCell = rows[i].getElementsByTagName('td')[2];
            if(nameCell && userCell) {
                let name = nameCell.innerText.toLowerCase();
                let user = userCell.innerText.toLowerCase();
                rows[i].style.display = (name.includes(filter) || user.includes(filter)) ? '' : 'none';
            }
        }
    }

    // Close modals when clicking outside
    window.onclick = function(e) {
        if(e.target.classList.contains('modal')) e.target.style.display = 'none';
    }
</script>

</body>
</html>