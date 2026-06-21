<?php
include 'koneksi.php';

$level = $_POST['level'] ?? '';
$parent_id = (int)($_POST['parent_id'] ?? 0);
$selected = (int)($_POST['selected'] ?? 0);

if($level == 'kota'){
    $res = mysqli_query($koneksi, "SELECT * FROM kota WHERE provinsi_id='$parent_id' ORDER BY nama ASC");
    echo '<option value="">Pilih Kota</option>';
    while($r = mysqli_fetch_assoc($res)){
        $sel = ($r['id'] == $selected) ? 'selected' : '';
        echo '<option value="'.$r['id'].'" '.$sel.'>'.htmlspecialchars($r['nama']).'</option>';
    }
}

if($level == 'kecamatan'){
    $res = mysqli_query($koneksi, "SELECT * FROM kecamatan WHERE kota_id='$parent_id' ORDER BY nama ASC");
    echo '<option value="">Pilih Kecamatan</option>';
    while($r = mysqli_fetch_assoc($res)){
        $sel = ($r['id'] == $selected) ? 'selected' : '';
        echo '<option value="'.$r['id'].'" '.$sel.'>'.htmlspecialchars($r['nama']).'</option>';
    }
}

if($level == 'desa'){
    $res = mysqli_query($koneksi, "SELECT * FROM desa WHERE kecamatan_id='$parent_id' ORDER BY nama ASC");
    echo '<option value="">Pilih Desa</option>';
    while($r = mysqli_fetch_assoc($res)){
        $sel = ($r['id'] == $selected) ? 'selected' : '';
        echo '<option value="'.$r['id'].'" '.$sel.'>'.htmlspecialchars($r['nama']).'</option>';
    }
}
?>
