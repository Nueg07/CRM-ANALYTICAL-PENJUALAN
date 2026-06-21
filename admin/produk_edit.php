<?php include 'header.php'; ?>
<?php 
include '../koneksi.php';
$id = $_GET['id'];
$data = mysqli_query($koneksi,"SELECT * FROM produk WHERE produk_id='$id'");
if(mysqli_num_rows($data) == 0){
  echo "<script>alert('Produk tidak ditemukan!'); window.location='produk.php';</script>";
  exit;
}
$d = mysqli_fetch_assoc($data);
?>

<div class="content-wrapper">
  <section class="content-header">
    <h1>Produk <small>Edit Produk</small></h1>
    <ol class="breadcrumb">
      <li><a href="index.php"><i class="fa fa-dashboard"></i> Home</a></li>
      <li><a href="produk.php">Produk</a></li>
      <li class="active">Edit Produk</li>
    </ol>
  </section>

  <section class="content">
    <div class="row">
      <section class="col-lg-12">
        <div class="box box-info">
          <div class="box-header">
            <h3 class="box-title">Edit Produk</h3>
          </div>
          <div class="box-body">

            <form method="post" action="produk_update.php" enctype="multipart/form-data">
              <input type="hidden" name="id" value="<?php echo $d['produk_id']; ?>">

              <div class="form-group">
                <label>Nama Produk</label>
                <input type="text" class="form-control" name="nama" required value="<?php echo htmlspecialchars($d['produk_nama']); ?>">
              </div>

              <div class="form-group">
                <label>Kategori</label>
                <select class="form-control" name="kategori" required>
                  <option value="">-- Pilih Kategori --</option>
                  <?php 
                  $kategori = mysqli_query($koneksi,"SELECT * FROM kategori ORDER BY kategori_nama ASC");
                  while($k = mysqli_fetch_array($kategori)){
                    $sel = ($d['produk_kategori'] == $k['kategori_id']) ? "selected" : "";
                    echo "<option value='".$k['kategori_id']."' $sel>".$k['kategori_nama']."</option>";
                  }
                  ?>
                </select>
              </div>

              <div class="form-group">
                <label>Promo</label>
                <select name="id_promo" class="form-control">
                  <option value="">-- Tidak Ada Promo --</option>
                  <?php 
                  $promo = mysqli_query($koneksi,"SELECT * FROM promo ORDER BY nama_promo ASC");
                  while($p = mysqli_fetch_array($promo)){
                    $sel = ($d['id_promo'] == $p['id_promo']) ? "selected" : "";
                    echo "<option value='{$p['id_promo']}' $sel>{$p['nama_promo']} ({$p['potongan_persen']}%)</option>";
                  }
                  ?>
                </select>
              </div>

              <div class="form-group">
                <label>Harga</label>
                <input type="number" class="form-control" name="harga" required value="<?php echo $d['produk_harga']; ?>">
              </div>

              <div class="form-group">
                <label>Harga Diskon</label>
                <input type="number" class="form-control" name="harga_diskon" value="<?php echo $d['produk_harga_diskon']; ?>">
              </div>

              <div class="form-group">
                <label>Keterangan</label>
                <textarea class="form-control" name="keterangan" rows="5"><?php echo htmlspecialchars($d['produk_keterangan']); ?></textarea>
              </div>

              <div class="form-group">
                <label>Berat (gram)</label>
                <input type="number" class="form-control" name="berat" required value="<?php echo $d['produk_berat']; ?>">
              </div>

              <div class="form-group">
                <label>Jumlah Stok</label>
                <input type="number" class="form-control" name="jumlah" required value="<?php echo $d['produk_jumlah']; ?>">
              </div>

              <?php for($i=1;$i<=3;$i++){ ?>
              <div class="form-group">
                <label>Foto Produk <?php echo $i; ?></label><br>
                <?php if($d["produk_foto$i"] != ""){ ?>
                  <img src="../gambar/produk/<?php echo $d["produk_foto$i"]; ?>" width="80"><br><br>
                <?php } ?>
                <input type="file" name="foto<?php echo $i; ?>">
              </div>
              <?php } ?>

              <input type="submit" class="btn btn-primary" value="Simpan Perubahan">
              <a href="produk.php" class="btn btn-default">Kembali</a>

            </form>

          </div>
        </div>
      </section>
    </div>
  </section>
</div>

<?php include 'footer.php'; ?>
