<?php
include '../koneksi.php';

if(isset($_GET['id'])){
    $id = $_GET['id'];

    // ubah status jadi 'Diproses' (kode status 3)
    $update = mysqli_query($koneksi, "UPDATE invoice SET invoice_status='3' WHERE invoice_id='$id'");

    if($update){
        header("location:transaksi.php?pesan=pesanan_diproses");
    } else {
        echo "Gagal memproses pesanan!";
    }
} else {
    header("location:transaksi.php");
}
?>
