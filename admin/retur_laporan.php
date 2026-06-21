<?php include 'header.php'; ?>
<div class="content-wrapper">
  <section class="content-header">
    <h1>
      Laporan Retur
      <small>Data Retur Barang Customer</small>
    </h1>
  </section>

  <section class="content">
    <div class="box">
      <div class="box-header">
        <h3 class="box-title">Data Retur</h3>
      </div>
      <div class="box-body">
        <table class="table table-bordered table-striped" id="table-datatable">
          <thead>
            <tr>
              <th width="1%">NO</th>
              <th>CUSTOMER</th>
              <th>PRODUK</th>
              <th>ALASAN</th>
              <th>FOTO</th>
              <th>STATUS</th>
              <th>TANGGAL</th>
            </tr>
          </thead>
          <tbody>
            <?php
            $no = 1;
            $data = mysqli_query($koneksi, "
                SELECT r.*, 
                       c.customer_nama AS customer, 
                       p.produk_nama AS produk
                FROM retur r
                LEFT JOIN customer c ON r.customer_id = c.customer_id
                LEFT JOIN produk p ON r.produk_id = p.produk_id
                ORDER BY r.created_at DESC
            ");
            while($d = mysqli_fetch_array($data)){
            ?>
              <tr>
                <td><?php echo $no++; ?></td>
                <td><?php echo htmlspecialchars($d['customer']); ?></td>
                <td><?php echo htmlspecialchars($d['produk']); ?></td>
                <td><?php echo htmlspecialchars($d['alasan']); ?></td>
                <td>
                  <?php if(!empty($d['foto'])): ?>
                    <img src="../gambar/retur/<?php echo $d['foto']; ?>" width="80">
                  <?php endif; ?>
                </td>
                <td>
                  <?php
                  if($d['status'] == 'diterima'){
                      echo "<span class='label label-success'>Diterima</span>";
                  } elseif($d['status'] == 'ditolak'){
                      echo "<span class='label label-danger'>Ditolak</span>";
                  } else {
                      echo "<span class='label label-warning'>Diproses</span>";
                  }
                  ?>
                </td>
                <td><?php echo date('d-m-Y H:i', strtotime($d['created_at'])); ?></td>
              </tr>
            <?php } ?>
          </tbody>
        </table>
      </div>
    </div>
  </section>
</div>
<?php include 'footer.php'; ?>
