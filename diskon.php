<?php 
include 'header.php';
include 'koneksi.php';

if (session_status() === PHP_SESSION_NONE) {
  session_start();
}
$id_customer = $_SESSION['customer']['id'] ?? 0;
?>

<div class="container">
  <!-- HERO SECTION -->
  <section class="py-5 bg-light border-bottom">
    <div class="container">
      <div class="row px-4 px-lg-5 py-lg-4 align-items-center">
        <div class="col-lg-6">
          <h1 class="h2 text-uppercase mb-0 font-weight-bold">Elektronik Station Shop</h1>
        </div>
        <div class="col-lg-6 text-lg-right">
          <nav aria-label="breadcrumb">
            <ol class="breadcrumb justify-content-lg-end mb-0 px-0 bg-transparent">
              <li class="breadcrumb-item"><a href="index.php" class="text-dark">Home</a></li>
              <li class="breadcrumb-item active text-danger" aria-current="page">Diskon Produk</li>
            </ol>
          </nav>
        </div>
      </div>
    </div>
  </section>

  <!-- SHOP CONTENT -->
  <section class="py-5">
    <div class="container p-0">
      <div class="row">
        <!-- SHOP SIDEBAR -->
        <div class="col-lg-3 order-2 order-lg-1 mb-5 mb-lg-0">
          <div class="py-2 px-4 bg-dark text-white mb-3 rounded shadow-sm">
            <strong class="small text-uppercase font-weight-bold">Kategori</strong>
          </div>
          <ul class="list-group list-group-flush shadow-sm rounded">
            <?php 
            $kategori = mysqli_query($koneksi,"SELECT * FROM kategori");
            while($k = mysqli_fetch_array($kategori)){
            ?>
              <li class="list-group-item d-flex justify-content-between align-items-center">
                <a class="text-dark reset-anchor" href="produk_kategori.php?id=<?= $k['kategori_id'] ?>">
                  <?= $k['kategori_nama'] ?>
                </a>
                <i class="fas fa-chevron-right small text-muted"></i>
              </li>
            <?php } ?>
          </ul>
        </div>

        <!-- SHOP LISTING -->
        <div class="col-lg-9 order-1 order-lg-2">
          <div class="row">
            <?php 
            // tampilkan hanya produk yang punya promo aktif (umum atau personal milik customer)
            $data = mysqli_query($koneksi, "
                SELECT DISTINCT p.* 
                FROM produk p
                JOIN promo pr ON pr.produk_id = p.produk_id
                WHERE pr.status='aktif'
                AND CURDATE() BETWEEN pr.tanggal_mulai AND pr.tanggal_selesai
                AND (
                    pr.jenis='umum'
                    OR (pr.jenis='personal' AND pr.customer_id='$id_customer')
                )
                ORDER BY p.produk_id DESC
            ");

            while($d = mysqli_fetch_array($data)){
              $promo = mysqli_query($koneksi, "
                  SELECT * FROM promo 
                  WHERE produk_id='{$d['produk_id']}'
                  AND status='aktif'
                  AND CURDATE() BETWEEN tanggal_mulai AND tanggal_selesai
                  AND (
                      jenis='umum'
                      OR (jenis='personal' AND customer_id='$id_customer')
                  )
                  ORDER BY jenis='personal' DESC
                  LIMIT 1
              ");
              $promoAktif = mysqli_fetch_assoc($promo);

              $hargaAsli = $d['produk_harga'];
              $hargaPromo = $hargaAsli - ($hargaAsli * $promoAktif['diskon_persen'] / 100);
              $diskon = $promoAktif['diskon_persen'];
            ?>
              <div class="col-lg-4 col-sm-6 mb-4">
                <div class="card border-0 shadow-sm h-100 position-relative">
                  <span class="badge badge-danger position-absolute" style="top:10px;left:10px;">-<?= $diskon; ?>%</span>

                  <a href="produk_detail.php?id=<?= $d['produk_id'] ?>">
                    <img class="card-img-top img-fluid" 
                         src="gambar/produk/<?= $d['produk_foto1'] ?>" 
                         alt="<?= $d['produk_nama']; ?>" 
                         style="height:220px;object-fit:cover;">
                  </a>

                  <div class="card-body text-center">
                    <h6 class="card-title mb-2">
                      <a class="text-dark font-weight-bold reset-anchor" href="produk_detail.php?id=<?= $d['produk_id'] ?>">
                        <?= $d['produk_nama']; ?>
                      </a>
                    </h6>
                    <p class="mb-2">
                      <span class="text-muted small"><del>Rp.<?= number_format($hargaAsli); ?></del></span><br>
                      <span class="text-danger font-weight-bold">Rp.<?= number_format($hargaPromo); ?></span>
                    </p>
                    <a class="btn btn-sm btn-danger text-white font-weight-bold px-3" 
                       href="keranjang_masukkan.php?id=<?= $d['produk_id']; ?>&redirect=index">
                      <i class="fas fa-cart-plus mr-1"></i> Add to Cart
                    </a>
                  </div>
                </div>
              </div>
            <?php } ?>
          </div>
        </div>
      </div>
    </div>
  </section>
</div>     

<?php include 'footer.php'; ?>
