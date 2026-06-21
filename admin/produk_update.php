<?php
include '../koneksi.php';

$id           = $_POST['id'];
$nama         = $_POST['nama'];
$kategori     = $_POST['kategori'];
$harga        = $_POST['harga'];
$harga_diskon = $_POST['harga_diskon'];
$keterangan   = $_POST['keterangan'];
$berat        = $_POST['berat'];
$jumlah       = $_POST['jumlah'];
$id_promo     = $_POST['id_promo'];

// Upload ulang foto (jika diubah)
for ($i = 1; $i <= 3; $i++) {
    if (!empty($_FILES['foto' . $i]['name'])) {
        $rand = rand();
        $filename = $_FILES['foto' . $i]['name'];
        $foto = $rand . "_" . $filename;
        move_uploaded_file($_FILES['foto' . $i]['tmp_name'], '../gambar/produk/' . $foto);
        mysqli_query($koneksi, "UPDATE produk SET produk_foto$i='$foto' WHERE produk_id='$id'");
    }
}

// Update data produk
mysqli_query($koneksi, "UPDATE produk SET 
    produk_nama='$nama', 
    produk_kategori='$kategori', 
    produk_harga='$harga', 
    produk_harga_diskon='$harga_diskon', 
    produk_keterangan='$keterangan',
    produk_berat='$berat', 
    produk_jumlah='$jumlah'
WHERE produk_id='$id'");

// Jika promo baru dipilih
if (!empty($id_promo)) {
    $promo = mysqli_query($koneksi, "SELECT * FROM promo WHERE id_promo='$id_promo'");
    if (mysqli_num_rows($promo) > 0) {
        $p = mysqli_fetch_assoc($promo);

        // Hitung harga diskon baru
        $harga_diskon_terhitung = $harga - ($harga * ($p['diskon_persen'] / 100));
        mysqli_query($koneksi, "UPDATE produk SET produk_harga_diskon='$harga_diskon_terhitung' WHERE produk_id='$id'");

        // Tambahkan promo baru untuk produk ini
        mysqli_query($koneksi, "INSERT INTO promo 
        (produk_id, customer_id, nama_promo, diskon_persen, tanggal_mulai, tanggal_selesai, jenis, status)
        VALUES 
        ('$id', '{$p['customer_id']}', '{$p['nama_promo']}', '{$p['diskon_persen']}', 
        '{$p['tanggal_mulai']}', '{$p['tanggal_selesai']}', '{$p['jenis']}', 'aktif')");
    }
}

header("location:produk.php?alert=update_sukses");
?>
