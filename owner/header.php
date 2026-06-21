<?php 
include '../koneksi.php';
session_start();

if (!isset($_SESSION['status']) || $_SESSION['status'] != "login" || $_SESSION['level'] != "owner") {
  header("location:../login.php?alert=belum_login");
  exit;
}

$id_owner = $_SESSION['id'];
$profil_query = mysqli_query($koneksi, "SELECT * FROM owner WHERE owner_id='$id_owner'");
$profil = mysqli_fetch_assoc($profil_query);
?>
<!DOCTYPE html>
<html>
<head>
  <meta charset="utf-8">
  <title>Owner Panel | Toko Elektronik</title>
  <meta content="width=device-width, initial-scale=1, maximum-scale=1, user-scalable=no" name="viewport">

  <!-- CSS -->
  <link rel="stylesheet" href="../assets/bower_components/bootstrap/dist/css/bootstrap.min.css">
  <link rel="stylesheet" href="../assets/bower_components/font-awesome/css/font-awesome.min.css">
  <link rel="stylesheet" href="../assets/bower_components/Ionicons/css/ionicons.min.css">
  <link rel="stylesheet" href="../assets/dist/css/AdminLTE.min.css">
  <link rel="stylesheet" href="../assets/dist/css/skins/_all-skins.min.css">
  <link rel="stylesheet" href="../assets/bower_components/datatables.net-bs/css/dataTables.bootstrap.min.css">
  <link rel="stylesheet" href="../assets/plugins/bootstrap-wysihtml5/bootstrap3-wysihtml5.min.css">
  <link rel="stylesheet" href="https://fonts.googleapis.com/css?family=Source+Sans+Pro:300,400,600,700">
</head>

<body class="hold-transition skin-green sidebar-mini">
<div class="wrapper">

  <!-- HEADER -->
  <header class="main-header">
    <!-- Logo -->
    <a href="index.php" class="logo">
      <span class="logo-mini"><b>OWN</b></span>
      <span class="logo-lg"><b>OWNER PANEL</b></span>
    </a>

    <!-- Navbar -->
    <nav class="navbar navbar-static-top">
      <a href="#" class="sidebar-toggle" data-toggle="push-menu" role="button"></a>
      <div class="navbar-custom-menu">
        <ul class="nav navbar-nav">
          <li class="dropdown user user-menu">
            <a href="#" class="dropdown-toggle" data-toggle="dropdown">
              <img src="<?= $profil['owner_foto'] ? "../gambar/user/".$profil['owner_foto'] : "../gambar/sistem/user.png"; ?>" class="user-image" alt="User Image">
              <span class="hidden-xs"><?= htmlspecialchars($_SESSION['nama']); ?> - Owner</span>
            </a>
          </li>
          <li><a href="logout.php"><i class="fa fa-sign-out"></i> Logout</a></li>
        </ul>
      </div>
    </nav>
  </header>

  <!-- SIDEBAR -->
  <aside class="main-sidebar">
    <section class="sidebar">
      <!-- User Panel -->
      <div class="user-panel">
        <div class="pull-left image">
          <img src="<?= $profil['owner_foto'] ? "../gambar/user/".$profil['owner_foto'] : "../gambar/sistem/user.png"; ?>" class="img-circle" alt="User Image" style="max-height:45px">
        </div>
        <div class="pull-left info">
          <p><?= htmlspecialchars($_SESSION['nama']); ?></p>
          <a href="#"><i class="fa fa-circle text-success"></i> Online</a>
        </div>
      </div>

      <!-- Menu -->
      <ul class="sidebar-menu" data-widget="tree">
        <li class="header">MAIN NAVIGATION</li>
        <li class="active"><a href="index.php"><i class="fa fa-dashboard"></i> <span>Dashboard</span></a></li>
        <li><a href="laporan.php"><i class="fa fa-file-text"></i> <span>Laporan Penjualan</span></a></li>
      
        <li><a href="grafik.php"><i class="fa fa-line-chart"></i> <span>Grafik Penjualan</span></a></li>
        <li><a href="analisis.php"><i class="fa fa-bar-chart"></i> <span>Analisis</span></a></li>
        <li><a href="gantipassword.php"><i class="fa fa-lock"></i> <span>Ganti Password</span></a></li>
        <li><a href="logout.php"><i class="fa fa-sign-out"></i> <span>Logout</span></a></li>
      </ul>
    </section>
  </aside>
