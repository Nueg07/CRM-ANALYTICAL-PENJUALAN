<?php include 'header.php'; ?>

<div class="content-wrapper">
  <section class="content-header">
    <h1>
      Analisis Penjualan (OLAP)
      <small>CRM Analytical - Panorama Elektronik</small>
    </h1>
  </section>

  <section class="content">

    <?php
    // ======== FILTER BULAN & TAHUN ==========
    $bulan = isset($_GET['bulan']) ? $_GET['bulan'] : date('m');
    $tahun = isset($_GET['tahun']) ? $_GET['tahun'] : date('Y');

    // sanitasi sederhana
    $bulan = mysqli_real_escape_string($koneksi, $bulan);
    $tahun = mysqli_real_escape_string($koneksi, $tahun);

    // nama bulan untuk tampilan
    $nama_bulan = [
      1 => "Januari", "Februari", "Maret", "April", "Mei", "Juni",
      "Juli", "Agustus", "September", "Oktober", "November", "Desember"
    ];

    // ======== QUERY ANALISIS =========

    // 1️⃣ Total Penjualan per Hari di Bulan Tersebut
    $sql_penjualan = "
        SELECT DAY(invoice_tanggal) AS hari,
               SUM(invoice_total_bayar) AS total_penjualan
        FROM invoice
        WHERE MONTH(invoice_tanggal) = '".(int)$bulan."'
          AND YEAR(invoice_tanggal) = '".(int)$tahun."'
          AND invoice_status = 5
        GROUP BY DAY(invoice_tanggal)
        ORDER BY hari ASC
    ";
    $q_penjualan = mysqli_query($koneksi, $sql_penjualan);
    $penjualan_labels = $penjualan_data = [];
    if ($q_penjualan && mysqli_num_rows($q_penjualan) > 0) {
      while ($r = mysqli_fetch_assoc($q_penjualan)) {
        $penjualan_labels[] = 'Hari '.$r['hari'];
        $penjualan_data[] = (int)$r['total_penjualan'];
      }
    }

    // 2️⃣ Produk Terlaris
    $produk_labels = $produk_data = [];
    $q_produk = mysqli_query($koneksi, "
      SELECT p.produk_nama, SUM(ip.jumlah) AS total_terjual
      FROM invoice_detail ip
      JOIN invoice i ON ip.invoice_id = i.invoice_id
      JOIN produk p ON ip.produk_id = p.produk_id
      WHERE MONTH(i.invoice_tanggal) = '".(int)$bulan."'
        AND YEAR(i.invoice_tanggal) = '".(int)$tahun."'
        AND i.invoice_status = 5
      GROUP BY ip.produk_id
      ORDER BY total_terjual DESC
      LIMIT 5
    ");
    while($r = mysqli_fetch_assoc($q_produk)){
      $produk_labels[] = $r['produk_nama'];
      $produk_data[] = (int)$r['total_terjual'];
    }

    // 3️⃣ Pelanggan Aktif
    $sql_pelanggan = "
      SELECT c.customer_id, c.customer_nama,
             COUNT(i.invoice_id) AS jumlah_transaksi,
             SUM(i.invoice_total_bayar) AS total_belanja,
             c.customer_diskon
      FROM invoice i
      JOIN customer c ON i.invoice_customer = c.customer_id
      WHERE MONTH(i.invoice_tanggal) = '".(int)$bulan."'
        AND YEAR(i.invoice_tanggal) = '".(int)$tahun."'
        AND i.invoice_status = 5
      GROUP BY i.invoice_customer
      ORDER BY total_belanja DESC
      LIMIT 5
    ";
    $q_pelanggan = mysqli_query($koneksi, $sql_pelanggan);
    $top_customer = null;
    if($q_pelanggan && mysqli_num_rows($q_pelanggan) > 0){
        mysqli_data_seek($q_pelanggan,0);
        $top_customer = mysqli_fetch_assoc($q_pelanggan);
    }

    // ========== TAMBAHAN ==========

    // 4️⃣ Total omzet
    $sql_total_omzet = "
        SELECT COALESCE(SUM(invoice_total_bayar),0) AS omzet
        FROM invoice
        WHERE MONTH(invoice_tanggal) = '".(int)$bulan."'
          AND YEAR(invoice_tanggal) = '".(int)$tahun."'
          AND invoice_status = 5
    ";
    $q_omzet = mysqli_query($koneksi, $sql_total_omzet);
    $omzet = 0;
    if ($q_omzet) {
        $res = mysqli_fetch_assoc($q_omzet);
        $omzet = (float) ($res['omzet'] ?? 0);
    }

    // 5️⃣ Perbandingan dengan bulan lalu
    $b = (int)$bulan;
    $y = (int)$tahun;
    $bulan_lalu = $b - 1;
    $tahun_lalu = $y;
    if ($bulan_lalu == 0) {
        $bulan_lalu = 12;
        $tahun_lalu = $y - 1;
    }
    $sql_omzet_lalu = "
        SELECT COALESCE(SUM(invoice_total_bayar),0) AS omzet_lalu
        FROM invoice
        WHERE MONTH(invoice_tanggal) = '".(int)$bulan_lalu."'
          AND YEAR(invoice_tanggal) = '".(int)$tahun_lalu."'
          AND invoice_status = 5
    ";
    $q_omzet_lalu = mysqli_query($koneksi, $sql_omzet_lalu);
    $omzet_lalu = 0;
    if ($q_omzet_lalu) {
        $res2 = mysqli_fetch_assoc($q_omzet_lalu);
        $omzet_lalu = (float) ($res2['omzet_lalu'] ?? 0);
    }
    $persentase_perubahan = 0;
    if ($omzet_lalu > 0) {
        $persentase_perubahan = (($omzet - $omzet_lalu) / $omzet_lalu) * 100;
    }

    // 6️⃣ OLAP matrix
    $sql_matrix = "
        SELECT p.produk_nama,
               DAY(i.invoice_tanggal) AS hari,
               SUM(ip.jumlah) AS total_jual
        FROM invoice_detail ip
        JOIN invoice i ON ip.invoice_id = i.invoice_id
        JOIN produk p ON ip.produk_id = p.produk_id
        WHERE MONTH(i.invoice_tanggal) = '".(int)$bulan."'
          AND YEAR(i.invoice_tanggal) = '".(int)$tahun."'
          AND i.invoice_status = 5
        GROUP BY ip.produk_id, DAY(i.invoice_tanggal)
        ORDER BY p.produk_nama, hari
    ";
    $q_matrix = mysqli_query($koneksi, $sql_matrix);
    $matrix = [];
    if ($q_matrix && mysqli_num_rows($q_matrix) > 0) {
        while ($m = mysqli_fetch_assoc($q_matrix)) {
            $produk_n = $m['produk_nama'];
            $hari_n = (int)$m['hari'];
            $matrix[$produk_n][$hari_n] = (int)$m['total_jual'];
        }
    }
    ?>

    <form method="get" class="form-inline" style="margin-bottom:10px">
      <div class="form-group">
        <label style="margin-right:8px">Bulan: </label>
        <select name="bulan" class="form-control" required>
          <?php
          for ($i = 1; $i <= 12; $i++) {
            $val = sprintf('%02d', $i);
            $selected = ($bulan == $val || $bulan == $i) ? 'selected' : '';
            echo "<option value='$val' $selected>{$nama_bulan[$i]}</option>";
          }
          ?>
        </select>
      </div>

      <div class="form-group" style="margin-left:10px">
        <label style="margin-right:8px">Tahun: </label>
        <select name="tahun" class="form-control" required>
          <?php
          $tahun_sekarang = date('Y');
          for ($t = 2020; $t <= $tahun_sekarang; $t++) {
            $selected = ($tahun == $t) ? 'selected' : '';
            echo "<option value='$t' $selected>$t</option>";
          }
          ?>
        </select>
      </div>

      <button type="submit" class="btn btn-primary" style="margin-left:10px">
        <i class="fa fa-search"></i> Tampilkan
      </button>
    </form>

    <style>
      /* inline kecil supaya tampil compact pada satu layar */
      .charts-row { display:flex; gap:12px; align-items:stretch; flex-wrap:wrap; }
      .chart-card { flex:1 1 48%; min-width:320px; }
      .chart-wrapper { height:300px; display:flex; align-items:center; justify-content:center; }
      .chart-canvas { width:100% !important; height:100% !important; }
      @media(max-width:900px){
        .chart-card { flex:1 1 100%; }
      }
    </style>

    <div class="charts-row" style="margin-bottom:12px;">
      <!-- PENJUALAN (LEFT, lebih lebar) -->
      <div class="chart-card">
        <div class="box box-primary">
          <div class="box-header with-border">
            <h3 class="box-title">Grafik Penjualan per Hari (<?= htmlspecialchars($nama_bulan[(int)$bulan]).' '.htmlspecialchars($tahun) ?>)</h3>
          </div>
          <div class="box-body chart-wrapper" id="penjualan-wrapper">
            <canvas id="chartPenjualan" class="chart-canvas"></canvas>
          </div>
        </div>
      </div>

      <!-- PRODUK TERLARIS (RIGHT) -->
      <div class="chart-card">
        <div class="box box-success">
          <div class="box-header with-border">
            <h3 class="box-title">Top Produk Terlaris</h3>
          </div>
          <div class="box-body chart-wrapper" id="produk-wrapper">
            <?php if(count($produk_labels) > 0): ?>
              <canvas id="chartProduk" class="chart-canvas"></canvas>
            <?php else: ?>
              <div class="alert alert-info">Tidak ada data produk terlaris untuk periode ini.</div>
            <?php endif; ?>
          </div>
        </div>
      </div>
    </div>

    <!-- PELANGGAN & ANALISIS RINGKAS (tampil berdampingan) -->
    <div class="row" style="margin-bottom:12px;">
      <div class="col-md-6">
        <div class="box box-warning">
          <div class="box-header with-border"><h3 class="box-title">Top 5 Pelanggan Paling Aktif</h3></div>
          <div class="box-body table-responsive" style="max-height:260px; overflow:auto;">
            <table class="table table-bordered table-striped" style="margin-bottom:0">
              <thead>
                <tr>
                  <th>Nama</th>
                  <th>Transaksi</th>
                  <th>Total</th>
                </tr>
              </thead>
              <tbody>
                <?php
                if ($q_pelanggan && mysqli_num_rows($q_pelanggan) > 0) {
                  mysqli_data_seek($q_pelanggan,0);
                  while ($p = mysqli_fetch_assoc($q_pelanggan)) {
                    echo '<tr>
                            <td>'.htmlspecialchars($p['customer_nama']).'</td>
                            <td>'.$p['jumlah_transaksi'].'</td>
                            <td>Rp '.number_format($p['total_belanja']).'</td>
                          </tr>';
                  }
                } else {
                  echo '<tr><td colspan="3" class="text-center">Tidak ada data transaksi bulan ini.</td></tr>';
                }
                ?>
              </tbody>
            </table>
          </div>
          <?php
            // tombol Kirim Promo hanya kalau ada top customer
            if ($top_customer) {
              echo '<div class="box-footer">';
              echo '<button class="btn btn-success btn-sm" data-toggle="modal" data-target="#modalPromoTop"><i class="fa fa-gift"></i> Kirim Promo ke '.$top_customer['customer_nama'].'</button>';
              echo '</div>';
            }
          ?>
        </div>
      </div>

      <div class="col-md-6">
        <div class="box box-info">
          <div class="box-header with-border"><h3 class="box-title">Hasil Analisis Singkat</h3></div>
          <div class="box-body" style="max-height:260px; overflow:auto;">
            <?php
              if(count($produk_labels)>0){
                echo "<p>Produk terlaris bulan ini adalah <strong>".$produk_labels[0]."</strong> dengan jumlah terjual ".$produk_data[0]." unit.</p>";
              } else {
                echo "<p>Tidak ada produk terlaris bulan ini.</p>";
              }

              if ($top_customer) {
                echo "<p>Pelanggan terbanyak bulan ini: <strong>".$top_customer['customer_nama']."</strong> (Rp ".number_format($top_customer['total_belanja']).").</p>";
              } else {
                echo "<p>Tidak ada pelanggan aktif bulan ini.</p>";
              }

              echo "<hr style='margin:8px 0'>";
              echo "<p><strong>Total omzet bulan ini:</strong> Rp ".number_format($omzet)."</p>";
              if ($omzet_lalu > 0) {
                  $arah = $persentase_perubahan >= 0 ? "naik" : "turun";
                  echo "<p><strong>Perbandingan dengan bulan lalu (".htmlspecialchars($nama_bulan[$bulan_lalu])." ".$tahun_lalu."):</strong> Omzet $arah ".round(abs($persentase_perubahan),2)."%</p>";
              } else {
                  echo "<p><strong>Perbandingan bulan lalu:</strong> Tidak ada data.</p>";
              }
            ?>
          </div>
        </div>
      </div>
    </div>

    <!-- MODAL PROMO -->
    <?php if($top_customer): ?>
    <div class="modal fade" id="modalPromoTop" tabindex="-1" role="dialog">
      <div class="modal-dialog modal-lg" role="document">
        <div class="modal-content">
          <div class="modal-header bg-success text-white">
            <button type="button" class="close" data-dismiss="modal">&times;</button>
            <h4 class="modal-title"><i class="fa fa-gift"></i> Kirim Promo ke <?= htmlspecialchars($top_customer['customer_nama']); ?></h4>
          </div>
          <form method="post" action="keluhan_admin_pesan.php">
            <div class="modal-body">
              <input type="hidden" name="id_customer" value="<?= $top_customer['customer_id']; ?>">
              <div class="form-group">
                <label>Pesan Promo</label>
                <?php
                  $promo_template = "Selamat! 🎉 Anda adalah pelanggan paling aktif bulan ini. Sebagai apresiasi, kami memberikan promo spesial untuk Anda. Silakan hubungi kami untuk info lebih lanjut. Terima kasih!";
                ?>
                <textarea name="pesan" class="form-control" rows="5" required><?= htmlspecialchars($promo_template); ?></textarea>
              </div>
            </div>
            <div class="modal-footer">
              <button type="button" class="btn btn-default" data-dismiss="modal">Batal</button>
              <button type="submit" class="btn btn-success"><i class="fa fa-paper-plane"></i> Kirim Promo</button>
            </div>
          </form>
        </div>
      </div>
    </div>
    <?php endif; ?>

    <!-- OLAP MATRIX (collapse agar tidak memanjang halaman) -->
    <div class="box box-default">
      <div class="box-header with-border">
        <h3 class="box-title">Analisis OLAP Produk × Hari</h3>
        <div class="box-tools">
          <button type="button" class="btn btn-box-tool" data-toggle="collapse" data-target="#olap-body" aria-expanded="false" aria-controls="olap-body">
            <i class="fa fa-chevron-down"></i> Tampilkan/Tutup
          </button>
        </div>
      </div>
      <div id="olap-body" class="box-body collapse">
        <?php if (!empty($matrix)): ?>
        <div class="table-responsive" style="max-height:360px; overflow:auto;">
          <table class="table table-bordered">
            <thead>
              <tr>
                <th>Produk</th>
                <?php for ($h=1; $h<=31; $h++): ?>
                  <th style="min-width:30px;"><?= $h ?></th>
                <?php endfor; ?>
                <th>Total</th>
              </tr>
            </thead>
            <tbody>
              <?php foreach ($matrix as $produk_name => $hariList):
                $rowTotal = 0;
              ?>
              <tr>
                <td><strong><?= htmlspecialchars($produk_name) ?></strong></td>
                <?php for ($h=1; $h<=31; $h++):
                  $val = isset($hariList[$h]) ? (int)$hariList[$h] : 0;
                  $rowTotal += $val;
                ?>
                  <td><?= $val ?></td>
                <?php endfor; ?>
                <td><strong><?= $rowTotal ?></strong></td>
              </tr>
              <?php endforeach; ?>
            </tbody>
          </table>
        </div>
        <?php else: ?>
          <div class="alert alert-info" style="margin:10px">Analisis OLAP Produk × Hari: Tidak ada data untuk periode ini.</div>
        <?php endif; ?>
      </div>
    </div>

  </section>
</div>

<script src="https://cdn.jsdelivr.net/npm/chart.js"></script>

<script>
  // Utility: safe JSON data from PHP
  const penjualanLabels = <?= json_encode($penjualan_labels ?: []) ?>;
  const penjualanData   = <?= json_encode($penjualan_data ?: []) ?>;
  const produkLabels    = <?= json_encode($produk_labels ?: []) ?>;
  const produkData      = <?= json_encode($produk_data ?: []) ?>;

  // === GRAFIK PENJUALAN (BAR CHART) ===
  (function(){
    const canvas = document.getElementById('chartPenjualan');
    const wrapper = document.getElementById('penjualan-wrapper');

    if (!canvas) return;

    // gunakan context agar kompatibel dan stabil
    const ctx = canvas.getContext('2d');

    if (!penjualanData || penjualanData.length === 0) {
      // tampilkan pesan di wrapper (bukan di dalam canvas)
      wrapper.innerHTML = "<div class='alert alert-info'>Tidak ada data penjualan untuk periode ini.</div>";
      return;
    }

    // clear wrapper content (canvas exists already) - ensure canvas visible
    wrapper.innerHTML = '<canvas id="chartPenjualan" class="chart-canvas"></canvas>';
    const canvasNew = document.getElementById('chartPenjualan');
    const ctxNew = canvasNew.getContext('2d');

    new Chart(ctxNew, {
      type: 'bar',
      data: {
        labels: penjualanLabels,
        datasets: [{
          label: 'Total Penjualan (Rp)',
          data: penjualanData,
          backgroundColor: penjualanData.map((v,i) => 'rgba(54,162,235,0.7)'),
          borderColor: penjualanData.map((v,i) => 'rgba(54,162,235,1)'),
          borderWidth: 1
        }]
      },
      options: {
        responsive: true,
        maintainAspectRatio: false,
        plugins: {
          tooltip: {
            callbacks: {
              label: function(context){
                // format rupiah simple
                const value = context.raw || 0;
                return 'Rp ' + value.toString().replace(/\B(?=(\d{3})+(?!\d))/g, ",");
              }
            }
          },
          legend: { display: false }
        },
        scales: {
          x: {
            ticks: { maxRotation: 0, autoSkip: true }
          },
          y: {
            beginAtZero: true,
            ticks: {
              callback: function(value) {
                // short format for y-axis
                if (value >= 1000000) return (value/1000000) + 'M';
                if (value >= 1000) return (value/1000) + 'K';
                return value;
              }
            }
          }
        }
      }
    });
  })();


  // === PRODUK TERLARIS (DONUT CHART yang mudah dibaca) ===
  (function(){
    const canvas = document.getElementById('chartProduk');
    const wrapper = document.getElementById('produk-wrapper');

    if (!canvas) return;

    if (!produkData || produkData.length === 0) {
      wrapper.innerHTML = "<div class='alert alert-info'>Tidak ada data produk terlaris untuk periode ini.</div>";
      return;
    }

    // create new canvas to avoid reuse issues
    wrapper.innerHTML = '<canvas id="chartProduk" class="chart-canvas"></canvas>';
    const ctx = document.getElementById('chartProduk').getContext('2d');

    new Chart(ctx, {
      type: 'doughnut',
      data: {
        labels: produkLabels,
        datasets: [{
          data: produkData,
          backgroundColor: ['#3498db','#2ecc71','#f1c40f','#e74c3c','#9b59b6'],
          hoverOffset: 6
        }]
      },
      options: {
        responsive: true,
        maintainAspectRatio: false,
        plugins: {
          legend: { position: 'bottom' },
          tooltip: {
            callbacks: {
              label: function(context){
                const label = context.label || '';
                const value = context.raw || 0;
                return label + ': ' + value + ' unit';
              }
            }
          }
        }
      }
    });
  })();
</script>

<?php include 'footer.php'; ?>
