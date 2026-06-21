<?php
include '../koneksi.php';

if(isset($_GET['id'])){
    $id = $_GET['id'];

    // ubah status jadi 'Dikirim' (kode status 4)
    $update = mysqli_query($koneksi, "UPDATE invoice SET invoice_status='4' WHERE invoice_id='$id'");

    if($update){
        header("location:transaksi.php?pesan=pesanan_dikirim");
    } else {
        echo "Gagal memperbarui status pesanan!";
    }
} else {
    header("location:transaksi.php");
}
?>
