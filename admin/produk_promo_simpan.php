<?php
include '../koneksi.php';

// Ambil data dari form dan escape untuk keamanan
$produk_id    = mysqli_real_escape_string($koneksi, $_POST['produk_id']);
$nama         = mysqli_real_escape_string($koneksi, $_POST['nama_promo']);
$jenis        = mysqli_real_escape_string($koneksi, $_POST['jenis']);
$diskon       = (float)$_POST['diskon_persen'];
$mulai        = mysqli_real_escape_string($koneksi, $_POST['tanggal_mulai']);
$selesai      = mysqli_real_escape_string($koneksi, $_POST['tanggal_selesai']);
$customer_id  = isset($_POST['customer_id']) ? mysqli_real_escape_string($koneksi, $_POST['customer_id']) : null;

// Jika promo umum, customer_id harus NULL
if($jenis === 'umum'){
    $customer_id = null;
}

// Simpan promo ke database
$query = "INSERT INTO promo 
(produk_id, customer_id, nama_promo, jenis, diskon_persen, tanggal_mulai, tanggal_selesai, status)
VALUES 
('$produk_id', " . ($customer_id !== null ? "'$customer_id'" : "NULL") . ", '$nama', '$jenis', '$diskon', '$mulai', '$selesai', 'aktif')";

if(mysqli_query($koneksi, $query)){
    header("Location: produk.php?alert=promo_sukses");
    exit;
}else{
    die("Error: " . mysqli_error($koneksi));
}
?>
