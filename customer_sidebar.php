<?php
// Ambil jumlah pesan belum dibaca oleh customer ini
$pesan_baru = 0;
if (isset($_SESSION['customer'])) {
    $cust = $_SESSION['customer'];
    $customer_id = is_array($cust) ? ($cust['customer_id'] ?? ($cust['id'] ?? 0)) : (int)$cust;
    
    // Pastikan tabel 'chat' ada sebelum query
    $cek_tabel = mysqli_query($koneksi, "SHOW TABLES LIKE 'chat'");
    if ($cek_tabel && mysqli_num_rows($cek_tabel) > 0) {
        $q = mysqli_query($koneksi, "SELECT COUNT(*) AS jml FROM chat WHERE penerima_id='$customer_id' AND dibaca='0'");
        if ($q) {
            $d = mysqli_fetch_assoc($q);
            $pesan_baru = $d['jml'] ?? 0;
        }
    }
}
?>

<ul>	
	<li style="margin-bottom: 10px;list-style:none;">
		<a href="customer.php" class="btn form-control" role="button">
			<i class="fa fa-home"></i> &nbsp; Dashboard
		</a>
	</li>

	<li style="margin-bottom: 10px;list-style:none;">
		<a href="customer_pesanan.php" class="btn form-control" role="button">
			<i class="fa fa-list"></i> &nbsp; Pesanan Saya
		</a>
	</li>

	<li style="margin-bottom: 10px;list-style:none; position: relative;">
		<a href="customer_keluhan.php" class="btn form-control" role="button">
			<i class="fa fa-comments"></i> &nbsp; Chat 
			<?php if($pesan_baru > 0): ?>
				<span style="
					position: absolute;
					top: 5px;
					right: 15px;
					background: red;
					color: white;
					font-size: 12px;
					padding: 2px 7px;
					border-radius: 10px;
				"><?= $pesan_baru ?></span>
			<?php endif; ?>
		</a>
	</li>

	<li style="margin-bottom: 10px;list-style:none;">
		<a href="customer_password.php" class="btn form-control" role="button">
			<i class="fa fa-lock"></i> &nbsp; Ganti Password
		</a>
	</li>

	<li style="margin-bottom: 10px;list-style:none;">
		<a href="customer_logout.php" class="btn form-control" role="button">
			<i class="fa fa-power-off"></i> &nbsp; Keluar
		</a>
	</li>			
</ul>
