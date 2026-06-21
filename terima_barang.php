<?php
session_start();
include 'koneksi.php';

// cek login
if (!isset($_SESSION['customer'])) {
    header("Location: masuk.php?alert=login-dulu");
    exit;
}

$customer = $_SESSION['customer'];
$id_customer = (int)$customer['id'];

if (isset($_GET['id'])) {
    $id_invoice = (int)$_GET['id'];

    // cek invoice milik customer dan status masih dikirim
    $cek = mysqli_query($koneksi, "SELECT * FROM invoice WHERE invoice_id='$id_invoice' AND invoice_customer='$id_customer' AND invoice_status='4'");
    if ($cek && mysqli_num_rows($cek) > 0) {
        // update jadi selesai
        mysqli_query($koneksi, "UPDATE invoice SET invoice_status='5' WHERE invoice_id='$id_invoice'");
        $_SESSION['success'] = "Pesanan berhasil dikonfirmasi sebagai diterima.";
    } else {
        $_SESSION['error'] = "Data pesanan tidak valid atau sudah diproses sebelumnya.";
    }
}

header("Location: customer_pesanan.php");
exit;
