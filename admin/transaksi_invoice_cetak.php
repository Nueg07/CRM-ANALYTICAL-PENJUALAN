<?php 
session_start();
include '../koneksi.php';
?>
<!DOCTYPE html>
<html>
<head>
  <title>Invoice</title>
  <style>
    body{ font-family: sans-serif; }
    .table{ border-collapse: collapse; width: 100%; }
    .table th,.table td{ padding: 5px 10px; border: 1px solid black; }
  </style>
</head>
<body>

<?php 
if(!isset($_GET['id'])){
  echo "<p>ID Invoice tidak ditemukan.</p>";
  exit;
}
$id_invoice = mysqli_real_escape_string($koneksi, $_GET['id']);
$invoice = mysqli_query($koneksi,"SELECT * FROM invoice WHERE invoice_id='$id_invoice' LIMIT 1");
if(mysqli_num_rows($invoice) < 1){
  echo "<p>Data invoice tidak ditemukan.</p>";
  exit;
}
$i = mysqli_fetch_assoc($invoice);
?>

<center>
  <h3>CYBER SPORT</h3>
  <p><b>INVOICE-00<?php echo $i['invoice_id'] ?></b></p>
</center>

<p>
  <?php echo $i['invoice_nama']; ?><br/>
  <?php echo $i['invoice_alamat']; ?><br/>
  <?php echo $i['invoice_provinsi']; ?>, <?php echo $i['invoice_kabupaten']; ?><br/>
  Hp. <?php echo $i['invoice_hp']; ?>
</p>

<table class="table">
  <thead>
    <tr>
      <th width="1%">NO</th>
      <th>Produk</th>
      <th>Harga</th>
      <th>Jumlah</th>
      <th>Total Harga</th>
    </tr>
  </thead>
  <tbody>
    <?php 
    $no = 1;
    $total = 0;
    $transaksi = mysqli_query($koneksi,"SELECT * FROM transaksi 
                                        JOIN produk ON transaksi_produk=produk_id 
                                        WHERE transaksi_invoice='$id_invoice'");
    while($d=mysqli_fetch_assoc($transaksi)){
      $sub_total = $d['transaksi_jumlah'] * $d['transaksi_harga'];
      $total += $sub_total;
    ?>
    <tr>
      <td><?php echo $no++; ?></td>
      <td><?php echo $d['produk_nama']; ?></td>
      <td><?php echo "Rp. ".number_format($d['transaksi_harga']); ?></td>
      <td><?php echo number_format($d['transaksi_jumlah']); ?></td>
      <td><?php echo "Rp. ".number_format($sub_total); ?></td>
    </tr>
    <?php } ?>
  </tbody>
  <tfoot>
    <tr>
      <th colspan="4" style="text-align:right">Total Belanja</th>
      <td><?php echo "Rp. ".number_format($total); ?></td>
    </tr>
    <tr>
      <th colspan="4" style="text-align:right">Ongkir (<?php echo $i['invoice_kurir'] ?>)</th>
      <td><?php echo "Rp. ".number_format($i['invoice_ongkir']); ?></td>
    </tr>
    <tr>
      <th colspan="4" style="text-align:right">Total Bayar</th>
      <td><b><?php echo "Rp. ".number_format($i['invoice_total_bayar']); ?></b></td>
    </tr>
  </tfoot>
</table>

<p>
  <b>Status :</b>
  <?php 
  $status = [
    0 => "Menunggu Pembayaran",
    1 => "Menunggu Konfirmasi",
    2 => "Ditolak",
    3 => "Dikonfirmasi & Sedang Diproses",
    4 => "Dikirim",
    5 => "Selesai"
  ];
  echo $status[$i['invoice_status']];
  ?>
</p>

<h4>Informasi Pembayaran:</h4>
<ul>
  <li>Bank BRI - No. Rekening: <b>1234 5678 9101</b> a.n <b>Dummy Nama</b></li>
  <li>Bank BCA - No. Rekening: <b>9876 5432 1098</b> a.n <b>Dummy Nama</b></li>
  <li>DANA - <b>0812 3456 7890</b> a.n <b>Dummy Nama</b></li>
  <li>OVO - <b>0812 3456 7890</b> a.n <b>Dummy Nama</b></li>
</ul>

<script>
  window.print();
</script>
</body>
</html>
