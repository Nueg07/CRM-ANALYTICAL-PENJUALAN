<!DOCTYPE html>
<html>
<head>
  <title>Laporan Data Customer</title>
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
    <h2>Laporan Data Customer di Toko Cyber Sport</h2>
  </center>


  <table class="table table-bordered table-striped" id="table-datatable">
    <thead>
      <tr>
        <th width="1%">NO</th>
        <th>NAMA</th>
        <th>EMAIL</th>
        <th>HANDPHONE</th>
        <th>ALAMAT</th>
        <th>TOTAL TRANSAKSI</th>
      </tr>
    </thead>
    <tbody>
      <?php 
      include '../koneksi.php';
      $no=1;
      $data = mysqli_query($koneksi,"SELECT * FROM customer");
      while($i = mysqli_fetch_array($data)){
        $id = $i['customer_id'];
        ?>
        <tr>
          <td><?php echo $no++ ?></td>
          <td><?php echo $i['customer_nama']; ?></td>
          <td><?php echo $i['customer_email']; ?></td>
          <td><?php echo $i['customer_hp']; ?></td>
          <td><?php echo $i['customer_alamat']; ?></td>
          <td style="text-align: center;">
            <?php 
            $dd = mysqli_query($koneksi,"SELECT * from invoice where invoice_customer='$id'");
            echo mysqli_num_rows($dd)." Transaksi";
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