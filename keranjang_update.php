<?php
session_start();
include 'koneksi.php';

// Pastikan ada data keranjang
if (!isset($_SESSION['keranjang']) || empty($_SESSION['keranjang'])) {
    echo "<script>alert('Keranjang kosong!'); window.location='keranjang.php';</script>";
    exit;
}

// Cek apakah form dikirim via POST
if ($_SERVER['REQUEST_METHOD'] === 'POST') {

    // Ambil data jumlah dari form
    $jumlahs = $_POST['jumlah'] ?? [];

    if (empty($jumlahs)) {
        echo "<script>alert('Tidak ada data untuk diperbarui!'); window.location='keranjang.php';</script>";
        exit;
    }

    // Loop tiap produk dari input jumlah
    foreach ($jumlahs as $produk_id => $jumlah) {
        $produk_id = (int)$produk_id;
        $jumlah    = (int)$jumlah;

        if ($jumlah <= 0) $jumlah = 1;

        // Ambil data stok dari database
        $cek = mysqli_query($koneksi, "SELECT produk_nama, produk_jumlah FROM produk WHERE produk_id='$produk_id' LIMIT 1");
        $p = mysqli_fetch_assoc($cek);

        if (!$p) continue; // jika produk tidak ditemukan

        $stok = (int)$p['produk_jumlah'];

        // Validasi stok
        if ($jumlah > $stok) {
            echo "<script>
                alert('Stok produk \"".addslashes($p['produk_nama'])."\" tidak mencukupi! Stok tersedia hanya $stok unit.');
                window.location='keranjang.php';
            </script>";
            exit;
        }

        // Update session sesuai produk_id
        foreach ($_SESSION['keranjang'] as $key => $item) {
            if ($item['produk'] == $produk_id) {
                $_SESSION['keranjang'][$key]['jumlah'] = $jumlah;
                break;
            }
        }
    }

    echo "<script>alert('Keranjang berhasil diperbarui!'); window.location='keranjang.php';</script>";
    exit;

} else {
    echo "<script>alert('Permintaan tidak valid!'); window.location='keranjang.php';</script>";
    exit;
}
?>
