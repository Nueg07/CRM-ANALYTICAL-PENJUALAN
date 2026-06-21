<?php
include 'koneksi.php';
if (session_status() === PHP_SESSION_NONE) session_start();

// cek login
if (!isset($_SESSION['customer'])) {
    header("location:masuk.php?alert=login-dulu");
    exit;
}

$customer = $_SESSION['customer'];
$id_customer = (int) $customer['id'];

$id_invoice = isset($_GET['id_invoice']) ? (int) $_GET['id_invoice'] : 0;
$id_produk  = isset($_GET['id_produk']) ? (int) $_GET['id_produk'] : 0;

// validasi invoice & produk sesuai customer + invoice selesai
$cek = mysqli_query($koneksi,"SELECT * FROM invoice 
    WHERE invoice_id='$id_invoice' 
    AND invoice_customer='$id_customer' 
    AND invoice_status='5'");
if(mysqli_num_rows($cek) == 0){
    $_SESSION['error'] = "Data invoice tidak valid atau belum selesai!";
    header("location:customer_pesanan.php");
    exit;
}

// cek apakah feedback sudah ada
$cek_fb = mysqli_query($koneksi,"SELECT * FROM feedback 
    WHERE id_produk='$id_produk' 
    AND id_customer='$id_customer'");
if(mysqli_num_rows($cek_fb) > 0){
    $_SESSION['error'] = "Feedback untuk produk ini sudah dikirim!";
    header("location:customer_pesanan.php");
    exit;
}

include 'header.php';
?>

<div class="container py-5">
    <h3 class="mb-4">Kirim Feedback</h3>

    <?php
    if (isset($_SESSION['error'])) {
        echo '<div class="alert alert-danger">'.$_SESSION['error'].'</div>';
        unset($_SESSION['error']);
    }
    if (isset($_SESSION['success'])) {
        echo '<div class="alert alert-success">'.$_SESSION['success'].'</div>';
        unset($_SESSION['success']);
    }
    ?>

    <form method="post" action="customer_feedback_act.php">
        <input type="hidden" name="id_invoice" value="<?= htmlspecialchars($id_invoice) ?>">
        <input type="hidden" name="id_produk" value="<?= htmlspecialchars($id_produk) ?>">

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
        <a href="customer_pesanan.php" class="btn btn-secondary">Batal</a>
    </form>
</div>

<?php include 'footer.php'; ?>
