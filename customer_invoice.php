<?php
// customer_invoice.php
if (session_status() === PHP_SESSION_NONE) session_start();
include 'koneksi.php';
include 'header.php';

// ===== Pastikan Login =====
if (!isset($_SESSION['customer'])) {
    header("Location: masuk.php?alert=login-dulu");
    exit;
}

// ===== Ambil ID Customer =====
$customer = $_SESSION['customer'];
$customer_id = 0;
if (is_array($customer)) {
    $customer_id = (int)($customer['customer_id'] ?? $customer['id'] ?? 0);
} elseif (is_numeric($customer)) {
    $customer_id = (int)$customer;
}
if ($customer_id <= 0) {
    header("Location: masuk.php?alert=login-dulu");
    exit;
}

// ===== Validasi ID Invoice =====
if (!isset($_GET['id']) || empty($_GET['id'])) {
    echo "<script>alert('Invoice tidak ditemukan'); location='customer_pesanan.php';</script>";
    exit;
}
$invoice_id = (int)$_GET['id'];

// ===== Ambil Data Invoice + Wilayah =====
$inv_q = mysqli_query($koneksi, "
    SELECT i.*, p.nama AS provinsi_nama, k.nama AS kabupaten_nama, kec.nama AS kecamatan_nama, d.nama AS desa_nama
    FROM invoice i
    LEFT JOIN provinsi p ON i.invoice_provinsi = p.id
    LEFT JOIN kota k ON i.invoice_kabupaten = k.id
    LEFT JOIN kecamatan kec ON i.invoice_kecamatan = kec.id
    LEFT JOIN desa d ON i.invoice_desa = d.id
    WHERE i.invoice_id = '$invoice_id' AND i.invoice_customer = '$customer_id'
");
$inv = $inv_q ? mysqli_fetch_assoc($inv_q) : null;
if (!$inv) {
    echo "<script>alert('Invoice tidak ditemukan'); location='customer_pesanan.php';</script>";
    exit;
}

// ===== Ambil Data Customer =====
$cust_q = mysqli_query($koneksi, "
    SELECT c.*, p.nama AS provinsi_nama, k.nama AS kabupaten_nama, kec.nama AS kecamatan_nama, d.nama AS desa_nama
    FROM customer c
    LEFT JOIN provinsi p ON c.provinsi_id = p.id
    LEFT JOIN kota k ON c.kota_id = k.id
    LEFT JOIN kecamatan kec ON c.kecamatan_id = kec.id
    LEFT JOIN desa d ON c.desa_id = d.id
    WHERE c.customer_id = '$customer_id' LIMIT 1
");
$cust = $cust_q && mysqli_num_rows($cust_q) ? mysqli_fetch_assoc($cust_q) : null;

// ===== Siapkan Data Tampilan =====
$display_name   = trim($inv['invoice_nama'] ?? '');
$display_alamat = trim($inv['invoice_alamat'] ?? '');
$display_hp     = trim($inv['invoice_hp'] ?? '');

if ($display_name === '' && $cust) $display_name = trim($cust['customer_nama'] ?? '');
if ($display_hp === '' && $cust) $display_hp = trim($cust['customer_hp'] ?? '');
if ($display_alamat === '' && $cust) $display_alamat = trim($cust['customer_alamat'] ?? '');

// ===== Gabungkan Alamat Lengkap =====
$alamat_parts = [];
if ($display_alamat !== '') $alamat_parts[] = $display_alamat;

// Prioritas: data invoice, fallback ke data customer
$prov = $inv['provinsi_nama'] ?: ($cust['provinsi_nama'] ?? '');
$kab  = $inv['kabupaten_nama'] ?: ($cust['kabupaten_nama'] ?? '');
$kec  = $inv['kecamatan_nama'] ?: ($cust['kecamatan_nama'] ?? '');
$des  = $inv['desa_nama'] ?: ($cust['desa_nama'] ?? '');

if ($des) $alamat_parts[] = $des;
if ($kec) $alamat_parts[] = "Kec. " . $kec;
if ($kab) $alamat_parts[] = $kab;
if ($prov) $alamat_parts[] = $prov;

$full_address = implode(', ', array_filter($alamat_parts));

// ===== Ambil Produk dari Invoice Detail =====
$prod_q = mysqli_query($koneksi, "
    SELECT d.*, p.produk_nama, p.produk_foto1
    FROM invoice_detail d
    LEFT JOIN produk p ON d.produk_id = p.produk_id
    WHERE d.invoice_id = '$invoice_id'
");

// ===== Status =====
$status_labels = [
    0 => "Menunggu Pembayaran",
    1 => "Menunggu Konfirmasi",
    2 => "Ditolak",
    3 => "Diproses",
    4 => "Dikirim",
    5 => "Selesai"
];
$badge_classes = [
    0 => "warning",
    1 => "secondary",
    2 => "danger",
    3 => "primary",
    4 => "info",
    5 => "success"
];
$status = (int)($inv['invoice_status'] ?? 0);
$label = $status_labels[$status] ?? "Tidak Diketahui";
$badge = $badge_classes[$status] ?? "secondary";
?>

<div class="container py-5">
  <h3 class="mb-4">🧾 Detail Invoice</h3>

  <div class="card mb-4">
    <div class="card-body">
      <h5>Invoice #<?= "INV-" . str_pad($invoice_id, 4, '0', STR_PAD_LEFT); ?></h5>
      <p><strong>Penerima:</strong> <?= htmlspecialchars($display_name ?: '-'); ?></p>
      <p><strong>Alamat:</strong> <?= htmlspecialchars($full_address ?: '-'); ?></p>
      <p><strong>Telepon:</strong> <?= htmlspecialchars($display_hp ?: '-'); ?></p>
      <p><strong>Status:</strong> <span class="badge bg-<?= $badge; ?>"><?= htmlspecialchars($label); ?></span></p>
    </div>
  </div>

  <div class="card mb-4">
    <div class="card-header bg-light">🛒 Daftar Produk</div>
    <div class="card-body p-0">
      <table class="table table-bordered mb-0">
        <thead class="table-light">
          <tr>
            <th>Produk</th>
            <th>Harga Awal</th>
            <th>Diskon (%)</th>
            <th>Jumlah</th>
            <th>Subtotal</th>
            <th width="220">Opsi / Riwayat Retur</th>
          </tr>
        </thead>
        <tbody>
        <?php
        $total_barang = 0;
        if ($prod_q && mysqli_num_rows($prod_q) > 0):
            while ($row = mysqli_fetch_assoc($prod_q)):
                $harga_awal     = (float)($row['harga_awal'] ?? 0);
                $diskon_persen  = (float)($row['diskon_persen'] ?? 0);
                $jumlah         = (int)($row['jumlah'] ?? 1);
                $subtotal       = (float)($row['subtotal'] ?? ($harga_awal * $jumlah));
                $total_barang  += $subtotal;

                $prod_id = (int)$row['produk_id'];
                $foto = !empty($row['produk_foto1']) ? ('gambar/produk/' . $row['produk_foto1']) : 'gambar/no-image.png';
        ?>
          <tr>
            <td>
              <img src="<?= htmlspecialchars($foto); ?>" width="60" height="60" class="me-2" style="object-fit:cover;border:1px solid #eee;">
              <?= htmlspecialchars($row['produk_nama'] ?? 'Produk'); ?>
            </td>
            <td>Rp <?= number_format($harga_awal,0,',','.'); ?></td>
            <td><?= $diskon_persen; ?>%</td>
            <td><?= $jumlah; ?></td>
            <td>Rp <?= number_format($subtotal,0,',','.'); ?></td>
            <td>
              <?php if ($status === 5): ?>
                <?php
                $cek_fb = mysqli_query($koneksi, "SELECT id_feedback FROM feedback WHERE id_produk = '$prod_id' AND id_customer = '$customer_id' LIMIT 1");
                if ($cek_fb && mysqli_num_rows($cek_fb) == 0): ?>
                  <a href="customer_feedback.php?id_invoice=<?= $invoice_id; ?>&id_produk=<?= $prod_id; ?>" class="btn btn-sm btn-warning mb-1">★ Feedback</a>
                <?php else: ?>
                  <span class="badge bg-success mb-1">Feedback Dikirim</span>
                <?php endif; ?>
                <br>
                <?php
                $cek_retur = mysqli_query($koneksi, "SELECT retur_id, retur_status, retur_alasan, retur_bukti, retur_tanggal FROM retur WHERE invoice_id = '$invoice_id' AND produk_id = '$prod_id' AND customer_id = '$customer_id' ORDER BY retur_tanggal DESC");
                if ($cek_retur && mysqli_num_rows($cek_retur) == 0): ?>
                    <a href="customer_retur.php?id_invoice=<?= $invoice_id; ?>&id_produk=<?= $prod_id; ?>" class="btn btn-sm btn-outline-danger mt-1">🔁 Ajukan Retur</a>
                <?php else: ?>
                    <?php while ($r = mysqli_fetch_assoc($cek_retur)): ?>
                        <div class="mt-1 p-2" style="background:#f8f9fa;border-radius:6px;">
                            <div><strong><?= htmlspecialchars($r['retur_status'] ?? 'Diajukan'); ?></strong> — <small><?= date('d M Y H:i', strtotime($r['retur_tanggal'] ?? 'now')); ?></small></div>
                            <?php if (!empty($r['retur_alasan'])): ?>
                                <div style="font-size:13px;color:#333;"><?= nl2br(htmlspecialchars($r['retur_alasan'])); ?></div>
                            <?php endif; ?>
                            <?php if (!empty($r['retur_bukti'])): ?>
                                <div class="mt-1"><a href="<?= 'gambar/retur/' . htmlspecialchars($r['retur_bukti']); ?>" target="_blank" class="small">Lihat Bukti</a></div>
                            <?php endif; ?>
                        </div>
                    <?php endwhile; ?>
                <?php endif; ?>
              <?php else: ?>
                <small class="text-muted">Opsi tersedia setelah pesanan selesai.</small>
              <?php endif; ?>
            </td>
          </tr>
        <?php endwhile; else: ?>
          <tr><td colspan="6" class="text-center">Tidak ada produk pada invoice ini.</td></tr>
        <?php endif; ?>
        </tbody>
        <tfoot>
          <tr>
            <th colspan="4" class="text-end">Total Produk</th>
            <th colspan="2">Rp <?= number_format($total_barang,0,',','.'); ?></th>
          </tr>
          <tr>
            <th colspan="4" class="text-end">Ongkir</th>
            <th colspan="2">Rp <?= number_format((float)($inv['invoice_ongkir'] ?? 0),0,',','.'); ?></th>
          </tr>
          <tr>
            <th colspan="4" class="text-end">Total Bayar</th>
            <th colspan="2" class="text-danger">Rp <?= number_format((float)($inv['invoice_total_bayar'] ?? 0),0,',','.'); ?></th>
          </tr>
        </tfoot>
      </table>
    </div>
  </div>

  <div class="d-flex justify-content-between">
    <a href="customer_pesanan.php" class="btn btn-secondary">← Kembali</a>
    <a href="cetak_invoice.php?id=<?= $invoice_id; ?>" target="_blank" class="btn btn-primary">Cetak Invoice</a>
  </div>
</div>

<?php include 'footer.php'; ?>
