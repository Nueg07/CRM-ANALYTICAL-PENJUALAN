<?php 
include 'header.php';
?>

<div class="content-wrapper">
  <section class="content-header">
    <h1>
      Dashboard Owner
      <small>Ringkasan Bisnis</small>
    </h1>
    <ol class="breadcrumb">
      <li><a href="#"><i class="fa fa-dashboard"></i> Home</a></li>
      <li class="active">Dashboard</li>
    </ol>
  </section>

  <section class="content">

    <!-- ====== INFO BOX ====== -->
    <div class="row">
      <!-- Pendapatan -->
      <div class="col-lg-3 col-xs-6">
        <div class="small-box bg-yellow">
          <div class="inner">
            <?php 
            $p = mysqli_fetch_assoc(mysqli_query($koneksi,"
              SELECT SUM(invoice_total_bayar) AS total 
              FROM invoice WHERE invoice_status IN ('5','selesai')
            "));
            ?>
            <h3>Rp <?= number_format($p['total'] ?? 0, 0, ',', '.'); ?></h3>
            <p>Total Pendapatan</p>
          </div>
          <div class="icon"><i class="ion ion-cash"></i></div>
        </div>
      </div>

      <!-- Transaksi Selesai -->
      <div class="col-lg-3 col-xs-6">
        <div class="small-box bg-aqua">
          <div class="inner">
            <?php 
            $trx = mysqli_query($koneksi,"SELECT * FROM invoice WHERE invoice_status IN ('5','selesai')");
            ?>
            <h3><?= mysqli_num_rows($trx); ?></h3>
            <p>Transaksi Selesai</p>
          </div>
          <div class="icon"><i class="ion ion-android-cart"></i></div>
        </div>
      </div>

      <!-- Customer -->
      <div class="col-lg-3 col-xs-6">
        <div class="small-box bg-red">
          <div class="inner">
            <?php $cust = mysqli_query($koneksi,"SELECT * FROM customer"); ?>
            <h3><?= mysqli_num_rows($cust); ?></h3>
            <p>Total Customer</p>
          </div>
          <div class="icon"><i class="ion ion-person"></i></div>
        </div>
      </div>

      <!-- Produk -->
      <div class="col-lg-3 col-xs-6">
        <div class="small-box bg-green">
          <div class="inner">
            <?php $produk = mysqli_query($koneksi,"SELECT * FROM produk"); ?>
            <h3><?= mysqli_num_rows($produk); ?></h3>
            <p>Total Produk</p>
          </div>
          <div class="icon"><i class="ion ion-cube"></i></div>
        </div>
      </div>
    </div>

    <!-- ====== GRAFIK ====== -->
    <div class="row">
      <!-- Grafik Penjualan Perbulan -->
      <section class="col-lg-6">
        <div class="box box-info">
          <div class="box-header"><h3 class="box-title">Grafik Pendapatan Per Bulan</h3></div>
          <div class="box-body">
            <canvas id="grafikPendapatan" height="180"></canvas>
          </div>
        </div>
      </section>

      <!-- Grafik Produk Terlaris -->
      <section class="col-lg-6">
        <div class="box box-info">
          <div class="box-header"><h3 class="box-title">Top 5 Produk Terlaris</h3></div>
          <div class="box-body">
            <canvas id="grafikProduk" height="180"></canvas>
          </div>
        </div>
      </section>
    </div>

    <!-- ====== STATISTIK TAMBAHAN ====== -->
    <div class="row">
      <!-- Produk Terjual -->
      <div class="col-md-4">
        <div class="info-box bg-light shadow-sm">
          <span class="info-box-icon bg-green"><i class="fa fa-shopping-bag"></i></span>
          <div class="info-box-content">
            <span class="info-box-text">Produk Terjual Bulan Ini</span>
            <span class="info-box-number">
              <?php 
              $bulan_ini = date('m');
              $sold = mysqli_fetch_assoc(mysqli_query($koneksi,"
                SELECT SUM(d.jumlah) AS jml 
                FROM invoice_detail d 
                JOIN invoice i ON d.invoice_id=i.invoice_id 
                WHERE i.invoice_status IN ('5','selesai') 
                AND MONTH(i.invoice_tanggal)='$bulan_ini'
              "));
              echo number_format($sold['jml'] ?? 0);
              ?>
            </span>
          </div>
        </div>
      </div>

      <!-- Customer Baru Bulan Ini -->
      <div class="col-md-4">
        <div class="info-box bg-light shadow-sm">
          <span class="info-box-icon bg-aqua"><i class="fa fa-user-plus"></i></span>
          <div class="info-box-content">
            <span class="info-box-text">Customer Baru Bulan Ini</span>
            <span class="info-box-number">
              <?php 
              // Jika tidak ada kolom tanggal daftar, tampilkan total id terbaru bulan ini (dari invoice)
              $cust_baru = mysqli_fetch_assoc(mysqli_query($koneksi,"
                SELECT COUNT(DISTINCT invoice_customer) AS total
                FROM invoice 
                WHERE MONTH(invoice_tanggal)=MONTH(CURRENT_DATE())
              "));
              echo $cust_baru['total'] ?? 0;
              ?>
            </span>
          </div>
        </div>
      </div>

      <!-- Transaksi Bulan Ini -->
      <div class="col-md-4">
        <div class="info-box bg-light shadow-sm">
          <span class="info-box-icon bg-yellow"><i class="fa fa-line-chart"></i></span>
          <div class="info-box-content">
            <span class="info-box-text">Transaksi Bulan Ini</span>
            <span class="info-box-number">
              <?php 
              $trx_month = mysqli_fetch_assoc(mysqli_query($koneksi,"
                SELECT COUNT(*) AS total FROM invoice 
                WHERE MONTH(invoice_tanggal)=MONTH(CURRENT_DATE()) 
                AND invoice_status IN ('5','selesai')
              "));
              echo $trx_month['total'] ?? 0;
              ?>
            </span>
          </div>
        </div>
      </div>
    </div>

  </section>
</div>

<?php include 'footer.php'; ?>

<!-- ====== SCRIPTS ====== -->
<script src="https://cdn.jsdelivr.net/npm/chart.js"></script>

<!-- Grafik Pendapatan -->
<script>
var ctx1 = document.getElementById('grafikPendapatan').getContext('2d');
new Chart(ctx1, {
  type: 'line',
  data: {
    labels: ['Jan','Feb','Mar','Apr','Mei','Jun','Jul','Agu','Sep','Okt','Nov','Des'],
    datasets: [{
      label: 'Pendapatan (Rp)',
      borderColor: 'rgba(54, 162, 235, 1)',
      backgroundColor: 'rgba(54,162,235,0.2)',
      borderWidth: 3,
      fill: true,
      data: [
        <?php
        for($b=1;$b<=12;$b++){
          $r = mysqli_fetch_assoc(mysqli_query($koneksi,"
            SELECT SUM(invoice_total_bayar) AS total 
            FROM invoice WHERE invoice_status IN ('5','selesai') 
            AND MONTH(invoice_tanggal)='$b'
          "));
          echo ($r['total'] ?? 0).",";
        }
        ?>
      ]
    }]
  },
  options: {
    responsive: true,
    scales: { y: { beginAtZero: true } },
    plugins: {
      legend: { display: false },
      tooltip: {
        callbacks: {
          label: function(ctx){
            return 'Rp ' + ctx.parsed.y.toLocaleString('id-ID');
          }
        }
      }
    }
  }
});
</script>

<!-- Grafik Produk Terlaris -->
<?php
$q = mysqli_query($koneksi,"
  SELECT p.produk_nama, COALESCE(SUM(d.jumlah),0) AS total 
  FROM produk p
  LEFT JOIN invoice_detail d ON p.produk_id=d.produk_id
  LEFT JOIN invoice i ON i.invoice_id=d.invoice_id AND i.invoice_status IN ('5','selesai')
  GROUP BY p.produk_id
  ORDER BY total DESC LIMIT 5
");
$produk_labels = [];
$produk_values = [];
while($r=mysqli_fetch_assoc($q)){
  $produk_labels[] = $r['produk_nama'];
  $produk_values[] = (int)$r['total'];
}
?>
<script>
var ctx2 = document.getElementById('grafikProduk').getContext('2d');
new Chart(ctx2, {
  type: 'bar',
  data: {
    labels: <?= json_encode($produk_labels); ?>,
    datasets: [{
      label: 'Produk Terlaris',
      backgroundColor: 'rgba(255, 206, 86, 0.8)',
      data: <?= json_encode($produk_values); ?>
    }]
  },
  options: {
    responsive: true,
    plugins: { legend: { display: false } },
    scales: { y: { beginAtZero: true } }
  }
});
</script>
