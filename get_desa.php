<?php
include 'koneksi.php';

$kecamatan_id = $_POST['kecamatan_id'] ?? '';
$selected = $_POST['selected'] ?? '';

// Pastikan kecamatan_id tidak kosong
if(!$kecamatan_id){
    echo '<option value="">Pilih Desa</option>';
    exit;
}

$q = mysqli_query($koneksi, "SELECT * FROM desa WHERE kecamatan_id='$kecamatan_id' ORDER BY nama ASC");

echo '<option value="">Pilih Desa</option>';
while($d = mysqli_fetch_assoc($q)){
    $sel = ($d['id'] == $selected) ? 'selected' : '';
    echo '<option value="'.$d['id'].'" '.$sel.'>'.htmlspecialchars($d['nama']).'</option>';
}
?>
