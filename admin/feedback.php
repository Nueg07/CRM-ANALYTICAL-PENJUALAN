<?php include 'header.php'; ?>
<div class="content-wrapper">
  <section class="content-header">
    <h1>
      Kepuasan Pelanggan
      <small>Analisis Feedback Customer</small>
    </h1>
  </section>

  <section class="content">

    <?php if (isset($_GET['alert'])): ?>
      <?php if ($_GET['alert'] == 'feedback_dihapus'): ?>
        <div class="alert alert-danger text-center">🗑️ Feedback telah dihapus.</div>
      <?php endif; ?>
    <?php endif; ?>

    <?php
      // FILTER KEPUASAN
      $filter = isset($_GET['kepuasan']) ? $_GET['kepuasan'] : 'semua';

      $where = "";
      if ($filter == 'puas') {
          $where = "AND f.rating >= 4";
      } elseif ($filter == 'cukup') {
          $where = "AND f.rating = 3";
      } elseif ($filter == 'kurang') {
          $where = "AND f.rating <= 2";
      }
    ?>

    <!-- FILTER FORM -->
    <div class="box">
      <div class="box-body">
        <form method="get" class="form-inline">
          <label>Filter Kepuasan:</label>
          <select name="kepuasan" class="form-control" onchange="this.form.submit()">
            <option value="semua" <?= ($filter=='semua')?'selected':'' ?>>Semua</option>
            <option value="puas" <?= ($filter=='puas')?'selected':'' ?>>Puas (⭐ ≥ 4)</option>
            <option value="cukup" <?= ($filter=='cukup')?'selected':'' ?>>Cukup (⭐ 3)</option>
            <option value="kurang" <?= ($filter=='kurang')?'selected':'' ?>>Kurang Puas (⭐ ≤ 2)</option>
          </select>
        </form>
      </div>
    </div>

    <!-- TABEL FEEDBACK -->
    <div class="box">
      <div class="box-header">
        <h3 class="box-title">Data Feedback Pelanggan</h3>
      </div>
      <div class="box-body">
        <table class="table table-bordered table-striped" id="table-datatable">
          <thead>
            <tr>
              <th width="1%">NO</th>
              <th>CUSTOMER</th>
              <th>PRODUK</th>
              <th>RATING</th>
              <th>STATUS</th>
              <th>KOMENTAR</th>
              <th>TANGGAL</th>
              <th width="10%">AKSI</th>
            </tr>
          </thead>
          <tbody>
            <?php
            $no = 1;
            $data = mysqli_query($koneksi, "
              SELECT f.*, 
                     c.customer_nama AS customer, 
                     p.produk_nama AS produk
              FROM feedback f
              LEFT JOIN customer c ON f.id_customer = c.customer_id
              LEFT JOIN produk p ON f.id_produk = p.produk_id
              WHERE 1=1 $where
              ORDER BY f.id_feedback DESC
            ");

            while($d = mysqli_fetch_assoc($data)){
              // STATUS KEPUASAN
              if ($d['rating'] >= 4) {
                $status = "<span class='label label-success'>Puas</span>";
              } elseif ($d['rating'] == 3) {
                $status = "<span class='label label-warning'>Cukup</span>";
              } else {
                $status = "<span class='label label-danger'>Kurang Puas</span>";
              }
            ?>
              <tr>
                <td><?= $no++; ?></td>
                <td><?= htmlspecialchars($d['customer']); ?></td>
                <td><?= htmlspecialchars($d['produk']); ?></td>
                <td>
                  <?php
                    for($i=1; $i<=5; $i++){
                      echo ($i <= $d['rating']) ? "⭐" : "☆";
                    }
                  ?>
                </td>
                <td><?= $status; ?></td>
                <td><?= htmlspecialchars($d['komentar']); ?></td>
                <td><?= date('d-m-Y H:i', strtotime($d['tanggal'])); ?></td>
                <td>
                  <a href="feedback_hapus.php?id=<?= $d['id_feedback']; ?>" 
                     onclick="return confirm('Yakin ingin menghapus feedback ini?')" 
                     class="btn btn-danger btn-sm">
                    <i class="fa fa-trash"></i> Hapus
                  </a>
                </td>
              </tr>
            <?php } ?>
          </tbody>
        </table>
      </div>
    </div>

  </section>
</div>
<?php include 'footer.php'; ?>
