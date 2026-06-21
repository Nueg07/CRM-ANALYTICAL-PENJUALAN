<?php
include 'koneksi.php';
session_start();

// Cek login
if (!isset($_SESSION['customer'])) {
    header("location:masuk.php?alert=login-dulu");
    exit;
}

// Ambil data customer dari session
$customer = $_SESSION['customer'];
$id_customer = (int)$customer['id'];  // Pastikan di tabel pakai kolom 'id' atau 'customer_id'

// Data dari form
$id_invoice = (int)$_POST['id'];
$metode     = mysqli_real_escape_string($koneksi, $_POST['metode']);
$filename   = $_FILES['bukti']['name'];
$tmp_name   = $_FILES['bukti']['tmp_name'];

// Validasi invoice agar benar-benar milik customer
$cek = mysqli_query($koneksi, "SELECT * FROM invoice WHERE invoice_id='$id_invoice' AND invoice_customer='$id_customer' LIMIT 1");
if (mysqli_num_rows($cek) == 0) {
    echo "<script>alert('Invoice tidak ditemukan atau bukan milik Anda.');location='customer.php';</script>";
    exit;
}

if ($filename != "") {
    $ext = pathinfo($filename, PATHINFO_EXTENSION);
    $newname = "bukti_" . time() . "." . $ext;

    // Folder tujuan
    $folder = "gambar/bukti_pembayaran/";
    if (!file_exists($folder)) {
        mkdir($folder, 0777, true);
    }

    $path = $folder . $newname;

    if (move_uploaded_file($tmp_name, $path)) {
        // Update invoice dengan bukti & status menunggu konfirmasi
        $update = mysqli_query($koneksi, "UPDATE invoice 
            SET invoice_bukti='$newname',
                invoice_metode='$metode',
                invoice_status='1'
            WHERE invoice_id='$id_invoice' AND invoice_customer='$id_customer'");
    } else {
        echo "<script>alert('Upload gagal!');location='customer_pembayaran.php?id=$id_invoice';</script>";
        exit;
    }
} else {
    // Jika tidak upload bukti (contoh COD), hanya update metode & status
    $update = mysqli_query($koneksi, "UPDATE invoice 
        SET invoice_metode='$metode',
            invoice_status='1'
        WHERE invoice_id='$id_invoice' AND invoice_customer='$id_customer'");
}

echo "<script>alert('Pembayaran berhasil diupload, menunggu konfirmasi admin');location='customer_pesanan.php';</script>";
exit;
?>
