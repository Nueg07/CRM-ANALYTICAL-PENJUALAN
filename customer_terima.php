<?php
session_start();
include 'koneksi.php';

if(!isset($_SESSION['customer_id'])){
    header("Location: login.php");
    exit;
}

$id_invoice = intval($_GET['id']);
$id_customer = $_SESSION['customer_id'];

// cek apakah invoice benar milik customer ini
$cek = mysqli_query($koneksi, "SELECT * FROM invoice WHERE invoice_id='$id_invoice' AND invoice_customer='$id_customer'");
if(mysqli_num_rows($cek) > 0){
    // update status jadi selesai (5)
    mysqli_query($koneksi, "UPDATE invoice SET invoice_status=5 WHERE invoice_id='$id_invoice'");
    $_SESSION['success'] = "Pesanan sudah dikonfirmasi diterima. Terima kasih!";
} else {
    $_SESSION['error'] = "Pesanan tidak ditemukan atau bukan milik Anda.";
}

header("Location: customer_pesanan.php");
exit;
