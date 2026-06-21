<?php
// Mulai session jika belum dimulai
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

// Hapus semua session (logout user)
session_unset();
session_destroy();

// Arahkan ke halaman utama (index toko)
header("Location: ../index.php?alert=logout");
exit;
?>
