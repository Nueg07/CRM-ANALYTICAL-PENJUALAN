<?php
session_start();
include 'koneksi.php';

$id = $_GET['id'];
$ambil = mysqli_query($koneksi, "SELECT * FROM pembelian WHERE id='$id'");
$pembelian = mysqli_fetch_assoc($ambil);

$detail = mysqli_query($koneksi, "SELECT * FROM pembelian_produk WHERE pembelian_id='$id'");
?>
<h2>Nota Pembelian</h2>
<p>Nama Penerima: <?= $pembelian['nama_penerima']; ?><br>
HP: <?= $pembelian['hp']; ?><br>
Alamat: <?= $pembelian['alamat']; ?><br>
Total Bayar: Rp <?= number_format($pembelian['total']); ?></p>

<table border="1" cellpadding="5" cellspacing="0">
<tr>
  <th>Produk</th>
  <th>Harga</th>
  <th>Jumlah</th>
  <th>Subtotal</th>
</tr>
<?php while($d = mysqli_fetch_assoc($detail)){ ?>
<tr>
  <td><?= $d['nama_produk']; ?></td>
  <td><?= number_format($d['harga']); ?></td>
  <td><?= $d['jumlah']; ?></td>
  <td><?= number_format($d['subtotal']); ?></td>
</tr>
<?php } ?>
</table>
