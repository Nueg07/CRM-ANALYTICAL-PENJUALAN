<?php 
include 'koneksi.php';

$nama    = mysqli_real_escape_string($koneksi, $_POST['nama']);
$email   = mysqli_real_escape_string($koneksi, $_POST['email']);
$hp      = mysqli_real_escape_string($koneksi, $_POST['hp']);
$alamat  = mysqli_real_escape_string($koneksi, $_POST['alamat']);
$password= mysqli_real_escape_string($koneksi, md5($_POST['password']));

// cek apakah email sudah dipakai
$cek_email = mysqli_query($koneksi,"SELECT * FROM customer WHERE customer_email='$email'");
if(mysqli_num_rows($cek_email) > 0){
    header("location:daftar.php?alert=duplikat");
} else {
    mysqli_query($koneksi, "INSERT INTO customer 
        (customer_nama, customer_email, customer_hp, customer_alamat, customer_password) 
        VALUES ('$nama','$email','$hp','$alamat','$password')");

    header("location:masuk.php?alert=terdaftar");
}
