<?php
include 'config/koneksi.php';

// Ambil parameter filter
$search = isset($_GET['search']) ? mysqli_real_escape_string($conn, $_GET['search']) : '';
$lokasi = isset($_GET['lokasi']) ? mysqli_real_escape_string($conn, $_GET['lokasi']) : '';
$tanggal = isset($_GET['tanggal']) ? mysqli_real_escape_string($conn, $_GET['tanggal']) : '';
$kategori_filter = isset($_GET['kategori']) ? mysqli_real_escape_string($conn, $_GET['kategori']) : '';
$status_filter = isset($_GET['status']) ? mysqli_real_escape_string($conn, $_GET['status']) : '';

// Deteksi apakah request AJAX
$is_ajax = isset($_GET['ajax']) && $_GET['ajax'] == 1;

// Build WHERE clause
$where = "WHERE 1=1";

if (!empty($search)) {
    $where .= " AND a.isi LIKE '%$search%'";
}
if (!empty($lokasi)) {
    $where .= " AND a.lokasi = '$lokasi'";
}
if (!empty($tanggal)) {
    $where .= " AND DATE(a.created_at) = '$tanggal'";
}
if (!empty($kategori_filter)) {
    $where .= " AND a.kategori = '$kategori_filter'";
}
if (!empty($status_filter)) {
    $where .= " AND COALESCE(
        (SELECT p.status FROM progress p WHERE p.aspiration_id = a.id ORDER BY p.id DESC LIMIT 1),
        a.status
    ) = '$status_filter'";
}

// Pagination
$limit = 10;
$page = isset($_GET['page']) ? (int)$_GET['page'] : 1;
if ($page < 1) $page = 1;
$start = ($page - 1) * $limit;

// Hitung total data
$total_query = mysqli_query($conn, "SELECT COUNT(*) as total FROM aspirations a $where");
$total_data = mysqli_fetch_assoc($total_query)['total'];
$total_pages = ceil($total_data / $limit);

// Ambil data dengan status terbaru
$data = mysqli_query($conn, "
    SELECT a.*,
        COALESCE(
            (SELECT p.status FROM progress p WHERE p.aspiration_id = a.id ORDER BY p.id DESC LIMIT 1),
            a.status
        ) as status_terbaru
    FROM aspirations a
    $where
    ORDER BY a.id DESC
    LIMIT $start, $limit
");

// Jika AJAX, hanya tampilkan konten result (tanpa layout)
if ($is_ajax) {
    // Output hanya bagian result (table + pagination)
    if(mysqli_num_rows($data) > 0):
?>
<div class="table-container">
    <div class="table-header">
        <h3><i class="fas fa-list-alt"></i> Daftar Aspirasi</h3>
        <p class="table-subtitle">Berikut adalah aspirasi yang telah disampaikan</p>
    </div>
    <div class="table-wrapper">
        <table id="aspirasiTable">
            <thead>
                <tr><th>No</th><th>Tanggal</th><th>Isi Aspirasi</th><th>Kategori</th><th>Lokasi</th><th>Status</th><th>Aksi</th></tr>
            </thead>
            <tbody>
                <?php 
                $no = $start + 1;
                while($d = mysqli_fetch_assoc($data)): 
                    $status = $d['status_terbaru'] ?? $d['status'];
                ?>
                <tr>
                    <td><?= $no++ ?></td>
                    <td><?= date('d/m/Y', strtotime($d['created_at'])) ?></td>
                    <td><?= htmlspecialchars(substr($d['isi'], 0, 100)) ?><?= strlen($d['isi']) > 100 ? '...' : '' ?></td>
                    <td><?= ucfirst($d['kategori']) ?></td>
                    <td><?= ucfirst($d['lokasi']) ?></td>
                    <td><span class="badge <?= $status ?>">
                        <?php 
                        $icon = '';
                        if($status == 'menunggu') $icon = '⏳';
                        elseif($status == 'pengecekan') $icon = '🔍';
                        elseif($status == 'proses') $icon = '⚙️';
                        elseif($status == 'selesai') $icon = '✅';
                        echo $icon . ' ' . strtoupper($status);
                        ?>
                    </span></td>
                    <td><a href="detail_aspirasi_guest.php?id=<?= $d['id']; ?>" class="btn-detail"><i class="fas fa-eye"></i> Detail</a></td>
                </tr>
                <?php endwhile; ?>
            </tbody>
        </table>
    </div>
</div>
<div class="pagination">
    <?php if($page > 1): ?>
        <a class="page-btn" data-page="<?= $page-1 ?>" href="javascript:void(0)">← Prev</a>
    <?php endif; ?>
    <?php for($i=1; $i <= $total_pages; $i++): ?>
        <a class="page-btn <?= $i == $page ? 'active' : '' ?>" data-page="<?= $i ?>" href="javascript:void(0)"><?= $i ?></a>
    <?php endfor; ?>
    <?php if($page < $total_pages): ?>
        <a class="page-btn" data-page="<?= $page+1 ?>" href="javascript:void(0)">Next →</a>
    <?php endif; ?>
</div>
<?php else: ?>
<div class="empty-state">
    <i class="fas fa-inbox"></i>
    <h4>Belum Ada Aspirasi</h4>
    <p>Belum ada aspirasi yang dipublikasikan.</p>
</div>
<?php endif;
    exit;
}

// Statistik (hanya untuk tampilan non-AJAX)
$total_aspirasi = mysqli_num_rows(mysqli_query($conn, "SELECT * FROM aspirations"));
$total_selesai = mysqli_num_rows(mysqli_query($conn, "
    SELECT a.* FROM aspirations a
    LEFT JOIN (
        SELECT aspiration_id, status, 
        ROW_NUMBER() OVER (PARTITION BY aspiration_id ORDER BY id DESC) as rn
        FROM progress
    ) p ON p.aspiration_id = a.id AND p.rn = 1
    WHERE COALESCE(p.status, a.status) = 'selesai'
"));
?>
<!DOCTYPE html>
<html>
<head>
    <title>Sistem Aspirasi Sekolah | SMK Negeri 7 Batam</title>
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@300;400;500;600;700;800&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="assets/guest.css">
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
            background: #f1f5f9;
            border: none;
            padding: 10px 16px;
            border-radius: 12px;
            cursor: pointer;
            font-weight: 500;
        }
        .btn-reset-filter:hover {
            background: #7f32a5;
        }
        /* Loading overlay untuk smooth AJAX */
        .result-loading {
            position: relative;
            min-height: 300px;
        }
        .result-loading::after {
            content: '';
            position: absolute;
            top: 0;
            left: 0;
            right: 0;
            bottom: 0;
            background: rgba(255,255,255,0.8);
            border-radius: 16px;
            z-index: 10;
        }
        .result-loading::before {
            content: "\f110";
            font-family: "Font Awesome 6 Free";
            font-weight: 900;
            position: absolute;
            top: 50%;
            left: 50%;
            transform: translate(-50%, -50%);
            font-size: 2rem;
            color: #6366f1;
            animation: fa-spin 1s infinite linear;
            z-index: 11;
        }
        @keyframes fa-spin {
            0% { transform: translate(-50%, -50%) rotate(0deg); }
            100% { transform: translate(-50%, -50%) rotate(360deg); }
        }
        .table-container, .empty-state, .pagination {
            transition: opacity 0.2s ease;
        }
        /* Perbaikan kecil untuk tampilan */
        .pagination .page-btn {
            cursor: pointer;
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
                <span>Sistem Aspirasi</span>
            </div>
            <div class="nav-menu">
                <button class="nav-link" onclick="scrollToSection('home')"><i class="fas fa-home"></i> Beranda</button>
                <button class="nav-link" onclick="scrollToSection('about')"><i class="fas fa-school"></i> Tentang</button>
                <button class="nav-link" onclick="scrollToSection('purpose')"><i class="fas fa-bullhorn"></i> Tujuan</button>
                <button class="nav-link" onclick="scrollToSection('aspirasi')"><i class="fas fa-list-alt"></i> Aspirasi</button>
            </div>
            <div class="nav-actions">
                <a href="auth/login.php" class="nav-login-btn"><i class="fas fa-sign-in-alt"></i> Login</a>
            </div>
        </div>
    </nav>

    <!-- Hero, About, Purpose, Quote -->
    <div id="home" class="hero-section fade-in" style="scroll-margin-top: 80px;">
        <div class="hero-badge"><i class="fas fa-graduation-cap"></i> SMK Negeri 7 Batam</div>
        <h1>Sistem Aspirasi Sekolah</h1>
        <p>Selamat datang di platform aspirasi sekolah. Kami mendengarkan suara Anda untuk menciptakan lingkungan belajar yang lebih baik.</p>
        <div class="hero-stats">
            <div class="hero-stat"><div class="hero-stat-number"><?= $total_aspirasi ?></div><div class="hero-stat-label">Aspirasi Tersalurkan</div></div>
            <div class="hero-stat"><div class="hero-stat-number"><?= $total_selesai ?></div><div class="hero-stat-label">Telah Ditindaklanjuti</div></div>
        </div>
    </div>

    <div id="about" class="about-section fade-in delay-1" style="scroll-margin-top: 80px;">
        <div class="section-header"><i class="fas fa-school"></i><h2>Tentang Sekolah Kami</h2></div>
        <div class="about-content">
            <p class="about-text">SMK Negeri 7 Batam adalah sekolah menengah kejuruan yang berkomitmen untuk mencetak generasi muda yang kompeten, berkarakter, dan siap bersaing di era global. Dengan berbagai program keahlian unggulan, kami terus berinovasi dalam memberikan pendidikan berkualitas.</p>
            <div class="about-grid">
                <div class="about-card"><i class="fas fa-eye"></i><h4>Visi</h4><p>Menjadi sekolah unggulan yang menghasilkan lulusan berkarakter, kompeten, dan berdaya saing global.</p></div>
                <div class="about-card"><i class="fas fa-bullseye"></i><h4>Misi</h4><p>Menyelenggarakan pendidikan berkualitas, mengembangkan bakat siswa, dan menjalin kerjasama dengan industri.</p></div>
                <div class="about-card"><i class="fas fa-heart"></i><h4>Nilai</h4><p>Integritas, Profesionalisme, Inovasi, Kerjasama, dan Kepedulian.</p></div>
            </div>
        </div>
    </div>

    <div id="purpose" class="purpose-section fade-in delay-2" style="scroll-margin-top: 80px;">
        <div class="section-header"><i class="fas fa-bullhorn"></i><h2>Tujuan Platform Ini</h2></div>
        <div class="purpose-content">
            <div class="purpose-text"><p>Platform Aspirasi Sekolah ini hadir sebagai jembatan komunikasi antara siswa, guru, dan pihak sekolah. Kami percaya bahwa setiap suara memiliki nilai dan setiap masukan adalah investasi untuk kemajuan bersama.</p></div>
            <div class="feature-list">
                <div class="feature-item"><i class="fas fa-comment-dots"></i><span>Menampung aspirasi siswa secara transparan</span></div>
                <div class="feature-item"><i class="fas fa-chart-line"></i><span>Memantau progress penanganan aspirasi</span></div>
                <div class="feature-item"><i class="fas fa-handshake"></i><span>Meningkatkan kerjasama antara warga sekolah</span></div>
                <div class="feature-item"><i class="fas fa-tachometer-alt"></i><span>Mempercepat respons terhadap permasalahan</span></div>
                <div class="feature-item"><i class="fas fa-users"></i><span>Menciptakan lingkungan belajar yang nyaman</span></div>
                <div class="feature-item"><i class="fas fa-star"></i><span>Mewujudkan sekolah yang lebih baik</span></div>
            </div>
        </div>
    </div>

    <div class="quote-card fade-in delay-2">
        <div class="quote-text"><i class="fas fa-quote-left"></i> Terima kasih kepada seluruh warga sekolah yang telah berpartisipasi aktif dalam menyampaikan aspirasi. Setiap masukan yang Anda berikan sangat berharga bagi kemajuan sekolah kita bersama. <i class="fas fa-quote-right"></i></div>
        <div class="quote-author">- Kepala SMK Negeri 7 Batam</div>
    </div>

    <!-- Filter Section -->
    <div id="filter" class="filter-section fade-in delay-3" style="scroll-margin-top: 80px;">
        <div class="section-header small"><i class="fas fa-search"></i><h3>Cari & Filter Aspirasi</h3></div>
        <div class="filter-box">
            <div class="filter-group"><label><i class="fas fa-search"></i> Cari Aspirasi</label><input type="text" id="searchInput" placeholder="Cari berdasarkan isi aspirasi..." value="<?= htmlspecialchars($search) ?>" autocomplete="off"></div>
            <div class="filter-group"><label><i class="fas fa-tag"></i> Kategori</label>
                <select id="kategoriSelect"><option value="">Semua Kategori</option>
                <?php $kategori = mysqli_query($conn, "SELECT * FROM categories"); while($k = mysqli_fetch_assoc($kategori)){ $selected = ($kategori_filter == $k['nama_kategori']) ? 'selected' : ''; ?>
                    <option value="<?= htmlspecialchars($k['nama_kategori']) ?>" <?= $selected ?>><?= htmlspecialchars($k['nama_kategori']) ?></option>
                <?php } ?>
                </select>
            </div>
            <div class="filter-group"><label><i class="fas fa-location-dot"></i> Lokasi</label>
                <select id="lokasiSelect"><option value="">Semua Lokasi</option>
                    <option value="kelas" <?= $lokasi=='kelas'?'selected':'' ?>>📚 Kelas</option>
                    <option value="toilet" <?= $lokasi=='toilet'?'selected':'' ?>>🚽 Toilet</option>
                    <option value="kantin" <?= $lokasi=='kantin'?'selected':'' ?>>🍽️ Kantin</option>
                    <option value="aula" <?= $lokasi=='aula'?'selected':'' ?>>🏛️ Aula</option>
                    <option value="perpustakaan" <?= $lokasi=='perpustakaan'?'selected':'' ?>>📖 Perpustakaan</option>
                    <option value="lapangan" <?= $lokasi=='lapangan'?'selected':'' ?>>⚽ Lapangan</option>
                    <option value="taman" <?= $lokasi=='taman'?'selected':'' ?>>🌿 Taman</option>
                    <option value="uks" <?= $lokasi=='uks'?'selected':'' ?>>🏥 UKS</option>
                </select>
            </div>
            <div class="filter-group"><label><i class="fas fa-calendar-alt"></i> Tanggal</label><input type="date" id="tanggalInput" value="<?= $tanggal ?>" max="<?= date('Y-m-d') ?>"></div>
            <div class="filter-group"><label><i class="fas fa-signal"></i> Status</label>
                <select id="statusSelect"><option value="">Semua Status</option>
                    <option value="menunggu" <?= $status_filter=='menunggu'?'selected':'' ?>>⏳ Menunggu</option>
                    <option value="proses" <?= $status_filter=='proses'?'selected':'' ?>>⚙️ Proses</option>
                    <option value="selesai" <?= $status_filter=='selesai'?'selected':'' ?>>✅ Selesai</option>
                </select>
            </div>
            <div class="filter-group filter-reset"><label>&nbsp;</label><button type="button" class="btn-reset-filter" onclick="resetFilter()"><i class="fas fa-rotate-left"></i> Reset Filter</button></div>
        </div>
    </div>

    <!-- Aspirasi List -->
    <div id="aspirasi" class="aspirasi-header fade-in delay-3" style="scroll-margin-top: 80px;">
        <div class="section-header small"><i class="fas fa-list-alt"></i><h3 style="color: var(--dark);">Daftar Aspirasi Terbaru</h3></div>
        <p class="subtitle">Berikut adalah aspirasi yang telah disampaikan oleh warga sekolah</p>
    </div>
    
    <div id="result">
        <?php
        // Tampilkan awal menggunakan data yang sudah diambil (non-AJAX)
        if(mysqli_num_rows($data) > 0):
        ?>
        <div class="table-container">
            <div class="table-header"><h3><i class="fas fa-list-alt"></i> Daftar Aspirasi</h3><p class="table-subtitle">Berikut adalah aspirasi yang telah disampaikan</p></div>
            <div class="table-wrapper">
                <table id="aspirasiTable">
                    <thead><tr><th>No</th><th>Tanggal</th><th>Isi Aspirasi</th><th>Kategori</th><th>Lokasi</th><th>Status</th><th>Aksi</th></tr></thead>
                    <tbody>
                        <?php $no = $start + 1; while($d = mysqli_fetch_assoc($data)): $status = $d['status_terbaru'] ?? $d['status']; ?>
                        <tr>
                            <td><?= $no++ ?></td>
                            <td><?= date('d/m/Y', strtotime($d['created_at'])) ?></td>
                            <td><?= htmlspecialchars(substr($d['isi'], 0, 100)) ?><?= strlen($d['isi']) > 100 ? '...' : '' ?></td>
                            <td><?= ucfirst($d['kategori']) ?></td>
                            <td><?= ucfirst($d['lokasi']) ?></td>
                            <td><span class="badge <?= $status ?>"><?php $icon = ''; if($status == 'menunggu') $icon = '⏳'; elseif($status == 'pengecekan') $icon = '🔍'; elseif($status == 'proses') $icon = '⚙️'; elseif($status == 'selesai') $icon = '✅'; echo $icon . ' ' . strtoupper($status); ?></span></td>
                            <td><a href="detail_aspirasi_guest.php?id=<?= $d['id']; ?>" class="btn-detail"><i class="fas fa-eye"></i> Detail</a></td>
                        </tr>
                        <?php endwhile; ?>
                    </tbody>
                </table>
            </div>
        </div>
        <div class="pagination">
            <?php if($page > 1): ?>
                <a class="page-btn" data-page="<?= $page-1 ?>" href="javascript:void(0)">← Prev</a>
            <?php endif; ?>
            <?php for($i=1; $i <= $total_pages; $i++): ?>
                <a class="page-btn <?= $i == $page ? 'active' : '' ?>" data-page="<?= $i ?>" href="javascript:void(0)"><?= $i ?></a>
            <?php endfor; ?>
            <?php if($page < $total_pages): ?>
                <a class="page-btn" data-page="<?= $page+1 ?>" href="javascript:void(0)">Next →</a>
            <?php endif; ?>
        </div>
        <?php else: ?>
        <div class="empty-state"><i class="fas fa-inbox"></i><h4>Belum Ada Aspirasi</h4><p>Belum ada aspirasi yang dipublikasikan.</p></div>
        <?php endif; ?>
    </div>

    <!-- Footer -->
    <div class="footer fade-in">
        <div class="social-links"><a href="#"><i class="fab fa-facebook"></i></a><a href="#"><i class="fab fa-instagram"></i></a><a href="#"><i class="fab fa-twitter"></i></a><a href="#"><i class="fab fa-youtube"></i></a></div>
        <p>&copy; 2024 SMK Negeri 7 Batam. All Rights Reserved.</p>
        <p>By Putri Annisa</p>
        <p style="margin-top: 10px; font-size: 11px;"><i class="fas fa-heart" style="color: #f94144;"></i> Dibangun dengan dedikasi untuk pendidikan yang lebih baik</p>
    </div>
</div>

<!-- Overlay & Popup -->
<div id="overlay" class="overlay" onclick="closeAll()"></div>
<?php if(isset($_GET['success'])): ?>
<div class="popup" id="popup" onclick="closePopup()"><div class="popup-content"><div class="success-icon">✅</div><p>Request berhasil dikirim! Silakan tunggu konfirmasi dari admin.</p><button onclick="closePopup()" class="btn-ok">OK</button></div></div>
<?php endif; ?>
<button id="scrollTopBtn" class="scroll-top-btn" onclick="scrollToTop()"><i class="fas fa-arrow-up"></i></button>

<script>
// Load data via AJAX tanpa refresh, dengan efek loading halus
function loadAspirasi(page = 1) {
    let search = document.getElementById('searchInput').value;
    let kategori = document.getElementById('kategoriSelect').value;
    let lokasi = document.getElementById('lokasiSelect').value;
    let tanggal = document.getElementById('tanggalInput').value;
    let status = document.getElementById('statusSelect').value;

    let params = new URLSearchParams();
    params.append('ajax', '1');
    params.append('page', page);
    if(search) params.append('search', search);
    if(kategori) params.append('kategori', kategori);
    if(lokasi) params.append('lokasi', lokasi);
    if(tanggal) params.append('tanggal', tanggal);
    if(status) params.append('status', status);

    let resultDiv = document.getElementById('result');
    // Tambahkan class loading overlay hanya jika result tidak kosong
    if (resultDiv.innerHTML.trim() !== '') {
        resultDiv.classList.add('result-loading');
    }
    
    fetch('?' + params.toString())
        .then(response => response.text())
        .then(html => {
            resultDiv.classList.remove('result-loading');
            resultDiv.innerHTML = html;
            // Re-attach event listeners untuk pagination yang baru dimuat
            attachPaginationEvents();
        })
        .catch(err => {
            console.error(err);
            resultDiv.classList.remove('result-loading');
            resultDiv.innerHTML = '<div class="error-state">Gagal memuat data. Silakan coba lagi.</div>';
        });
}

// Pasang event listener ke semua tombol pagination (baik yang lama maupun baru)
function attachPaginationEvents() {
    document.querySelectorAll('.pagination .page-btn').forEach(btn => {
        // Hindari duplikasi event dengan melepas dulu (opsional)
        btn.removeEventListener('click', paginationClickHandler);
        btn.addEventListener('click', paginationClickHandler);
    });
}

function paginationClickHandler(e) {
    e.preventDefault();
    let pageNum = this.getAttribute('data-page');
    if(pageNum) loadAspirasi(pageNum);
}

// Event listener untuk filter otomatis (tanpa refresh)
function bindFilterEvents() {
    let searchInput = document.getElementById('searchInput');
    let kategoriSelect = document.getElementById('kategoriSelect');
    let lokasiSelect = document.getElementById('lokasiSelect');
    let tanggalInput = document.getElementById('tanggalInput');
    let statusSelect = document.getElementById('statusSelect');
    
    const triggerLoad = () => loadAspirasi(1);
    
    if(searchInput) {
        searchInput.addEventListener('input', function() {
            clearTimeout(window.filterTimeout);
            window.filterTimeout = setTimeout(triggerLoad, 500);
        });
    }
    if(kategoriSelect) kategoriSelect.addEventListener('change', triggerLoad);
    if(lokasiSelect) lokasiSelect.addEventListener('change', triggerLoad);
    if(tanggalInput) tanggalInput.addEventListener('change', triggerLoad);
    if(statusSelect) statusSelect.addEventListener('change', triggerLoad);
}

// Reset filter
function resetFilter() {
    document.getElementById('searchInput').value = '';
    document.getElementById('kategoriSelect').value = '';
    document.getElementById('lokasiSelect').value = '';
    document.getElementById('tanggalInput').value = '';
    document.getElementById('statusSelect').value = '';
    loadAspirasi(1);
}

// Smooth scroll
function scrollToSection(sectionId) {
    const element = document.getElementById(sectionId);
    if(element) element.scrollIntoView({ behavior: 'smooth', block: 'start' });
}

// Scroll to top
const scrollTopBtn = document.getElementById("scrollTopBtn");
window.onscroll = function() {
    if(document.body.scrollTop > 200 || document.documentElement.scrollTop > 200) {
        scrollTopBtn.classList.add("show");
    } else {
        scrollTopBtn.classList.remove("show");
    }
};
function scrollToTop() { window.scrollTo({ top: 0, behavior: "smooth" }); }

// Popup close
function closePopup() { let popup = document.getElementById('popup'); if(popup) popup.style.display = 'none'; }
function closeAll() { /* custom jika ada */ }

// Inisialisasi saat halaman selesai dimuat
document.addEventListener('DOMContentLoaded', function() {
    bindFilterEvents();
    attachPaginationEvents();
});
</script>
</body>
</html>