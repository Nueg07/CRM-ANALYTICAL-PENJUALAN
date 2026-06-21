<?php
if (session_status() !== PHP_SESSION_ACTIVE) session_start();
include 'koneksi.php';
include 'header.php';

if (!isset($_SESSION['keranjang']) || !is_array($_SESSION['keranjang']) || count($_SESSION['keranjang']) == 0) {
    echo "<div class='container py-5 text-center'>
            <div class='alert alert-info shadow-sm p-4 rounded'>
                <h5>🛍️ Keranjang Belanja Kosong</h5>
                <p>Yuk belanja sekarang dan temukan produk terbaik!</p>
                <a href='index.php' class='btn btn-dark mt-2'>Belanja Sekarang</a>
            </div>
          </div>";
    include 'footer.php';
    exit;
}

$today = date('Y-m-d');
$jumlah_total = 0;
$id_customer = $_SESSION['customer']['id'] ?? 0;
?>

<div class="container py-5">
    <div class="d-flex justify-content-between align-items-center mb-4">
        <h2 class="fw-bold text-uppercase">🛒 Keranjang Belanja</h2>
        <a href="index.php" class="btn btn-outline-dark btn-sm"><i class="fa fa-shopping-bag"></i> Lanjut Belanja</a>
    </div>

    <form id="formKeranjang" method="post" action="checkout.php">
        <div class="table-responsive shadow-sm rounded">
            <table class="table align-middle table-hover mb-0">
                <thead class="table-dark text-center">
                    <tr>
                        <th><input type="checkbox" id="selectAll" title="Pilih Semua"></th>
                        <th>#</th>
                        <th>Produk</th>
                        <th>Harga</th>
                        <th>Qty</th>
                        <th>Subtotal</th>
                        <th>Aksi</th>
                    </tr>
                </thead>
                <tbody>
                    <?php
                    $no = 1;
                    foreach ($_SESSION['keranjang'] as $index => $item) {
                        $id_produk = (int)$item['produk'];
                        $qty = max(1, (int)$item['jumlah']);

                        $res = mysqli_query($koneksi, "SELECT * FROM produk WHERE produk_id='$id_produk' LIMIT 1");
                        $prod = $res ? mysqli_fetch_assoc($res) : null;
                        if (!$prod) continue;

                        $harga_awal = (float)$prod['produk_harga'];

                        // Ambil promo aktif untuk produk ini & customer
                        $promo_q = mysqli_query($koneksi, "
                            SELECT * FROM promo
                            WHERE status='aktif'
                              AND tanggal_mulai <= '$today'
                              AND tanggal_selesai >= '$today'
                              AND (produk_id=0 OR produk_id='$id_produk')
                              AND (jenis='umum' OR (jenis='personal' AND customer_id='$id_customer'))
                            ORDER BY jenis='personal' DESC, diskon_persen DESC
                            LIMIT 1
                        ");
                        $promo = ($promo_q && mysqli_num_rows($promo_q) > 0) ? mysqli_fetch_assoc($promo_q) : null;

                        $diskon = 0;
                        $promo_jenis = '';
                        $harga_diskon = $harga_awal;

                        if ($promo) {
                            if ($promo['jenis'] === 'umum' || ($promo['jenis'] === 'personal' && $promo['customer_id'] == $id_customer)) {
                                $diskon = (float)$promo['diskon_persen'];
                                $promo_jenis = $promo['jenis'];
                                $harga_diskon = $harga_awal - ($harga_awal * $diskon / 100);
                            }
                        }

                        $subtotal = $harga_diskon * $qty;
                        $jumlah_total += $subtotal;

                        $_SESSION['keranjang'][$index]['harga_diskon'] = $harga_diskon;
                        $_SESSION['keranjang'][$index]['diskon_persen'] = $diskon;
                        $_SESSION['keranjang'][$index]['promo_jenis'] = $promo_jenis;
                    ?>
                        <tr>
                            <td class="text-center">
                                <input type="checkbox" name="selected_produk[]" value="<?= $id_produk ?>" class="itemCheckbox">
                            </td>
                            <td class="text-center fw-semibold"><?= $no++; ?></td>
                            <td>
                                <div class="d-flex align-items-center">
                                    <img src="gambar/produk/<?= $prod['produk_foto1'] ?? 'noimage.png' ?>" alt="" width="60" class="rounded me-3 border">
                                    <div>
                                        <strong><?= htmlspecialchars($prod['produk_nama']); ?></strong><br>
                                        <?php if ($diskon > 0): ?>
                                            <span class="badge bg-success mt-1">Promo <?= ucfirst($promo_jenis) ?> <?= $diskon ?>%</span>
                                        <?php endif; ?>
                                    </div>
                                </div>
                            </td>
                            <td class="text-center">
                                <strong>Rp <?= number_format($harga_diskon, 0, ',', '.'); ?></strong>
                                <?php if ($diskon > 0): ?>
                                    <br><small class="text-muted"><s>Rp <?= number_format($harga_awal, 0, ',', '.'); ?></s></small>
                                <?php endif; ?>
                            </td>
                            <td class="text-center" style="width:100px;">
                                <input type="number" name="jumlah[<?= $id_produk ?>]" value="<?= $qty ?>" min="1" class="form-control form-control-sm text-center">
                            </td>
                            <td class="text-center text-success fw-semibold">Rp <?= number_format($subtotal, 0, ',', '.'); ?></td>
                            <td class="text-center">
                                <a href="keranjang_hapus.php?id=<?= $id_produk ?>" class="btn btn-sm btn-outline-danger" onclick="return confirm('Hapus produk ini dari keranjang?')">
                                    <i class="fa fa-trash"></i>
                                </a>
                            </td>
                        </tr>
                    <?php } ?>
                </tbody>
                <tfoot class="table-light">
                    <tr>
                        <th colspan="5" class="text-end text-uppercase">Total Keseluruhan :</th>
                        <th colspan="2" class="text-success fw-bold fs-5">Rp <?= number_format($jumlah_total, 0, ',', '.'); ?></th>
                    </tr>
                </tfoot>
            </table>
        </div>

        <div class="mt-4 d-flex justify-content-between align-items-center">
            <div>
                <button class="btn btn-outline-dark" onclick="event.preventDefault(); this.form.action='keranjang_update.php'; this.form.submit();">
                    <i class="fa fa-sync-alt"></i> Update Keranjang
                </button>
            </div>
            <div>
                <button class="btn btn-dark px-4" type="submit">
                    <i class="fa fa-credit-card"></i> Checkout Barang Terpilih
                </button>
            </div>
        </div>
    </form>
</div>

<script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>
<script>
$("#selectAll").on("click", function(){
    $(".itemCheckbox").prop("checked", $(this).prop("checked"));
});
$(".itemCheckbox").on("click", function(){
    if(!$(this).prop("checked")){
        $("#selectAll").prop("checked", false);
    }
});
</script>

<?php include 'footer.php'; ?>
