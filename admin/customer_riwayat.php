<?php 
include "../koneksi.php";

$id = intval($_POST['id_customer']);

$q = mysqli_query($koneksi, "
    SELECT 
        i.invoice_tanggal,
        p.produk_nama,
        d.jumlah,
        d.harga,
        (d.jumlah * d.harga) AS total
    FROM invoice_detail d
    JOIN invoice i ON d.invoice_id = i.invoice_id
    JOIN produk p ON d.produk_id = p.produk_id
    WHERE i.invoice_customer = $id AND i.invoice_status = 5
    ORDER BY i.invoice_tanggal DESC
");

?>

<table class="table table-bordered table-striped">
  <thead>
    <tr>
      <th>Tanggal</th>
      <th>Produk</th>
      <th>Jumlah</th>
      <th>Harga</th>
      <th>Total</th>
    </tr>
  </thead>
  <tbody>
    <?php 
    if(mysqli_num_rows($q) > 0){
        while($r = mysqli_fetch_assoc($q)){
            echo "
            <tr>
              <td>".date('d-m-Y', strtotime($r['invoice_tanggal']))."</td>
              <td>{$r['produk_nama']}</td>
              <td>{$r['jumlah']}</td>
              <td>Rp ".number_format($r['harga'])."</td>
              <td>Rp ".number_format($r['total'])."</td>
            </tr>";
        }
    } else {
        echo "<tr><td colspan='5' class='text-center'>Belum ada riwayat belanja.</td></tr>";
    }
    ?>
  </tbody>
</table>
