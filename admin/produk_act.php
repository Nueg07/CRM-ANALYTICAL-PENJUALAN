<?php
include '../koneksi.php';

// Ambil data dari form
$nama         = $_POST['nama'];
$kategori     = $_POST['kategori'];
$harga        = $_POST['harga'];
$keterangan   = $_POST['keterangan'];
$berat        = $_POST['berat'];
$jumlah       = $_POST['jumlah'];
$id_promo     = $_POST['id_promo'];

// Upload foto produk
$foto = array();
for ($i = 1; $i <= 3; $i++) {
    $foto[$i] = "";
    if (!empty($_FILES['foto' . $i]['name'])) {
        $rand = rand();
        $filename = $_FILES['foto' . $i]['name'];
        $foto[$i] = $rand . "_" . $filename;
        move_uploaded_file($_FILES['foto' . $i]['tmp_name'], '../gambar/produk/' . $foto[$i]);
    }
}

// Cek apakah produk memiliki promo
$harga_diskon = 0;
if (!empty($id_promo)) {
    $cekPromo = mysqli_query($koneksi, "SELECT * FROM promo WHERE id_promo='$id_promo' AND status='aktif'");
    if (mysqli_num_rows($cekPromo) > 0) {
        $p = mysqli_fetch_assoc($cekPromo);
        $diskon = $p['diskon_persen'];
        $harga_diskon = $harga - ($harga * ($diskon / 100));
    }
}

// Simpan ke tabel produk
mysqli_query($koneksi, "
    INSERT INTO produk (
        produk_nama,
        produk_kategori,
        id_promo,
        produk_harga,
        produk_harga_diskon,
        produk_keterangan,
        produk_berat,
        produk_jumlah,
        produk_foto1,
        produk_foto2,
        produk_foto3
    ) VALUES (
        '$nama',
        '$kategori',
        '$id_promo',
        '$harga',
        '$harga_diskon',
        '$keterangan',
        '$berat',
        '$jumlah',
        '{$foto[1]}',
        '{$foto[2]}',
        '{$foto[3]}'
    )
");

header("location:produk.php?alert=sukses");
?>
