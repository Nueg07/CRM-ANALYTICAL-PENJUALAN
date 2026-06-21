<?php
include '../koneksi.php';
session_start();

// Pastikan admin sudah login
if (!isset($_SESSION['status']) || $_SESSION['status'] != "login") {
    header("location:../masuk.php?alert=belum_login");
    exit;
}

// Pastikan ada parameter ID invoice
if (!isset($_GET['id']) || empty($_GET['id'])) {
    echo "<script>alert('ID transaksi tidak ditemukan'); location='transaksi.php';</script>";
    exit;
}

$id_invoice = intval($_GET['id']);

// Ambil data invoice
$invoice = mysqli_query($koneksi, "SELECT * FROM invoice WHERE invoice_id='$id_invoice' LIMIT 1");
if (!$invoice || mysqli_num_rows($invoice) == 0) {
    echo "<script>alert('Data transaksi tidak ditemukan'); location='transaksi.php';</script>";
    exit;
}

$inv = mysqli_fetch_assoc($invoice);

// Pastikan statusnya bisa ditolak (misalnya masih menunggu konfirmasi)
if (!in_array($inv['invoice_status'], [1])) {
    echo "<script>alert('Transaksi ini tidak bisa ditolak pada status saat ini'); location='transaksi.php';</script>";
    exit;
}

// Ambil semua produk di invoice ini
$detail = mysqli_query($koneksi, "SELECT * FROM invoice_detail WHERE invoice_id='$id_invoice'");
if ($detail && mysqli_num_rows($detail) > 0) {
    while ($d = mysqli_fetch_assoc($detail)) {
        $produk_id = intval($d['produk_id']);
        $jumlah = intval($d['jumlah']);

        // Kembalikan stok produk
        mysqli_query($koneksi, "UPDATE produk SET produk_jumlah = produk_jumlah + $jumlah WHERE produk_id='$produk_id'");
    }
}

// Update status invoice jadi ditolak (2)
$update = mysqli_query($koneksi, "UPDATE invoice SET invoice_status='2' WHERE invoice_id='$id_invoice'");

if ($update) {
    echo "<script>alert('Pesanan berhasil ditolak!'); location='transaksi.php';</script>";
} else {
    echo "<script>alert('Gagal menolak pesanan.'); location='transaksi.php';</script>";
}
?>
