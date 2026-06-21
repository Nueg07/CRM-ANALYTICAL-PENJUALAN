<?php include 'header.php'; ?>
<div class="content-wrapper">

  <section class="content-header">
    <h1>
      Dashboard
      <small>Control panel</small>
    </h1>
    <ol class="breadcrumb">
      <li><a href="#"><i class="fa fa-dashboard"></i> Home</a></li>
      <li class="active">Dashboard</li>
    </ol>
  </section>

  <section class="content">

    <!-- BOX RINGKASAN -->
    <div class="row">
      <!-- Produk -->
      <div class="col-lg-3 col-xs-6">
        <div class="small-box bg-green">
          <div class="inner">
            <?php $produk = mysqli_query($koneksi,"SELECT * FROM produk"); ?>
            <h3><?= mysqli_num_rows($produk); ?></h3>
            <p>Jumlah Produk</p>
          </div>
          <div class="icon"><i class="ion ion-stats-bars"></i></div>
          <a href="produk.php" class="small-box-footer">More info <i class="fa fa-arrow-circle-right"></i></a>
        </div>
      </div>

      <!-- Customer -->
      <div class="col-lg-3 col-xs-6">
        <div class="small-box bg-red">
          <div class="inner">
            <?php $customer = mysqli_query($koneksi,"SELECT * FROM customer"); ?>
            <h3><?= mysqli_num_rows($customer); ?></h3>
            <p>Jumlah Customer</p>
          </div>
          <div class="icon"><i class="ion ion-person"></i></div>
          <a href="customer.php" class="small-box-footer">More info <i class="fa fa-arrow-circle-right"></i></a>
        </div>
      </div>

      <!-- Invoice Selesai -->
      <div class="col-lg-3 col-xs-6">
        <div class="small-box bg-aqua">
          <div class="inner">
            <?php $invoice = mysqli_query($koneksi,"SELECT * FROM invoice WHERE invoice_status = 5"); ?>
            <h3><?= mysqli_num_rows($invoice); ?></h3>
            <p>Transaksi Selesai</p>
          </div>
          <div class="icon"><i class="ion ion-android-list"></i></div>
          <a href="transaksi.php" class="small-box-footer">More info <i class="fa fa-arrow-circle-right"></i></a>
        </div>
      </div>

      <!-- Pengguna -->
      <div class="col-lg-3 col-xs-6">
        <div class="small-box bg-yellow">
          <div class="inner">
            <?php $admin = mysqli_query($koneksi,"SELECT * FROM admin"); ?>
            <h3><?= mysqli_num_rows($admin); ?></h3>
            <p>Jumlah Pengguna</p>
          </div>
          <div class="icon"><i class="ion ion-person-add"></i></div>
          <a href="admin.php" class="small-box-footer">More info <i class="fa fa-arrow-circle-right"></i></a>
        </div>
      </div>
    </div>

    <!-- TOTAL PENDAPATAN -->
    <div class="row">
      <div class="col-lg-12">
        <div class="small-box bg-blue">
          <div class="inner">
            <?php 
              $totalPendapatan = mysqli_fetch_assoc(mysqli_query($koneksi,"
                SELECT SUM(invoice_total_bayar) AS total 
                FROM invoice 
                WHERE invoice_status = 5
              "));
              $totalAll = (int)($totalPendapatan['total'] ?? 0);
            ?>
            <h3>Rp <?= number_format($totalAll, 0, ',', '.'); ?></h3>
            <p>Total Pendapatan Keseluruhan</p>
          </div>
          <div class="icon"><i class="ion ion-cash"></i></div>
        </div>
      </div>
    </div>

    <!-- TABEL PRODUK -->
    <div class="row">
      <section class="col-lg-12">
        <div class="box box-info">
          <div class="box-header">
            <h3 class="box-title">📦 Stok & Penjualan Produk</h3>
          </div>
          <div class="box-body">
            <div class="table-responsive">
              <table class="table table-bordered table-striped" id="table-datatable">
                <thead class="bg-light">
                  <tr>
                    <th width="1%">No</th>
                    <th>Produk</th>
                    <th class="text-center">Sisa Stok</th>
                    <th class="text-center">Jumlah Terjual</th>
                  </tr>
                </thead>
                <tbody>
                <?php
                $no = 1;
                $produk = mysqli_query($koneksi, "SELECT * FROM produk ORDER BY produk_nama ASC");
                while ($p = mysqli_fetch_assoc($produk)) {
                  $idp = $p['produk_id'];
                  $jual = mysqli_query($koneksi,"
                    SELECT SUM(d.jumlah) AS total_terjual
                    FROM invoice_detail d
                    JOIN invoice i ON i.invoice_id = d.invoice_id
                    WHERE i.invoice_status = 5 AND d.produk_id='$idp'
                  ");
                  $j = mysqli_fetch_assoc($jual);
                  $terjual = (int)($j['total_terjual'] ?? 0);
                ?>
                  <tr>
                    <td class="text-center"><?= $no++; ?></td>
                    <td><?= htmlspecialchars($p['produk_nama']); ?></td>
                    <td class="text-center"><?= number_format($p['produk_jumlah']); ?></td>
                    <td class="text-center text-success"><b><?= number_format($terjual); ?></b></td>
                  </tr>
                <?php } ?>
                </tbody>
              </table>
            </div>
          </div>
        </div>
      </section>
    </div>

    <!-- GRAFIK -->
    <div class="row">
      <!-- Penjualan Perbulan -->
      <section class="col-lg-6">
        <div class="box box-info">
          <div class="box-header"><h3 class="box-title">Grafik Penjualan Per Bulan (Rp)</h3></div>
          <div class="box-body"><canvas id="grafik1"></canvas></div>
        </div>
      </section>

      <!-- Produk Terjual -->
      <section class="col-lg-6">
        <div class="box box-info">
          <div class="box-header"><h3 class="box-title">Grafik Produk Terjual</h3></div>
          <div class="box-body"><canvas id="grafik2"></canvas></div>
        </div>
      </section>
    </div>

  </section>
</div>

<?php include 'footer.php'; ?>
<script src="https://cdn.jsdelivr.net/npm/chart.js"></script>

<!-- GRAFIK PENJUALAN PER BULAN -->
<script>
const ctx1 = document.getElementById('grafik1').getContext('2d');
const grafik1 = new Chart(ctx1, {
  type: 'line',
  data: {
    labels: ['Jan','Feb','Mar','Apr','Mei','Jun','Jul','Agu','Sep','Okt','Nov','Des'],
    datasets: [{
      label: 'Total Penjualan (Rp)',
      backgroundColor: 'rgba(54,162,235,0.2)',
      borderColor: 'rgba(54,162,235,1)',
      borderWidth: 2,
      tension: 0.3,
      fill: true,
      data: [
        <?php
        for ($bulan=1; $bulan<=12; $bulan++) {
          $q = mysqli_query($koneksi,"
            SELECT SUM(invoice_total_bayar) AS total 
            FROM invoice 
            WHERE invoice_status = 5 
            AND MONTH(invoice_tanggal)='$bulan'
          ");
          $r = mysqli_fetch_assoc($q);
          echo ($r['total'] ?? 0) . ",";
        }
        ?>
      ]
    }]
  },
  options: {
    responsive: true,
    plugins: {
      legend: { display: false },
      tooltip: {
        callbacks: {
          label: ctx => 'Rp ' + ctx.parsed.y.toLocaleString('id-ID')
        }
      }
    },
    scales: {
      y: {
        beginAtZero: true,
        ticks: { callback: v => 'Rp ' + v.toLocaleString('id-ID') }
      }
    }
  }
});
</script>

<!-- GRAFIK PRODUK TERJUAL -->
<?php
$produk_labels = [];
$produk_values = [];
$q = mysqli_query($koneksi,"
  SELECT p.produk_nama, COALESCE(SUM(d.jumlah),0) AS total
  FROM produk p
  LEFT JOIN invoice_detail d ON p.produk_id = d.produk_id
  LEFT JOIN invoice i ON d.invoice_id = i.invoice_id AND i.invoice_status = 5
  GROUP BY p.produk_id
  ORDER BY total DESC
  LIMIT 10
");
while($row = mysqli_fetch_assoc($q)){
  $produk_labels[] = $row['produk_nama'];
  $produk_values[] = (int)$row['total'];
}
?>
<script>
const ctx2 = document.getElementById('grafik2').getContext('2d');
const grafik2 = new Chart(ctx2, {
  type: 'bar',
  data: {
    labels: <?= json_encode($produk_labels); ?>,
    datasets: [{
      label: 'Produk Terjual',
      backgroundColor: 'rgba(255,206,86,0.7)',
      borderColor: 'rgba(255,206,86,1)',
      borderWidth: 1,
      data: <?= json_encode($produk_values); ?>
    }]
  },
  options: {
    responsive: true,
    scales: {
      y: { beginAtZero: true, ticks: { precision: 0 } }
    }
  }
});
</script>
