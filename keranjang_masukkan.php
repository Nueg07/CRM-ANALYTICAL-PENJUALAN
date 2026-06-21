<?php
session_start();
include 'koneksi.php';

$id_produk = isset($_GET['id']) ? (int)$_GET['id'] : 0;
$jumlah = isset($_GET['jumlah']) ? max(1,(int)$_GET['jumlah']) : 1;

// Pastikan ada parameter redirect dan aman
$redirect = isset($_GET['redirect']) ? $_GET['redirect'] : 'index.php';
$redirect = basename($redirect); // aman, hilangkan path
if (!str_ends_with($redirect, '.php')) $redirect .= '.php';

// Ambil data produk
$produk_q = mysqli_query($koneksi, "SELECT * FROM produk WHERE produk_id='$id_produk'");
$produk = mysqli_fetch_assoc($produk_q);

if (!$produk) {
    header("Location: $redirect?alert=produk_tidak_ada");
    exit;
}

$id_customer = $_SESSION['customer']['id'] ?? 0;

// Hitung promo
$harga_asli = $produk['produk_harga'];
$harga_diskon = $harga_asli;
$promoAktif = null;

if ($id_customer > 0) {
    $promo_q = mysqli_query($koneksi, "
        SELECT * FROM promo
        WHERE produk_id='$id_produk'
          AND status='aktif'
          AND CURDATE() BETWEEN tanggal_mulai AND tanggal_selesai
          AND (jenis='umum' OR (jenis='personal' AND customer_id='$id_customer'))
        ORDER BY jenis='personal' DESC, diskon_persen DESC
        LIMIT 1
    ");
} else {
    $promo_q = mysqli_query($koneksi, "
        SELECT * FROM promo
        WHERE produk_id='$id_produk'
          AND status='aktif'
          AND CURDATE() BETWEEN tanggal_mulai AND tanggal_selesai
          AND jenis='umum'
        ORDER BY diskon_persen DESC
        LIMIT 1
    ");
}

if ($promo_q && mysqli_num_rows($promo_q) > 0) {
    $promoAktif = mysqli_fetch_assoc($promo_q);
    if ($promoAktif['jenis'] === 'umum' || ($promoAktif['jenis']==='personal' && $promoAktif['customer_id']==$id_customer)) {
        $harga_diskon = $harga_asli - ($harga_asli * $promoAktif['diskon_persen']/100);
    } else {
        $promoAktif = null;
    }
}

// Cek stok
if ($produk['produk_jumlah'] < $jumlah) {
    header("Location: $redirect?alert=stok_kurang");
    exit;
}

// Inisialisasi keranjang
if (!isset($_SESSION['keranjang'])) $_SESSION['keranjang'] = [];

// Tambah / update keranjang
$found = false;
foreach ($_SESSION['keranjang'] as &$item) {
    if ($item['produk']==$id_produk) {
        $item['jumlah'] += $jumlah;
        $item['harga'] = $harga_diskon;
        $item['diskon'] = $promoAktif['diskon_persen'] ?? 0;
        $item['promo_jenis'] = $promoAktif['jenis'] ?? '';
        $found = true;
        break;
    }
}
if (!$found) {
    $_SESSION['keranjang'][] = [
        'produk'=>$id_produk,
        'jumlah'=>$jumlah,
        'harga'=>$harga_diskon,
        'diskon'=>$promoAktif['diskon_persen'] ?? 0,
        'promo_jenis'=>$promoAktif['jenis'] ?? ''
    ];
}

// Redirect sukses
header("Location: $redirect?alert=berhasil_ditambahkan");
exit;
