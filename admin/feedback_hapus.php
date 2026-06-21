<?php
include '../koneksi.php';
session_start();

// Pastikan hanya admin yang bisa hapus
if (!isset($_SESSION['admin_id'])) {
    header("Location: ../login.php?alert=belum_login");
    exit;
}

// Cek ID yang dikirim
if (isset($_GET['id'])) {
    $id = (int) $_GET['id'];

    // Cek apakah feedback ada
    $cek = mysqli_query($koneksi, "SELECT * FROM feedback WHERE id_feedback='$id'");
    if (mysqli_num_rows($cek) > 0) {
        mysqli_query($koneksi, "DELETE FROM feedback WHERE id_feedback='$id'");
        header("Location: feedback.php?alert=feedback_dihapus");
        exit;
    } else {
        header("Location: feedback.php?alert=feedback_tidak_ditemukan");
        exit;
    }
} else {
    header("Location: feedback.php");
    exit;
}
?>
