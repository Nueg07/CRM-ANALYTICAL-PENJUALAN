<?php
include '../koneksi.php';

$id = $_POST['id'];
$resi = $_POST['resi'];

// Simpan resi + ubah status ke 'Dikirim' (kode 4)
mysqli_query($koneksi, "UPDATE invoice SET invoice_resi='$resi', invoice_status='4' WHERE invoice_id='$id'");

header("location:transaksi.php?pesan=pesanan_dikirim");
?>
