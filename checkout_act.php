<?php
// checkout_act.php
if (session_status() !== PHP_SESSION_ACTIVE) session_start();
include 'koneksi.php';

// Ambil customer ID dari session
$customer_id = 0;
if(!empty($_SESSION['customer'])){
    $c = $_SESSION['customer'];
    if(is_array($c)){
        $customer_id = (int)($c['customer_id'] ?? $c['id'] ?? 0);
    } else {
        $customer_id = (int)$c;
    }
}
if($customer_id == 0 && isset($_SESSION['customer_id'])) $customer_id = (int)$_SESSION['customer_id'];
if($customer_id <= 0){
    echo "<script>alert('Silakan login dahulu'); location='masuk.php';</script>";
    exit;
}

// Pastikan ada produk yang akan dibeli
if(!isset($_SESSION['checkout']) || empty($_SESSION['checkout'])){
    echo "<script>alert('Tidak ada produk yang dipilih untuk checkout'); location='keranjang.php';</script>";
    exit;
}

$checkout_items = $_SESSION['checkout'];
$ongkir = isset($_POST['ongkir']) ? (float)$_POST['ongkir'] : 0;
$total_berat = isset($_POST['berat']) ? (float)$_POST['berat'] : 0;

// Hitung total bayar
$jumlah_total = 0;
foreach($checkout_items as $it){
    // Pastikan personal promo sesuai customer
    $harga_diskon = floatval($it['harga_diskon']);
    if(isset($it['diskon_persen']) && ($it['diskon_persen']>0) && ($it['diskon_persen']>0)){
        if(($it['diskon_persen']>0) && isset($it['customer_id']) && intval($it['customer_id']) !== $customer_id){
            $harga_diskon = floatval($it['harga_awal']);
            $it['diskon_persen'] = 0;
        }
    }
    $jumlah_total += $it['subtotal'];
}
$total_bayar = $jumlah_total + $ongkir;

// Mulai transaction
mysqli_begin_transaction($koneksi);

// Insert invoice
$invoice_sql = "INSERT INTO invoice (invoice_customer, invoice_tanggal, invoice_total_bayar, invoice_ongkir, invoice_status)
                VALUES ('".intval($customer_id)."', NOW(), '".floatval($total_bayar)."', '".floatval($ongkir)."', 0)";
$ok = mysqli_query($koneksi, $invoice_sql);
if(!$ok){
    mysqli_rollback($koneksi);
    die("Gagal membuat invoice: ".mysqli_error($koneksi));
}
$invoice_id = mysqli_insert_id($koneksi);

// Proses setiap produk checkout
foreach($checkout_items as $item){
    $pid = intval($item['id']);
    $qty = intval($item['jumlah']);
    $harga_awal = floatval($item['harga_awal'] ?? $item['harga_diskon']);
    $diskon = floatval($item['diskon_persen'] ?? 0);
    $harga_diskon = floatval($item['harga_diskon']);
    $subtotal = floatval($item['subtotal']);

    // Pastikan personal promo sesuai customer
    if(isset($item['promo_jenis']) && $item['promo_jenis']=='personal'){
        if(intval($item['customer_id'] ?? 0) !== $customer_id){
            $harga_diskon = $harga_awal;
            $diskon = 0;
            $subtotal = $harga_diskon*$qty;
        }
    }

    // Cek stok sebelum insert
    $stok_q = mysqli_query($koneksi, "SELECT produk_jumlah FROM produk WHERE produk_id='$pid' LIMIT 1");
    $stok_row = $stok_q ? mysqli_fetch_assoc($stok_q) : null;
    $stok_now = $stok_row ? (int)$stok_row['produk_jumlah'] : 0;
    if($qty > $stok_now){
        mysqli_rollback($koneksi);
        die("Stok produk tidak mencukupi untuk '{$item['nama']}'");
    }

    // Insert invoice_detail
    $detail_sql = "INSERT INTO invoice_detail (invoice_id, produk_id, jumlah, harga_awal, diskon_persen, harga_diskon, subtotal)
                   VALUES ('$invoice_id','$pid','$qty','$harga_awal','$diskon','$harga_diskon','$subtotal')";
    if(!mysqli_query($koneksi, $detail_sql)){
        mysqli_rollback($koneksi);
        die("Gagal insert detail: ".mysqli_error($koneksi));
    }

    // Update stok produk
    $new_stok = $stok_now - $qty;
    if(!mysqli_query($koneksi, "UPDATE produk SET produk_jumlah='$new_stok' WHERE produk_id='$pid'")){
        mysqli_rollback($koneksi);
        die("Gagal update stok produk: ".mysqli_error($koneksi));
    }

    // Hapus produk dari keranjang
    if(isset($_SESSION['keranjang'])){
        foreach($_SESSION['keranjang'] as $k => $cart_item){
            if(intval($cart_item['produk']) === $pid){
                unset($_SESSION['keranjang'][$k]);
                break;
            }
        }
    }
}

// Commit transaction
mysqli_commit($koneksi);

// Hapus session checkout
unset($_SESSION['checkout']);

// Pesan sukses
$_SESSION['success'] = "Pesanan berhasil dibuat!";
header("Location: customer_pesanan.php");
exit;
?>
