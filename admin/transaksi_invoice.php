<?php 
include 'header.php'; 
include '../koneksi.php'; 
?>

<div class="content-wrapper">
  <section class="content-header">
    <h1>Transaksi <small>Detail Invoice / Pesanan</small></h1>
    <ol class="breadcrumb">
      <li><a href="#"><i class="fa fa-dashboard"></i> Home</a></li>
      <li class="active">Transaksi</li>
    </ol>
  </section>

  <section class="content">
    <div class="row">
      <section class="col-lg-12">
        <div class="box box-info">
          <div class="box-header">
            <h3 class="box-title">Detail Invoice</h3>
          </div>
          <div class="box-body">

<?php 
if (!isset($_GET['id'])) {
    echo "<div class='alert alert-danger'>ID Invoice tidak ditemukan.</div>";
    include 'footer.php';
    exit;
}

$id_invoice = mysqli_real_escape_string($koneksi, $_GET['id']);

// ✅ Ambil data invoice + customer + alamat lengkap
$q = "
SELECT 
    i.*, 
    c.customer_nama, c.customer_hp, c.customer_alamat,
    p.nama AS provinsi_nama,
    k.nama AS kota_nama,
    kc.nama AS kecamatan_nama,
    d.nama AS desa_nama
FROM invoice i
LEFT JOIN customer c ON c.customer_id = i.invoice_customer
LEFT JOIN provinsi p ON p.id = c.provinsi_id
LEFT JOIN kota k ON k.id = c.kota_id
LEFT JOIN kecamatan kc ON kc.id = c.kecamatan_id
LEFT JOIN desa d ON d.id = c.desa_id
WHERE i.invoice_id = '$id_invoice'
LIMIT 1
";
$invoice = mysqli_query($koneksi, $q);

if (!$invoice || mysqli_num_rows($invoice) < 1) {
    echo "<div class='alert alert-warning'>Invoice tidak ditemukan.</div>";
    include 'footer.php';
    exit;
}

$i = mysqli_fetch_assoc($invoice);
?>

<div class="col-lg-12">
  <a href="transaksi.php" class="btn btn-primary btn-sm"><i class="fa fa-arrow-left"></i> KEMBALI</a>
  <a href="transaksi_invoice_cetak.php?id=<?php echo $i['invoice_id']; ?>" target="_blank" class="btn btn-default btn-sm"><i class="fa fa-print"></i> CETAK</a>
  <br><br>

  <h4>INVOICE-00<?php echo $i['invoice_id']; ?></h4>

  <h5><b>Alamat Pengiriman</b></h5>
  <p>
    <b><?php echo htmlspecialchars($i['invoice_nama'] ?: $i['customer_nama']); ?></b><br>
    <?php 
    $alamat_pengiriman = trim($i['invoice_alamat']) ?: $i['customer_alamat'];
    echo nl2br(htmlspecialchars($alamat_pengiriman)); 
    ?><br>

    <?php
    $wilayah = [];
    if (!empty($i['desa_nama'])) $wilayah[] = "Desa " . htmlspecialchars($i['desa_nama']);
    if (!empty($i['kecamatan_nama'])) $wilayah[] = "Kecamatan " . htmlspecialchars($i['kecamatan_nama']);
    if (!empty($i['kota_nama'])) $wilayah[] = "Kota " . htmlspecialchars($i['kota_nama']);
    if (!empty($i['provinsi_nama'])) $wilayah[] = "Provinsi " . htmlspecialchars($i['provinsi_nama']);
    echo implode(', ', $wilayah);
    ?><br>

    <b>HP:</b> <?php echo htmlspecialchars($i['invoice_hp'] ?: $i['customer_hp']); ?>
  </p>

  <div class="table-responsive">
    <table class="table table-bordered table-striped">
      <thead class="bg-light">
        <tr>
          <th class="text-center">No</th>
          <th colspan="2">Produk</th>
          <th class="text-center">Harga</th>
          <th class="text-center">Jumlah</th>
          <th class="text-center">Subtotal</th>
        </tr>
      </thead>
      <tbody>
        <?php
        $no = 1;
        $total = 0;

        $transaksi = mysqli_query($koneksi, "
          SELECT d.*, p.produk_nama, p.produk_foto1 
          FROM invoice_detail d 
          LEFT JOIN produk p ON d.produk_id = p.produk_id 
          WHERE d.invoice_id = '$id_invoice'
        ");

        while ($d = mysqli_fetch_assoc($transaksi)) {
            // Tentukan harga yang benar
            $harga_produk = 0;

            if (!empty($d['harga'])) {
                $harga_produk = $d['harga'];
            } elseif (!empty($d['harga_diskon'])) {
                $harga_produk = $d['harga_diskon'];
            } else {
                $harga_produk = $d['harga_awal'];
            }

            // Hitung subtotal
            $sub_total = !empty($d['subtotal']) ? $d['subtotal'] : ($d['jumlah'] * $harga_produk);
            $total += $sub_total;
        ?>
        <tr>
          <td class="text-center"><?php echo $no++; ?></td>
          <td class="text-center">
            <?php if (empty($d['produk_foto1'])) { ?>
              <img src="../gambar/sistem/produk.png" width="50">
            <?php } else { ?>
              <img src="../gambar/produk/<?php echo $d['produk_foto1']; ?>" width="50">
            <?php } ?>
          </td>
          <td><?php echo htmlspecialchars($d['produk_nama'] ?? 'Produk terhapus'); ?></td>
          <td class="text-center">Rp <?php echo number_format($harga_produk, 0, ',', '.'); ?></td>
          <td class="text-center"><?php echo (int)$d['jumlah']; ?></td>
          <td class="text-center">Rp <?php echo number_format($sub_total, 0, ',', '.'); ?></td>
        </tr>
        <?php } ?>
      </tbody>
      <tfoot>
        <tr>
          <td colspan="4"></td>
          <th>Total Belanja</th>
          <td class="text-center">Rp <?php echo number_format($total, 0, ',', '.'); ?></td>
        </tr>
        <tr>
          <td colspan="4"></td>
          <th>Ongkir</th>
          <td class="text-center">Rp <?php echo number_format($i['invoice_ongkir'], 0, ',', '.'); ?></td>
        </tr>
        <tr class="bg-light">
          <td colspan="4"></td>
          <th>Total Bayar</th>
          <td class="text-center text-success"><b>Rp <?php echo number_format($i['invoice_total_bayar'], 0, ',', '.'); ?></b></td>
        </tr>
      </tfoot>
    </table>
  </div>

  <h5>Status Pesanan:</h5>
  <?php
  $status = [
    0 => "<span class='label label-warning'>Menunggu Pembayaran</span>",
    1 => "<span class='label label-default'>Menunggu Konfirmasi</span>",
    2 => "<span class='label label-danger'>Ditolak</span>",
    3 => "<span class='label label-primary'>Diproses</span>",
    4 => "<span class='label label-warning'>Dikirim</span>",
    5 => "<span class='label label-success'>Selesai</span>"
  ];
  echo $status[$i['invoice_status']] ?? "<span class='label label-default'>Tidak diketahui</span>";
  ?>

</div>

<?php include 'footer.php'; ?>
