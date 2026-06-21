<?php include 'header.php'; ?>
<div class="content-wrapper">
  <section class="content-header">
    <h1>
      Keluhan Customer
      <small>Daftar, Balasan & Hapus Data</small>
    </h1>
  </section>

  <section class="content">

    <div class="box box-info shadow-sm">
      <div class="box-header with-border d-flex justify-content-between align-items-center">
        <h3 class="box-title"><i class="fa fa-comments"></i> Data Keluhan Customer</h3>

        <div class="pull-right d-flex align-items-center">
          <!-- 🔹 Filter Waktu -->
          <form method="get" class="form-inline" style="margin-right: 10px;">
            <select name="filter" class="form-control input-sm" onchange="this.form.submit()">
              <option value="semua" <?= (!isset($_GET['filter']) || $_GET['filter']=='semua') ? 'selected' : ''; ?>>Semua Waktu</option>
              <option value="hari" <?= (isset($_GET['filter']) && $_GET['filter']=='hari') ? 'selected' : ''; ?>>Hari Ini</option>
              <option value="minggu" <?= (isset($_GET['filter']) && $_GET['filter']=='minggu') ? 'selected' : ''; ?>>Minggu Ini</option>
              <option value="bulan" <?= (isset($_GET['filter']) && $_GET['filter']=='bulan') ? 'selected' : ''; ?>>Bulan Ini</option>
            </select>
          </form>

          <!-- Tombol kirim pesan baru -->
          <button class="btn btn-success btn-sm" data-toggle="modal" data-target="#modalPesanBaru">
            <i class="fa fa-plus"></i> Kirim Pesan Baru
          </button>
        </div>
      </div>

      <div class="box-body table-responsive">
        <table class="table table-bordered table-striped" id="table-datatable">
          <thead>
            <tr class="text-center bg-light-blue">
              <th width="5%">No</th>
              <th width="15%">Customer</th>
              <th width="25%">Pesan Customer</th>
              <th width="12%">Tanggal</th>
              <th width="8%">Status</th>
              <th width="25%">Balasan Admin</th>
              <th width="15%">Aksi</th>
            </tr>
          </thead>
          <tbody>
            <?php
            include '../koneksi.php';
            $no = 1;

            // 🔹 Filter Waktu
            $filter = isset($_GET['filter']) ? $_GET['filter'] : 'semua';
            $where = "";

            if ($filter == "hari") {
              $where = "WHERE DATE(k.tanggal) = CURDATE()";
            } elseif ($filter == "minggu") {
              $where = "WHERE YEARWEEK(k.tanggal, 1) = YEARWEEK(CURDATE(), 1)";
            } elseif ($filter == "bulan") {
              $where = "WHERE MONTH(k.tanggal) = MONTH(CURDATE()) AND YEAR(k.tanggal) = YEAR(CURDATE())";
            }

            // 🔹 Query dengan filter
            $data = mysqli_query($koneksi,"
              SELECT k.*, c.customer_nama 
              FROM keluhan k 
              LEFT JOIN customer c ON k.id_customer = c.customer_id 
              $where
              ORDER BY k.tanggal DESC
            ");
            while($d = mysqli_fetch_assoc($data)):
            ?>
            <tr>
              <td class="text-center"><?= $no++; ?></td>
              <td><strong><?= htmlspecialchars($d['customer_nama']); ?></strong></td>
              <td><?= nl2br(htmlspecialchars($d['pesan'] ?: '-')); ?></td>
              <td class="text-center"><?= date('d-m-Y H:i', strtotime($d['tanggal'])); ?></td>
              <td class="text-center">
                <?php if($d['status'] == 'baru'): ?>
                  <span class="label label-warning"><i class="fa fa-exclamation-circle"></i> Baru</span>
                <?php elseif($d['status'] == 'dibaca'): ?>
                  <span class="label label-info"><i class="fa fa-eye"></i> Dibaca</span>
                <?php else: ?>
                  <span class="label label-success"><i class="fa fa-check-circle"></i> Ditanggapi</span>
                <?php endif; ?>
              </td>
              <td>
                <?php if(!empty($d['balasan'])): ?>
                  <div class="well well-sm" style="margin-bottom: 5px; background: #f9f9f9;">
                    <strong><?= htmlspecialchars($d['balasan']); ?></strong><br>
                    <small class="text-muted">
                      <i class="fa fa-clock-o"></i> <?= date('d-m-Y H:i', strtotime($d['tanggal_balasan'])); ?>
                    </small>
                  </div>
                <?php else: ?>
                  <em class="text-muted">Belum ada balasan</em>
                <?php endif; ?>
              </td>
              <td class="text-center">
                <button class="btn btn-sm btn-primary" data-toggle="modal" data-target="#modalBalas<?= $d['id_keluhan']; ?>">
                  <i class="fa fa-reply"></i>
                </button>
                <button class="btn btn-sm btn-danger" data-toggle="modal" data-target="#modalHapus<?= $d['id_keluhan']; ?>">
                  <i class="fa fa-trash"></i>
                </button>
              </td>
            </tr>

            <!-- MODAL BALAS -->
            <div class="modal fade" id="modalBalas<?= $d['id_keluhan']; ?>" tabindex="-1" role="dialog">
              <div class="modal-dialog modal-lg" role="document">
                <div class="modal-content">
                  <div class="modal-header bg-primary">
                    <button type="button" class="close" data-dismiss="modal">&times;</button>
                    <h4 class="modal-title text-white">
                      <i class="fa fa-reply"></i> Balas Keluhan - <?= htmlspecialchars($d['customer_nama']); ?>
                    </h4>
                  </div>
                  <form method="post" action="keluhan_act.php">
                    <div class="modal-body">
                      <input type="hidden" name="id_keluhan" value="<?= $d['id_keluhan']; ?>">
                      <div class="form-group">
                        <label>Pesan Customer:</label>
                        <textarea class="form-control" rows="3" readonly><?= htmlspecialchars($d['pesan']); ?></textarea>
                      </div>
                      <div class="form-group">
                        <label>Balasan Admin:</label>
                        <textarea name="balasan" class="form-control" rows="3" required placeholder="Tulis balasan di sini..."><?= htmlspecialchars($d['balasan']); ?></textarea>
                      </div>
                    </div>
                    <div class="modal-footer">
                      <button type="button" class="btn btn-default" data-dismiss="modal">Tutup</button>
                      <button type="submit" class="btn btn-primary"><i class="fa fa-paper-plane"></i> Kirim Balasan</button>
                    </div>
                  </form>
                </div>
              </div>
            </div>

            <!-- MODAL HAPUS -->
            <div class="modal fade" id="modalHapus<?= $d['id_keluhan']; ?>" tabindex="-1" role="dialog">
              <div class="modal-dialog modal-sm" role="document">
                <div class="modal-content">
                  <div class="modal-header bg-danger text-white">
                    <button type="button" class="close" data-dismiss="modal">&times;</button>
                    <h4 class="modal-title"><i class="fa fa-trash"></i> Hapus Keluhan</h4>
                  </div>
                  <form method="post" action="keluhan_hapus.php">
                    <div class="modal-body text-center">
                      <input type="hidden" name="id_keluhan" value="<?= $d['id_keluhan']; ?>">
                      <p>Apakah kamu yakin ingin menghapus keluhan dari:</p>
                      <strong><?= htmlspecialchars($d['customer_nama']); ?></strong><br><br>
                      <small class="text-muted"><?= htmlspecialchars($d['pesan']); ?></small>
                    </div>
                    <div class="modal-footer text-center">
                      <button type="button" class="btn btn-default" data-dismiss="modal">Batal</button>
                      <button type="submit" class="btn btn-danger"><i class="fa fa-trash"></i> Hapus</button>
                    </div>
                  </form>
                </div>
              </div>
            </div>

            <?php endwhile; ?>
          </tbody>
        </table>
      </div>
    </div>

  </section>
</div>

<!-- 🔹 MODAL PESAN BARU -->
<div class="modal fade" id="modalPesanBaru" tabindex="-1" role="dialog">
  <div class="modal-dialog modal-lg" role="document">
    <div class="modal-content">
      <div class="modal-header bg-success text-white">
        <button type="button" class="close" data-dismiss="modal">&times;</button>
        <h4 class="modal-title"><i class="fa fa-envelope"></i> Kirim Pesan Baru ke Customer</h4>
      </div>
      <form method="post" action="keluhan_admin_pesan.php">
        <div class="modal-body">
          <div class="form-group">
            <label>Pilih Customer:</label>
            <select name="id_customer" class="form-control" required>
              <option value="">-- Pilih Customer --</option>
              <?php
              $cust = mysqli_query($koneksi, "SELECT * FROM customer ORDER BY customer_nama ASC");
              while($c = mysqli_fetch_assoc($cust)){
                  echo "<option value='{$c['customer_id']}'>".$c['customer_nama']."</option>";
              }
              ?>
            </select>
          </div>

          <div class="form-group">
            <label>Pesan dari Admin:</label>
            <textarea name="balasan" class="form-control" rows="3" required placeholder="Tulis pesan ke customer..."></textarea>
          </div>
        </div>
        <div class="modal-footer">
          <button type="button" class="btn btn-default" data-dismiss="modal">Tutup</button>
          <button type="submit" class="btn btn-success"><i class="fa fa-paper-plane"></i> Kirim Pesan</button>
        </div>
      </form>
    </div>
  </div>
</div>

<?php include 'footer.php'; ?>
