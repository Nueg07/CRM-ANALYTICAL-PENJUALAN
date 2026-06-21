<?php
session_start();
include 'koneksi.php';

if(!isset($_SESSION['customer'])){
    header("Location: masuk.php?alert=login-dulu");
    exit;
}

$customer = $_SESSION['customer'];
$id_customer = $customer['id'];

// ==========================================
// TANDAI SEMUA BALASAN ADMIN SEBAGAI DIBACA
// ==========================================
mysqli_query($koneksi, "
    UPDATE keluhan 
    SET status='dibaca' 
    WHERE id_customer='$id_customer' 
    AND balasan IS NOT NULL 
    AND balasan != '' 
    AND status='ditanggapi'
");

// ==========================================
// PROSES KIRIM PESAN BARU DARI CUSTOMER
// ==========================================
if(isset($_POST['pesan'])){
    $pesan = mysqli_real_escape_string($koneksi, $_POST['pesan']);
    mysqli_query($koneksi,"
        INSERT INTO keluhan (id_customer, pesan, status, tanggal) 
        VALUES ('$id_customer','$pesan','baru',NOW())
    ");
    $_SESSION['success'] = "Keluhan berhasil dikirim!";
    header("Location: customer_keluhan.php");
    exit;
}

include 'header.php';
?>

<div class="container py-5">
  <div class="row">
    <div class="col-lg-8 mx-auto">
      <div class="card shadow">
        <div class="card-header bg-primary text-white d-flex justify-content-between align-items-center">
          <h5 class="mb-0">💬 Chat Keluhan Customer</h5>
        </div>

        <div class="card-body" style="max-height: 500px; overflow-y: auto; background-color: #f9f9f9;">
          <?php if(isset($_SESSION['success'])): ?>
            <div class="alert alert-success"><?= $_SESSION['success']; unset($_SESSION['success']); ?></div>
          <?php endif; ?>

          <?php
          $keluhan = mysqli_query($koneksi,"SELECT * FROM keluhan WHERE id_customer='$id_customer' ORDER BY tanggal ASC");
          if(mysqli_num_rows($keluhan) == 0){
              echo "<p class='text-center text-muted'>Belum ada percakapan keluhan.</p>";
          }
          while($k = mysqli_fetch_assoc($keluhan)){
          ?>
              <!-- Pesan Customer -->
              <div class="d-flex justify-content-end mb-3">
                <div class="bg-primary text-white p-3 rounded" style="max-width:70%;">
                  <small class="d-block text-end"><?= date('d-m-Y H:i', strtotime($k['tanggal'])); ?></small>
                  <?= nl2br(htmlspecialchars($k['pesan'])); ?>
                </div>
              </div>

              <!-- Balasan Admin -->
              <?php if(!empty($k['balasan'])): ?>
              <div class="d-flex justify-content-start mb-3">
                <div class="bg-light border p-3 rounded" style="max-width:70%;">
                  <strong>Admin:</strong><br>
                  <?= nl2br(htmlspecialchars($k['balasan'])); ?><br>
                  <small class="text-muted"><?= date('d-m-Y H:i', strtotime($k['tanggal_balasan'])); ?></small>
                </div>
              </div>
              <?php endif; ?>
          <?php } ?>
        </div>

        <div class="card-footer bg-white">
          <form method="post">
            <div class="input-group">
              <textarea name="pesan" class="form-control" placeholder="Ketik keluhan Anda..." required></textarea>
              <button type="submit" class="btn btn-primary">Kirim</button>
            </div>
          </form>
        </div>
      </div>
    </div>
  </div>
</div>

<?php include 'footer.php'; ?>
