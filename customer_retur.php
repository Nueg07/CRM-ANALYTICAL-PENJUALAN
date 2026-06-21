<?php
if (session_status() === PHP_SESSION_NONE) session_start();
include 'koneksi.php';
include 'header.php';

// Pastikan login
if (!isset($_SESSION['customer'])) {
    header("location:masuk.php?alert=login-dulu");
    exit;
}

$customer = $_SESSION['customer'];
$id_customer = (int)$customer['id'];

// Ambil parameter
$id_invoice = isset($_GET['id_invoice']) ? (int)$_GET['id_invoice'] : 0;
$id_produk  = isset($_GET['id_produk']) ? (int)$_GET['id_produk'] : 0;

// Validasi invoice milik customer
$cek = mysqli_query($koneksi, "SELECT * FROM invoice 
                               WHERE invoice_id='$id_invoice' 
                               AND invoice_customer='$id_customer' LIMIT 1");

if (!$cek || mysqli_num_rows($cek) === 0) {
    $_SESSION['error'] = "Data invoice tidak valid.";
    header("location:customer_pesanan.php");
    exit;
}
?>

<div class="container py-5">
    <h3 class="mb-4">Ajukan Retur Barang</h3>

    <?php if (isset($_SESSION['error'])): ?>
        <div class="alert alert-danger"><?= htmlspecialchars($_SESSION['error']); ?></div>
        <?php unset($_SESSION['error']); ?>
    <?php endif; ?>

    <?php if (isset($_SESSION['success'])): ?>
        <div class="alert alert-success"><?= htmlspecialchars($_SESSION['success']); ?></div>
        <?php unset($_SESSION['success']); ?>
    <?php endif; ?>

    <form method="post" action="customer_retur_act.php" enctype="multipart/form-data" class="border p-4 rounded shadow-sm">
        <input type="hidden" name="id_invoice" value="<?= htmlspecialchars($id_invoice); ?>">
        <input type="hidden" name="id_produk" value="<?= htmlspecialchars($id_produk); ?>">

        <div class="form-group mb-3">
            <label for="alasan">Alasan Retur</label>
            <textarea name="alasan" id="alasan" class="form-control" rows="4" required></textarea>
        </div>

        <div class="form-group mb-3">
            <label for="foto">Upload Foto Bukti (opsional)</label>
            <input type="file" name="foto" id="foto" class="form-control">
            <small class="form-text text-muted">Format: JPG, JPEG, PNG, WEBP (maks 2MB)</small>
        </div>

        <div class="d-flex gap-2">
            <button type="submit" class="btn btn-danger">Ajukan Retur</button>
            <a href="customer_pesanan.php" class="btn btn-secondary">Batal</a>
        </div>
    </form>
</div>

<?php include 'footer.php'; ?>
