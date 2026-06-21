<?php
include '../koneksi.php';
session_start();
date_default_timezone_set('Asia/Jakarta');

// Pastikan data dikirim
if (!isset($_POST['id_keluhan']) || !isset($_POST['balasan'])) {
    $_SESSION['error'] = "Data tidak lengkap!";
    header("Location: keluhan.php");
    exit;
}

$id_keluhan = (int)$_POST['id_keluhan'];
$balasan    = mysqli_real_escape_string($koneksi, $_POST['balasan']);
$tanggal    = date('Y-m-d H:i:s');

// Cek apakah data keluhan ada
$cek = mysqli_query($koneksi, "SELECT * FROM keluhan WHERE id_keluhan='$id_keluhan'");
if (mysqli_num_rows($cek) == 0) {
    $_SESSION['error'] = "Keluhan tidak ditemukan!";
    header("Location: keluhan.php");
    exit;
}

// Update balasan di database
$update = mysqli_query($koneksi, "
    UPDATE keluhan 
    SET 
        balasan = '$balasan', 
        tanggal_balasan = '$tanggal', 
        status = 'dibalas'
    WHERE id_keluhan = '$id_keluhan'
");

if ($update) {
    $_SESSION['success'] = "Balasan berhasil dikirim!";
} else {
    $_SESSION['error'] = "Gagal mengirim balasan: " . mysqli_error($koneksi);
}

header("Location: keluhan.php");
exit;
?>
