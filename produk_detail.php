<?php
include 'header.php';
include 'koneksi.php';

if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

// Pastikan customer login
$id_customer = $_SESSION['customer']['id'] ?? 0;

// Ambil ID produk
$id = isset($_GET['id']) ? (int)$_GET['id'] : 0;
if ($id <= 0) {
    echo "<div class='container py-5'><div class='alert alert-danger'>Produk tidak ditemukan.</div></div>";
    include 'footer.php';
    exit;
}

// Ambil data produk
$data = mysqli_query($koneksi, "SELECT * FROM produk 
                                JOIN kategori ON produk_kategori=kategori_id 
                                WHERE produk_id='$id'");
$d = mysqli_fetch_assoc($data);
if (!$d) {
    echo "<div class='container py-5'><div class='alert alert-danger'>Produk tidak ditemukan.</div></div>";
    include 'footer.php';
    exit;
}

// ==== CEK PROMO AKTIF (UMUM ATAU PERSONAL SESUAI CUSTOMER) ====
$promoAktif = null;

if ($id_customer > 0) {
    $promoQuery = mysqli_query($koneksi, "
        SELECT * FROM promo 
        WHERE produk_id='$id'
          AND status='aktif'
          AND CURDATE() BETWEEN tanggal_mulai AND tanggal_selesai
          AND (jenis='umum' OR (jenis='personal' AND customer_id='$id_customer'))
        ORDER BY jenis='personal' DESC, diskon_persen DESC
        LIMIT 1
    ");
    if (mysqli_num_rows($promoQuery) > 0) {
        $promoAktif = mysqli_fetch_assoc($promoQuery);
    }
} else {
    // Jika belum login, hanya tampilkan promo umum
    $promoQuery = mysqli_query($koneksi, "
        SELECT * FROM promo 
        WHERE produk_id='$id'
          AND status='aktif'
          AND CURDATE() BETWEEN tanggal_mulai AND tanggal_selesai
          AND jenis='umum'
        ORDER BY diskon_persen DESC
        LIMIT 1
    ");
    if (mysqli_num_rows($promoQuery) > 0) {
        $promoAktif = mysqli_fetch_assoc($promoQuery);
    }
}

// Hitung harga promo jika ada
$hargaAsli = $d['produk_harga'];
$hargaPromo = $hargaAsli;
if ($promoAktif) {
    if ($promoAktif['jenis'] == 'umum' || ($promoAktif['jenis'] == 'personal' && $promoAktif['customer_id'] == $id_customer)) {
        $hargaPromo = $hargaAsli - ($hargaAsli * $promoAktif['diskon_persen'] / 100);
    } else {
        // Promo personal untuk user lain tidak berlaku
        $promoAktif = null;
    }
}
?>

<div class="container mt-4">
  <?php if (isset($_GET['alert'])): ?>
      <?php if ($_GET['alert'] == 'berhasil_ditambahkan'): ?>
          <div class="alert alert-success text-center shadow-sm rounded">
              ✅ Produk berhasil ditambahkan ke keranjang.
          </div>
      <?php elseif ($_GET['alert'] == 'stok_kurang'): ?>
          <div class="alert alert-danger text-center shadow-sm rounded">
              ❌ Stok produk tidak mencukupi.
          </div>
      <?php elseif ($_GET['alert'] == 'produk_tidak_ada'): ?>
          <div class="alert alert-danger text-center shadow-sm rounded">
              ⚠️ Produk tidak ditemukan.
          </div>
      <?php endif; ?>
  <?php endif; ?>
</div>

<section class="py-5">
  <div class="container">
    <div class="row mb-5">
      <!-- Gambar Produk -->
      <div class="col-lg-6">
        <div class="row m-sm-0">
          <div class="col-sm-2 p-sm-0 order-2 order-sm-1 mt-2 mt-sm-0">
            <div class="owl-thumbs d-flex flex-row flex-sm-column" data-slider-id="1">
              <?php for ($i = 1; $i <= 3; $i++):
                  $foto = !empty($d['produk_foto'.$i]) && file_exists("gambar/produk/".$d['produk_foto'.$i])
                          ? $d['produk_foto'.$i] : 'produk.png'; ?>
                  <div class="owl-thumb-item flex-fill mb-2 mr-2 mr-sm-0">
                      <img class="w-100" src="gambar/produk/<?= $foto ?>" alt="foto produk <?= $i ?>">
                  </div>
              <?php endfor; ?>
            </div>
          </div>

          <div class="col-sm-10 order-1 order-sm-2">
            <div class="owl-carousel product-slider" data-slider-id="1">
              <?php for ($i = 1; $i <= 3; $i++):
                  $foto = !empty($d['produk_foto'.$i]) && file_exists("gambar/produk/".$d['produk_foto'.$i])
                          ? $d['produk_foto'.$i] : 'produk.png'; ?>
                  <a class="d-block" href="gambar/produk/<?= $foto ?>" data-lightbox="product" title="Foto produk <?= $i ?>">
                      <img class="img-fluid" src="gambar/produk/<?= $foto ?>" alt="foto produk <?= $i ?>">
                  </a>
              <?php endfor; ?>
            </div>
          </div>
        </div>
      </div>

      <!-- Detail Produk -->
      <div class="col-lg-6">
        <h1><?= htmlspecialchars($d['produk_nama']) ?></h1>

        <!-- Harga produk -->
        <?php if ($promoAktif): ?>
          <p class="lead mb-1">
            <span class="text-muted"><del>Rp <?= number_format($hargaAsli, 0, ',', '.') ?></del></span><br>
            <span class="text-danger h4 font-weight-bold">Rp <?= number_format($hargaPromo, 0, ',', '.') ?></span>
          </p>
          <span class="badge badge-success">
            Promo <?= ucfirst($promoAktif['jenis']) ?>: <?= $promoAktif['diskon_persen'] ?>%
          </span>
        <?php else: ?>
          <p class="text-muted lead">Rp <?= number_format($hargaAsli, 0, ',', '.') ?></p>
        <?php endif; ?>

        <div class="row align-items-stretch mb-4 mt-3">
          <div class="col-sm-5 pr-sm-0">
            <div class="border d-flex align-items-center justify-content-between py-1 px-3 bg-white border-white">
              <span class="small text-uppercase text-gray mr-4 no-select">Jumlah</span>
              <div class="quantity">
                <button class="dec-btn p-0"><i class="fas fa-caret-left"></i></button>
                <input id="jumlahInput" class="form-control border-0 shadow-0 p-0 text-center" type="text" value="1">
                <button class="inc-btn p-0"><i class="fas fa-caret-right"></i></button>
              </div>
            </div>
          </div>
          <div class="col-sm-3 pl-sm-0">
            <a id="tambahKeranjangBtn" 
               class="btn btn-dark btn-sm btn-block h-100 d-flex align-items-center justify-content-center px-0" 
               href="#">
               + Keranjang
            </a>
          </div>
        </div>

        <ul class="list-unstyled small d-inline-block">
          <li class="px-3 py-2 mb-1 bg-white"><strong>STOK:</strong> <span class="ml-2 text-muted" id="stokProduk"><?= $d['produk_jumlah'] ?></span></li>
          <li class="px-3 py-2 mb-1 bg-white"><strong>BERAT:</strong> <span class="ml-2 text-muted"><?= $d['produk_berat'] ?> Gram</span></li>
          <li class="px-3 py-2 mb-1 bg-white"><strong>Kategori:</strong> 
            <a class="reset-anchor ml-2" href="produk_kategori.php?id=<?= $d['produk_kategori'] ?>">
              <?= htmlspecialchars($d['kategori_nama']) ?>
            </a>
          </li>
        </ul>
      </div>
    </div>

    <!-- Keterangan Produk -->
    <div class="tab-content">
      <div class="tab-pane fade show active">
        <div class="p-4 p-lg-5 bg-white">
          <h6 class="text-uppercase">Keterangan Produk</h6>
          <p class="text-muted text-small mb-0"><?= nl2br(htmlspecialchars($d['produk_keterangan'])) ?></p>
        </div>
      </div>
    </div>

    <!-- Feedback Pelanggan -->
    <div class="p-4 p-lg-5 bg-white mt-4 rounded shadow-sm">
      <h5 class="text-uppercase mb-3">Ulasan & Feedback Pelanggan</h5>
      <?php 
      $feedback = mysqli_query($koneksi, "SELECT f.*, c.customer_nama 
                                          FROM feedback f 
                                          JOIN customer c ON f.id_customer = c.customer_id 
                                          WHERE f.id_produk='$id'
                                          ORDER BY f.tanggal DESC");

      if (mysqli_num_rows($feedback) > 0): ?>
          <?php while ($fb = mysqli_fetch_assoc($feedback)): ?>
              <div class="border-bottom pb-3 mb-3">
                  <strong><?= htmlspecialchars($fb['customer_nama']) ?></strong>
                  <span class="text-muted small"> • <?= date('d M Y', strtotime($fb['tanggal'])) ?></span>
                  <div class="mt-1">
                      <?php 
                      for ($i = 1; $i <= 5; $i++) {
                          echo $i <= $fb['rating'] ? "⭐" : "☆";
                      }
                      ?>
                  </div>
                  <p class="mt-2 mb-0"><?= nl2br(htmlspecialchars($fb['komentar'])) ?></p>
              </div>
          <?php endwhile; ?>
      <?php else: ?>
          <div class="alert alert-info mb-0">Belum ada ulasan untuk produk ini.</div>
      <?php endif; ?>

      <!-- FORM FEEDBACK -->
      <?php
      if ($id_customer > 0) {
          // Cek apakah user pernah beli produk
          $cek_invoice = mysqli_query($koneksi, "
              SELECT i.invoice_id 
              FROM invoice_detail d
              JOIN invoice i ON d.invoice_id = i.invoice_id
              WHERE d.produk_id = '$id' 
                AND i.invoice_customer = '$id_customer' 
                AND i.invoice_status = 5
              LIMIT 1
          ");
          $invoice = mysqli_fetch_assoc($cek_invoice);
          if ($invoice) {
              // Cek apakah sudah kirim feedback
              $cek_fb = mysqli_query($koneksi, "
                  SELECT * FROM feedback 
                  WHERE id_produk = '$id' 
                    AND id_customer = '$id_customer'
              ");
              if (mysqli_num_rows($cek_fb) == 0):
      ?>
      <form method="post" action="customer_feedback_act.php" class="mt-3">
          <input type="hidden" name="id_produk" value="<?= $id ?>">
          <input type="hidden" name="id_invoice" value="<?= $invoice['invoice_id'] ?>">
          <div class="form-group">
              <label>Rating</label>
              <select name="rating" class="form-control" required>
                  <option value="">- Pilih Rating -</option>
                  <option value="5">⭐⭐⭐⭐⭐ Sangat Puas</option>
                  <option value="4">⭐⭐⭐⭐ Puas</option>
                  <option value="3">⭐⭐⭐ Cukup</option>
                  <option value="2">⭐⭐ Kurang</option>
                  <option value="1">⭐ Sangat Buruk</option>
              </select>
          </div>
          <div class="form-group">
              <label>Komentar</label>
              <textarea name="komentar" class="form-control" required></textarea>
          </div>
          <button type="submit" class="btn btn-primary">Kirim Feedback</button>
      </form>
      <?php 
              else:
                  echo "<div class='alert alert-info mt-3'>Anda sudah mengirim feedback untuk produk ini.</div>";
              endif;
          } else {
              echo "<div class='alert alert-info mt-3'>Anda harus membeli produk ini untuk memberi feedback.</div>";
          }
      } else {
          echo "<div class='alert alert-info mt-3'>Silakan login untuk memberi feedback.</div>";
      }
      ?>
    </div>

    <!-- Produk Lainnya -->
    <h2 class="h5 text-uppercase mb-4 mt-5">Produk Lainnya</h2>
    <div class="row">
      <?php 
      $lainnya = mysqli_query($koneksi, "SELECT * FROM produk 
                                         WHERE produk_kategori='".$d['produk_kategori']."' 
                                         AND produk_id!='$id' 
                                         ORDER BY RAND() LIMIT 5");
      while ($ll = mysqli_fetch_assoc($lainnya)) {
          $foto_lain = !empty($ll['produk_foto1']) && file_exists("gambar/produk/".$ll['produk_foto1']) 
                        ? $ll['produk_foto1'] : 'produk.png'; ?>
          <div class="col-lg-2 col-sm-6 mb-3">
              <div class="product text-center">
                  <a class="d-block mb-2" href="produk_detail.php?id=<?= $ll['produk_id'] ?>">
                      <img class="img-fluid w-100" src="gambar/produk/<?= $foto_lain ?>" alt="produk lainnya">
                  </a>
                  <h6>
                    <a class="reset-anchor" href="produk_detail.php?id=<?= $ll['produk_id'] ?>">
                      <?= htmlspecialchars($ll['produk_nama']) ?>
                    </a>
                  </h6>
                  <p class="small text-muted">Rp <?= number_format($ll['produk_harga'], 0, ',', '.') ?></p>
              </div>
          </div>
      <?php } ?>
    </div>
  </div>
</section>

<script>
document.addEventListener('DOMContentLoaded', function() {
    const decBtn = document.querySelector('.dec-btn');
    const incBtn = document.querySelector('.inc-btn');
    const jumlahInput = document.getElementById('jumlahInput');
    const stok = parseInt(document.getElementById('stokProduk').textContent);
    const addBtn = document.getElementById('tambahKeranjangBtn');

    decBtn.addEventListener('click', function() {
        let val = parseInt(jumlahInput.value);
        if (val > 1) jumlahInput.value = val - 1;
    });

    incBtn.addEventListener('click', function() {
        let val = parseInt(jumlahInput.value);
        if (val < stok) jumlahInput.value = val + 1;
        else alert("❌ Jumlah melebihi stok tersedia!");
    });

    addBtn.addEventListener('click', function(e) {
        e.preventDefault();
        let jumlah = parseInt(jumlahInput.value);
        if (jumlah < 1) jumlah = 1;
        if (jumlah > stok) {
            alert("❌ Stok tidak mencukupi!");
            return;
        }
        window.location.href = `keranjang_masukkan.php?id=<?= $d['produk_id'] ?>&jumlah=${jumlah}&redirect=produk_detail.php?id=<?= $d['produk_id'] ?>`;
    });
});
</script>

<?php include 'footer.php'; ?>
