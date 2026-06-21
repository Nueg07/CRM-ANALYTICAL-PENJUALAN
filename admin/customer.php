<?php include '../koneksi.php'; ?>
<?php include 'header.php'; ?>

<div class="content-wrapper">
  <section class="content-header">
    <h1>
      Customer
      <small>Data Customer</small>
    </h1>
  </section>

  <section class="content">
    <div class="box">
      <div class="box-header">
        <h3 class="box-title">Customer</h3>
        <a href="customer_cetak.php" class="btn btn-success btn-sm pull-right">
          <i class="fa fa-print"></i> CETAK
        </a>
      </div>

      <div class="box-body">
        <table class="table table-bordered table-striped" id="table-datatable">
          <thead>
            <tr>
              <th width="1%">NO</th>
              <th>NAMA</th>
              <th>EMAIL</th>
              <th>HP</th>
              <th>ALAMAT</th>
              <th>TOTAL TRANSAKSI</th>
              <th>PROMO</th>
              <th>OPSI</th>
            </tr>
          </thead>
          <tbody>
            <?php
            $no = 1;

            $data = mysqli_query($koneksi, "
              SELECT 
                  c.customer_id,
                  c.customer_nama,
                  c.customer_email,
                  c.customer_hp,
                  c.customer_alamat,
                  COUNT(i.invoice_id) AS total_transaksi
              FROM customer c
              LEFT JOIN invoice i 
                  ON c.customer_id = i.invoice_customer 
                  AND i.invoice_status = 5
              GROUP BY c.customer_id
              ORDER BY total_transaksi DESC
            ");

            while ($d = mysqli_fetch_array($data)) {
              $transaksi = intval($d['total_transaksi']);
              $sisa_transaksi = 5 - $transaksi;
            ?>
              <tr>
                <td><?php echo $no++; ?></td>
                <td><?php echo htmlspecialchars($d['customer_nama']); ?></td>
                <td><?php echo htmlspecialchars($d['customer_email']); ?></td>
                <td><?php echo htmlspecialchars($d['customer_hp']); ?></td>
                <td><?php echo htmlspecialchars($d['customer_alamat']); ?></td>
                <td><?php echo $transaksi; ?></td>
                <td>
                  <?php 
                    if ($transaksi >= 5) {
                      echo '<span class="label label-success"><i class="fa fa-gift"></i> Berhak Mendapatkan Promo</span>';
                    } else {
                      echo '<span class="label label-default">Butuh '.$sisa_transaksi.' transaksi lagi</span>';
                    }
                  ?>
                </td>
                <td>

                  <!-- OPSI EDIT & HAPUS -->
                  <a href="customer_edit.php?id=<?php echo $d['customer_id']; ?>" class="btn btn-warning btn-sm"><i class="fa fa-cog"></i></a>
                  <a href="customer_hapus.php?id=<?php echo $d['customer_id']; ?>" class="btn btn-danger btn-sm" onclick="return confirm('Yakin hapus customer ini?')"><i class="fa fa-trash"></i></a>

                  <!-- TOMBOL RIWAYAT BELANJA (AJAX) -->
                  <button class="btn btn-info btn-sm btnBelanja" data-id="<?php echo $d['customer_id']; ?>">
                    <i class="fa fa-shopping-cart"></i> Riwayat Belanja
                  </button>

                  <?php if ($transaksi >= 5): ?>
                    <!-- Tombol Kirim Promo -->
                    <button class="btn btn-success btn-sm" data-toggle="modal" data-target="#modalPromo<?php echo $d['customer_id']; ?>">
                      <i class="fa fa-gift"></i> Kirim Promo
                    </button>
                  <?php endif; ?>

                </td>
              </tr>

              <!-- ================= MODAL PROMO ================= -->
              <?php 
              $promo_template = "Selamat! 🎉 Anda telah menyelesaikan 5 transaksi di toko kami. 
Sebagai bentuk apresiasi, kami ingin memberikan promo spesial untuk Anda. 
Silakan hubungi kami atau tunggu informasi lanjutan mengenai promo menarik ini. 
Terima kasih atas kepercayaan Anda berbelanja di toko kami!";
              ?>
              <div class="modal fade" id="modalPromo<?php echo $d['customer_id']; ?>" tabindex="-1" role="dialog">
                <div class="modal-dialog modal-lg" role="document">
                  <div class="modal-content">
                    <div class="modal-header bg-success text-white">
                      <button type="button" class="close" data-dismiss="modal">&times;</button>
                      <h4 class="modal-title"><i class="fa fa-gift"></i> Kirim Promo ke <?= htmlspecialchars($d['customer_nama']); ?></h4>
                    </div>
                    <form method="post" action="keluhan_admin_pesan.php">
                      <div class="modal-body">
                        <input type="hidden" name="id_customer" value="<?= $d['customer_id']; ?>">
                        <div class="form-group">
                          <label>Pesan Promo</label>
                          <textarea name="pesan" class="form-control" rows="4" required><?= htmlspecialchars($promo_template); ?></textarea>
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

            <?php } ?>
          </tbody>
        </table>
      </div>
    </div>
  </section>
</div>

<?php include 'footer.php'; ?>

<!-- ================= MODAL RIWAYAT BELANJA GLOBAL (1 SAJA) ================= -->
<div class="modal fade" id="modalBelanja" tabindex="-1" role="dialog">
  <div class="modal-dialog modal-lg" role="document">
    <div class="modal-content">

      <div class="modal-header bg-info text-white">
        <button type="button" class="close" data-dismiss="modal">&times;</button>
        <h4 class="modal-title"><i class="fa fa-shopping-cart"></i> Riwayat Belanja</h4>
      </div>

      <div class="modal-body">
        <div id="dataBelanja">Memuat data...</div>
      </div>

      <div class="modal-footer">
        <button type="button" class="btn btn-default" data-dismiss="modal">Tutup</button>
      </div>

    </div>
  </div>
</div>

<style>
  .box-body { overflow-x: auto; }
  table th, table td { vertical-align: middle !important; }
</style>

<script>
$(document).ready(function(){

  // DATATABLE
  if (!$.fn.DataTable.isDataTable('#table-datatable')) {
    $('#table-datatable').DataTable({
      "pageLength": 10,
      "ordering": true,
      "language": {
        "lengthMenu": "Tampilkan _MENU_ data per halaman",
        "zeroRecords": "Tidak ada data ditemukan",
        "info": "Menampilkan _PAGE_ dari _PAGES_ halaman",
        "infoEmpty": "Tidak ada data tersedia",
        "search": "Cari : ",
        "paginate": { "previous": "Sebelumnya", "next": "Berikutnya" }
      }
    });
  }

  // ========= LOAD RIWAYAT BELANJA (AJAX MODE) =========
  $(".btnBelanja").click(function(){
      let id = $(this).data("id");

      $("#modalBelanja").modal("show");
      $("#dataBelanja").html("Memuat data...");

      $.ajax({
          url: "customer_riwayat.php",
          type: "POST",
          data: { id_customer: id },
          success: function(data){
              $("#dataBelanja").html(data);
          }
      });
  });

});
</script>
