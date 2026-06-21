<?php
// customer_pesanan.php — versi revisi stabil dan lengkap (dengan fix link feedback & retur)
session_start();
include 'koneksi.php';
include 'header.php';

// Pastikan sudah login
if (!isset($_SESSION['customer'])) {
    header("Location: masuk.php?alert=login-dulu");
    exit;
}

$customer = $_SESSION['customer'];
$id_customer = is_array($customer) ? (int)($customer['id'] ?? $customer['customer_id'] ?? 0) : (int)$customer;
if ($id_customer <= 0) {
    header("Location: masuk.php?alert=login-dulu");
    exit;
}
?>

<style>
.pesanan-card { border: 1px solid #e9e9e9; border-radius:10px; margin-bottom:18px; overflow:hidden; box-shadow:0 2px 6px rgba(0,0,0,0.03); }
.pesanan-header { background:#fbfbfb; padding:12px 16px; display:flex; justify-content:space-between; align-items:center; border-bottom:1px solid #f1f1f1; }
.pesanan-body { padding:14px 16px; }
.pesanan-item { display:flex; gap:12px; padding:10px 0; border-bottom:1px dashed #eee; align-items:flex-start; }
.pesanan-item:last-child { border-bottom:none; }
.item-img { width:72px; height:72px; object-fit:cover; border-radius:8px; background:#fff; border:1px solid #f0f0f0; }
.item-info { flex:1; }
.status-badge { padding:6px 10px; border-radius:8px; font-weight:600; font-size:13px; }
.status-0 { background:#fff3cd; color:#856404; }
.status-1 { background:#e2e3e5; color:#41464b; }
.status-2 { background:#f8d7da; color:#721c24; }
.status-3 { background:#cfe2ff; color:#084298; }
.status-4 { background:#d1ecf1; color:#0c5460; }
.status-5 { background:#d4edda; color:#155724; }
</style>

<div class="container py-4">
    <h3 class="mb-3">📦 Pesanan Saya</h3>

    <?php if (!empty($_SESSION['success'])): ?>
        <div class="alert alert-success"><?= htmlspecialchars($_SESSION['success']); ?></div>
        <?php unset($_SESSION['success']); endif; ?>

    <?php
    // Ambil semua invoice berdasarkan customer
    $invs_q = mysqli_query($koneksi, "SELECT * FROM invoice WHERE invoice_customer='$id_customer' ORDER BY invoice_id DESC");
    if (!$invs_q || mysqli_num_rows($invs_q) == 0) {
        echo "<div class='alert alert-info'>Belum ada pesanan.</div>";
        include 'footer.php';
        exit;
    }

    // Loop setiap invoice
    while ($inv = mysqli_fetch_assoc($invs_q)):
        $id_invoice = (int)$inv['invoice_id'];
        $status = isset($inv['invoice_status']) ? (int)$inv['invoice_status'] : 0;
        $tanggal = isset($inv['invoice_tanggal']) ? date('d M Y', strtotime($inv['invoice_tanggal'])) : '-';

        // Map status
        $statusTextMap = [
            0 => "Menunggu Pembayaran",
            1 => "Menunggu Konfirmasi Admin",
            2 => "Ditolak",
            3 => "Diproses",
            4 => "Dikirim",
            5 => "Selesai"
        ];
        $statusText = $statusTextMap[$status] ?? "Tidak Diketahui";
    ?>
    <div class="pesanan-card">
        <div class="pesanan-header">
            <div>
                <strong>INVOICE-<?= str_pad($id_invoice,4,'0',STR_PAD_LEFT); ?></strong>
                &nbsp;•&nbsp; <small class="text-muted"><?= $tanggal; ?></small>
            </div>
            <div>
                <span class="status-badge status-<?= $status; ?>"><?= htmlspecialchars($statusText); ?></span>
            </div>
        </div>

        <div class="pesanan-body">
            <?php
            // Ambil detail produk berdasarkan invoice_id
            $details_q = mysqli_query($koneksi, "SELECT * FROM invoice_detail WHERE invoice_id='$id_invoice'");
            if (!$details_q || mysqli_num_rows($details_q) == 0) {
                echo "<div class='alert alert-light'>Tidak ada detail produk untuk invoice ini.</div>";
            } else {
                while ($d = mysqli_fetch_assoc($details_q)):
                    $pid = (int)$d['produk_id'];
                    $p_q = mysqli_query($koneksi, "SELECT * FROM produk WHERE produk_id='$pid' LIMIT 1");
                    $p = ($p_q && mysqli_num_rows($p_q)) ? mysqli_fetch_assoc($p_q) : [];

                    $produk_nama = $p['produk_nama'] ?? $d['produk_nama'] ?? 'Produk';
                    $gambar_field = $p['produk_gambar'] ?? $p['produk_foto1'] ?? $p['gambar'] ?? '';
                    $gambar_path = !empty($gambar_field) ? 'gambar/produk/' . $gambar_field : 'gambar/no-image.png';

                    $harga_awal = (float)($d['harga_awal'] ?? $p['produk_harga'] ?? 0);
                    $diskon = (float)($d['diskon_persen'] ?? $p['produk_diskon'] ?? 0);
                    $harga_diskon = isset($d['harga_diskon']) && $d['harga_diskon'] !== null
                        ? (float)$d['harga_diskon']
                        : $harga_awal - ($harga_awal * ($diskon / 100));
                    $subtotal = (float)($d['subtotal'] ?? ($harga_diskon * (int)$d['jumlah']));
            ?>
            <div class="pesanan-item">
                <div><img src="<?= htmlspecialchars($gambar_path); ?>" class="item-img" alt=""></div>
                <div class="item-info">
                    <div class="fw-semibold"><?= htmlspecialchars($produk_nama); ?></div>
                    <div class="text-muted small">Jumlah: <?= (int)$d['jumlah']; ?></div>
                    <div style="margin-top:6px;">
                        <?php if ($harga_diskon < $harga_awal): ?>
                            <div class="small text-muted text-decoration-line-through">Rp <?= number_format($harga_awal,0,',','.'); ?></div>
                            <div class="fw-bold text-danger">Rp <?= number_format($harga_diskon,0,',','.'); ?></div>
                        <?php else: ?>
                            <div class="fw-bold">Rp <?= number_format($harga_awal,0,',','.'); ?></div>
                        <?php endif; ?>
                    </div>
                    <div class="mt-2 fw-bold text-success">Subtotal: Rp <?= number_format($subtotal,0,',','.'); ?></div>
                </div>
            </div>
            <?php endwhile; } ?>

            <?php
            // Ongkir dan total bayar
            $ongkir = (float)($inv['invoice_ongkir'] ?? $inv['ongkir'] ?? $inv['invoice_biaya_kirim'] ?? 0);
            $total_bayar = (float)($inv['invoice_total_bayar'] ?? 0);
            $subtotal_barang = max($total_bayar - $ongkir, 0);
            ?>
            <div class="text-end mt-3 border-top pt-2">
                <div class="small text-muted">Subtotal Barang:</div>
                <div class="fw-bold">Rp <?= number_format($subtotal_barang,0,',','.'); ?></div>
                <div class="small text-muted mt-1">Ongkos Kirim:</div>
                <div class="fw-bold text-primary">Rp <?= number_format($ongkir,0,',','.'); ?></div>
                <div class="border-top mt-2 pt-2">
                    <div class="small text-muted">Total Bayar:</div>
                    <div class="fw-bold text-danger fs-6">Rp <?= number_format($total_bayar,0,',','.'); ?></div>
                </div>
            </div>

            <div class="d-flex justify-content-between align-items-center mt-3">
                <div>
                    <?php
                    if ($status === 0) {
                        echo '<a href="customer_pembayaran.php?id=' . $id_invoice . '" class="btn btn-sm btn-danger">Bayar Sekarang</a> ';
                    } elseif ($status === 4) {
                        echo '<a href="terima_barang.php?id=' . $id_invoice . '" class="btn btn-sm btn-success" onclick="return confirm(\'Terima barang sudah diterima?\')">Terima Barang</a> ';
                    } elseif ($status === 5) {
                        // ✅ Ambil produk pertama dari invoice untuk link feedback & retur
                        $getProduk = mysqli_query($koneksi, "SELECT produk_id FROM invoice_detail WHERE invoice_id='$id_invoice' LIMIT 1");
                        $produkData = mysqli_fetch_assoc($getProduk);
                        $id_produk_link = $produkData['produk_id'] ?? 0;

                        echo '<a href="customer_feedback.php?id_invoice=' . $id_invoice . '&id_produk=' . $id_produk_link . '" class="btn btn-sm btn-primary">Beri Ulasan</a> ';
                        echo '<a href="customer_retur.php?id_invoice=' . $id_invoice . '&id_produk=' . $id_produk_link . '" class="btn btn-sm btn-outline-danger">Ajukan Retur</a> ';
                    }
                    ?>
                </div>
                <div class="text-end">
                    <a href="customer_invoice.php?id=<?= $id_invoice; ?>" class="btn btn-sm btn-outline-secondary mt-2">Lihat Detail</a>

                </div>
            </div>
        </div>
    </div>
    <?php endwhile; ?>
</div>

<?php include 'footer.php'; ?>
