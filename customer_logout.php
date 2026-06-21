<?php
// logout.php
session_start();

// Hapus semua session agar tidak nyangkut di user lain
$_SESSION = [];

// Hapus cookie session jika ada
if (ini_get("session.use_cookies")) {
    $params = session_get_cookie_params();
    setcookie(session_name(), '', time() - 42000,
        $params["path"], $params["domain"],
        $params["secure"], $params["httponly"]
    );
}

// Hapus session keranjang & checkout
unset($_SESSION['keranjang']);
unset($_SESSION['checkout']);
unset($_SESSION['customer']);
unset($_SESSION['customer_id']);
unset($_SESSION['success']);

// Akhiri session
session_destroy();

// Arahkan kembali ke halaman login atau beranda
header("Location: masuk.php");
exit;
?>
