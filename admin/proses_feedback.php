<?php
session_start();
include '../config/koneksi.php';

/** @var mysqli $conn */

// Cek login admin
if (!isset($_SESSION['user']) || $_SESSION['user']['role'] != 'admin') {
    header("Location: ../auth/login.php");
    exit;
}

// Validasi data POST
if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    header("Location: dashboard.php");
    exit;
}

$aspiration_id = isset($_POST['aspiration_id']) ? (int)$_POST['aspiration_id'] : 0;
$admin_id = $_SESSION['user']['id'];
$isi_feedback = trim($_POST['feedback'] ?? '');

if ($aspiration_id <= 0 || empty($isi_feedback)) {
    header("Location: detail_aspirasi.php?id=$aspiration_id&error=Feedback tidak boleh kosong");
    exit;
}

// Proses upload foto jika ada
$foto = '';
if (isset($_FILES['foto']) && $_FILES['foto']['error'] === 0) {
    $allowed = ['jpg', 'jpeg', 'png', 'gif'];
    $ext = strtolower(pathinfo($_FILES['foto']['name'], PATHINFO_EXTENSION));
    if (in_array($ext, $allowed) && $_FILES['foto']['size'] <= 2 * 1024 * 1024) {
        $foto = time() . '_' . basename($_FILES['foto']['name']);
        move_uploaded_file($_FILES['foto']['tmp_name'], "../uploads/" . $foto);
    }
}

// Simpan feedback
if ($foto) {
    $query = "INSERT INTO feedbacks (aspiration_id, admin_id, isi_feedback, foto) VALUES ($aspiration_id, $admin_id, '" . mysqli_real_escape_string($conn, $isi_feedback) . "', '$foto')";
} else {
    $query = "INSERT INTO feedbacks (aspiration_id, admin_id, isi_feedback) VALUES ($aspiration_id, $admin_id, '" . mysqli_real_escape_string($conn, $isi_feedback) . "')";
}

if (mysqli_query($conn, $query)) {
    // Optional: update status aspirasi menjadi 'proses'
    mysqli_query($conn, "UPDATE aspirations SET status='proses' WHERE id=$aspiration_id");
    header("Location: detail_aspirasi.php?id=$aspiration_id&success=Feedback terkirim");
} else {
    header("Location: detail_aspirasi.php?id=$aspiration_id&error=Gagal menyimpan feedback");
}
exit;
?>