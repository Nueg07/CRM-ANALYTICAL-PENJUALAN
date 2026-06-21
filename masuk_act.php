<?php
session_start();
include 'koneksi.php';

// Tangkap data dari form
$email    = mysqli_real_escape_string($koneksi, $_POST['email']);
$password = md5($_POST['password']); // Sesuai database

// Cek customer berdasarkan email & password
$q = mysqli_query($koneksi, "SELECT * FROM customer WHERE customer_email='$email' AND customer_password='$password'");
$d = mysqli_fetch_assoc($q);

if ($d) {
    // Simpan semua data customer ke session
    $_SESSION['customer'] = [
        'id'      => $d['customer_id'],
        'nama'    => $d['customer_nama'],
        'email'   => $d['customer_email'],
        'hp'      => $d['customer_hp'],
        'alamat'  => $d['customer_alamat']
    ];

    header("Location: index.php");
    exit;
} else {
    echo "<script>alert('Email atau password salah!'); location='masuk.php';</script>";
    exit;
}
?>
