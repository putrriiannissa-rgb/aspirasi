<?php
session_start();
include '../config/koneksi.php';

/** @var mysqli $conn */

if (!isset($_SESSION['user'])) {
    exit;
}

$user_id = $_SESSION['user']['id'];
$mode = $_GET['mode'] ?? 'semua';

$search   = mysqli_real_escape_string($conn, $_GET['search'] ?? '');
$lokasi   = mysqli_real_escape_string($conn, $_GET['lokasi'] ?? '');
$kategori = mysqli_real_escape_string($conn, $_GET['kategori'] ?? '');
$tanggal  = mysqli_real_escape_string($conn, $_GET['tanggal'] ?? '');

// FILTER MODE
$where_extra = "";
if ($mode == 'saya') {
    $where_extra = " AND a.user_id='$user_id'";
}

// FILTER INPUT
$filter_sql = "";

if (!empty($search)) {
    $filter_sql .= " AND a.isi LIKE '%$search%'";
}

if (!empty($lokasi)) {
    $filter_sql .= " AND a.lokasi='$lokasi'";
}

if (!empty($kategori)) {
    $filter_sql .= " AND a.kategori='$kategori'";
}

if (!empty($tanggal)) {
    $filter_sql .= " AND DATE(a.created_at)='$tanggal'";
}

// QUERY
$query = "
SELECT a.*, u.name,
COALESCE(
    (
        SELECT p.status
        FROM progress p
        WHERE p.aspiration_id = a.id
        ORDER BY p.id DESC
        LIMIT 1
    ),
    a.status
) as status_terbaru

FROM aspirations a
JOIN users u ON a.user_id = u.id

WHERE 1=1
$where_extra
$filter_sql

ORDER BY a.id DESC
";

$data = mysqli_query($conn, $query);

// OUTPUT HTML
if (mysqli_num_rows($data) > 0):

    while ($d = mysqli_fetch_assoc($data)):

        $status = $d['status_terbaru'] ?? $d['status'];
?>

<div class="aspirasi-card">

    <div class="card-header-info">
        <span class="card-date">
            <i class="fas fa-calendar"></i>
            <?= date('d/m/Y', strtotime($d['created_at'])) ?>
        </span>

        <span class="card-location">
            <i class="fas fa-location-dot"></i>
            <?= ucfirst($d['lokasi']) ?>
        </span>

        <span class="card-user">
            <i class="fas fa-user"></i>
            <?= ($d['user_id'] == $user_id) ? 'Saya' : 'Siswa' ?>
        </span>
    </div>

    <div class="card-content">

        <p class="aspirasi-text">
            <?= htmlspecialchars(substr($d['isi'], 0, 120)) ?>
            <?php if(strlen($d['isi']) > 120): ?>
                ...
            <?php endif; ?>
        </p>

        <div class="card-tags">
            <span class="tag kategori">
                <i class="fas fa-tag"></i>
                <?= ucfirst($d['kategori']) ?>
            </span>
        </div>

        <div class="badge <?= $status ?>">
            <?php
            $icon = '';

            if($status == 'menunggu') {
                $icon = '⏳';
            } elseif($status == 'pengecekan') {
                $icon = '🔍';
            } elseif($status == 'proses') {
                $icon = '⚙️';
            } elseif($status == 'selesai') {
                $icon = '✅';
            }

            echo $icon . ' ' . strtoupper($status);
            ?>
        </div>

    </div>

    <div class="card-actions">

        <img
            class="img-preview"
            src="../uploads/<?= $d['foto']; ?>"
            onerror="this.src='../uploads/default.png'"
            alt="Foto"
        >

        <div class="action-buttons">

            <a class="btn-detail"
               href="detail_aspirasi.php?id=<?= $d['id']; ?>">
                <i class="fas fa-eye"></i>
                Detail
            </a>

            <?php if ($mode == 'saya' && ($status == 'menunggu' || $status == 'pengecekan')): ?>

                <a class="btn-edit"
                   href="edit_aspirasi.php?id=<?= $d['id']; ?>">
                    <i class="fas fa-edit"></i>
                    Edit
                </a>

                <a class="btn-delete"
                   href="hapus_aspirasi.php?id=<?= $d['id']; ?>"
                   onclick="return confirm('Yakin ingin menghapus aspirasi ini?')">

                    <i class="fas fa-trash"></i>
                    Hapus
                </a>

            <?php endif; ?>

        </div>
    </div>
</div>

<?php
    endwhile;

else:
?>

<div class="empty-state">
    <i class="fas fa-inbox"></i>
    <h4>Tidak ada data</h4>
    <p>Aspirasi tidak ditemukan</p>
</div>

<?php endif; ?>