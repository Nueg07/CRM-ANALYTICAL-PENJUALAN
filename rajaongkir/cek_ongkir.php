<?php
$asal = $_POST['asal'];
$id_kabupaten = $_POST['kab_id'];
$kurir = $_POST['kurir'];
$berat = $_POST['berat'];

$curl = curl_init();
curl_setopt_array($curl, array(
  CURLOPT_URL => "https://api.rajaongkir.com/starter/cost",
  CURLOPT_RETURNTRANSFER => true,
  CURLOPT_CUSTOMREQUEST => "POST",
  CURLOPT_POSTFIELDS => "origin=".$asal."&destination=".$id_kabupaten."&weight=".$berat."&courier=".$kurir,
  CURLOPT_HTTPHEADER => array("content-type: application/x-www-form-urlencoded","key: 8f22875183c8c65879ef1ed0615d3371"),
));
$response = curl_exec($curl);
curl_close($curl);

$data = json_decode($response, true);
if(isset($data['rajaongkir']['results'][0]['costs'])){
  echo "<table class='table table-bordered'>";
  echo "<tr><th>Kurir</th><th>Service</th><th>Ongkir</th><th>ETD</th><th>Pilih</th></tr>";
  $kurir_nama = $data['rajaongkir']['results'][0]['name'];
  foreach($data['rajaongkir']['results'][0]['costs'] as $row){
    $harga = $row['cost'][0]['value'];
    $service = $row['service'];
    $etd = $row['cost'][0]['etd'];
    echo "<tr>";
    echo "<td>".$kurir_nama."</td>";
    echo "<td>".$service."</td>";
    echo "<td>Rp. ".number_format($harga)."</td>";
    echo "<td>".$etd." hari</td>";
    echo "<td><input type='radio' class='pilih-kurir' harga='".$harga."' service='".$service."'></td>";
    echo "</tr>";
  }
  echo "</table>";
}
