<?php
include 'koneksi.php';

// menangkap data yang dikirim dari form
$username = mysqli_real_escape_string($koneksi, $_POST['username']);
$password = mysqli_real_escape_string($koneksi, md5($_POST['password']));

// cek login admin
$login_admin = mysqli_query($koneksi, "SELECT * FROM admin WHERE admin_username='$username' AND admin_password='$password'");
$cek_admin = mysqli_num_rows($login_admin);

// cek login owner
$login_owner = mysqli_query($koneksi, "SELECT * FROM owner WHERE owner_username='$username' AND owner_password='$password'");
$cek_owner = mysqli_num_rows($login_owner);

if($cek_admin > 0){
    session_start();
    $data = mysqli_fetch_assoc($login_admin);
    $_SESSION['id'] = $data['admin_id'];
    $_SESSION['nama'] = $data['admin_nama'];
    $_SESSION['username'] = $data['admin_username'];
    $_SESSION['level'] = "admin";
    $_SESSION['status'] = "login";

    header("location:admin/");
}
else if($cek_owner > 0){
    session_start();
    $data = mysqli_fetch_assoc($login_owner);
    $_SESSION['id'] = $data['owner_id'];
    $_SESSION['nama'] = $data['owner_nama'];
    $_SESSION['username'] = $data['owner_username'];
    $_SESSION['level'] = "owner";
    $_SESSION['status'] = "login";

    header("location:owner/");
}
else{
    header("location:login.php?alert=gagal");
}
