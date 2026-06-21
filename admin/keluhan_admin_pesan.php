<?php
include '../koneksi.php';
date_default_timezone_set('Asia/Jakarta');

// Cek apakah form dikirim
if ($_SERVER['REQUEST_METHOD'] === 'POST') {

    // Ambil data
    $id_customer = isset($_POST['id_customer']) ? (int)$_POST['id_customer'] : 0;

    // Ambil isi pesan — bisa dari field "pesan" atau "balasan" tergantung form pemanggilnya
    $isi_pesan = '';
    if (!empty($_POST['pesan'])) {
        $isi_pesan = $_POST['pesan'];
    } elseif (!empty($_POST['balasan'])) {
        $isi_pesan = $_POST['balasan'];
    }

    $balasan = mysqli_real_escape_string($koneksi, trim($isi_pesan));
    $tanggal_balasan = date('Y-m-d H:i:s');

    // Validasi input
    if ($id_customer > 0 && !empty($balasan)) {
        // Admin mengirim pesan baru ke customer
        // Karena admin yang memulai pesan, maka kolom 'pesan' dikosongi
        $insert = mysqli_query($koneksi, "
            INSERT INTO keluhan (id_customer, pesan, tanggal, status, balasan, tanggal_balasan)
            VALUES ('$id_customer', '', NOW(), 'ditanggapi', '$balasan', '$tanggal_balasan')
        ");

        if ($insert) {
            echo "<script>
                alert('Pesan berhasil dikirim ke customer!');
                window.location='keluhan.php';
            </script>";
        } else {
            $error = mysqli_error($koneksi);
            echo "<script>
                alert('Gagal mengirim pesan: " . addslashes($error) . "');
                window.location='keluhan.php';
            </script>";
        }
    } else {
        echo "<script>
            alert('Pastikan memilih customer dan mengisi pesan.');
            window.location='keluhan.php';
        </script>";
    }
    exit;
}

// Jika bukan metode POST, arahkan kembali
header("Location: keluhan.php");
exit;
?>
