<?php
$provinsi_id = $_GET['prov_id'];
$curl = curl_init();
curl_setopt_array($curl, array(
  CURLOPT_URL => "https://api.rajaongkir.com/starter/city?province=".$provinsi_id,
  CURLOPT_RETURNTRANSFER => true,
  CURLOPT_HTTPHEADER => array("key: 8f22875183c8c65879ef1ed0615d3371"),
));
$response = curl_exec($curl);
curl_close($curl);
$data = json_decode($response, true);
echo "<option value=''>Pilih Kabupaten/Kota</option>";
foreach($data['rajaongkir']['results'] as $city){
  echo "<option value='".$city['city_id']."'>".$city['type']." ".$city['city_name']."</option>";
}
