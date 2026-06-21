<?php
include '../koneksi.php';

// Ambil ID promo dari form
$id_promo = $_POST['id_promo'];

// Ambil data promo untuk tahu produk yang terlibat
$promo = mysqli_query($koneksi, "SELECT * FROM promo WHERE id_promo='$id_promo'");
if (mysqli_num_rows($promo) > 0) {
    $p = mysqli_fetch_assoc($promo);
    $produk_id = $p['produk_id'];

    // Ubah status promo jadi nonaktif
    mysqli_query($koneksi, "UPDATE promo SET status='nonaktif' WHERE id_promo='$id_promo'");

    // Kembalikan harga produk ke harga asli (tanpa diskon)
    // Jika kamu ingin menyimpan harga diskon di kolom produk_harga_diskon, ini akan direset ke 0
    mysqli_query($koneksi, "UPDATE produk SET produk_harga_diskon = 0 WHERE produk_id='$produk_id'");
}

header("Location: produk.php?alert=promo_nonaktif");
exit();
?>
