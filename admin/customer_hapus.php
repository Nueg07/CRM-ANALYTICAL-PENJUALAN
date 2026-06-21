<?php 
include '../koneksi.php';
$id = $_GET['id'];

// ambil semua invoice milik customer
$data = mysqli_query($koneksi, "SELECT * FROM invoice WHERE invoice_customer='$id'");
while($d = mysqli_fetch_array($data)){
    $id_invoice = $d['invoice_id'];

    // hapus transaksi dulu (child dari invoice)
    mysqli_query($koneksi,"DELETE FROM transaksi WHERE transaksi_invoice='$id_invoice'");
}

// hapus invoice (child dari customer)
mysqli_query($koneksi, "DELETE FROM invoice WHERE invoice_customer='$id'");

// terakhir baru hapus customer
mysqli_query($koneksi, "DELETE FROM customer WHERE customer_id='$id'");

header("location:customer.php");
