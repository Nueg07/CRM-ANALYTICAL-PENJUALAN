<?php 
include '../koneksi.php';

$mulai = isset($_GET['tgl_mulai']) ? $_GET['tgl_mulai'] : "";
$selesai = isset($_GET['tgl_selesai']) ? $_GET['tgl_selesai'] : "";

$query = "SELECT invoice.*, customer.customer_nama 
          FROM invoice 
          LEFT JOIN customer ON invoice.invoice_customer=customer.customer_id
          WHERE invoice_status='5'";

if($mulai != "" && $selesai != ""){
  $query .= " AND date(invoice_tanggal) >= '$mulai' AND date(invoice_tanggal) <= '$selesai'";
}

$query .= " ORDER BY invoice_tanggal DESC";
$laporan = mysqli_query($koneksi, $query);
?>

<!DOCTYPE html>
<html>
<head>
  <title>Cetak Laporan Penjualan</title>
  <style>
    body { font-family: Arial; }
    table { border-collapse: collapse; width: 100%; margin-top: 20px; }
    th, td { border: 1px solid #000; padding: 8px; text-align: center; }
    th { background: #f2f2f2; }
  </style>
</head>
<body onload="window.print()">

<h2 align="center">LAPORAN PENJUALAN</h2>
<p align="center">
  <?php 
  if($mulai && $selesai){
    echo "Periode: ".date('d-m-Y', strtotime($mulai))." s/d ".date('d-m-Y', strtotime($selesai));
  } else {
    echo "Semua Data";
  }
  ?>
</p>

<table>
  <thead>
    <tr>
      <th>No</th>
      <th>Tanggal</th>
      <th>No Invoice</th>
      <th>Customer</th>
      <th>Total Bayar</th>
    </tr>
  </thead>
  <tbody>
    <?php 
    $no=1;
    $total=0;
    while($d = mysqli_fetch_array($laporan)){
      $total += $d['invoice_total_bayar'];
    ?>
    <tr>
      <td><?php echo $no++; ?></td>
      <td><?php echo date('d-m-Y', strtotime($d['invoice_tanggal'])); ?></td>
      <td><?php echo $d['invoice_id']; ?></td>
      <td><?php echo $d['customer_nama']; ?></td>
      <td><?php echo "Rp ".number_format($d['invoice_total_bayar'],0,',','.'); ?></td>
    </tr>
    <?php } ?>
    <tr>
      <th colspan="4">Total</th>
      <th><?php echo "Rp ".number_format($total,0,',','.'); ?></th>
    </tr>
  </tbody>
</table>

</body>
</html>
