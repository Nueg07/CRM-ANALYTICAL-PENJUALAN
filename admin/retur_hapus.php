<?php
session_start();
include '../koneksi.php';

$retur_id = isset($_POST['retur_id']) ? (int)$_POST['retur_id'] : 0;

if ($retur_id <= 0) {
    $_SESSION['error'] = "Data retur tidak ditemukan.";
    header("location:retur.php");
    exit;
}

// hapus data
$hapus = mysqli_query($koneksi, "DELETE FROM retur WHERE retur_id='$retur_id'");

if ($hapus) {
    $_SESSION['success'] = "Data retur berhasil dihapus.";
} else {
    $_SESSION['error'] = "Gagal menghapus retur: " . mysqli_error($koneksi);
}

header("location:retur.php");
exit;
