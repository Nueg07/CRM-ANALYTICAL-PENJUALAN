 <footer class="bg-dark text-white">
        <div class="container py-4">
          <div class="row py-5">
            <div class="col-md-4 mb-3 mb-md-0">
              <h6 class="text-uppercase mb-3">Customer services</h6>
              <ul class="list-unstyled mb-0">
                <li><a class="footer-link" href="#">Help &amp; Contact Us</a></li>
                <li><a class="footer-link" href="#">Online Stores</a></li>
                <li><a class="footer-link" href="#">Terms &amp; Conditions</a></li>
              </ul>
            </div>            
            <div class="col-md-4">
              <h6 class="text-uppercase mb-3">Social media</h6>
              <ul class="list-unstyled mb-0">
                <li><a class="footer-link" href="#">Twitter</a></li>
                <li><a class="footer-link" href="#">Instagram</a></li>
                <li><a class="footer-link" href="#">Tumblr</a></li>
                <li><a class="footer-link" href="#">Pinterest</a></li>
              </ul>
            </div>
            <div class="col-md-4">
              <h6 class="text-uppercase mb-3">Social media</h6>
              <ul class="list-unstyled mb-0">
                <li><a class="footer-link" href="#">Pengiriman</a></li>
                <li><a class="footer-link" href="#">Tracking Resi</a></li>
              </ul>
            </div>
          </div>
          <div class="border-top pt-4" style="border-color: #1d1d1d !important">
            <div class="row">
              <div class="col-lg-6">
                <p class="small text-muted mb-0">&copy; 2021 All rights reserved.</p>
              </div>
              <div class="col-lg-6 text-lg-right">
                <p class="small text-muted mb-0">Template designed by <a class="text-white reset-anchor" href="#">Elektronik Station</a></p>
              </div>
            </div>
          </div>
        </div>
      </footer>
      <!-- JavaScript files-->
      <script src="baru/vendor/jquery/jquery.min.js"></script>
      <script src="baru/vendor/bootstrap/js/bootstrap.bundle.min.js"></script>
      <script src="baru/vendor/lightbox2/js/lightbox.min.js"></script>
      <script src="baru/vendor/nouislider/nouislider.min.js"></script>
      <script src="baru/vendor/bootstrap-select/js/bootstrap-select.min.js"></script>
      <script src="baru/vendor/owl.carousel2/owl.carousel.min.js"></script>
      <script src="baru/vendor/owl.carousel2.thumbs/owl.carousel2.thumbs.min.js"></script>
      <script src="baru/js/front.js"></script>
      <script>
        // ------------------------------------------------------- //
        //   Inject SVG Sprite - 
        //   see more here 
        //   https://css-tricks.com/ajaxing-svg-sprite/
        // ------------------------------------------------------ //
        function injectSvgSprite(path) {
        
            var ajax = new XMLHttpRequest();
            ajax.open("GET", path, true);
            ajax.send();
            ajax.onload = function(e) {
            var div = document.createElement("div");
            div.className = 'd-none';
            div.innerHTML = ajax.responseText;
            document.body.insertBefore(div, document.body.childNodes[0]);
            }
        }
        // this is set to BootstrapTemple website as you cannot 
        // inject local SVG sprite (using only 'icons/orion-svg-sprite.svg' path)
        // while using file:// protocol
        // pls don't forget to change to your domain :)
        injectSvgSprite('https://bootstraptemple.com/files/icons/orion-svg-sprite.svg'); 
        
      </script>


      <script>

       $(document).ready(function(){

        function numberWithCommas(x) {
         return x.toString().replace(/\B(?=(\d{3})+(?!\d))/g, ",");
       }

       $('.jumlah').on("keyup",function(){
         var nomor = $(this).attr('nomor');

         var jumlah = $(this).val();

         var harga = $("#harga_"+nomor).val();

         var total = jumlah*harga;

         var t = numberWithCommas(total);

         $("#total_"+nomor).text("Rp. "+t+" ,-");
       });
     });




       $(document).ready(function(){
        $('#provinsi').change(function(){
         var prov = $('#provinsi').val();


         var provinsi = $("#provinsi :selected").text();

         $.ajax({
          type : 'GET',
          url : 'rajaongkir/cek_kabupaten.php',
          data :  'prov_id=' + prov,
          success: function (data) {
           $("#kabupaten").html(data);
           $("#provinsi2").val(provinsi);
         }
       });
       });

        $(document).on("change","#kabupaten",function(){

         var asal = 152;
         var kab = $('#kabupaten').val();
         var kurir = "a";
         var berat = $('#berat2').val();

         var kabupaten = $("#kabupaten :selected").text();

         $.ajax({
          type : 'POST',
          url : 'rajaongkir/cek_ongkir.php',
          data :  {'kab_id' : kab, 'kurir' : kurir, 'asal' : asal, 'berat' : berat},
          success: function (data) {
           $("#ongkir").html(data);
               // alert(data);

               // $("#provinsi").val(prov);
               $("#kabupaten2").val(kabupaten);

             }
           });
       });

        function format_angka(x) {
         return x.toString().replace(/\B(?=(\d{3})+(?!\d))/g, ",");
       }

       $(document).on("change", '.pilih-kurir', function(event) { 
         // alert("new link clicked!");
         var kurir = $(this).attr("kurir");
         var service = $(this).attr("service");
         var ongkir = $(this).attr("harga");
         var total_bayar = $("#total_bayar").val();

         $("#kurir").val(kurir);
         $("#service").val(service);
         $("#ongkir2").val(ongkir);
         var total = parseInt(total_bayar) + parseInt(ongkir);
         $("#tampil_ongkir").text("Rp. "+ format_angka(ongkir) +" ,-");
         $("#tampil_total").text("Rp. "+ format_angka(total) +" ,-");
       });


      // $(".pilih-kurir").on("change",function(){

      //    alert('sd');
         // var asal = 152;
         // var kab = $('#kabupaten').val();
         // var kurir = "a";
         // var berat = $('#berat2').val();

         // $.ajax({
         //    type : 'POST',
         //    url : 'rajaongkir/cek_ongkir.php',
         //    data :  {'kab_id' : kab, 'kurir' : kurir, 'asal' : asal, 'berat' : berat},
         //    success: function (data) {
         //       $("#ongkir").html(data);
         //       // alert(data);

         //    }
         // });
      // });



    });
  </script>


      <!-- FontAwesome CSS - loading as last, so it doesn't block rendering-->
      <link rel="stylesheet" href="https://use.fontawesome.com/releases/v5.7.1/css/all.css" integrity="sha384-fnmOCqbTlWIlj8LyTjo7mOUStjsKC4pOpQbqyi7RrhN7udi9RwhKkMHpvLbHG9Sr" crossorigin="anonymous">
    </div>
  </body>
</html>