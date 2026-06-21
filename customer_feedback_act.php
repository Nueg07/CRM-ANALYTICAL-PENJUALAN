<?php
include 'koneksi.php';
if (session_status() === PHP_SESSION_NONE) session_start();

// Pastikan customer login
if (!isset($_SESSION['customer'])) {
    $_SESSION['error'] = "Silakan login dulu untuk mengirim feedback!";
    header("location:masuk.php");
    exit;
}

$id_customer = (int)$_SESSION['customer']['id'];

// Ambil data POST
$id_produk  = isset($_POST['id_produk']) ? (int)$_POST['id_produk'] : 0;
$id_invoice = isset($_POST['id_invoice']) ? (int)$_POST['id_invoice'] : 0;
$rating     = isset($_POST['rating']) ? (int)$_POST['rating'] : 0;
$komentar   = isset($_POST['komentar']) ? trim($_POST['komentar']) : "";

// Validasi input
if ($id_produk <= 0 || $id_invoice <= 0 || $rating < 1 || $rating > 5 || empty($komentar)) {
    $_SESSION['error'] = "Data feedback tidak valid!";
    header("location: produk_detail.php?id=".$id_produk);
    exit;
}

// Cek invoice milik customer dan sudah selesai (invoice_status = 5)
$cek_invoice = mysqli_query($koneksi, "
    SELECT i.invoice_id
    FROM invoice i
    JOIN invoice_detail d ON i.invoice_id=d.invoice_id
    WHERE i.invoice_id='$id_invoice'
      AND i.invoice_customer='$id_customer'
      AND i.invoice_status=5
      AND d.produk_id='$id_produk'
    LIMIT 1
");
if (mysqli_num_rows($cek_invoice) == 0) {
    $_SESSION['error'] = "Anda belum membeli produk ini atau invoice belum selesai!";
    header("location: produk_detail.php?id=".$id_produk);
    exit;
}

// Cek apakah feedback sudah dikirim
$cek_fb = mysqli_query($koneksi, "
    SELECT * FROM feedback 
    WHERE id_produk='$id_produk' 
      AND id_customer='$id_customer'
");
if (mysqli_num_rows($cek_fb) > 0) {
    $_SESSION['error'] = "Feedback untuk produk ini sudah dikirim!";
    header("location: produk_detail.php?id=".$id_produk);
    exit;
}

// Simpan feedback
$stmt = $koneksi->prepare("
    INSERT INTO feedback (id_produk, id_customer, rating, komentar, tanggal)
    VALUES (?, ?, ?, ?, NOW())
");
$stmt->bind_param("iiis", $id_produk, $id_customer, $rating, $komentar);

if ($stmt->execute()) {
    $_SESSION['success'] = "Feedback berhasil dikirim!";
} else {
    $_SESSION['error'] = "Gagal menyimpan feedback: " . $stmt->error;
}

// Kembali ke halaman produk
header("location: produk_detail.php?id=".$id_produk);
exit;
?>
