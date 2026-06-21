<?php
session_start();
include 'koneksi.php';
include 'header.php';

// ===== Validasi Login Aman =====
$customer_id = 0;
if (isset($_SESSION['customer'])) {
    $c = $_SESSION['customer'];
    if (is_array($c)) {
        if (isset($c['customer_id'])) $customer_id = $c['customer_id'];
        elseif (isset($c['id'])) $customer_id = $c['id'];
    } elseif (is_numeric($c)) {
        $customer_id = (int)$c;
    }
}

if ($customer_id == 0) {
    echo "<script>alert('Session customer invalid, silakan login ulang'); location='masuk.php';</script>";
    exit;
}

// Ambil data customer
$cust_q = mysqli_query($koneksi, "SELECT * FROM customer WHERE customer_id='$customer_id'");
$customer_data = mysqli_fetch_assoc($cust_q);
if (!$customer_data) {
    echo "<script>alert('Data customer tidak ditemukan, silakan login ulang'); location='masuk.php';</script>";
    exit;
}

// ===== Simpan perubahan profil/Alamat =====
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['simpan_profil'])) {
    $nama      = mysqli_real_escape_string($koneksi, $_POST['nama'] ?? '');
    $email     = mysqli_real_escape_string($koneksi, $_POST['email'] ?? '');
    $hp        = mysqli_real_escape_string($koneksi, $_POST['hp'] ?? '');
    $alamat    = mysqli_real_escape_string($koneksi, $_POST['alamat'] ?? '');
    $provinsi  = (int)($_POST['provinsi'] ?? 0);
    $kota      = (int)($_POST['kota'] ?? 0);
    $kecamatan = (int)($_POST['kecamatan'] ?? 0);
    $desa      = (int)($_POST['desa'] ?? 0);

    if ($provinsi > 0 && $kota > 0 && $kecamatan > 0 && $desa > 0) {
        $update_q = mysqli_query($koneksi, "UPDATE customer SET 
            customer_nama='$nama',
            customer_email='$email',
            customer_hp='$hp',
            customer_alamat='$alamat',
            provinsi_id='$provinsi',
            kota_id='$kota',
            kecamatan_id='$kecamatan',
            desa_id='$desa'
            WHERE customer_id='$customer_id'
        ");

        if ($update_q) {
            echo "<script>alert('Profil berhasil diperbarui'); location='customer.php';</script>";
            exit;
        } else {
            echo "<script>alert('Gagal menyimpan data');</script>";
        }
    } else {
        echo "<script>alert('Semua data alamat harus diisi');</script>";
    }
}

// Ambil data Provinsi
$provinsi_q = mysqli_query($koneksi, "SELECT * FROM provinsi ORDER BY nama ASC");
?>

<style>
/* ======== Styling Tambahan Elegan ======== */
body {
    background-color: #f7f8fa;
}
.card {
    border-radius: 15px;
    border: none;
}
.card-body label {
    font-weight: 500;
}
.sidebar-menu .btn {
    text-align: left;
    background-color: #fff;
    border: 1px solid #ddd;
    border-radius: 10px;
    transition: all 0.3s;
}
.sidebar-menu .btn:hover {
    background-color: #0d6efd;
    color: #fff;
    border-color: #0d6efd;
}
.sidebar-menu i {
    width: 22px;
    text-align: center;
}
.profile-title {
    font-weight: 700;
    letter-spacing: 0.5px;
}
.form-control {
    border-radius: 10px;
}
.btn-dark {
    border-radius: 10px;
    font-weight: 500;
    transition: all 0.3s;
}
.btn-dark:hover {
    background-color: #000;
}
.section-header {
    background: linear-gradient(90deg, #ffffffff, #ffffffff);
    color: #000000ff;
    border-radius: 12px;
    padding: 20px;
    box-shadow: 0 2px 10px rgba(0,0,0,0.15);
}
</style>

<div class="container py-5">
    <section class="section-header text-center mb-5">
        <h1 class="h3 text-uppercase mb-1">👤 Profil & Alamat Customer</h1>
        <p class="mb-0">Selamat datang kembali, <strong><?= htmlspecialchars($customer_data['customer_nama'] ?? '') ?></strong> 👋</p>
    </section>

    <div class="row">
        <!-- Sidebar -->
        <div class="col-lg-3 mb-4">
            <div class="card shadow-sm">
                <div class="card-body sidebar-menu p-3">
                    <ul class="list-unstyled m-0">
                        <li class="mb-2">
                            <a href="customer.php" class="btn w-100"><i class="fa fa-home"></i> Dashboard</a>
                        </li>
                        <li class="mb-2">
                            <a href="customer_pesanan.php" class="btn w-100"><i class="fa fa-list"></i> Pesanan Saya</a>
                        </li>
                        <li class="mb-2">
                            <a href="customer_keluhan.php" class="btn w-100"><i class="fa fa-comments"></i> Chat Keluhan</a>
                        </li>
                        <li class="mb-2">
                            <a href="customer_password.php" class="btn w-100"><i class="fa fa-lock"></i> Ganti Password</a>
                        </li>
                        <li class="mb-0">
                            <a href="customer_logout.php" class="btn w-100 text-danger border-danger"><i class="fa fa-power-off"></i> Keluar</a>
                        </li>
                    </ul>
                </div>
            </div>
        </div>

        <!-- Main Profil -->
        <div class="col-lg-9">
            <div class="card shadow-sm">
                <div class="card-body p-4">
                    <h5 class="profile-title mb-4">✏️ Edit Profil & Alamat</h5>
                    <form method="post">
                        <div class="row g-3">
                            <div class="col-md-6">
                                <label>Nama Lengkap</label>
                                <input type="text" name="nama" class="form-control" value="<?= htmlspecialchars($customer_data['customer_nama'] ?? '') ?>" required>
                            </div>
                            <div class="col-md-6">
                                <label>Email</label>
                                <input type="email" name="email" class="form-control" value="<?= htmlspecialchars($customer_data['customer_email'] ?? '') ?>" required>
                            </div>
                            <div class="col-md-6">
                                <label>Nomor HP</label>
                                <input type="text" name="hp" class="form-control" value="<?= htmlspecialchars($customer_data['customer_hp'] ?? '') ?>" required>
                            </div>
                            <div class="col-md-12">
                                <label>Alamat Lengkap</label>
                                <textarea name="alamat" class="form-control" rows="3" required><?= htmlspecialchars($customer_data['customer_alamat'] ?? '') ?></textarea>
                            </div>

                            <div class="col-md-6">
                                <label>Provinsi</label>
                                <select name="provinsi" id="provinsi" class="form-control" required>
                                    <option value="">Pilih Provinsi</option>
                                    <?php while ($p = mysqli_fetch_assoc($provinsi_q)) { ?>
                                        <option value="<?= $p['id'] ?>" <?= ($p['id'] == ($customer_data['provinsi_id'] ?? 0)) ? 'selected' : '' ?>><?= htmlspecialchars($p['nama']) ?></option>
                                    <?php } ?>
                                </select>
                            </div>
                            <div class="col-md-6">
                                <label>Kota</label>
                                <select name="kota" id="kota" class="form-control" required>
                                    <option value="">Pilih Kota</option>
                                </select>
                            </div>
                            <div class="col-md-6">
                                <label>Kecamatan</label>
                                <select name="kecamatan" id="kecamatan" class="form-control" required>
                                    <option value="">Pilih Kecamatan</option>
                                </select>
                            </div>
                            <div class="col-md-6">
                                <label>Desa</label>
                                <select name="desa" id="desa" class="form-control" required>
                                    <option value="">Pilih Desa</option>
                                </select>
                            </div>
                        </div>

                        <div class="mt-4 text-end">
                            <button type="submit" name="simpan_profil" class="btn btn-dark px-4 py-2">
                                💾 Simpan Profil & Alamat
                            </button>
                        </div>
                    </form>
                </div>
            </div>
        </div>
    </div>
</div>

<!-- JQuery -->
<script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>
<script>
$(document).ready(function(){
    var provinsi = <?= $customer_data['provinsi_id'] ?? 0 ?>;
    var kota = <?= $customer_data['kota_id'] ?? 0 ?>;
    var kecamatan = <?= $customer_data['kecamatan_id'] ?? 0 ?>;
    var desa = '<?= $customer_data['desa_id'] ?? '' ?>';

    function loadKota(provinsi_id, selected=0, callback=null){
        if(!provinsi_id){
            $("#kota").html('<option value="">Pilih Kota</option>');
            return;
        }
        $.post("get_wilayah.php", {level:'kota', parent_id:provinsi_id, selected:selected}, function(data){
            $("#kota").html(data);
            if(callback) callback();
        }).fail(function(){
            $("#kota").html('<option value="">Gagal load Kota</option>');
        });
    }

    function loadKecamatan(kota_id, selected=0, callback=null){
        if(!kota_id){
            $("#kecamatan").html('<option value="">Pilih Kecamatan</option>');
            return;
        }
        $.post("get_wilayah.php", {level:'kecamatan', parent_id:kota_id, selected:selected}, function(data){
            $("#kecamatan").html(data);
            if(callback) callback();
        }).fail(function(){
            $("#kecamatan").html('<option value="">Gagal load Kecamatan</option>');
        });
    }

    function loadDesa(kecamatan_id, selected=''){
        if(!kecamatan_id){
            $("#desa").html('<option value="">Pilih Desa</option>');
            return;
        }
        $.post("get_desa.php", {kecamatan_id:kecamatan_id, selected:selected}, function(data){
            $("#desa").html(data);
        }).fail(function(){
            $("#desa").html('<option value="">Gagal load Desa</option>');
        });
    }

    if(provinsi){
        loadKota(provinsi, kota, function(){
            if(kota){
                loadKecamatan(kota, kecamatan, function(){
                    if(kecamatan){
                        loadDesa(kecamatan, desa);
                    }
                });
            }
        });
    }

    $("#provinsi").change(function(){ 
        var provId = $(this).val();
        loadKota(provId);
        $("#kecamatan,#desa").html('<option value="">Pilih Kecamatan/Desa</option>'); 
    });

    $("#kota").change(function(){ 
        var kotaId = $(this).val();
        loadKecamatan(kotaId);
        $("#desa").html('<option value="">Pilih Desa</option>'); 
    });

    $("#kecamatan").change(function(){ 
        var kecId = $(this).val();
        loadDesa(kecId); 
    });
});
</script>

<?php include 'footer.php'; ?>
