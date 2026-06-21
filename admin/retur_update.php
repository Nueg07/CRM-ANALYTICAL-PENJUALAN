<?php
session_start();
include '../koneksi.php';

$retur_id = isset($_POST['retur_id']) ? (int)$_POST['retur_id'] : 0;
$status = isset($_POST['status']) ? trim($_POST['status']) : 'pending';

// Validasi status enum
$allowed = ['pending', 'disetujui', 'ditolak'];
if (!in_array($status, $allowed)) {
    $_SESSION['error'] = "Status tidak valid!";
    header("location:retur.php");
    exit;
}

// Validasi ID retur
if ($retur_id <= 0) {
    $_SESSION['error'] = "Data retur tidak ditemukan.";
    header("location:retur.php");
    exit;
}

// Update data retur
$update = mysqli_query($koneksi, "UPDATE retur SET retur_status='$status' WHERE retur_id='$retur_id'");

if ($update) {
    $_SESSION['success'] = "Status retur berhasil diperbarui.";
} else {
    $_SESSION['error'] = "Gagal memperbarui status retur: " . mysqli_error($koneksi);
}

header("location:retur.php");
exit;
