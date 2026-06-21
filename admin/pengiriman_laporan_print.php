<!DOCTYPE html>
<html>
<head>
  <title>Laporan Data Pengiriman</title>
</head>
<body>

  <style type="text/css">
  body{
    font-family: sans-serif;
  }

  .table{
    width: 100%;
  }

  th,td{
  }
  .table,
  .table th,
  .table td {
    padding: 5px;
    border: 1px solid black;
    border-collapse: collapse;
  }
</style>

<center>
  <h2>Laporan Data pengiriman di Toko Cyber Sport</h2>
</center>


<table class="table table-bordered table-striped" id="table-datatable">
  <thead>
   <th width="1%">NO</th>
   <th>NO.INVOICE</th>
   <th>TANGGAL</th>
   <th>CUSTOMER</th>
   <th>PENGIRIMAN</th>
   <th>TOTAL BAYAR</th>
   <th class="text-center">STATUS</th>
 </tr>
</thead>
<tbody>
  <?php 
  include '../koneksi.php';
  $no = 1;
  $invoice = mysqli_query($koneksi,"select * from invoice,customer where customer_id=invoice_customer order by invoice_id desc");
  while($i = mysqli_fetch_array($invoice)){
    ?>
    <tr>
      <td><?php echo $no++; ?></td>
      <td>INVOICE-00<?php echo $i['invoice_id'] ?></td>
      <td><?php echo date('d-m-Y', strtotime($i['invoice_tanggal'])); ?></td>
      <td><?php echo $i['customer_nama'] ?></td>
      <td><?php echo $i['invoice_kurir'] ?></td>
      <td><?php echo "Rp. ".number_format($i['invoice_total_bayar'])." ,-" ?></td>
      <td class="text-center">
        <?php 
        if($i['invoice_status'] == 0){
          echo "<span class='label label-warning'>Menunggu Pembayaran</span>";
        }elseif($i['invoice_status'] == 1){
          echo "<span class='label label-default'>Menunggu Konfirmasi</span>";
        }elseif($i['invoice_status'] == 2){
          echo "<span class='label label-danger'>Ditolak</span>";
        }elseif($i['invoice_status'] == 3){
          echo "<span class='label label-primary'>Dikonfirmasi & Sedang Diproses</span>";
        }elseif($i['invoice_status'] == 4){
          echo "<span class='label label-warning'>Dikirim</span>";
        }elseif($i['invoice_status'] == 5){
          echo "<span class='label label-success'>Selesai</span>";
        }
        ?>
      </td>                                          
    </tr>
    <?php 
  }
  ?>
</tbody>
</table>


<script>
  window.print();
</script>
</html>