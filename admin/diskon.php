<?php
include '../koneksi.php';
if(isset($_GET['id'])){
    $id = (int)$_GET['id'];
    mysqli_query($koneksi,"UPDATE customer SET customer_diskon = 5 WHERE customer_id = $id");
    echo "<script>alert('Diskon 5% berhasil diberikan');window.location='analisis.php';</script>";
}
?>
