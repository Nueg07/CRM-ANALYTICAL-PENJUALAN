<?php
session_start();
include '../koneksi.php';

if (!isset($_SESSION['level'])) {
    header("location:../login.php?alert=belum_login");
    exit;
}

if ($_SESSION['level'] != "admin" && $_SESSION['level'] != "owner") {
    header("location:../login.php?alert=tidak_izin");
    exit;
}

include 'header.php';

$query = mysqli_query($koneksi, "
    SELECT i.*, c.customer_nama 
    FROM invoice i
    LEFT JOIN customer c ON i.invoice_customer = c.customer_id
    WHERE i.invoice_status IN (3, 5)
    ORDER BY i.invoice_id DESC
");
?>

<div class="content-wrapper">
  <section class="content-header">
    <h1>Transaksi Selesai <small>Daftar pesanan yang telah selesai</small></h1>
    <ol class="breadcrumb">
      <li><a href="index.php"><i class="fa fa-dashboard"></i> Home</a></li>
      <li class="active">Transaksi Selesai</li>
    </ol>
  </section>

  <section class="content">
    <div class="box box-success">
      <div class="box-header with-border">
        <h3 class="box-title"><i class="fa fa-check"></i> Daftar Transaksi Selesai</h3>
      </div>

      <div class="box-body">
        <?php if(isset($_GET['alert']) && $_GET['alert']=='selesai'): ?>
          <div class="alert alert-success">
            <i class="fa fa-check"></i> Transaksi berhasil diselesaikan!
          </div>
        <?php endif; ?>

        <div class="table-responsive">
          <table class="table table-bordered table-striped" id="table-datatable">
            <thead class="bg-light">
              <tr class="text-center">
                <th width="5%">No</th>
                <th>No. Invoice</th>
                <th>Customer</th>
                <th>Tanggal</th>
                <th>Total Bayar</th>
                <th width="10%">Aksi</th>
              </tr>
            </thead>
            <tbody>
              <?php 
              $no = 1;
              if (mysqli_num_rows($query) > 0) {
                  while ($row = mysqli_fetch_assoc($query)) { ?>
                      <tr>
                        <td class="text-center"><?= $no++; ?></td>
                        <td><strong>INV-<?= str_pad($row['invoice_id'], 4, '0', STR_PAD_LEFT) ?></strong></td>
                        <td><?= htmlspecialchars($row['customer_nama'] ?? 'Tidak diketahui') ?></td>
                        <td><?= date('d/m/Y', strtotime($row['invoice_tanggal'])) ?></td>
                        <td>Rp <?= number_format($row['invoice_total_bayar'], 0, ',', '.') ?></td>
                        <td class="text-center">
                          <a href="transaksi_invoice.php?id=<?= $row['invoice_id'] ?>" class="btn btn-sm btn-info">
                            <i class="fa fa-eye"></i> Detail
                          </a>
                        </td>
                      </tr>
              <?php }
              } else {
                  echo '<tr><td colspan="6" class="text-center text-muted">Belum ada transaksi selesai.</td></tr>';
              } ?>
            </tbody>
          </table>
        </div>
      </div>
    </div>
  </section>
</div>

<?php include 'footer.php'; ?>
