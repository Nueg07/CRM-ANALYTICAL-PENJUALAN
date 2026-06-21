<?php
include 'koneksi.php';

if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

$file = basename($_SERVER['PHP_SELF']);

// Proteksi halaman jika belum login
if (!isset($_SESSION['customer'])) {

    $lindungi = ['customer.php','customer_logout.php'];
    if (in_array($file, $lindungi)) {
        header("Location: index.php");
        exit;
    }

    if ($file == "checkout.php") {
        header("Location: masuk.php?alert=login-dulu");
        exit;
    }

} else {
    // Sudah login
    $lindungi = ['masuk.php','daftar.php'];
    if (in_array($file, $lindungi)) {
        header("Location: customer.php");
        exit;
    }
}

// Cek keranjang sebelum checkout
if ($file == "checkout.php") {
    if (!isset($_SESSION['keranjang']) || count($_SESSION['keranjang']) == 0) {
        header("Location: keranjang.php?alert=keranjang_kosong");
        exit;
    }
}

$jumlah_isi_keranjang = isset($_SESSION['keranjang']) ? count($_SESSION['keranjang']) : 0;

/* ==========================================================
   CEK PESAN BARU DARI ADMIN (KELUHAN)
   ========================================================== */
$jumlah_pesan_baru = 0;
if (isset($_SESSION['customer'])) {
    $id_customer = $_SESSION['customer']['id'];

    // Hitung pesan baru yang sudah dibalas admin tapi belum dibaca customer
    $cek_pesan = mysqli_query($koneksi, "
        SELECT COUNT(*) AS total 
        FROM keluhan 
        WHERE id_customer = '$id_customer'
        AND balasan IS NOT NULL 
        AND balasan != ''
        AND status = 'ditanggapi'
    ");

    if ($cek_pesan && mysqli_num_rows($cek_pesan) > 0) {
        $hasil = mysqli_fetch_assoc($cek_pesan);
        $jumlah_pesan_baru = $hasil ? (int)$hasil['total'] : 0;
    }
}
?>
<!DOCTYPE html>
<html lang="id">
<head>
  <meta charset="utf-8">
  <title>TOKO ELEKTRONIK</title>
  <meta name="viewport" content="width=device-width, initial-scale=1">

  <!-- Bootstrap -->
  <link rel="stylesheet" href="baru/vendor/bootstrap/css/bootstrap.min.css">
  <!-- Custom Style -->
  <link rel="stylesheet" href="baru/css/style.default.css" id="theme-stylesheet">
  <!-- FontAwesome -->
  <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.0/css/all.min.css">

  <style>
    /* Navbar Styling */
    .navbar {
      transition: all 0.3s ease-in-out;
    }
    .navbar-brand span {
      font-weight: 800;
      letter-spacing: 1px;
    }
    .navbar-nav .nav-link {
      font-weight: 500;
      padding: 10px 18px;
      transition: 0.3s;
    }
    .navbar-nav .nav-link:hover {
      color: #ffc107 !important;
      transform: translateY(-2px);
    }
    .cart-badge {
      background: #ffc107;
      color: #000;
      font-size: 12px;
      font-weight: bold;
      padding: 3px 7px;
      border-radius: 20px;
      position: relative;
      top: -8px;
      left: -5px;
    }
    .nav-item .btn-login {
      border: 1px solid #ffc107;
      border-radius: 20px;
      padding: 6px 15px;
      font-weight: 500;
      transition: 0.3s;
    }
    .nav-item .btn-login:hover {
      background: #ffc107;
      color: #000 !important;
    }
    /* Notifikasi chat badge */
    .notif-badge {
      background: #dc3545;
      color: #fff;
      font-size: 11px;
      font-weight: bold;
      border-radius: 50%;
      padding: 3px 7px;
      margin-left: 5px;
      position: relative;
      top: -2px;
    }
    /* Notif di samping nama user */
    .notif-name {
      background: #dc3545;
      color: #fff;
      font-size: 10px;
      font-weight: bold;
      border-radius: 50%;
      padding: 3px 6px;
      margin-left: 4px;
      position: relative;
      top: -3px;
    }
  </style>
</head>
<body>
  <div class="page-holder">
    <!-- HEADER -->
    <header class="header bg-white shadow-sm">
      <div class="container px-0 px-lg-3">
        <nav class="navbar navbar-expand-lg navbar-light py-3 px-lg-0">
          <a class="navbar-brand" href="index.php">
            <span class="text-dark">TOKO ELEKTRONIK</span>
          </a>
          <button class="navbar-toggler" type="button" data-toggle="collapse" data-target="#navbarSupportedContent">
            <span class="navbar-toggler-icon"></span>
          </button>
          <div class="collapse navbar-collapse" id="navbarSupportedContent">
            
            <!-- MENU KIRI -->
            <ul class="navbar-nav mr-auto ml-lg-4">
              <li class="nav-item"><a class="nav-link <?php echo ($file=='index.php'?'active':''); ?>" href="index.php">Home</a></li>
              <li class="nav-item"><a class="nav-link <?php echo ($file=='shop.php'?'active':''); ?>" href="shop.php">Shop</a></li>
              <li class="nav-item"><a class="nav-link <?php echo ($file=='diskon.php'?'active':''); ?>" href="diskon.php">Diskon</a></li>
              <li class="nav-item"><a class="nav-link" href="login.php">Admin</a></li>
            </ul>

            <!-- MENU KANAN -->
            <ul class="navbar-nav ml-auto align-items-center">
              <!-- CART -->
              <li class="nav-item mr-3">
                <a class="nav-link position-relative" href="keranjang.php">
                  <i class="fas fa-shopping-cart fa-lg text-dark"></i>
                  <?php if($jumlah_isi_keranjang > 0){ ?>
                    <span class="cart-badge"><?php echo $jumlah_isi_keranjang; ?></span>
                  <?php } ?>
                </a>
              </li>

              <!-- LOGIN / PROFIL -->
              <?php if(isset($_SESSION['customer'])){ ?>
                <li class="nav-item dropdown">
                  <a class="nav-link dropdown-toggle text-dark" href="#" id="userDropdown" role="button" data-toggle="dropdown">
                    <i class="fas fa-user-circle mr-1"></i> 
                    <?php echo $_SESSION['customer']['nama']; ?>
                    <?php if($jumlah_pesan_baru > 0){ ?>
                      <span class="notif-name"><?php echo $jumlah_pesan_baru; ?></span>
                    <?php } ?>
                  </a>
                  <div class="dropdown-menu dropdown-menu-right">
                    <a class="dropdown-item" href="customer.php"><i class="fas fa-user mr-2"></i> Dashboard</a>
                    <a class="dropdown-item" href="customer_pesanan.php"><i class="fas fa-list mr-2"></i> Pesanan Saya</a>

                    <!-- CHAT / KELUHAN -->
                    <a class="dropdown-item" href="customer_keluhan.php">
                      <i class="fas fa-comments mr-2"></i> Chat 
                      <?php if($jumlah_pesan_baru > 0){ ?>
                        <span class="notif-badge"><?php echo $jumlah_pesan_baru; ?></span>
                      <?php } ?>
                    </a>

                    <a class="dropdown-item" href="customer_password.php"><i class="fas fa-lock mr-2"></i> Ganti Password</a>
                    <div class="dropdown-divider"></div>
                    <a class="dropdown-item text-danger" href="customer_logout.php"><i class="fas fa-sign-out-alt mr-2"></i> Keluar</a>
                  </div>
                </li>
              <?php } else { ?>
                <li class="nav-item">
                  <a class="nav-link btn-login text-dark" href="masuk.php">
                    <i class="fas fa-sign-in-alt mr-1"></i> Login
                  </a>
                </li>
              <?php } ?>
            </ul>
          </div>
        </nav>
      </div>
    </header>
