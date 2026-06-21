<?php include 'header.php'; ?>
<div class="content-wrapper">
  <section class="content-header">
    <h1>
      Laporan Feedback
      <small>Data Feedback Customer</small>
    </h1>
  </section>

  <section class="content">
    <div class="box">
      <div class="box-header">
        <h3 class="box-title">Data Feedback</h3>
      </div>
      <div class="box-body">
        <table class="table table-bordered table-striped" id="table-datatable">
          <thead>
            <tr>
              <th width="1%">NO</th>
              <th>CUSTOMER</th>
              <th>PRODUK</th>
              <th>RATING</th>
              <th>KOMENTAR</th>
              <th>TANGGAL</th>
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
                ORDER BY f.tanggal DESC
            ");
            while($d = mysqli_fetch_array($data)){
            ?>
              <tr>
                <td><?php echo $no++; ?></td>
                <td><?php echo htmlspecialchars($d['customer']); ?></td>
                <td><?php echo htmlspecialchars($d['produk']); ?></td>
                <td>
                  <?php
                  $rating = (int)$d['rating'];
                  echo str_repeat("⭐", $rating); 
                  ?>
                </td>
                <td><?php echo htmlspecialchars($d['komentar']); ?></td>
                <td><?php echo date('d-m-Y H:i', strtotime($d['tanggal'])); ?></td>
              </tr>
            <?php } ?>
          </tbody>
        </table>
      </div>
    </div>
  </section>
</div>
<?php include 'footer.php'; ?>
