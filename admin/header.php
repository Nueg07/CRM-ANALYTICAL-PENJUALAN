<?php 
include '../koneksi.php';

// Cek session
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}
if (!isset($_SESSION['status']) || $_SESSION['status'] != "login") {
    header("location:../login.php?alert=belum_login");
    exit;
}

// Ambil data admin login
$id_admin = $_SESSION['id'];
$profil_query = mysqli_query($koneksi, "SELECT * FROM admin WHERE admin_id='$id_admin'");
$profil = mysqli_fetch_assoc($profil_query);

/* ================================================================
   🔴 1. Cek Customer yang Sudah 5x Belanja tapi Belum Dapat Promo
================================================================ */
$cek_promo_query = mysqli_query($koneksi, "
    SELECT COUNT(*) AS jml
    FROM customer c
    LEFT JOIN (
        SELECT invoice_customer, COUNT(invoice_id) AS total
        FROM invoice
        WHERE invoice_status = 5
        GROUP BY invoice_customer
    ) AS i ON i.invoice_customer = c.customer_id
    WHERE i.total >= 5 
    AND c.customer_id NOT IN (
        SELECT DISTINCT id_customer 
        FROM keluhan 
        WHERE pesan LIKE '%promo%' OR balasan LIKE '%promo%'
    )
");
$promo_belum = mysqli_fetch_assoc($cek_promo_query)['jml'] ?? 0;

/* ================================================================
   💬 2. Cek Pesan Baru Customer (yang belum dibalas)
================================================================ */
$pesan_baru_query = mysqli_query($koneksi, "
    SELECT COUNT(*) AS jml
    FROM keluhan
    WHERE (balasan IS NULL OR balasan = '') 
");
$pesan_baru = mysqli_fetch_assoc($pesan_baru_query)['jml'] ?? 0;
?>
<!DOCTYPE html>
<html>
<head>
  <meta charset="utf-8">
  <meta http-equiv="X-UA-Compatible" content="IE=edge">
  <title>Admin Panel | Toko Elektronik</title>
  <meta content="width=device-width, initial-scale=1, maximum-scale=1, user-scalable=no" name="viewport">
  
  <!-- CSS -->
  <link rel="stylesheet" href="../assets/bower_components/bootstrap/dist/css/bootstrap.min.css">
  <link rel="stylesheet" href="../assets/bower_components/font-awesome/css/font-awesome.min.css">
  <link rel="stylesheet" href="../assets/bower_components/Ionicons/css/ionicons.min.css">
  <link rel="stylesheet" href="../assets/dist/css/AdminLTE.min.css">
  <link rel="stylesheet" href="../assets/dist/css/skins/_all-skins.min.css">
  <link rel="stylesheet" href="../assets/bower_components/datatables.net-bs/css/dataTables.bootstrap.min.css">
  <link rel="stylesheet" href="../assets/bower_components/morris.js/morris.css">
  <link rel="stylesheet" href="../assets/bower_components/jvectormap/jquery-jvectormap.css">
  <link rel="stylesheet" href="../assets/bower_components/bootstrap-datepicker/dist/css/bootstrap-datepicker.min.css">
  <link rel="stylesheet" href="../assets/bower_components/bootstrap-daterangepicker/daterangepicker.css">
  <link rel="stylesheet" href="../assets/plugins/bootstrap-wysihtml5/bootstrap3-wysihtml5.min.css">
  <link rel="stylesheet" href="https://fonts.googleapis.com/css?family=Source+Sans+Pro:300,400,600,700">
</head>
<body class="hold-transition skin-blue sidebar-mini">
<div class="wrapper">

  <!-- HEADER -->
  <header class="main-header">
    <!-- Logo -->
    <a href="index.php" class="logo">
      <span class="logo-mini"><b>ELEK</b></span>
      <span class="logo-lg"><b>ELEKTRONIK</b></span>
    </a>

    <!-- Navbar -->
    <nav class="navbar navbar-static-top">
      <a href="#" class="sidebar-toggle" data-toggle="push-menu" role="button">
        <span class="sr-only">Toggle navigation</span>
      </a>

      <div class="navbar-custom-menu">
        <ul class="nav navbar-nav">

          <!-- 🔔 Notifikasi -->
          <li class="dropdown notifications-menu">
            <a href="#" class="dropdown-toggle" data-toggle="dropdown" title="Notifikasi">
              <i class="fa fa-bell"></i>
              <?php if(($pesan_baru + $promo_belum) > 0): ?>
                <span class="label label-danger"><?= $pesan_baru + $promo_belum; ?></span>
              <?php endif; ?>
            </a>
            <ul class="dropdown-menu">
              <li class="header">Anda memiliki <?= $pesan_baru + $promo_belum; ?> notifikasi</li>
              <li>
                <ul class="menu">
                  <?php if($pesan_baru > 0): ?>
                    <li><a href="keluhan.php"><i class="fa fa-comments text-yellow"></i> <?= $pesan_baru; ?> pesan baru dari customer</a></li>
                  <?php endif; ?>
                  <?php if($promo_belum > 0): ?>
                    <li><a href="customer.php"><i class="fa fa-gift text-red"></i> <?= $promo_belum; ?> customer layak promo</a></li>
                  <?php endif; ?>
                  <?php if($pesan_baru == 0 && $promo_belum == 0): ?>
                    <li><a><i class="fa fa-check text-green"></i> Tidak ada notifikasi baru</a></li>
                  <?php endif; ?>
                </ul>
              </li>
            </ul>
          </li>

          <!-- Profil -->
          <li class="dropdown user user-menu">
            <a href="#" class="dropdown-toggle" data-toggle="dropdown">
              <img src="<?= $profil['admin_foto'] ? '../gambar/user/'.$profil['admin_foto'] : '../gambar/sistem/user.png'; ?>" class="user-image" alt="User Image">
              <span class="hidden-xs"><?= $_SESSION['nama']; ?> - Admin</span>
            </a>
          </li>
          <li><a href="logout.php"><i class="fa fa-sign-out"></i> LOGOUT</a></li>
        </ul>
      </div>
    </nav>
  </header>

  <!-- SIDEBAR -->
  <aside class="main-sidebar">
    <section class="sidebar">
      <div class="user-panel">
        <div class="pull-left image">
          <img src="<?= $profil['admin_foto'] ? '../gambar/user/'.$profil['admin_foto'] : '../gambar/sistem/user.png'; ?>" class="img-circle" style="max-height:45px" alt="User Image">
        </div>
        <div class="pull-left info">
          <p><?= $_SESSION['nama']; ?></p>
          <a href="#"><i class="fa fa-circle text-success"></i> Online</a>
        </div>
      </div>

      <ul class="sidebar-menu" data-widget="tree">
        <li class="header">MAIN NAVIGATION</li>
        <li><a href="index.php"><i class="fa fa-dashboard"></i> <span>DASHBOARD</span></a></li>

        <li class="treeview">
          <a href="#">
            <i class="fa fa-pie-chart"></i>
            <span>MASTER</span>
            <span class="pull-right-container"><i class="fa fa-angle-left pull-right"></i></span>
          </a>
          <ul class="treeview-menu">
            <li><a href="admin.php"><i class="fa fa-circle-o"></i> DATA ADMIN</a></li>
            <li><a href="kategori.php"><i class="fa fa-circle-o"></i> DATA KATEGORI</a></li>
            <li><a href="produk.php"><i class="fa fa-circle-o"></i> DATA PRODUK</a></li>

            <li>
              <a href="customer.php"><i class="fa fa-circle-o"></i> DATA CUSTOMER
                <?php if($promo_belum > 0): ?>
                  <small class="label pull-right bg-red"><?= $promo_belum; ?></small>
                <?php endif; ?>
              </a>
            </li>
            <li><a href="retur.php"><i class="fa fa-circle-o"></i> DATA RETUR</a></li>
            <li><a href="feedback.php"><i class="fa fa-circle-o"></i> DATA FEEDBACK</a></li>
          </ul>
        </li>

        
        <li>
          <a href="keluhan.php">
            <i class="fa fa-comments"></i> 
            <span>Chat Customer</span>
            <?php if($pesan_baru > 0): ?>
              <small class="label pull-right bg-yellow"><?= $pesan_baru; ?></small>
            <?php endif; ?>
          </a>
        </li>

        <li><a href="transaksi.php"><i class="fa fa-retweet"></i> <span>TRANSAKSI</span></a></li>
        <li><a href="analisis.php"><i class="fa fa-bar-chart"></i> <span>Analisis</span></a></li>
        <li><a href="gantipassword.php"><i class="fa fa-lock"></i> <span>GANTI PASSWORD</span></a></li>
        <li><a href="logout.php"><i class="fa fa-sign-out"></i> <span>LOGOUT</span></a></li>
      </ul>
    </section>
  </aside>
