<?php
include 'koneksi.php';

// ==========================
// Input dari checkout
// ==========================
$desa_id = isset($_POST['desa_id']) ? (int)$_POST['desa_id'] : 0;
$berat = isset($_POST['berat']) ? (int)$_POST['berat'] : 0; // dalam gram
$total_barang = isset($_POST['total_barang']) ? (int)$_POST['total_barang'] : 0;

// ==========================
// Ambil data wilayah tujuan
// ==========================
$q = mysqli_query($koneksi, "
    SELECT 
        d.id AS desa_id, d.nama AS desa_nama, d.ongkir_dasar,
        kc.nama AS kecamatan_nama,
        k.nama AS kota_nama,
        p.nama AS provinsi_nama
    FROM desa d
    LEFT JOIN kecamatan kc ON kc.id = d.kecamatan_id
    LEFT JOIN kota k ON k.id = kc.kota_id
    LEFT JOIN provinsi p ON p.id = k.provinsi_id
    WHERE d.id = '$desa_id'
    LIMIT 1
");

$d = $q ? mysqli_fetch_assoc($q) : null;

// ==========================
// Jika wilayah tidak ditemukan
// ==========================
if (!$d) {
    $ongkir = 15000;
    $total_bayar = $total_barang + $ongkir;
    echo json_encode([
        "ongkir" => number_format($ongkir, 0, ',', '.'),
        "total_bayar" => number_format($total_bayar, 0, ',', '.'),
        "ongkir_raw" => $ongkir,
        "total_bayar_raw" => $total_bayar
    ]);
    exit;
}

// ==========================
// Asal toko (tetap): Selajambe, Kuningan, Jawa Barat
// ==========================
$asal_kecamatan = "selajambe";
$asal_kota = "kuningan";
$asal_provinsi = "jawa barat";

// ==========================
// Hitung berat → dalam kilogram dibulatkan ke atas
// ==========================
$berat_kg = max(1, ceil($berat / 1000)); // minimal 1 kg
$tarif_per_kg = 4000; // biaya per kg tambahan

// ==========================
// Tentukan ongkir dasar wilayah
// ==========================
$provinsi_tujuan  = strtolower($d['provinsi_nama'] ?? '');
$kota_tujuan      = strtolower($d['kota_nama'] ?? '');
$kecamatan_tujuan = strtolower($d['kecamatan_nama'] ?? '');
$ongkir_dasar = 0;

if (!empty($d['ongkir_dasar']) && $d['ongkir_dasar'] > 0) {
    // Jika sudah ada nilai ongkir_dasar di database
    $ongkir_dasar = (int)$d['ongkir_dasar'];
} else {
    // Logika otomatis berdasarkan hierarki wilayah
    if (strpos($provinsi_tujuan, 'jawa barat') !== false) {
        if (strpos($kota_tujuan, 'kuningan') !== false) {
            if (strpos($kecamatan_tujuan, 'selajambe') !== false) {
                $ongkir_dasar = 6000; // sama kecamatan
            } else {
                $ongkir_dasar = 9000; // beda kecamatan tapi masih 1 kabupaten
            }
        } else {
            $ongkir_dasar = 13000; // beda kabupaten tapi masih Jawa Barat
        }
    } else {
        $ongkir_dasar = 22000; // beda provinsi
    }
}

// ==========================
// Hitung total ongkir
// ==========================
$ongkir = $ongkir_dasar + ($tarif_per_kg * $berat_kg);
$total_bayar = $total_barang + $ongkir;

// ==========================
// Output JSON ke checkout.php
// ==========================
echo json_encode([
    "ongkir" => number_format($ongkir, 0, ',', '.'),
    "total_bayar" => number_format($total_bayar, 0, ',', '.'),
    "ongkir_raw" => $ongkir,
    "total_bayar_raw" => $total_bayar,
    "detail" => [
        "wilayah_tujuan" => ucwords($d['kecamatan_nama'] . ', ' . $d['kota_nama'] . ', ' . $d['provinsi_nama']),
        "ongkir_dasar" => $ongkir_dasar,
        "tarif_per_kg" => $tarif_per_kg,
        "total_kg" => $berat_kg,
        "total_berat_input" => $berat . " gram"
    ]
]);
?>
