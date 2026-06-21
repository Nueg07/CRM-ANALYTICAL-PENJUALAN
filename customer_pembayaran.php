<?php
session_start();
include 'koneksi.php';
include 'header.php';

// Cek login
if (!isset($_SESSION['customer'])) {
    header("Location: masuk.php?alert=login-dulu");
    exit;
}

// Ambil data customer dari session
$customer = $_SESSION['customer'];
$id_customer = (int)$customer['id']; // pastikan kolom customer pakai "id" di tabel

// Ambil ID invoice dari URL
$id_invoice = isset($_GET['id']) ? (int)$_GET['id'] : 0;

// Ambil data invoice milik customer
$invoice_q = mysqli_query($koneksi, "SELECT * FROM invoice WHERE invoice_customer='$id_customer' AND invoice_id='$id_invoice' LIMIT 1");
$invoice = mysqli_fetch_assoc($invoice_q);

// Jika invoice tidak ditemukan / bukan milik customer
if (!$invoice) {
    echo "<div class='container py-5'><div class='alert alert-danger'>Invoice tidak ditemukan atau bukan milik Anda.</div></div>";
    include 'footer.php';
    exit;
}
?>

<div class="container">
    <section class="py-5 bg-light">
        <div class="container">
            <div class="row px-4 px-lg-5 py-lg-4 align-items-center">
                <div class="col-lg-6">
                    <h1 class="h2 text-uppercase mb-0">Pembayaran Customer</h1>
                </div>
            </div>
        </div>
    </section>

    <section class="py-5">
        <div class="container p-0">
            <div class="row">
                <div class="col-lg-3 order-2 order-lg-1">
                    <?php include 'customer_sidebar.php'; ?>
                </div>

                <div class="col-lg-9 order-2 order-lg-2 mb-5 mb-lg-0">
                    <div class="card">
                        <div class="card-body">

                            <h5>INVOICE-00<?php echo $invoice['invoice_id']; ?></h5>
                            <p>Tanggal: <?php echo date('d-m-Y', strtotime($invoice['invoice_tanggal'])); ?></p>
                            <p>Total Bayar: <b>Rp <?php echo number_format($invoice['invoice_total_bayar']); ?></b></p>

                            <form action="customer_pembayaran_act.php" method="post" enctype="multipart/form-data">
                                <input type="hidden" name="id" value="<?php echo $id_invoice; ?>">

                                <div class="form-group">
                                    <label>Pilih Metode Pembayaran</label>
                                    <select name="metode" id="metode" class="form-control" required>
                                        <option value="">-- Pilih --</option>
                                        <option value="bank">Transfer Bank</option>
                                        <option value="cod">Bayar di Tempat (COD)</option>
                                        <option value="ewallet">E-Wallet (OVO/DANA/GoPay)</option>
                                    </select>
                                </div>

                                <!-- DETAIL PEMBAYARAN -->
                                <div id="detailPembayaran" class="mt-3" style="display:none;">
                                    <div class="alert alert-info">
                                        <h6>Informasi Pembayaran</h6>
                                        <div id="infoPembayaran"></div>
                                    </div>
                                </div>

                                <div class="form-group">
                                    <label>Upload Bukti Pembayaran</label>
                                    <input type="file" name="bukti" class="form-control-file" required>
                                </div>

                                <button type="submit" class="btn btn-danger mt-2">Upload Pembayaran</button>
                            </form>

                        </div>
                    </div>
                </div>
            </div>
        </div>
    </section>
</div>

<script>
document.getElementById("metode").addEventListener("change", function() {
    var metode = this.value;
    var detail = document.getElementById("detailPembayaran");
    var info = document.getElementById("infoPembayaran");

    if(metode === "bank"){
        detail.style.display = "block";
        info.innerHTML = `
            <p><b>Bank:</b> BRI</p>
            <p><b>No. Rekening:</b> 123-456-7890</p>
            <p><b>Atas Nama:</b> Nugraha Saputra</p>
        `;
    } else if(metode === "ewallet"){
        detail.style.display = "block";
        info.innerHTML = `
            <p><b>DANA:</b> 0838-2260-6487 (Dana Nugraha Saputra)</p>
            <p><b>OVO:</b> 0838-2260-6487 (OVO) Nugraha Saputra</p>
            <p><b>GoPay:</b> 0838-2260-6487 (GoPay Nugraha Saputra)</p>
        `;
    } else if(metode === "cod"){
        detail.style.display = "block";
        info.innerHTML = `<p><b>Pembayaran di tempat (COD)</b> - Bayar langsung ke kurir saat barang diterima.</p>`;
    } else {
        detail.style.display = "none";
        info.innerHTML = "";
    }
});
</script>

<?php include 'footer.php'; ?>
