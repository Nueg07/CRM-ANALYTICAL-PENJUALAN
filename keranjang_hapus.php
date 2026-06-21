<?php 
include 'koneksi.php';
session_start();

// Pastikan parameter ada agar tidak undefined
$id_produk = isset($_GET['id']) ? $_GET['id'] : 0;
$redirect  = isset($_GET['redirect']) ? $_GET['redirect'] : "";

// Hapus produk dari keranjang (jika ada)
if (isset($_SESSION['keranjang']) && is_array($_SESSION['keranjang'])) {
    foreach ($_SESSION['keranjang'] as $key => $item) {
        if ($item['produk'] == $id_produk) {
            unset($_SESSION['keranjang'][$key]);
        }
    }

    // Reindex array agar tidak ada index lompat
    $_SESSION['keranjang'] = array_values($_SESSION['keranjang']);
}

// Tentukan halaman redirect dengan aman
switch ($redirect) {
    case "index":
        $r = "index.php";
        break;
    case "detail":
        $r = "produk_detail.php?id=" . $id_produk;
        break;
    case "keranjang":
        $r = "keranjang.php";
        break;
    default:
        $r = "keranjang.php"; // fallback default
        break;
}

// Jika ingin debugging, bisa aktifkan ini:
// print_r($_SESSION['keranjang']); exit;

// Redirect ke halaman tujuan
header("Location: " . $r);
exit;
?>
