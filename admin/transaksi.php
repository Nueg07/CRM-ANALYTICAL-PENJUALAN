<?php include 'header.php'; ?>

<div class="content-wrapper">
  <section class="content-header">
    <h1>
      Transaksi
      <small>Kelola Pesanan Customer</small>
    </h1>
    <ol class="breadcrumb">
      <li><a href="#"><i class="fa fa-dashboard"></i> Home</a></li>
      <li class="active">Transaksi</li>
    </ol>
  </section>

  <section class="content">
    <div class="row">
      <section class="col-lg-12">
        <div class="box box-info">

          <div class="box-header with-border">
            <h3 class="box-title"><i class="fa fa-shopping-cart"></i> Daftar Pesanan</h3>
          </div>

          <div class="box-body">

            <!-- Alert notifikasi -->
            <?php if(isset($_GET['alert']) && $_GET['alert']=='selesai'): ?>
              <div class="alert alert-success">
                <i class="fa fa-check"></i> Transaksi berhasil diselesaikan dan stok produk telah diperbarui.
              </div>
            <?php endif; ?>

            <!-- Filter status + waktu -->
            <form method="GET" class="form-inline" style="margin-bottom:15px; display:flex; flex-wrap:wrap; gap:10px; align-items:center;">
              <label for="status" class="mr-2">Filter Status:</label>
              <select name="status" id="status" class="form-control">
                <option value="">-- Semua Status --</option>
                <option value="0" <?= (isset($_GET['status']) && $_GET['status']=='0') ? 'selected' : ''; ?>>Menunggu Pembayaran</option>
                <option value="1" <?= (isset($_GET['status']) && $_GET['status']=='1') ? 'selected' : ''; ?>>Menunggu Konfirmasi</option>
                <option value="3" <?= (isset($_GET['status']) && $_GET['status']=='3') ? 'selected' : ''; ?>>Diproses</option>
                <option value="4" <?= (isset($_GET['status']) && $_GET['status']=='4') ? 'selected' : ''; ?>>Dikirim</option>
                <option value="5" <?= (isset($_GET['status']) && $_GET['status']=='5') ? 'selected' : ''; ?>>Selesai</option>
                <option value="2" <?= (isset($_GET['status']) && $_GET['status']=='2') ? 'selected' : ''; ?>>Ditolak</option>
              </select>

              <label for="bulan" class="ml-3 mr-2">Bulan:</label>
              <select name="bulan" id="bulan" class="form-control">
                <option value="">-- Semua Bulan --</option>
                <?php
                $nama_bulan = [
                  1=>'Januari', 2=>'Februari', 3=>'Maret', 4=>'April', 5=>'Mei', 6=>'Juni',
                  7=>'Juli', 8=>'Agustus', 9=>'September', 10=>'Oktober', 11=>'November', 12=>'Desember'
                ];
                for($b=1;$b<=12;$b++):
                ?>
                  <option value="<?= $b ?>" <?= (isset($_GET['bulan']) && $_GET['bulan']==$b) ? 'selected' : ''; ?>>
                    <?= $nama_bulan[$b]; ?>
                  </option>
                <?php endfor; ?>
              </select>

              <label for="tahun" class="ml-3 mr-2">Tahun:</label>
              <select name="tahun" id="tahun" class="form-control">
                <option value="">-- Semua Tahun --</option>
                <?php
                $tahun_sekarang = date('Y');
                for($t=$tahun_sekarang; $t>=2020; $t--):
                ?>
                  <option value="<?= $t ?>" <?= (isset($_GET['tahun']) && $_GET['tahun']==$t) ? 'selected' : ''; ?>>
                    <?= $t ?>
                  </option>
                <?php endfor; ?>
              </select>

              <button type="submit" class="btn btn-primary ml-2">
                <i class="fa fa-filter"></i> Terapkan
              </button>

              <a href="transaksi.php" class="btn btn-default ml-2">
                <i class="fa fa-refresh"></i> Reset
              </a>
            </form>

            <div class="table-responsive">
              <table class="table table-bordered table-striped" id="table-datatable">
                <thead class="bg-light">
                  <tr class="text-center">
                    <th width="1%">No</th>
                    <th>No. Invoice</th>
                    <th>Tanggal</th>
                    <th>Customer</th>
                    <th>Kurir</th>
                    <th>Ongkir</th>
                    <th>Total Bayar</th>
                    <th>Status</th>
                    <th width="18%">Tindakan</th>
                  </tr>
                </thead>
                <tbody>
                  <?php 
                  $no = 1;

                  // Filter status
                  $filterStatus = isset($_GET['status']) && $_GET['status'] !== '' ? "i.invoice_status = '".intval($_GET['status'])."'" : "1=1";

                  // Filter bulan dan tahun
                  $filterBulan = isset($_GET['bulan']) && $_GET['bulan'] !== '' ? "MONTH(i.invoice_tanggal) = '".intval($_GET['bulan'])."'" : "1=1";
                  $filterTahun = isset($_GET['tahun']) && $_GET['tahun'] !== '' ? "YEAR(i.invoice_tanggal) = '".intval($_GET['tahun'])."'" : "1=1";

                  // Gabungkan semua filter
                  $whereClause = "WHERE $filterStatus AND $filterBulan AND $filterTahun";

                  // Ambil data invoice
                  $invoice = mysqli_query($koneksi, "
                    SELECT i.*, c.customer_nama 
                    FROM invoice i 
                    JOIN customer c ON c.customer_id = i.invoice_customer 
                    $whereClause
                    ORDER BY i.invoice_status ASC, i.invoice_id DESC
                  ");

                  $statusLabel = [
                    0 => ['Menunggu Pembayaran','label-warning','fa-clock-o'],
                    1 => ['Menunggu Konfirmasi','label-default','fa-hourglass-half'],
                    2 => ['Ditolak','label-danger','fa-times'],
                    3 => ['Diproses','label-primary','fa-cogs'],
                    4 => ['Dikirim','label-info','fa-truck'],
                    5 => ['Selesai','label-success','fa-check']
                  ];

                  if (mysqli_num_rows($invoice) == 0) {
                      echo "<tr><td colspan='9' class='text-center text-muted'>Tidak ada transaksi ditemukan untuk filter ini.</td></tr>";
                  }

                  while($i = mysqli_fetch_assoc($invoice)):
                    [$statusText, $labelClass, $icon] = $statusLabel[$i['invoice_status']] ?? ['Tidak Diketahui','label-default','fa-question'];
                    $ongkir = $i['invoice_ongkir'] ?? 0;
                  ?>
                  <tr>
                    <td class="text-center"><?= $no++; ?></td>
                    <td><strong>INV-<?= str_pad($i['invoice_id'], 4, '0', STR_PAD_LEFT); ?></strong></td>
                    <td><?= date('d/m/Y', strtotime($i['invoice_tanggal'])); ?></td>
                    <td><?= htmlspecialchars($i['customer_nama']); ?></td>
                    <td><?= htmlspecialchars($i['invoice_kurir'] ?? '-'); ?></td>
                    <td>Rp <?= number_format($ongkir,0,',','.'); ?></td>
                    <td><strong>Rp <?= number_format($i['invoice_total_bayar'],0,',','.'); ?></strong></td>
                    <td class="text-center">
                      <span class="label <?= $labelClass; ?>">
                        <i class="fa <?= $icon; ?>"></i> <?= $statusText; ?>
                      </span>
                    </td>
                    <td class="text-center">
                      <a href="transaksi_invoice.php?id=<?= $i['invoice_id']; ?>" class="btn btn-sm btn-success">
                        <i class="fa fa-print"></i> Invoice
                      </a>

                      <?php if($i['invoice_status'] == 0): ?>
                        <span class="btn btn-sm btn-secondary disabled"><i class="fa fa-clock-o"></i> Menunggu Pembayaran</span>

                      <?php elseif($i['invoice_status'] == 1): ?>
                        <a href="transaksi_proses.php?id=<?= $i['invoice_id']; ?>" class="btn btn-sm btn-primary" onclick="return confirm('Konfirmasi dan proses pesanan ini?')">
                          <i class="fa fa-cogs"></i> Proses
                        </a>

                        <a href="transaksi_tolak.php?id=<?= $i['invoice_id']; ?>" class="btn btn-sm btn-danger" onclick="return confirm('Tolak pesanan ini?')">
                          <i class="fa fa-times"></i> Tolak
                        </a>

                      <?php elseif($i['invoice_status'] == 3): ?>
                        <a href="transaksi_kirim.php?id=<?= $i['invoice_id']; ?>" class="btn btn-sm btn-info" onclick="return confirm('Kirim pesanan ini?')">
                          <i class="fa fa-truck"></i> Kirim
                        </a>

                      <?php elseif($i['invoice_status'] == 4): ?>
                        <a href="transaksi_selesai_aksi.php?id=<?= $i['invoice_id']; ?>" class="btn btn-sm btn-success" onclick="return confirm('Tandai pesanan ini selesai?')">
                          <i class="fa fa-check"></i> Selesai
                        </a>
                      <?php endif; ?>

                      <button type="button" class="btn btn-sm btn-warning" data-toggle="modal" data-target="#buktiPembayaran_<?= $i['invoice_id']; ?>">
                        <i class="fa fa-image"></i> Bukti
                      </button>

                      <a class="btn btn-sm btn-danger" href="transaksi_hapus.php?id=<?= $i['invoice_id']; ?>" onclick="return confirm('Yakin ingin menghapus transaksi ini?')">
                        <i class="fa fa-trash"></i>
                      </a>
                    </td>
                  </tr>

                  <!-- Modal bukti pembayaran -->
                  <div class="modal fade" id="buktiPembayaran_<?= $i['invoice_id']; ?>" tabindex="-1" role="dialog">
                    <div class="modal-dialog modal-dialog-centered">
                      <div class="modal-content">
                        <div class="modal-header">
                          <h4 class="modal-title"><i class="fa fa-image"></i> Bukti Pembayaran - INV<?= $i['invoice_id']; ?></h4>
                          <button type="button" class="close" data-dismiss="modal">&times;</button>
                        </div>
                        <div class="modal-body text-center">
                          <?php if(empty($i['invoice_bukti'])): ?>
                            <p class="text-muted">Belum ada bukti pembayaran.</p>
                          <?php else: ?>
                            <img src="../gambar/bukti_pembayaran/<?= $i['invoice_bukti']; ?>" class="img-responsive img-thumbnail" style="max-height:400px;">
                          <?php endif; ?>
                        </div>
                        <div class="modal-footer">
                          <button type="button" class="btn btn-secondary" data-dismiss="modal">Tutup</button>
                        </div>
                      </div>
                    </div>
                  </div>

                  <?php endwhile; ?>
                </tbody>
              </table>
            </div>
          </div>

        </div>
      </section>
    </div>
  </section>
</div>

<?php include 'footer.php'; ?>
