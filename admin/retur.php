<?php
session_start();
include '../koneksi.php';
include 'header.php';

// Fungsi menampilkan badge status retur
function tampil_status($status)
{
    switch ($status) {
        case 'pending':
            return "<span class='badge bg-warning text-dark'>Menunggu Konfirmasi</span>";
        case 'disetujui':
            return "<span class='badge bg-success'>Disetujui</span>";
        case 'ditolak':
            return "<span class='badge bg-danger'>Ditolak</span>";
        default:
            return "<span class='badge bg-secondary'>" . htmlspecialchars($status) . "</span>";
    }
}

// Notifikasi session
if (isset($_SESSION['success'])) {
    echo '<div class="alert alert-success text-center">' . $_SESSION['success'] . '</div>';
    unset($_SESSION['success']);
}
if (isset($_SESSION['error'])) {
    echo '<div class="alert alert-danger text-center">' . $_SESSION['error'] . '</div>';
    unset($_SESSION['error']);
}
?>

<div class="content-wrapper">
  <section class="content-header">
    <h1><i class="fa fa-refresh"></i> Data Retur Barang</h1>
  </section>

  <section class="content">
    <div class="box box-info">
      <div class="box-header with-border">
        <h3 class="box-title">Daftar Pengajuan Retur</h3>
      </div>

      <div class="box-body table-responsive">
        <table class="table table-bordered table-striped">
          <thead>
            <tr class="bg-primary text-white">
              <th style="width:40px;">No</th>
              <th>Invoice</th>
              <th>Customer</th>
              <th>Produk</th>
              <th>Alasan Retur</th>
              <th>Foto Bukti</th>
              <th>Status</th>
              <th style="width:230px;">Aksi</th>
            </tr>
          </thead>
          <tbody>
            <?php
            $no = 1;
            // Gunakan kolom "retur_id" sebagai primary key
            $query = mysqli_query($koneksi, "
                SELECT r.*, c.customer_nama, p.produk_nama 
                FROM retur r
                LEFT JOIN customer c ON r.customer_id = c.customer_id
                LEFT JOIN produk p ON r.produk_id = p.produk_id
                ORDER BY r.retur_id DESC
            ");

            if (!$query) {
                echo "<tr><td colspan='8' class='text-center text-danger'>Query error: " . mysqli_error($koneksi) . "</td></tr>";
            } elseif (mysqli_num_rows($query) == 0) {
                echo "<tr><td colspan='8' class='text-center'>Belum ada data retur.</td></tr>";
            } else {
                while ($d = mysqli_fetch_assoc($query)) {
            ?>
                  <tr>
                    <td><?= $no++; ?></td>
                    <td><b>INV-00<?= htmlspecialchars($d['invoice_id']); ?></b></td>
                    <td><?= htmlspecialchars($d['customer_nama']); ?></td>
                    <td><?= htmlspecialchars($d['produk_nama']); ?></td>
                    <td style="max-width:250px;white-space:pre-wrap;"><?= nl2br(htmlspecialchars($d['retur_alasan'] ?? $d['alasan'])); ?></td>
                    <td>
                      <?php
                      $foto = $d['retur_bukti'] ?? $d['foto'] ?? '';
                      if (!empty($foto)): ?>
                        <a href="../gambar/retur/<?= htmlspecialchars($foto); ?>" target="_blank">
                          <img src="../gambar/retur/<?= htmlspecialchars($foto); ?>" width="70" class="img-thumbnail">
                        </a>
                      <?php else: ?>
                        <span class="text-muted">Tidak ada</span>
                      <?php endif; ?>
                    </td>
                    <td><?= tampil_status($d['retur_status'] ?? $d['status']); ?></td>
                    <td>
                      <form method="post" action="retur_update.php" class="d-inline">
                        <input type="hidden" name="retur_id" value="<?= $d['retur_id']; ?>">
                        <select name="status" class="form-control" style="width:130px;display:inline-block;">
                          <option value="pending" <?= ($d['retur_status'] ?? $d['status']) == 'pending' ? 'selected' : ''; ?>>Pending</option>
                          <option value="disetujui" <?= ($d['retur_status'] ?? $d['status']) == 'disetujui' ? 'selected' : ''; ?>>Disetujui</option>
                          <option value="ditolak" <?= ($d['retur_status'] ?? $d['status']) == 'ditolak' ? 'selected' : ''; ?>>Ditolak</option>
                        </select>
                        <button type="submit" class="btn btn-sm btn-primary">Simpan</button>
                      </form>

                      <form method="post" action="retur_hapus.php" class="d-inline" onsubmit="return confirm('Yakin ingin menghapus retur ini?');">
                        <input type="hidden" name="retur_id" value="<?= $d['retur_id']; ?>">
                        <button type="submit" class="btn btn-sm btn-danger">Hapus</button>
                      </form>
                    </td>
                  </tr>
            <?php
                }
            }
            ?>
          </tbody>
        </table>
      </div>
    </div>
  </section>
</div>

<?php include 'footer.php'; ?>
