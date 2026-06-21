<?php
if (session_status() === PHP_SESSION_NONE) session_start();
include 'koneksi.php';

// Cek login
if (!isset($_SESSION['customer'])) {
    header("location:masuk.php?alert=login-dulu");
    exit;
}

$customer = $_SESSION['customer'];
$id_customer = (int)$customer['id'];

// Ambil data dari form
$id_invoice = isset($_POST['id_invoice']) ? (int)$_POST['id_invoice'] : 0;
$id_produk  = isset($_POST['id_produk']) ? (int)$_POST['id_produk'] : 0;
$alasan     = isset($_POST['alasan']) ? mysqli_real_escape_string($koneksi, trim($_POST['alasan'])) : '';

// Validasi data
if ($id_invoice === 0 || $id_produk === 0 || empty($alasan)) {
    $_SESSION['error'] = "Data retur tidak lengkap.";
    header("location:customer_retur.php?id_invoice=$id_invoice&id_produk=$id_produk");
    exit;
}

// Pastikan invoice milik customer
$cek_invoice = mysqli_query($koneksi, "SELECT * FROM invoice 
                                       WHERE invoice_id='$id_invoice' 
                                       AND invoice_customer='$id_customer' LIMIT 1");

if (!$cek_invoice || mysqli_num_rows($cek_invoice) === 0) {
    $_SESSION['error'] = "Invoice tidak ditemukan atau bukan milik Anda.";
    header("location:customer_pesanan.php");
    exit;
}

// Upload foto (opsional)
$foto_name = '';
if (!empty($_FILES['foto']['name'])) {
    $ext = strtolower(pathinfo($_FILES['foto']['name'], PATHINFO_EXTENSION));
    $allowed = ['jpg', 'jpeg', 'png', 'webp'];

    if (!in_array($ext, $allowed)) {
        $_SESSION['error'] = "Format foto tidak diperbolehkan.";
        header("location:customer_retur.php?id_invoice=$id_invoice&id_produk=$id_produk");
        exit;
    }

    if ($_FILES['foto']['size'] > 2 * 1024 * 1024) { // Maks 2MB
        $_SESSION['error'] = "Ukuran file terlalu besar. Maksimal 2MB.";
        header("location:customer_retur.php?id_invoice=$id_invoice&id_produk=$id_produk");
        exit;
    }

    $upload_dir = "gambar/retur/";
    if (!is_dir($upload_dir)) {
        mkdir($upload_dir, 0777, true);
    }

    $foto_name = "retur_" . time() . "_" . rand(1000, 9999) . "." . $ext;
    $upload_path = $upload_dir . $foto_name;

    if (!move_uploaded_file($_FILES['foto']['tmp_name'], $upload_path)) {
        $_SESSION['error'] = "Gagal mengupload foto.";
        header("location:customer_retur.php?id_invoice=$id_invoice&id_produk=$id_produk");
        exit;
    }
}

// Simpan ke database (disesuaikan dengan struktur tabel)
$status = 'pending';
$query = "INSERT INTO retur (
              invoice_id,
              produk_id,
              customer_id,
              retur_tanggal,
              retur_status,
              retur_alasan,
              retur_bukti
          ) VALUES (
              '$id_invoice',
              '$id_produk',
              '$id_customer',
              NOW(),
              '$status',
              '$alasan',
              '$foto_name'
          )";

if (mysqli_query($koneksi, $query)) {
    $_SESSION['success'] = "Pengajuan retur berhasil dikirim. Silakan tunggu konfirmasi dari admin.";
} else {
    $_SESSION['error'] = "Gagal menyimpan data retur: " . mysqli_error($koneksi);
}

header("location:customer_pesanan.php");
exit;
?>
