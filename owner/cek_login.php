<?php
session_start();
if($_SESSION['status'] != "login" || $_SESSION['level'] != "owner"){
    header("location:../login.php?alert=belum_login");
}
