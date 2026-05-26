<?php
session_start();
include '../config/koneksi.php';

/** @var mysqli $conn */

// Aktifkan error reporting untuk debugging
mysqli_report(MYSQLI_REPORT_ERROR | MYSQLI_REPORT_STRICT);

if (!isset($_SESSION['user']) || $_SESSION['user']['role'] != 'admin') {
    header("Location: ../auth/login.php");
    exit;
}

$id = isset($_GET['id']) ? (int)$_GET['id'] : 0;

if ($id <= 0) {
    header("Location: siswa.php?error=invalid_id");
    exit;
}

// Cek apakah user dengan id ini ada dan role-nya siswa
$check = mysqli_prepare($conn, "SELECT id, role FROM users WHERE id = ?");
mysqli_stmt_bind_param($check, "i", $id);
mysqli_stmt_execute($check);
$result = mysqli_stmt_get_result($check);
$user = mysqli_fetch_assoc($result);
mysqli_stmt_close($check);

if (!$user) {
    header("Location: siswa.php?error=user_not_found");
    exit;
}

if ($user['role'] !== 'siswa') {
    header("Location: siswa.php?error=not_siswa");
    exit;
}

// Mulai transaksi agar konsisten
mysqli_begin_transaction($conn);

try {
    // 1. Ambil semua id aspirasi milik siswa
    $stmt = mysqli_prepare($conn, "SELECT id FROM aspirations WHERE user_id = ?");
    mysqli_stmt_bind_param($stmt, "i", $id);
    mysqli_stmt_execute($stmt);
    $res = mysqli_stmt_get_result($stmt);
    $aspiration_ids = [];
    while ($row = mysqli_fetch_assoc($res)) {
        $aspiration_ids[] = $row['id'];
    }
    mysqli_stmt_close($stmt);

    if (!empty($aspiration_ids)) {
        $placeholders = implode(',', array_fill(0, count($aspiration_ids), '?'));
        $types = str_repeat('i', count($aspiration_ids));

        // 2. Hapus progress (anak dari aspirations)
        $delProgress = mysqli_prepare($conn, "DELETE FROM progress WHERE aspiration_id IN ($placeholders)");
        mysqli_stmt_bind_param($delProgress, $types, ...$aspiration_ids);
        mysqli_stmt_execute($delProgress);
        mysqli_stmt_close($delProgress);

        // 3. Hapus feedbacks (anak dari aspirations)
        $delFeedback = mysqli_prepare($conn, "DELETE FROM feedbacks WHERE aspiration_id IN ($placeholders)");
        mysqli_stmt_bind_param($delFeedback, $types, ...$aspiration_ids);
        mysqli_stmt_execute($delFeedback);
        mysqli_stmt_close($delFeedback);
    }

    // 4. Hapus aspirasi siswa
    $delAsp = mysqli_prepare($conn, "DELETE FROM aspirations WHERE user_id = ?");
    mysqli_stmt_bind_param($delAsp, "i", $id);
    mysqli_stmt_execute($delAsp);
    mysqli_stmt_close($delAsp);

    // 5. Hapus siswa
    $delUser = mysqli_prepare($conn, "DELETE FROM users WHERE id = ? AND role = 'siswa'");
    mysqli_stmt_bind_param($delUser, "i", $id);
    mysqli_stmt_execute($delUser);
    $affected = mysqli_stmt_affected_rows($delUser);
    mysqli_stmt_close($delUser);

    if ($affected === 0) {
        throw new Exception("Tidak ada baris yang terhapus, mungkin siswa sudah tidak ada.");
    }

    mysqli_commit($conn);
    header("Location: siswa.php?success=deleted");
    exit;

} catch (Exception $e) {
    mysqli_rollback($conn);
    die("Gagal menghapus: " . $e->getMessage());
}
?>