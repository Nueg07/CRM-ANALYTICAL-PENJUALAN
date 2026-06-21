<?php
// checkout.php
if (session_status() !== PHP_SESSION_ACTIVE) session_start();
include 'koneksi.php';
include 'header.php';

// Ambil customer id dari session
$customer_id = 0;
if (!empty($_SESSION['customer'])) {
    $c = $_SESSION['customer'];
    if (is_array($c)) {
        $customer_id = isset($c['customer_id']) ? (int)$c['customer_id'] : (int)($c['id'] ?? 0);
    } else {
        $customer_id = (int)$c;
    }
}
if ($customer_id == 0 && isset($_SESSION['customer_id'])) $customer_id = (int)$_SESSION['customer_id'];
if ($customer_id <= 0) {
    echo "<script>alert('Silakan login terlebih dahulu'); location='masuk.php';</script>";
    exit;
}

// Ambil data customer
$cust_q = mysqli_query($koneksi, "SELECT * FROM customer WHERE customer_id='" . intval($customer_id) . "' LIMIT 1");
$customer_data = $cust_q ? mysqli_fetch_assoc($cust_q) : null;
if (!$customer_data) {
    echo "<script>alert('Data customer tidak ditemukan'); location='masuk.php';</script>";
    exit;
}

// Pastikan ada keranjang
if (!isset($_SESSION['keranjang']) || !is_array($_SESSION['keranjang']) || count($_SESSION['keranjang']) == 0) {
    echo "<script>alert('Keranjang kosong'); location='keranjang.php';</script>";
    exit;
}

// Pastikan user memilih produk
if (!isset($_POST['selected_produk']) || empty($_POST['selected_produk'])) {
    echo "<script>alert('Pilih produk yang ingin di-checkout terlebih dahulu!'); location='keranjang.php';</script>";
    exit;
}

$selected_ids = $_POST['selected_produk'];
$jumlahs = $_POST['jumlah'] ?? [];

// Helper nama wilayah
function getNamaWilayah($table, $id, $koneksi)
{
    $id = (int)$id;
    if ($id <= 0) return '-';
    $table = preg_replace('/[^a-z_]/', '', $table);
    $q = mysqli_query($koneksi, "SELECT nama FROM $table WHERE id='" . $id . "' LIMIT 1");
    $r = $q ? mysqli_fetch_assoc($q) : null;
    return $r ? $r['nama'] : '-';
}

$nama_provinsi  = getNamaWilayah('provinsi', $customer_data['provinsi_id'] ?? 0, $koneksi);
$nama_kota      = getNamaWilayah('kota', $customer_data['kota_id'] ?? 0, $koneksi);
$nama_kecamatan = getNamaWilayah('kecamatan', $customer_data['kecamatan_id'] ?? 0, $koneksi);
$nama_desa      = getNamaWilayah('desa', $customer_data['desa_id'] ?? 0, $koneksi);

// =============================
// Proses checkout: hanya produk yang dicentang
// =============================
$today = date('Y-m-d');
$selected_cart = [];
$jumlah_total = 0;
$total_berat = 0;

foreach ($_SESSION['keranjang'] as $item) {
    $id_produk = (int)($item['produk'] ?? 0);
    if (!in_array($id_produk, $selected_ids)) continue;

    $jumlah = isset($jumlahs[$id_produk]) ? max(1, (int)$jumlahs[$id_produk]) : max(1, (int)$item['jumlah']);

    // Ambil data produk
    $pq = mysqli_query($koneksi, "SELECT * FROM produk WHERE produk_id='$id_produk' LIMIT 1");
    $p = $pq ? mysqli_fetch_assoc($pq) : null;
    if (!$p) continue;

    // Cek stok
    if ($jumlah > $p['produk_jumlah']) {
        echo "<script>alert('Stok \"{$p['produk_nama']}\" tidak mencukupi!'); window.location='keranjang.php';</script>";
        exit;
    }

    $harga_awal = (float)$p['produk_harga'];
    $total_berat += ((float)$p['produk_berat']) * $jumlah;

    // Promo aktif hanya untuk customer yang sesuai
    $promo_q = mysqli_query($koneksi, "
        SELECT * FROM promo 
        WHERE status='aktif' 
          AND tanggal_mulai <= '$today' 
          AND tanggal_selesai >= '$today'
          AND (produk_id=0 OR produk_id='$id_produk')
          AND (jenis='umum' OR (jenis='personal' AND customer_id='$customer_id'))
        ORDER BY jenis='personal' DESC, diskon_persen DESC
        LIMIT 1
    ");
    $promo = ($promo_q && mysqli_num_rows($promo_q) > 0) ? mysqli_fetch_assoc($promo_q) : null;

    $diskon_persen = 0;
    $harga_diskon = $harga_awal;

    if ($promo) {
        if ($promo['jenis'] == 'umum' || ($promo['jenis'] == 'personal' && $promo['customer_id'] == $customer_id)) {
            $diskon_persen = (float)$promo['diskon_persen'];
            $harga_diskon = $harga_awal - ($harga_awal * $diskon_persen / 100);
        }
    }

    $subtotal = $harga_diskon * $jumlah;
    $jumlah_total += $subtotal;

    $selected_cart[] = [
        'id' => $id_produk,
        'nama' => $p['produk_nama'],
        'harga_awal' => $harga_awal,
        'harga_diskon' => $harga_diskon,
        'diskon_persen' => $diskon_persen,
        'jumlah' => $jumlah,
        'subtotal' => $subtotal
    ];
}

// =============================
// Ambil ongkir dari get_ongkir.php
// =============================
$desa_id = (int)($customer_data['desa_id'] ?? 0);
$ongkir = 0;

if ($desa_id > 0) {
    // Pastikan path ini benar ke file get_ongkir.php kamu
    $url = __DIR__ . "/get_ongkir.php";

    // Jalankan get_ongkir secara lokal (tanpa HTTP)
    $_POST_BACKUP = $_POST;
    $_POST = [
        'desa_id' => $desa_id,
        'berat' => $total_berat,
        'total_barang' => $jumlah_total
    ];

    ob_start();
    include $url;
    $result = ob_get_clean();

    $_POST = $_POST_BACKUP; // kembalikan POST semula

    if ($result) {
        $response = json_decode($result, true);
        if (isset($response['ongkir_raw'])) {
            $ongkir = (int)$response['ongkir_raw'];
        }
    }
}

// =============================
// Hitung total bayar akhir
// =============================
$total_bayar = $jumlah_total + $ongkir;

// Simpan session checkout
$_SESSION['checkout'] = $selected_cart;
?>

<div class="card">
  <div class="card-body">
    <h3>Checkout</h3>
    <div class="row">
      <div class="col-md-6">
        <h5>Alamat Pengiriman</h5>
        <p><strong><?= htmlspecialchars($customer_data['customer_nama']); ?></strong><br>
           <?= nl2br(htmlspecialchars($customer_data['customer_alamat'])); ?><br>
           <?= htmlspecialchars("$nama_provinsi / $nama_kota / $nama_kecamatan / $nama_desa"); ?><br>
           HP: <?= htmlspecialchars($customer_data['customer_hp']); ?>
        </p>
      </div>
      <div class="col-md-6">
        <h5>Ringkasan</h5>
        <table class="table">
          <thead><tr><th>Produk</th><th>Harga</th><th>Qty</th><th>Subtotal</th></tr></thead>
          <tbody>
            <?php foreach ($selected_cart as $it): ?>
              <tr>
                <td><?= htmlspecialchars($it['nama']); ?></td>
                <td>Rp <?= number_format($it['harga_diskon']); ?> <?php if ($it['diskon_persen'] > 0) echo "<br><small class='text-muted'>(-{$it['diskon_persen']}%)</small>"; ?></td>
                <td><?= $it['jumlah']; ?></td>
                <td>Rp <?= number_format($it['subtotal']); ?></td>
              </tr>
            <?php endforeach; ?>
          </tbody>
          <tfoot>
            <tr><th colspan="3">Total Berat</th><th><?= $total_berat; ?> gr</th></tr>
            <tr><th colspan="3">Ongkir</th><th>Rp <?= number_format($ongkir); ?></th></tr>
            <tr><th colspan="3">Total Bayar</th><th>Rp <?= number_format($total_bayar); ?></th></tr>
          </tfoot>
        </table>

        <form method="post" action="checkout_act.php">
          <input type="hidden" name="ongkir" value="<?= $ongkir; ?>">
          <input type="hidden" name="berat" value="<?= $total_berat; ?>">
          <?php foreach ($selected_cart as $it): ?>
            <input type="hidden" name="produk[<?= $it['id']; ?>]" value="<?= $it['jumlah']; ?>">
          <?php endforeach; ?>
          <button class="btn btn-success">Pesan Sekarang</button>
        </form>

      </div>
    </div>
  </div>
</div>

<?php include 'footer.php'; ?>
