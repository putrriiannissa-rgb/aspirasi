<?php
include 'config/koneksi.php';

$search = $_GET['search'] ?? '';
$kategori = $_GET['kategori'] ?? '';
$lokasi = $_GET['lokasi'] ?? '';
$tanggal = $_GET['tanggal'] ?? '';
$status = $_GET['status'] ?? '';

$where = "WHERE 1=1";

if($search != ''){
    $where .= " AND a.isi LIKE '%$search%'";
}

if($kategori != ''){
    $where .= " AND a.kategori='$kategori'";
}

if($lokasi != ''){
    $where .= " AND a.lokasi='$lokasi'";
}

if($tanggal != ''){
    $where .= " AND DATE(a.created_at)='$tanggal'";
}

if($status != ''){
    $where .= " AND COALESCE(
        (
            SELECT p.status
            FROM progress p
            WHERE p.aspiration_id = a.id
            ORDER BY p.id DESC
            LIMIT 1
        ),
        a.status
    ) = '$status'";
}

$data = mysqli_query($conn, "
SELECT a.*,
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
$where
ORDER BY a.id DESC
");

$no = 1;
?>

<div class="table-container">
    <div class="table-wrapper">
        <table id="aspirasiTable">
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

            <?php if(mysqli_num_rows($data) > 0): ?>

                <?php while($d = mysqli_fetch_assoc($data)): 
                    $status = $d['status_terbaru'];
                ?>

                <tr>
                    <td><?= $no++ ?></td>

                    <td>
                        <?= date('d/m/Y', strtotime($d['created_at'])) ?>
                    </td>

                    <td>
                        <?= htmlspecialchars(substr($d['isi'],0,100)) ?>
                        <?php if(strlen($d['isi']) > 100): ?>...<?php endif; ?>
                    </td>

                    <td><?= ucfirst($d['kategori']) ?></td>

                    <td><?= ucfirst($d['lokasi']) ?></td>

                    <td>
                        <span class="badge <?= $status ?>">
                            <?php
                            $icon = '';

                            if($status == 'menunggu') $icon = '⏳';
                            elseif($status == 'pengecekan') $icon = '🔍';
                            elseif($status == 'proses') $icon = '⚙️';
                            elseif($status == 'selesai') $icon = '✅';

                            echo $icon . ' ' . strtoupper($status);
                            ?>
                        </span>
                    </td>

                    <td>
                        <a href="detail_aspirasi_guest.php?id=<?= $d['id']; ?>" class="btn-detail">
                            Detail
                        </a>
                    </td>
                </tr>

                <?php endwhile; ?>

            <?php else: ?>

                <tr>
                    <td colspan="7" style="text-align:center;padding:20px;">
                        Tidak ada data aspirasi
                    </td>
                </tr>

            <?php endif; ?>

            </tbody>
        </table>
    </div>
</div>