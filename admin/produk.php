<?php include 'header.php'; ?>
<div class="content-wrapper">
    <section class="content-header">
        <h1>Produk <small>Data Produk</small></h1>
        <ol class="breadcrumb">
            <li><a href="#"><i class="fa fa-dashboard"></i> Home</a></li>
            <li class="active">Produk</li>
        </ol>
    </section>

    <section class="content">
        <div class="row">
            <section class="col-lg-12">
                <div class="box box-info">
                    <div class="box-header">
                        <h3 class="box-title">Data Produk</h3>
                        <a href="produk_tambah.php" class="btn btn-sm btn-primary pull-right">
                            <i class="fa fa-plus"></i> Tambah Produk
                        </a>
                        <a href="produk_cetak.php" target="_blank" class="btn btn-success btn-sm">
                            <i class="fa fa-print"></i> Cetak
                        </a>
                    </div>

                    <div class="box-body">

                        <!-- ========= FILTER KATEGORI ========= -->
                        <form method="get" class="row" style="margin-bottom:15px;">
                            <div class="col-md-4">
                                <label>Pilih Kategori</label>
                                <select name="kategori" class="form-control" onchange="this.form.submit()">
                                    <option value="">-- Semua Kategori --</option>

                                    <?php 
                                    include '../koneksi.php';
                                    $kategoriQ = mysqli_query($koneksi, "SELECT * FROM kategori ORDER BY kategori_nama ASC");

                                    while ($k = mysqli_fetch_array($kategoriQ)) {
                                        $selected = (isset($_GET['kategori']) && $_GET['kategori'] == $k['kategori_id']) ? "selected" : "";
                                        echo "<option value='{$k['kategori_id']}' $selected>{$k['kategori_nama']}</option>";
                                    }
                                    ?>
                                </select>
                            </div>
                        </form>
                        <!-- ========= END FILTER ========= -->

                        <div class="table-responsive">
                            <table class="table table-bordered table-striped" id="table-datatable">
                                <thead>
                                    <tr>
                                        <th width="1%">NO</th>
                                        <th>NAMA PRODUK</th>
                                        <th>KATEGORI</th>
                                        <th>HARGA ASLI</th>
                                        <th>HARGA SETELAH DISKON</th>
                                        <th>JUMLAH</th>
                                        <th width="15%">FOTO</th>
                                        <th>PROMO AKTIF</th>
                                        <th width="15%">OPSI</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    <?php
                                    $no = 1;

                                    // WHERE kategori FILTER
                                    $whereKategori = "";
                                    if (isset($_GET['kategori']) && $_GET['kategori'] != "") {
                                        $kat = intval($_GET['kategori']);
                                        $whereKategori = "WHERE produk_kategori = '$kat'";
                                    }

                                    // QUERY PRODUK + KATEGORI
                                    $data = mysqli_query($koneksi, "
                                        SELECT * FROM produk 
                                        JOIN kategori ON kategori_id = produk_kategori 
                                        $whereKategori
                                        ORDER BY produk_id DESC
                                    ");

                                    if (mysqli_num_rows($data) > 0) {
                                        while ($d = mysqli_fetch_array($data)) {
                                            $pid = $d['produk_id'];

                                            // AMBIL PROMO AKTIF
                                            $promo = mysqli_query($koneksi, "
                                                SELECT * FROM promo 
                                                WHERE produk_id='$pid' 
                                                AND status='aktif' 
                                                AND CURDATE() BETWEEN tanggal_mulai AND tanggal_selesai
                                                ORDER BY tanggal_mulai DESC LIMIT 1
                                            ");
                                            $promoAktif = (mysqli_num_rows($promo) > 0) ? mysqli_fetch_assoc($promo) : null;

                                            // HITUNG DISKON
                                            $hargaAsli = $d['produk_harga'];
                                            $hargaDiskon = $hargaAsli;
                                            if ($promoAktif) {
                                                $persen = $promoAktif['diskon_persen'];
                                                $hargaDiskon = $hargaAsli - ($hargaAsli * $persen / 100);
                                            }
                                    ?>
                                            <tr>
                                                <td><?= $no++; ?></td>
                                                <td><?= htmlspecialchars($d['produk_nama']); ?></td>
                                                <td><?= htmlspecialchars($d['kategori_nama']); ?></td>
                                                <td>Rp. <?= number_format($hargaAsli, 0, ',', '.'); ?>,-</td>
                                                <td>
                                                    <?php if ($promoAktif) { ?>
                                                        <b style="color:green;">Rp. <?= number_format($hargaDiskon, 0, ',', '.'); ?>,-</b>
                                                    <?php } else { ?>
                                                        <span>-</span>
                                                    <?php } ?>
                                                </td>
                                                <td><?= number_format($d['produk_jumlah']); ?></td>

                                                <td>
                                                    <?php
                                                    for ($i = 1; $i <= 3; $i++) {
                                                        $foto = $d["produk_foto$i"];
                                                        if ($foto == "") {
                                                            $foto = "../gambar/sistem/produk.png";
                                                        } else {
                                                            $foto = "../gambar/produk/" . $foto;
                                                        }
                                                        echo "<center><img src='$foto' style='width:50px;height:auto;margin:2px;'></center>";
                                                    }
                                                    ?>
                                                </td>

                                                <td>
                                                    <?php if ($promoAktif) { ?>
                                                        <span class="label label-success">
                                                            <?= htmlspecialchars($promoAktif['nama_promo']); ?> (<?= $promoAktif['diskon_persen']; ?>%)
                                                        </span><br>
                                                        <small><?= strtoupper($promoAktif['jenis']); ?></small><br>

                                                        <form method="post" action="produk_promo_nonaktif.php" style="margin-top:5px;">
                                                            <input type="hidden" name="id_promo" value="<?= $promoAktif['id_promo']; ?>">
                                                            <button type="submit" class="btn btn-xs btn-danger" onclick="return confirm('Nonaktifkan promo ini?')">
                                                                <i class="fa fa-times"></i> Nonaktifkan
                                                            </button>
                                                        </form>
                                                    <?php } else { ?>
                                                        <span class="label label-default">Tidak ada promo</span>
                                                    <?php } ?>
                                                </td>

                                                <td>
                                                    <a href="produk_edit.php?id=<?= $d['produk_id']; ?>" class="btn btn-warning btn-sm">
                                                        <i class="fa fa-cog"></i>
                                                    </a>

                                                    <a href="produk_hapus.php?id=<?= $d['produk_id']; ?>" onclick="return confirm('Yakin ingin menghapus produk ini?');" class="btn btn-danger btn-sm">
                                                        <i class="fa fa-trash"></i>
                                                    </a>

                                                    <button class="btn btn-info btn-sm" data-toggle="modal" data-target="#promoModal<?= $d['produk_id']; ?>">
                                                        <i class="fa fa-gift"></i>
                                                    </button>
                                                </td>
                                            </tr>

                                            <!-- MODAL PROMO -->
                                            <div class="modal fade" id="promoModal<?= $d['produk_id']; ?>">
                                                <div class="modal-dialog">
                                                    <div class="modal-content">
                                                        <form method="post" action="produk_promo_simpan.php">
                                                            <div class="modal-header bg-info">
                                                                <h4 class="modal-title">Tambah Promo - <?= htmlspecialchars($d['produk_nama']); ?></h4>
                                                                <button type="button" class="close" data-dismiss="modal">&times;</button>
                                                            </div>

                                                            <div class="modal-body">

                                                                <input type="hidden" name="produk_id" value="<?= $d['produk_id']; ?>">

                                                                <div class="form-group">
                                                                    <label>Nama Promo</label>
                                                                    <input type="text" name="nama_promo" class="form-control" required>
                                                                </div>

                                                                <div class="form-group">
                                                                    <label>Jenis Promo</label>
                                                                    <select name="jenis" class="form-control" onchange="toggleCustomer(this, <?= $d['produk_id']; ?>)">
                                                                        <option value="umum">Promo Umum</option>
                                                                        <option value="personal">Promo Personal</option>
                                                                    </select>
                                                                </div>

                                                                <div class="form-group" id="custField<?= $d['produk_id']; ?>" style="display:none;">
                                                                    <label>Pilih Customer (untuk promo personal)</label>
                                                                    <select name="customer_id" class="form-control">
                                                                        <option value="">-- Pilih Customer --</option>
                                                                        <?php
                                                                        $cust = mysqli_query($koneksi, "SELECT * FROM customer ORDER BY customer_nama ASC");
                                                                        while ($c = mysqli_fetch_array($cust)) {
                                                                            echo "<option value='{$c['customer_id']}'>{$c['customer_nama']} - {$c['customer_email']}</option>";
                                                                        }
                                                                        ?>
                                                                    </select>
                                                                </div>

                                                                <div class="form-group">
                                                                    <label>Diskon (%)</label>
                                                                    <input type="number" name="diskon_persen" class="form-control" min="1" max="50" required>
                                                                </div>

                                                                <div class="form-group">
                                                                    <label>Tanggal Mulai</label>
                                                                    <input type="date" name="tanggal_mulai" class="form-control" required>
                                                                </div>

                                                                <div class="form-group">
                                                                    <label>Tanggal Selesai</label>
                                                                    <input type="date" name="tanggal_selesai" class="form-control" required>
                                                                </div>

                                                            </div>

                                                            <div class="modal-footer">
                                                                <button type="submit" class="btn btn-primary">Simpan Promo</button>
                                                                <button type="button" class="btn btn-default" data-dismiss="modal">Tutup</button>
                                                            </div>

                                                        </form>
                                                    </div>
                                                </div>
                                            </div>

                                    <?php
                                        }
                                    } else {
                                        echo "<tr><td colspan='9' class='text-center'>Belum ada produk</td></tr>";
                                    }
                                    ?>
                                </tbody>
                            </table>
                        </div>

                    </div>
                </div>
            </section>
        </div>
    </section>
</div>

<script>
function toggleCustomer(select, id){
    var field = document.getElementById("custField" + id);
    field.style.display = (select.value === "personal") ? "block" : "none";
}
</script>

<?php include 'footer.php'; ?>
