<?php 
include '../koneksi.php';

$id = $_GET['id'];

// Hapus invoice (otomatis invoice_produk ikut terhapus karena ON DELETE CASCADE)
mysqli_query($koneksi, "DELETE FROM invoice WHERE invoice_id='$id'");

header("location:transaksi.php");
exit;
?>
