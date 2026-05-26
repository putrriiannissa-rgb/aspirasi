<?php
session_start();
include '../config/koneksi.php';

/** @var mysqli $conn */

if (!isset($_SESSION['user']) || $_SESSION['user']['role'] != 'siswa') {
    header("Location: ../auth/login.php");
    exit;
}

$user_id = $_SESSION['user']['id'];
$mode = isset($_GET['mode']) ? $_GET['mode'] : 'semua';

// Ambil nilai filter
$search = isset($_GET['search']) ? mysqli_real_escape_string($conn, $_GET['search']) : '';
$lokasi = isset($_GET['lokasi']) ? mysqli_real_escape_string($conn, $_GET['lokasi']) : '';
$kategori = isset($_GET['kategori']) ? mysqli_real_escape_string($conn, $_GET['kategori']) : '';
$tanggal = isset($_GET['tanggal']) ? mysqli_real_escape_string($conn, $_GET['tanggal']) : '';
$status = isset($_GET['status']) ? mysqli_real_escape_string($conn, $_GET['status']) : '';

// Statistik
$total_aspirasi_saya = mysqli_num_rows(mysqli_query($conn, "SELECT * FROM aspirations WHERE user_id='$user_id'"));
$total_selesai = mysqli_num_rows(mysqli_query($conn, "
    SELECT a.* FROM aspirations a
    LEFT JOIN (
        SELECT aspiration_id, status, 
        ROW_NUMBER() OVER (PARTITION BY aspiration_id ORDER BY id DESC) as rn
        FROM progress
    ) p ON p.aspiration_id = a.id AND p.rn = 1
    WHERE a.user_id='$user_id' AND COALESCE(p.status, a.status) = 'selesai'
"));

$where_extra = "";
if ($mode == 'saya') {
    $where_extra = " AND a.user_id = '$user_id'";
}

// Filter conditions
$filter_sql = "";
if (!empty($search)) {
    $filter_sql .= " AND a.isi LIKE '%$search%'";
}
if (!empty($lokasi)) {
    $filter_sql .= " AND a.lokasi = '$lokasi'";
}
if (!empty($kategori)) {
    $filter_sql .= " AND a.kategori = '$kategori'";
}
if (!empty($tanggal)) {
    $filter_sql .= " AND DATE(a.created_at) = '$tanggal'";
}
if (!empty($status)) {
    $filter_sql .= " AND COALESCE(
        (SELECT p.status FROM progress p WHERE p.aspiration_id = a.id ORDER BY p.id DESC LIMIT 1),
        a.status
    ) = '$status'";
}

$limit = 10;
$page = isset($_GET['page']) ? (int)$_GET['page'] : 1;
if ($page < 1) $page = 1;
$start = ($page - 1) * $limit;

$total_query = mysqli_query($conn, "
    SELECT COUNT(*) as total
    FROM aspirations a
    JOIN users u ON a.user_id = u.id
    WHERE 1=1 $where_extra $filter_sql
");
$total_data = mysqli_fetch_assoc($total_query)['total'];
$total_pages = ceil($total_data / $limit);

$query = "SELECT a.*, u.name,
    COALESCE(
        (SELECT p.status FROM progress p WHERE p.aspiration_id = a.id ORDER BY p.id DESC LIMIT 1),
        a.status
    ) as status_terbaru
    FROM aspirations a 
    JOIN users u ON a.user_id = u.id 
    WHERE 1=1 $where_extra $filter_sql
    ORDER BY a.id DESC
    LIMIT $start, $limit";
$data = mysqli_query($conn, $query);

function renderAspirasiTable($data, $start, $mode, $conn, $page, $total_pages, $search, $lokasi, $kategori, $tanggal, $status) {
    $html = '';
    if(mysqli_num_rows($data) > 0) {
        $html .= '<div class="table-wrapper">
            <table class="aspirasi-table">
                <thead>
                    <tr>
                        <th>No</th>
                        <th>Tanggal</th>
                        <th>Isi Aspirasi</th>
                        <th>Kategori</th>
                        <th>Lokasi</th>
                        <th>Status</th>
                        <th>Foto</th>
                        <th>Aksi</th>
                    </tr>
                </thead>
                <tbody>';
        $no = $start + 1;
        while($d = mysqli_fetch_assoc($data)) {
            $status_aspirasi = $d['status_terbaru'] ?? $d['status'];
            $html .= '<tr>
                <td>' . $no++ . '</td>
                <td>' . date('d/m/Y', strtotime($d['created_at'])) . '</td>
                <td>' . htmlspecialchars(substr($d['isi'], 0, 80)) . (strlen($d['isi']) > 80 ? '...' : '') . '</td>
                <td>' . ucfirst($d['kategori']) . '</td>
                <td>' . ucfirst($d['lokasi']) . '</td>
                <td><span class="badge ' . $status_aspirasi . '">' . 
                    match($status_aspirasi) {
                        'menunggu' => '⏳ MENUNGGU',
                        'pengecekan' => '🔍 PENGECEKAN',
                        'proses' => '⚙️ PROSES',
                        'selesai' => '✅ SELESAI',
                        default => strtoupper($status_aspirasi)
                    } . 
                '</span></td>
                <td><img class="table-img" src="../uploads/' . $d['foto'] . '" onerror="this.src=\'../uploads/default.png\'"></td>
                <td><div class="table-actions">
                    <a class="btn-detail" href="detail_aspirasi.php?id=' . $d['id'] . '">Detail</a>';
            if ($mode == 'saya' && ($status_aspirasi == 'menunggu' || $status_aspirasi == 'pengecekan')) {
                $html .= '<a class="btn-edit" href="edit_aspirasi.php?id=' . $d['id'] . '">Edit</a>
                          <a class="btn-delete" href="hapus_aspirasi.php?id=' . $d['id'] . '" onclick="return confirm(\'Yakin ingin menghapus?\')">Hapus</a>';
            }
            $html .= '</div></td></tr>';
        }
        $html .= '</tbody>
            </table>
        </div>';
        $html .= '<div class="pagination">';
        if($page > 1) {
            $html .= '<a class="page-btn" data-page="' . ($page-1) . '" href="javascript:void(0)">← Prev</a>';
        }
        for($i=1; $i <= $total_pages; $i++) {
            $active = ($i == $page) ? 'active' : '';
            $html .= '<a class="page-btn ' . $active . '" data-page="' . $i . '" href="javascript:void(0)">' . $i . '</a>';
        }
        if($page < $total_pages) {
            $html .= '<a class="page-btn" data-page="' . ($page+1) . '" href="javascript:void(0)">Next →</a>';
        }
        $html .= '</div>';
    } else {
        $html = '<div class="empty-state">
            <i class="fas fa-inbox"></i>
            <h4>Belum Ada Aspirasi</h4>
            <p>Belum ada data aspirasi</p>
        </div>';
    }
    return $html;
}

if (!empty($_SERVER['HTTP_X_REQUESTED_WITH']) && strtolower($_SERVER['HTTP_X_REQUESTED_WITH']) == 'xmlhttprequest') {
    echo renderAspirasiTable($data, $start, $mode, $conn, $page, $total_pages, $search, $lokasi, $kategori, $tanggal, $status);
    exit;
}
?>

<!DOCTYPE html>
<html>
<head>
    <title>Dashboard Siswa | Sistem Aspirasi</title>
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@300;400;500;600;700;800&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="../assets/siswa.css">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <style>
        .filter-group select, .filter-group input {
            width: 100%;
            padding: 10px 12px;
            border: 1px solid #e2e8f0;
            border-radius: 12px;
            font-family: 'Inter', sans-serif;
        }
        .btn-reset-filter {
            background: #3b2e91;
            border: none;
            padding: 10px 16px;
            border-radius: 12px;
            cursor: pointer;
            font-weight: 500;
            color: white;
        }
        .btn-reset-filter:hover {
            background: #5226a4;
        }
        /* Perbaikan loading smooth tanpa patah */
        #result {
            transition: opacity 0.2s ease;
            min-height: 300px;
        }
        .result-loading {
            opacity: 0.5;
            position: relative;
            pointer-events: none;
        }
        .result-loading::after {
            content: "\f110";
            font-family: "Font Awesome 6 Free";
            font-weight: 900;
            position: absolute;
            top: 50%;
            left: 50%;
            transform: translate(-50%, -50%);
            font-size: 2rem;
            color: #3b2e91;
            animation: fa-spin 1s infinite linear;
            z-index: 10;
            background: rgba(255,255,255,0.8);
            width: 60px;
            height: 60px;
            border-radius: 50%;
            display: flex;
            align-items: center;
            justify-content: center;
            pointer-events: none;
        }
        @keyframes fa-spin {
            0% { transform: translate(-50%, -50%) rotate(0deg); }
            100% { transform: translate(-50%, -50%) rotate(360deg); }
        }
        .table-wrapper {
            overflow-x: auto;
        }
        /* Transisi halus untuk tabel */
        .aspirasi-table {
            width: 100%;
            border-collapse: collapse;
        }
        .aspirasi-table tbody tr {
            transition: background-color 0.1s ease;
        }
    </style>
</head>
<body>

<div class="container">
    <!-- Navbar -->
    <nav class="navbar">
        <div class="nav-container">
            <div class="nav-logo">
                <i class="fas fa-graduation-cap"></i>
                <span>Siswa Panel</span>
            </div>
            <div class="nav-menu">
                <button class="nav-link active" onclick="scrollToSection('dashboard')">
                    <i class="fas fa-home"></i> Beranda
                </button>
                <button class="nav-link" onclick="scrollToSection('aspirasi')">
                    <i class="fas fa-list-alt"></i> Aspirasi
                </button>
                <button class="nav-link" onclick="scrollToSection('form')">
                    <i class="fas fa-plus-circle"></i> Tambah
                </button>
            </div>
            <div class="nav-actions">
                <a href="profil.php" class="nav-btn-small">
                    <i class="fas fa-user-circle"></i> Profil
                </a>
                <a href="../auth/logout.php" class="nav-btn-small logout" onclick="return confirm('Yakin logout?')">
                    <i class="fas fa-sign-out-alt"></i> Logout
                </a>
            </div>
        </div>
    </nav>

    <!-- Welcome Section -->
    <div id="dashboard" class="welcome-section" style="scroll-margin-top: 80px;">
        <div class="welcome-badge">
            <i class="fas fa-user-graduate"></i> Selamat Datang
        </div>
        <h1>Halo, <?= htmlspecialchars($_SESSION['user']['name']); ?>!</h1>
        <p>
            Sampaikan aspirasi, saran, atau masukan Anda untuk kemajuan sekolah. 
            Setiap suara Anda sangat berharga bagi kami.
        </p>
        
        <div class="quick-stats">
            <div class="quick-stat">
                <div class="quick-stat-number"><?= $total_aspirasi_saya ?></div>
                <div class="quick-stat-label">Aspirasi Saya</div>
            </div>
            <div class="quick-stat">
                <div class="quick-stat-number"><?= $total_selesai ?></div>
                <div class="quick-stat-label">Selesai</div>
            </div>
            <div class="quick-stat">
                <div class="quick-stat-number"><?= $total_aspirasi_saya - $total_selesai ?></div>
                <div class="quick-stat-label">Dalam Proses</div>
            </div>
        </div>
    </div>

    <!-- Mode Tabs -->
    <div class="mode-tabs">
        <button class="mode-tab <?= $mode == 'semua' ? 'active' : '' ?>" data-mode="semua">
            <i class="fas fa-globe"></i> Semua Aspirasi
        </button>
        <button class="mode-tab <?= $mode == 'saya' ? 'active' : '' ?>" data-mode="saya">
            <i class="fas fa-user"></i> Aspirasi Saya
        </button>
    </div>

    <!-- Form Tambah Aspirasi -->
    <div id="form" class="form-card" style="scroll-margin-top: 80px;">
        <div class="form-card-header">
            <h3><i class="fas fa-pen-alt"></i> Buat Aspirasi Baru</h3>
            <button class="toggle-form-btn" onclick="toggleForm()">
                <i class="fas fa-chevron-down" id="formToggleIcon"></i>
            </button>
        </div>
        <div id="formAspirasi" class="form-card-body" style="display: none;">
            <form action="proses_tambah.php" method="POST" enctype="multipart/form-data">
                <div class="form-group">
                    <label><i class="fas fa-comment"></i> Isi Aspirasi</label>
                    <textarea name="isi" placeholder="Tulis aspirasi Anda di sini..." required></textarea>
                </div>
                <div class="form-row">
                    <div class="form-group">
                        <label><i class="fas fa-location-dot"></i> Lokasi</label>
                        <select name="lokasi" required>
                            <option value="">-- Pilih Lokasi --</option>
                            <option value="toilet">🚽 Toilet</option>
                            <option value="kelas">📚 Kelas</option>
                            <option value="kantin">🍽️ Kantin</option>
                            <option value="lapangan">⚽ Lapangan</option>
                            <option value="aula">🏛️ Aula</option>
                            <option value="perpustakaan">📖 Perpustakaan</option>
                            <option value="taman">🌿 Taman</option>
                        </select>
                    </div>
                    <div class="form-group">
                        <label><i class="fas fa-tag"></i> Kategori</label>
                        <select name="category_id" required>
                            <option value="">Pilih Kategori</option>
                            <?php
                            $kategori_opts = mysqli_query($conn, "SELECT * FROM categories");
                            while($k = mysqli_fetch_assoc($kategori_opts)){
                                echo '<option value="'.$k['id'].'">'.$k['nama_kategori'].'</option>';
                            }
                            ?>
                        </select>
                    </div>
                </div>
                <div class="form-group">
                    <label><i class="fas fa-image"></i> Foto Pendukung</label>
                    <input type="file" name="foto" accept="image/*" required>
                    <small class="form-help">Format: JPG, PNG, GIF (Max 2MB)</small>
                </div>
                <button type="submit" class="btn-submit">
                    <i class="fas fa-paper-plane"></i> Kirim Aspirasi
                </button>
            </form>
        </div>
    </div>

    <!-- Filter Section -->
    <div id="aspirasi" class="filter-section" style="scroll-margin-top: 80px;">
        <div class="filter-title">
            <i class="fas fa-filter"></i> Filter Aspirasi
        </div>
        <div class="filter-grid">
            <div class="filter-group">
                <label><i class="fas fa-search"></i> Cari Aspirasi</label>
                <input type="text" id="searchInput" placeholder="Cari berdasarkan isi..." value="<?= htmlspecialchars($search) ?>" autocomplete="off">
            </div>
            <div class="filter-group">
                <label><i class="fas fa-tag"></i> Kategori</label>
                <select id="kategoriSelect">
                    <option value="">Semua Kategori</option>
                    <?php
                    $kategori_list = mysqli_query($conn, "SELECT * FROM categories");
                    while($kat = mysqli_fetch_assoc($kategori_list)){
                        $selected = ($kategori == $kat['nama_kategori']) ? 'selected' : '';
                        echo '<option value="'.htmlspecialchars($kat['nama_kategori']).'" '.$selected.'>'.htmlspecialchars($kat['nama_kategori']).'</option>';
                    }
                    ?>
                </select>
            </div>
            <div class="filter-group">
                <label><i class="fas fa-location-dot"></i> Lokasi</label>
                <select id="lokasiSelect">
                    <option value="">Semua Lokasi</option>
                    <option value="kelas" <?= $lokasi=='kelas'?'selected':'' ?>>📚 Kelas</option>
                    <option value="toilet" <?= $lokasi=='toilet'?'selected':'' ?>>🚽 Toilet</option>
                    <option value="kantin" <?= $lokasi=='kantin'?'selected':'' ?>>🍽️ Kantin</option>
                    <option value="aula" <?= $lokasi=='aula'?'selected':'' ?>>🏛️ Aula</option>
                    <option value="perpustakaan" <?= $lokasi=='perpustakaan'?'selected':'' ?>>📖 Perpustakaan</option>
                    <option value="lapangan" <?= $lokasi=='lapangan'?'selected':'' ?>>⚽ Lapangan</option>
                    <option value="uks" <?= $lokasi=='uks'?'selected':'' ?>>🏥 UKS</option>
                    <option value="taman" <?= $lokasi=='taman'?'selected':'' ?>>🌿 Taman</option>
                </select>
            </div>
            <div class="filter-group">
                <label><i class="fas fa-calendar-alt"></i> Tanggal</label>
                <input type="date" id="tanggalInput" value="<?= $tanggal ?>" max="<?= date('Y-m-d') ?>">
            </div>
            <div class="filter-group">
                <label><i class="fas fa-signal"></i> Status</label>
                <select id="statusSelect">
                    <option value="">Semua Status</option>
                    <option value="menunggu" <?= $status=='menunggu'?'selected':'' ?>>⏳ Menunggu</option>
                    <option value="proses" <?= $status=='proses'?'selected':'' ?>>⚙️ Proses</option>
                    <option value="selesai" <?= $status=='selesai'?'selected':'' ?>>✅ Selesai</option>
                </select>
            </div>
            <div class="filter-group filter-reset">
                <label>&nbsp;</label>
                <button type="button" class="btn-reset-filter" id="resetFilterBtn">
                    <i class="fas fa-rotate-left"></i> Reset Filter
                </button>
            </div>
        </div>
    </div>
    
    <!-- Aspirasi List -->
    <div class="aspirasi-header">
        <h3><i class="fas fa-list-alt"></i> Daftar Aspirasi</h3>
        <p class="subtitle">Berikut adalah aspirasi yang telah disampaikan</p>
    </div>
    
    <div id="result">
        <?= renderAspirasiTable($data, $start, $mode, $conn, $page, $total_pages, $search, $lokasi, $kategori, $tanggal, $status) ?>
    </div>
</div>

<button id="scrollTopBtn" class="scroll-top-btn" onclick="scrollToTop()">
    <i class="fas fa-arrow-up"></i>
</button>

<script>
    let currentMode = '<?= $mode ?>';
    let currentPage = <?= $page ?>;
    let currentSearch = '<?= addslashes($search) ?>';
    let currentLokasi = '<?= addslashes($lokasi) ?>';
    let currentKategori = '<?= addslashes($kategori) ?>';
    let currentTanggal = '<?= addslashes($tanggal) ?>';
    let currentStatus = '<?= addslashes($status) ?>';
    
    let filterTimeout;
    let isLoading = false;
    let currentRequest = null;
    
    async function fetchAspirasi() {
        if (isLoading) return;
        isLoading = true;
        
        const resultDiv = document.getElementById('result');
        // Hanya tambah class loading, tidak ganti HTML (menghindari patah)
        resultDiv.classList.add('result-loading');
        
        const params = new URLSearchParams();
        params.append('mode', currentMode);
        params.append('page', currentPage);
        if (currentSearch) params.append('search', currentSearch);
        if (currentLokasi) params.append('lokasi', currentLokasi);
        if (currentKategori) params.append('kategori', currentKategori);
        if (currentTanggal) params.append('tanggal', currentTanggal);
        if (currentStatus) params.append('status', currentStatus);
        
        try {
            const response = await fetch('?' + params.toString(), {
                headers: { 'X-Requested-With': 'XMLHttpRequest' }
            });
            const html = await response.text();
            // Ganti konten tanpa efek kedip keras
            resultDiv.innerHTML = html;
            const newUrl = window.location.pathname + '?' + params.toString();
            window.history.pushState({}, '', newUrl);
            attachPaginationEvents();
        } catch (error) {
            console.error('Error fetching data:', error);
            resultDiv.innerHTML = '<div class="empty-state"><i class="fas fa-exclamation-triangle"></i><h4>Terjadi Kesalahan</h4><p>Gagal memuat data. Silakan coba lagi.</p></div>';
        } finally {
            resultDiv.classList.remove('result-loading');
            isLoading = false;
        }
    }
    
    function attachPaginationEvents() {
        document.querySelectorAll('.page-btn[data-page]').forEach(btn => {
            btn.removeEventListener('click', paginationClickHandler);
            btn.addEventListener('click', paginationClickHandler);
        });
    }
    
    function paginationClickHandler(e) {
        e.preventDefault();
        const page = parseInt(this.getAttribute('data-page'));
        if (!isNaN(page) && page !== currentPage) {
            currentPage = page;
            fetchAspirasi();
            scrollToSection('aspirasi');
        }
    }
    
    function applyFilter() {
        currentPage = 1;
        fetchAspirasi();
    }
    
    function triggerDebouncedFilter() {
        clearTimeout(filterTimeout);
        filterTimeout = setTimeout(() => { applyFilter(); }, 400);
    }
    
    const searchInput = document.getElementById('searchInput');
    const kategoriSelect = document.getElementById('kategoriSelect');
    const lokasiSelect = document.getElementById('lokasiSelect');
    const tanggalInput = document.getElementById('tanggalInput');
    const statusSelect = document.getElementById('statusSelect');
    const resetBtn = document.getElementById('resetFilterBtn');
    
    function updateAndFilter() {
        currentSearch = searchInput.value;
        currentKategori = kategoriSelect.value;
        currentLokasi = lokasiSelect.value;
        currentTanggal = tanggalInput.value;
        currentStatus = statusSelect.value;
        applyFilter();
    }
    
    if (searchInput) {
        searchInput.addEventListener('input', () => {
            currentSearch = searchInput.value;
            triggerDebouncedFilter();
        });
    }
    if (kategoriSelect) kategoriSelect.addEventListener('change', updateAndFilter);
    if (lokasiSelect) lokasiSelect.addEventListener('change', updateAndFilter);
    if (tanggalInput) tanggalInput.addEventListener('change', updateAndFilter);
    if (statusSelect) statusSelect.addEventListener('change', updateAndFilter);
    
    if (resetBtn) {
        resetBtn.addEventListener('click', () => {
            searchInput.value = '';
            kategoriSelect.value = '';
            lokasiSelect.value = '';
            tanggalInput.value = '';
            statusSelect.value = '';
            currentSearch = '';
            currentKategori = '';
            currentLokasi = '';
            currentTanggal = '';
            currentStatus = '';
            currentPage = 1;
            fetchAspirasi();
        });
    }
    
    const modeTabs = document.querySelectorAll('.mode-tab');
    modeTabs.forEach(tab => {
        tab.addEventListener('click', (e) => {
            const mode = tab.getAttribute('data-mode');
            if (mode && mode !== currentMode) {
                currentMode = mode;
                currentPage = 1;
                currentSearch = searchInput.value;
                currentKategori = kategoriSelect.value;
                currentLokasi = lokasiSelect.value;
                currentTanggal = tanggalInput.value;
                currentStatus = statusSelect.value;
                fetchAspirasi();
                modeTabs.forEach(t => t.classList.remove('active'));
                tab.classList.add('active');
            }
        });
    });
    
    window.addEventListener('popstate', (event) => {
        const urlParams = new URLSearchParams(window.location.search);
        currentMode = urlParams.get('mode') || 'semua';
        currentPage = parseInt(urlParams.get('page')) || 1;
        currentSearch = urlParams.get('search') || '';
        currentLokasi = urlParams.get('lokasi') || '';
        currentKategori = urlParams.get('kategori') || '';
        currentTanggal = urlParams.get('tanggal') || '';
        currentStatus = urlParams.get('status') || '';
        if (searchInput) searchInput.value = currentSearch;
        if (kategoriSelect) kategoriSelect.value = currentKategori;
        if (lokasiSelect) lokasiSelect.value = currentLokasi;
        if (tanggalInput) tanggalInput.value = currentTanggal;
        if (statusSelect) statusSelect.value = currentStatus;
        modeTabs.forEach(tab => {
            if (tab.getAttribute('data-mode') === currentMode) tab.classList.add('active');
            else tab.classList.remove('active');
        });
        fetchAspirasi();
    });
    
    function scrollToSection(sectionId) {
        const element = document.getElementById(sectionId);
        if (element) element.scrollIntoView({ behavior: 'smooth', block: 'start' });
        document.querySelectorAll('.nav-link').forEach(link => {
            link.classList.remove('active');
            if (link.getAttribute('onclick')?.includes(sectionId)) link.classList.add('active');
        });
    }
    
    function toggleForm() {
        let form = document.getElementById("formAspirasi");
        let icon = document.getElementById("formToggleIcon");
        if (form.style.display === "none" || form.style.display === "") {
            form.style.display = "block";
            icon.classList.remove("fa-chevron-down");
            icon.classList.add("fa-chevron-up");
        } else {
            form.style.display = "none";
            icon.classList.remove("fa-chevron-up");
            icon.classList.add("fa-chevron-down");
        }
    }
    
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
    
    attachPaginationEvents();
</script>
</body>
</html>