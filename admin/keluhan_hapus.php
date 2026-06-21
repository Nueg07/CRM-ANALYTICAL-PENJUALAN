<?php
include '../koneksi.php';

// ✅ Pastikan session aktif
if (session_status() === PHP_SESSION_NONE) {
  session_start();
}

// ✅ Cek apakah sudah login
if (!isset($_SESSION['status']) || $_SESSION['status'] != "login") {
  header("location:../login.php?alert=belum_login");
  exit;
}

// ✅ Hanya izinkan admin atau owner
if (!in_array($_SESSION['level'], ['admin', 'owner'])) {
  header("location:index.php?alert=tidak_berhak");
  exit;
}

// ✅ Pastikan ID dikirim lewat POST
if (isset($_POST['id_keluhan']) && !empty($_POST['id_keluhan'])) {
  $id = intval($_POST['id_keluhan']);

  // Cek apakah data ada
  $cek = mysqli_query($koneksi, "SELECT * FROM keluhan WHERE id_keluhan='$id'");
  if (mysqli_num_rows($cek) > 0) {

    $hapus = mysqli_query($koneksi, "DELETE FROM keluhan WHERE id_keluhan='$id'");

    if ($hapus) {
      header("location:keluhan.php?alert=hapus_sukses");
      exit;
    } else {
      die("❌ Gagal menghapus data: " . mysqli_error($koneksi));
    }

  } else {
    header("location:keluhan.php?alert=data_tidak_ditemukan");
    exit;
  }
} else {
  header("location:keluhan.php?alert=tidak_ada_id");
  exit;
}
?>
