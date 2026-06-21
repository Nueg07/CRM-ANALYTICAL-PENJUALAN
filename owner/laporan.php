<?php 
include 'header.php'; 
?>

<div class="content-wrapper">

  <section class="content-header">
    <h1>
      Laporan Penjualan
      <small>Owner</small>
    </h1>
    <ol class="breadcrumb">
      <li><a href="index.php"><i class="fa fa-dashboard"></i> Dashboard</a></li>
      <li class="active">Laporan Penjualan</li>
    </ol>
  </section>

  <section class="content">

    <div class="box box-info">
      <div class="box-header">
        <h3 class="box-title">Filter Laporan</h3>
      </div>
      <div class="box-body">
        <form method="get" action="">
          <div class="row">
            <div class="col-lg-3">
              <label>Dari Tanggal</label>
              <input type="date" name="tgl_mulai" class="form-control" value="<?php echo isset($_GET['tgl_mulai']) ? $_GET['tgl_mulai'] : ""; ?>">
            </div>
            <div class="col-lg-3">
              <label>Sampai Tanggal</label>
              <input type="date" name="tgl_selesai" class="form-control" value="<?php echo isset($_GET['tgl_selesai']) ? $_GET['tgl_selesai'] : ""; ?>">
            </div>
            <div class="col-lg-2">
              <br>
              <button type="submit" class="btn btn-primary btn-sm"><i class="fa fa-search"></i> Tampilkan</button>
            </div>
            <div class="col-lg-2">
              <br>
              <a href="laporan.php" class="btn btn-sm btn-danger"><i class="fa fa-times"></i> Reset</a>
            </div>
          </div>
        </form>
      </div>
    </div>


    <div class="box box-info">
      <div class="box-header">
        <h3 class="box-title">Data Laporan Penjualan</h3>

        <?php 
        $urlFilter = "";
        if(isset($_GET['tgl_mulai']) && isset($_GET['tgl_selesai'])){
          $urlFilter = "&tgl_mulai=".$_GET['tgl_mulai']."&tgl_selesai=".$_GET['tgl_selesai'];
        }
        ?>
        <div class="box-tools">
          <a href="laporan_cetak.php?<?php echo $urlFilter; ?>" target="_blank" class="btn btn-success btn-sm">
            <i class="fa fa-print"></i> Cetak
          </a>
        </div>
      </div>

      <div class="box-body">

        <div class="table-responsive">
          <table class="table table-bordered" id="table-datatable">
            <thead>
              <tr>
                <th width="1%">No</th>
                <th>Tanggal</th>
                <th>No Invoice</th>
                <th>Customer</th>
                <th>Status</th>
                <th>Total Bayar</th>
              </tr>
            </thead>
            <tbody>
              <?php 
              $no=1;
              $total_semua = 0;

              $query = "SELECT invoice.*, customer.customer_nama 
                        FROM invoice 
                        LEFT JOIN customer ON invoice.invoice_customer=customer.customer_id
                        WHERE invoice_status='5'";

              if(isset($_GET['tgl_mulai']) && isset($_GET['tgl_selesai']) && $_GET['tgl_mulai'] != "" && $_GET['tgl_selesai'] != ""){
                $mulai = $_GET['tgl_mulai'];
                $selesai = $_GET['tgl_selesai'];
                $query .= " AND date(invoice_tanggal) >= '$mulai' AND date(invoice_tanggal) <= '$selesai'";
              }

              $query .= " ORDER BY invoice_tanggal DESC";
              $laporan = mysqli_query($koneksi, $query);
              while($d = mysqli_fetch_array($laporan)){
                $total_semua += $d['invoice_total_bayar'];
              ?>
                <tr>
                  <td><?php echo $no++; ?></td>
                  <td><?php echo date('d-m-Y', strtotime($d['invoice_tanggal'])); ?></td>
                  <td><?php echo $d['invoice_id']; ?></td>
                  <td><?php echo $d['customer_nama']; ?></td>
                  <td><span class='label label-success'>Selesai</span></td>
                  <td><?php echo "Rp ".number_format($d['invoice_total_bayar'],0,',','.'); ?></td>
                </tr>
              <?php } ?>
            </tbody>
            <tfoot>
              <tr>
                <th colspan="5" class="text-right">Total</th>
                <th><?php echo "Rp ".number_format($total_semua,0,',','.'); ?></th>
              </tr>
            </tfoot>
          </table>
        </div>

      </div>
    </div>

  </section>

</div>
<?php include 'footer.php'; ?>
