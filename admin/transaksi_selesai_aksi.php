<?php
include '../koneksi.php';
session_start();

// Pastikan user admin/owner
if (!isset($_SESSION['level']) || ($_SESSION['level'] != 'admin' && $_SESSION['level'] != 'owner')) {
    header("location:../login.php?alert=tidak_izin");
    exit;
}

if (isset($_GET['id'])) {
    $invoice_id = intval($_GET['id']);

    // Cek invoice ada atau tidak
    $cek = mysqli_query($koneksi, "SELECT * FROM invoice WHERE invoice_id='$invoice_id'");
    if (mysqli_num_rows($cek) > 0) {

        // Update status ke 5 (selesai)
        mysqli_query($koneksi, "UPDATE invoice SET invoice_status = 5 WHERE invoice_id='$invoice_id'");

        // Kurangi stok produk berdasarkan invoice_detail
        $detail = mysqli_query($koneksi, "SELECT produk_id, jumlah FROM invoice_detail WHERE invoice_id='$invoice_id'");
        while ($d = mysqli_fetch_assoc($detail)) {
            $produk_id = $d['produk_id'];
            $jumlah = $d['jumlah'];
            mysqli_query($koneksi, "UPDATE produk SET produk_stok = GREATEST(produk_stok - $jumlah, 0) WHERE produk_id = '$produk_id'");
        }

        header("location:transaksi.php?alert=selesai");
        exit;
    } else {
        header("location:transaksi.php?alert=tidak_ditemukan");
        exit;
    }
} else {
    header("location:transaksi.php?alert=error");
    exit;
}
?>
