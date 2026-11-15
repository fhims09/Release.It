<?php
require 'config/koneksi.php';
$judul = 'Napas';
$kelas = 'napas';
require 'includes/header.php';
?>
<section class="hero tengah">
  <h1>Ruang tenang</h1>
  <p>Ikuti lingkaran. Tarik napas 4 detik, tahan 7 detik, hembuskan 8 detik.</p>
  <div id="lingkaran" class="lingkaran"><span id="aba">Siap?</span></div>
  <button id="btnNapas" class="tombol">Mulai</button>
</section>
<?php require 'includes/footer.php'; ?>
