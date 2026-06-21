<?php 
include 'header.php'; 
?>

<div class="content-wrapper">
  <section class="content-header">
    <h1>
      Grafik Penjualan
      <small>Owner</small>
    </h1>
    <ol class="breadcrumb">
      <li><a href="index.php"><i class="fa fa-dashboard"></i> Dashboard</a></li>
      <li class="active">Grafik Penjualan</li>
    </ol>
  </section>

  <section class="content">
    <div class="box box-success">
      <div class="box-header with-border d-flex justify-content-between">
        <h3 class="box-title">📊 Statistik Penjualan</h3>
        <form method="get" class="form-inline" style="float: right;">
          <label class="mr-2">Lihat Berdasarkan:</label>
          <select name="filter" onchange="this.form.submit()" class="form-control">
            <option value="hari" <?= ($_GET['filter'] ?? '') == 'hari' ? 'selected' : '' ?>>Per Hari</option>
            <option value="minggu" <?= ($_GET['filter'] ?? '') == 'minggu' ? 'selected' : '' ?>>Per Minggu</option>
            <option value="bulan" <?= ($_GET['filter'] ?? '') == 'bulan' ? 'selected' : '' ?>>Per Bulan</option>
            <option value="tahun" <?= ($_GET['filter'] ?? '') == 'tahun' ? 'selected' : '' ?>>Per Tahun</option>
          </select>
        </form>
      </div>

      <div class="box-body">
        <?php
        include '../koneksi.php';
        $filter = $_GET['filter'] ?? 'bulan';
        $label = "";
        $data_penjualan = "";

        if($filter == "hari"){
          $label = "Per Hari";
          $query = "
            SELECT DATE(invoice_tanggal) AS periode, SUM(invoice_total_bayar) AS total 
            FROM invoice 
            WHERE invoice_status IN ('5','selesai')
            GROUP BY DATE(invoice_tanggal)
            ORDER BY DATE(invoice_tanggal)";
        } elseif($filter == "minggu"){
          $label = "Per Minggu";
          $query = "
            SELECT YEARWEEK(invoice_tanggal,1) AS periode, SUM(invoice_total_bayar) AS total 
            FROM invoice 
            WHERE invoice_status IN ('5','selesai')
            GROUP BY YEARWEEK(invoice_tanggal,1)
            ORDER BY YEARWEEK(invoice_tanggal,1)";
        } elseif($filter == "tahun"){
          $label = "Per Tahun";
          $query = "
            SELECT YEAR(invoice_tanggal) AS periode, SUM(invoice_total_bayar) AS total 
            FROM invoice 
            WHERE invoice_status IN ('5','selesai')
            GROUP BY YEAR(invoice_tanggal)
            ORDER BY YEAR(invoice_tanggal)";
        } else {
          $label = "Per Bulan";
          $query = "
            SELECT DATE_FORMAT(invoice_tanggal, '%Y-%m') AS periode, SUM(invoice_total_bayar) AS total 
            FROM invoice 
            WHERE invoice_status IN ('5','selesai')
            GROUP BY DATE_FORMAT(invoice_tanggal, '%Y-%m')
            ORDER BY DATE_FORMAT(invoice_tanggal, '%Y-%m')";
        }

        $data = mysqli_query($koneksi, $query);
        $periode = [];
        $total = [];
        $total_semua = 0;
        while($d = mysqli_fetch_assoc($data)){
          $periode[] = $d['periode'];
          $total[] = $d['total'];
          $total_semua += $d['total'];
        }
        ?>

        <div class="row mb-4">
          <div class="col-md-4">
            <div class="small-box bg-green">
              <div class="inner">
                <h3>Rp <?= number_format($total_semua,0,',','.'); ?></h3>
                <p>Total Penjualan <?= $label; ?></p>
              </div>
              <div class="icon">
                <i class="fa fa-shopping-cart"></i>
              </div>
            </div>
          </div>
        </div>

        <canvas id="grafikPenjualan" height="120"></canvas>
      </div>
    </div>
  </section>
</div>

<script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
<script>
const ctx = document.getElementById('grafikPenjualan').getContext('2d');
const data = {
  labels: <?= json_encode($periode); ?>,
  datasets: [{
    label: 'Total Penjualan (Rp)',
    data: <?= json_encode($total); ?>,
    fill: true,
    backgroundColor: 'rgba(54, 162, 235, 0.4)',
    borderColor: 'rgba(54, 162, 235, 1)',
    borderWidth: 3,
    tension: 0.3,
    pointBackgroundColor: '#1E88E5',
    pointRadius: 5,
  }]
};

const options = {
  responsive: true,
  plugins: {
    legend: { display: true, position: 'top' },
    tooltip: {
      callbacks: {
        label: function(context){
          return 'Rp ' + context.parsed.y.toLocaleString('id-ID');
        }
      }
    }
  },
  scales: {
    y: {
      beginAtZero: true,
      ticks: {
        callback: function(value){ return 'Rp ' + value.toLocaleString('id-ID'); }
      }
    },
    x: {
      ticks: { color: '#333' }
    }
  },
  animation: {
    duration: 1500,
    easing: 'easeOutQuart'
  }
};

new Chart(ctx, {
  type: 'line',
  data: data,
  options: options
});
</script>

<?php include 'footer.php'; ?>
