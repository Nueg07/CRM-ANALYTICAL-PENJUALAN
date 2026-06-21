<!DOCTYPE html>
<html>
<head>
  <title>Laporan Data Produk</title>
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
    <h2>Laporan Produk Cyber Sport</h2>
  </center>


  <table class="table table-bordered table-striped" id="table-datatable">
    <thead>
      <tr>
        <th width="1%">NO</th>
        <th>NAMA</th>
        <th>JUMLAH</th>
        <th>HARGA SATUAN</th>
        <th>HARGA DISKON</th>
        <th>BERAT</th>
      </tr>
    </thead>
    <tbody>
      <?php 
      include '../koneksi.php';
      $no=1;
      $data = mysqli_query($koneksi,"SELECT * FROM produk");
      while($i = mysqli_fetch_array($data)){
        ?>
        <tr>
          <td><?php echo $no++ ?></td>
          <td><?php echo $i['produk_nama']; ?></td>
          <td><?php echo $i['produk_jumlah']; ?></td>
          <td><?php echo "Rp.".number_format($i['produk_harga']) ?></td>           
          <td><?php echo "Rp.".number_format($i['produk_harga_diskon']) ?></td>           
          <td><?php echo $i['produk_berat']." /Gram"; ?></td>
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