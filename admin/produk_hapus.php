<?php
include '../koneksi.php';

$id = $_GET['id'] ?? 0;

// cek data produk
$cek = mysqli_query($koneksi, "SELECT * FROM produk WHERE produk_id='$id'");
if(mysqli_num_rows($cek) == 0){
    echo "<script>alert('Produk tidak ditemukan!'); window.location='produk.php';</script>";
    exit;
}

$data = mysqli_fetch_assoc($cek);

// hapus data retur terkait produk ini
mysqli_query($koneksi, "DELETE FROM retur WHERE produk_id='$id'");

// hapus file gambar jika ada
for($i=1;$i<=3;$i++){
    $foto = $data["produk_foto$i"];
    if($foto != "" && file_exists("../gambar/produk/".$foto)){
        unlink("../gambar/produk/".$foto);
    }
}

// hapus produk
mysqli_query($koneksi, "DELETE FROM produk WHERE produk_id='$id'");

header("location:produk.php?alert=hapus");
