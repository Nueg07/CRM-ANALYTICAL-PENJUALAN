<?php include 'header.php'; ?>
<?php include '../koneksi.php'; ?>

<div class="content-wrapper">
  <section class="content-header">
    <h1>Produk <small>Tambah Produk Baru</small></h1>
    <ol class="breadcrumb">
      <li><a href="#"><i class="fa fa-dashboard"></i> Home</a></li>
      <li class="active">Tambah Produk</li>
    </ol>
  </section>

  <section class="content">
    <div class="row">
      <section class="col-lg-12">       
        <div class="box box-info">
          <div class="box-header">
            <h3 class="box-title">Tambah Produk</h3>
            <a href="produk.php" class="btn btn-danger btn-sm pull-right"><i class="fa fa-reply"></i> &nbsp Kembali</a> 
          </div>
          <div class="box-body">

            <form action="produk_act.php" method="post" enctype="multipart/form-data">

              <div class="form-group">
                <label>Nama Produk</label>
                <input type="text" class="form-control" name="nama" required placeholder="Masukkan Nama Produk ..">
              </div>

              <div class="form-group">
                <label>Kategori Produk</label>
                <select name="kategori" required class="form-control">
                  <option value="">-- Pilih Kategori --</option>
                  <?php 
                  $kategori = mysqli_query($koneksi,"SELECT * FROM kategori ORDER BY kategori_nama ASC");
                  while($d = mysqli_fetch_array($kategori)){
                    echo "<option value='{$d['kategori_id']}'>{$d['kategori_nama']}</option>";
                  }
                  ?>
                </select>
              </div>

              <div class="form-group">
                <label>Pilih Promo</label>
                <select name="id_promo" class="form-control">
                  <option value="">-- Tidak Ada Promo --</option>
                  <?php 
                  $promo = mysqli_query($koneksi,"SELECT * FROM promo ORDER BY nama_promo ASC");
                  while($p = mysqli_fetch_array($promo)){
                    echo "<option value='{$p['id_promo']}'>{$p['nama_promo']} ({$p['diskon_persen']}%)</option>";
                  }
                  ?>
                </select>
              </div>

              <div class="form-group">
                <label>Harga</label>
                <input type="number" class="form-control" name="harga" required placeholder="Masukkan Harga ..">
              </div>

              <div class="form-group">
                <label>Keterangan</label>
                <textarea name="keterangan" class="form-control textarea" required rows="5" placeholder="Masukkan Keterangan .."></textarea>
              </div>

              <div class="form-group">
                <label>Berat Produk (gram)</label>
                <input type="number" class="form-control" name="berat" required placeholder="Masukkan Berat Produk ..">
              </div>

              <div class="form-group">
                <label>Jumlah Stok</label>
                <input type="number" class="form-control" name="jumlah" required placeholder="Masukkan Jumlah Stok ..">
              </div>

              <div class="form-group">
                <label>Foto 1 (Utama)</label>
                <input type="file" name="foto1">
              </div>

              <div class="form-group">
                <label>Foto 2</label>
                <input type="file" name="foto2">
              </div>

              <div class="form-group">
                <label>Foto 3</label>
                <input type="file" name="foto3">
              </div>

              <div class="form-group">
                <input type="submit" class="btn btn-sm btn-primary" value="Simpan">
              </div>

            </form>

          </div>
        </div>
      </section>
    </div>
  </section>
</div>

<?php include 'footer.php'; ?>
