<?php
ob_start(); // cegah "headers already sent"
include '../koneksi.php';
session_start();

// ✅ Cek session admin (pastikan sesuai dengan session yang dibuat saat login)
if (!isset($_SESSION['admin_id'])) {
    header("Location: ../login.php?alert=belum_login");
    exit;
}

$id = isset($_GET['id']) ? (int)$_GET['id'] : 0;

// Ambil data feedback
$q = mysqli_query($koneksi, "SELECT * FROM feedback WHERE id_feedback='$id'");
$f = mysqli_fetch_assoc($q);

if (!$f) {
    header("Location: feedback.php?alert=feedback_tidak_ditemukan");
    exit;
}

// Jika form disubmit
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $rating = (int)$_POST['rating'];
    $komentar = mysqli_real_escape_string($koneksi, $_POST['komentar']);

    $update = mysqli_query($koneksi, "
        UPDATE feedback 
        SET rating='$rating', komentar='$komentar' 
        WHERE id_feedback='$id'
    ");

    if ($update) {
        header("Location: feedback.php?alert=feedback_diedit");
    } else {
        echo "<script>alert('Gagal mengedit feedback!'); window.location='feedback.php';</script>";
    }
    exit;
}

include 'header.php';
?>

<div class="content-wrapper">
  <section class="content-header">
    <h1>Edit Feedback</h1>
  </section>

  <section class="content">
    <div class="box">
      <div class="box-header">
        <h3 class="box-title">Edit Feedback</h3>
      </div>
      <div class="box-body">
        <form method="POST">
          <div class="form-group">
            <label>Rating (1–5)</label>
            <input type="number" name="rating" class="form-control" min="1" max="5" 
                   value="<?= htmlspecialchars($f['rating']) ?>" required>
          </div>
          <div class="form-group">
            <label>Komentar</label>
            <textarea name="komentar" class="form-control" required><?= htmlspecialchars($f['komentar']) ?></textarea>
          </div>
          <button type="submit" class="btn btn-primary">Simpan Perubahan</button>
          <a href="feedback.php" class="btn btn-secondary">Kembali</a>
        </form>
      </div>
    </div>
  </section>
</div>

<?php include 'footer.php'; ?>
<?php ob_end_flush(); ?>
