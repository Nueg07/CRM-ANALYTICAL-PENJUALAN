<?php 
include 'header.php'; 
include 'koneksi.php'; 

if (session_status() === PHP_SESSION_NONE) {
  session_start();
}
$id_customer = $_SESSION['customer']['id'] ?? 0;
?>

<!-- NOTIFIKASI PROMO PERSONAL -->
<?php 
if ($id_customer != 0) {
    $promo_personal = mysqli_query($koneksi, "
        SELECT p.produk_nama, pr.diskon_persen, pr.tanggal_selesai 
        FROM promo pr 
        JOIN produk p ON pr.produk_id = p.produk_id
        WHERE pr.customer_id = '$id_customer' 
        AND pr.status = 'aktif'
        AND CURDATE() BETWEEN pr.tanggal_mulai AND pr.tanggal_selesai
    ");
    if (mysqli_num_rows($promo_personal) > 0) {
        echo '<div class="container mt-4">';
        echo '<div class="alert alert-success shadow-sm" role="alert">';
        echo '<h5 class="mb-2"><i class="fas fa-gift text-warning"></i> Promo Personal Untukmu 🎁</h5>';
        echo '<ul class="mb-0 pl-3">';
        while ($p = mysqli_fetch_assoc($promo_personal)) {
            echo '<li><strong>'.htmlspecialchars($p['produk_nama']).'</strong> diskon <b>'.$p['diskon_persen'].'%</b> 
                  hingga <b>'.date('d M Y', strtotime($p['tanggal_selesai'])).'</b></li>';
        }
        echo '</ul>';
        echo '</div></div>';
    }
}
?>

<!-- HERO SECTION-->
<section class="hero d-flex align-items-center bg-white" 
         style="min-height: 75vh; overflow: hidden; position: relative;">
  <div class="container py-5">
    <div class="row align-items-center">
      <!-- TEKS PROMO -->
      <div class="col-lg-6 mb-4 mb-lg-0">
        <p class="small text-uppercase text-muted mb-2">Produk Terbaru</p>
        <h1 class="display-5 fw-bold mb-3">Diskon 20% Untuk Produk Baru</h1>
        <p class="lead mb-4">Belanja produk elektronik rumah tangga terbaik dengan harga spesial hari ini.</p>
        <a class="btn btn-warning btn-lg rounded-pill shadow-sm px-4" href="shop.php">
          Elektronik Produk
        </a>
      </div>
      <!-- GAMBAR PRODUK -->
      <div class="col-lg-6 text-center">
        <img src="baru/img/header.png" alt="Produk Elektronik"
             class="img-fluid"
             style="max-height: 400px; object-fit: contain;">
      </div>
    </div>
  </div>
</section>

<!-- CATEGORIES SECTION-->
<section class="pt-5">
  <div class="container">
    <header class="text-center">            
      <h2 class="h5 text-uppercase mb-4">Original Collection</h2>
    </header>
    <div class="row">
      <div class="col-md-4 mb-4 mb-md-0">
        <a class="category-item" href="#">
          <img class="img-fluid rounded shadow-sm" src="baru/img/philips-setrika-listrik-hd1172-5036-491948-e154df951515587813576e7f4dcdf5a5-webp-zoom.jpg" alt="">
        </a>
      </div>

      <div class="col-md-4 mb-4 mb-md-0">
        <a class="category-item mb-4" href="#">
          <img class="img-fluid rounded shadow-sm" src="baru/img/OIP (1).jpg" alt="">
        </a>
        <a class="category-item" href="#">
          <img class="img-fluid rounded shadow-sm" src="baru/img/unnamed.jpg" alt="">
        </a>
      </div>

      <div class="col-md-4">
        <a class="category-item" href="#">
          <img class="img-fluid rounded shadow-sm" src="baru/img/73P.jpg" alt="">
        </a>
      </div>
    </div>
  </div>
</section>

<!-- TRENDING PRODUCTS-->
<section class="py-5">
  <div class="container">
    <header>
      <p class="small text-muted small text-uppercase mb-1">The main quality</p>
      <h2 class="h5 text-uppercase mb-4">Top Trending Produk</h2>
    </header>
    <div class="row">
      <!-- PRODUCT-->
      <?php 
        $top = mysqli_query($koneksi,"SELECT * FROM produk ORDER BY rand() LIMIT 8");
        while($t = mysqli_fetch_array($top)){
      ?>
          <div class="col-xl-3 col-lg-4 col-sm-6 mb-4">
            <div class="product text-center shadow-sm p-3 rounded bg-white h-100">
              <div class="position-relative mb-3">
                <a class="d-block" href="produk_detail.php?id=<?php echo $t['produk_id'] ?>">
                  <img class="img-fluid w-100" src="gambar/produk/<?php echo $t['produk_foto1'] ?>" alt="...">
                </a>
                <div class="product-overlay">
                  <ul class="mb-0 list-inline">                        
                    <li class="list-inline-item m-0 p-0">
                      <a class="btn btn-sm btn-dark" href="keranjang_masukkan.php?id=<?php echo $t['produk_id']; ?>&redirect=index">
                        Add to cart
                      </a>
                    </li>
                  </ul>
                </div>
              </div>
              <h6>
                <a class="reset-anchor" href="produk_detail.php?id=<?php echo $t['produk_id'] ?>">
                  <?php echo $t['produk_nama']; ?>
                </a>
              </h6>
              <p class="small text-muted">
                <?php echo "Rp.".number_format($t['produk_harga']); ?>
              </p>
            </div>
          </div>
      <?php
        }
      ?>
    </div>
  </div>
</section>

<!-- SERVICES-->
<section class="py-5 bg-light">
  <div class="container">
    <div class="row text-center">
      <div class="col-lg-4 mb-3 mb-lg-0">
        <div class="d-inline-block">
          <div class="media align-items-end">
            <svg class="svg-icon svg-icon-big svg-icon-light">
              <use xlink:href="#delivery-time-1"> </use>
            </svg>
            <div class="media-body text-left ml-3">
              <h6 class="text-uppercase mb-1">Free shipping</h6>
              <p class="text-small mb-0 text-muted">Free shipping worldwide</p>
            </div>
          </div>
        </div>
      </div>
      <div class="col-lg-4 mb-3 mb-lg-0">
        <div class="d-inline-block">
          <div class="media align-items-end">
            <svg class="svg-icon svg-icon-big svg-icon-light">
              <use xlink:href="#helpline-24h-1"> </use>
            </svg>
            <div class="media-body text-left ml-3">
              <h6 class="text-uppercase mb-1">24 x 7 Service</h6>
              <p class="text-small mb-0 text-muted">Siap melayani Anda kapan saja</p>
            </div>
          </div>
        </div>
      </div>
      <div class="col-lg-4">
        <div class="d-inline-block">
          <div class="media align-items-end">
            <svg class="svg-icon svg-icon-big svg-icon-light">
              <use xlink:href="#label-tag-1"> </use>
            </svg>
            <div class="media-body text-left ml-3">
              <h6 class="text-uppercase mb-1">Festival Offer</h6>
              <p class="text-small mb-0 text-muted">Diskon menarik setiap bulan</p>
            </div>
          </div>
        </div>
      </div>
    </div>
  </div>
</section>

<?php include 'footer.php'; ?>
