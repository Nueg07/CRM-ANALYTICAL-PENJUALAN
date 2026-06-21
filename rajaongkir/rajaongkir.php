<?php
// Get Kota Asal
$curl = curl_init();  
curl_setopt_array($curl, array(
  CURLOPT_URL => "https://api.rajaongkir.com/starter/city",
  CURLOPT_RETURNTRANSFER => true,
  CURLOPT_ENCODING => "",
  CURLOPT_MAXREDIRS => 10,
  CURLOPT_TIMEOUT => 30,
  CURLOPT_HTTP_VERSION => CURL_HTTP_VERSION_1_1,
  CURLOPT_CUSTOMREQUEST => "GET",
  CURLOPT_HTTPHEADER => array(
    "key: 8f22875183c8c65879ef1ed0615d3371"
  ),
));

$response = curl_exec($curl);
$err = curl_error($curl);
curl_close($curl);

echo "<label>Kota Asal</label><br>";
echo "<select name='asal' id='asal'>";
echo "<option value=''>Pilih Kota Asal</option>";
if(!$err){
  $data = json_decode($response, true);
  foreach($data['rajaongkir']['results'] as $city){
    echo "<option value='".$city['city_id']."'>".$city['city_name']."</option>";
  }
}
echo "</select><br><br><br>";

// Get Provinsi Tujuan
$curl = curl_init();
curl_setopt_array($curl, array(
  CURLOPT_URL => "https://api.rajaongkir.com/starter/province",
  CURLOPT_RETURNTRANSFER => true,
  CURLOPT_ENCODING => "",
  CURLOPT_MAXREDIRS => 10,
  CURLOPT_TIMEOUT => 30,
  CURLOPT_HTTP_VERSION => CURL_HTTP_VERSION_1_1,
  CURLOPT_CUSTOMREQUEST => "GET",
  CURLOPT_HTTPHEADER => array(
    "key: 8f22875183c8c65879ef1ed0615d3371"
  ),
));
$response = curl_exec($curl);
$err = curl_error($curl);
curl_close($curl);

echo "Provinsi Tujuan<br>";
echo "<select name='provinsi' id='provinsi'>";
echo "<option value=''>Pilih Provinsi Tujuan</option>";
if(!$err){
  $data = json_decode($response, true);
  foreach($data['rajaongkir']['results'] as $prov){
    echo "<option value='".$prov['province_id']."'>".$prov['province']."</option>";
  }
}
echo "</select><br><br>";
?>

<!DOCTYPE html>
<html>
<head>
  <script src="https://ajax.googleapis.com/ajax/libs/jquery/3.2.1/jquery.min.js"></script>
</head>
<body>

  <label>Kabupaten Tujuan</label><br>
  <select id="kabupaten" name="kabupaten"></select><br><br>

  <label>Kurir</label><br>
  <select id="kurir" name="kurir">
    <option value="jne">JNE</option>
    <option value="tiki">TIKI</option>
    <option value="pos">POS INDONESIA</option>
  </select><br><br>

  <label>Berat (gram)</label><br>
  <input id="berat" type="text" name="berat" value="500" />
  <br><br>

  <input id="cek" type="button" value="Cek Ongkir"/>

  <div id="ongkir"></div>

</body>
</html>

<script type="text/javascript">
$(document).ready(function(){
  $('#provinsi').change(function(){
    var prov = $('#provinsi').val();
    $.ajax({
      type : 'GET',
      url : 'cek_kabupaten.php',
      data : 'prov_id=' + prov,
      success: function (data) {
        $("#kabupaten").html(data);
      }
    });
  });

  $("#cek").click(function(){
    var asal = $('#asal').val();
    var kab = $('#kabupaten').val();
    var kurir = $('#kurir').val();
    var berat = $('#berat').val();

    $.ajax({
      type : 'POST',
      url : 'cek_ongkir.php',
      data : { 'asal': asal, 'kab_id' : kab, 'kurir' : kurir, 'berat' : berat },
      success: function (data) {
        $("#ongkir").html(data);
      }
    });
  });
});
</script>
