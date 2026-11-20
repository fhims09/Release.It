<?php
require 'config/koneksi.php';
$judul = 'Napas';
$kelas = 'napas';
require 'includes/header.php';
?>
<section class="hero tengah hero-napas" id="areaNapas">
  <div class="header-napas">
    <div class="badge-zen" aria-hidden="true">🍃 LATIHAN MINDFULNESS</div>
    <h1>Ruang Tenang</h1>
    <p class="petunjuk-napas">Atur irama pernapasan untuk menurunkan kadar hormon stres, menyeimbangkan detak jantung, dan mengembalikan kedamaian batin. Duduklah santai dan biarkan tubuhmu rileks.</p>
  </div>

  <!-- Pilihan Mode / Teknik Pernapasan -->
  <div class="wadah-pilihan-metode">
    <div class="label-metode">PILIH TEKNIK PERNAPASAN:</div>
    <div class="daftar-metode-napas" role="tablist">
      <button type="button" class="btn-metode-napas aktif" data-metode="478" role="tab" aria-selected="true">
        <span class="metode-nama">Metode 4-7-8</span>
        <span class="metode-sub">Relaksasi &amp; Pengantar Tidur</span>
      </button>
      <button type="button" class="btn-metode-napas" data-metode="box" role="tab" aria-selected="false">
        <span class="metode-nama">Box Breathing (4-4-4-4)</span>
        <span class="metode-sub">Fokus &amp; Kendali Kecemasan</span>
      </button>
      <button type="button" class="btn-metode-napas" data-metode="quick" role="tab" aria-selected="false">
        <span class="metode-nama">Napas Cepat (4-2-4)</span>
        <span class="metode-sub">Pereda Panik Seketika</span>
      </button>
    </div>
  </div>

  <!-- Indikator Tahapan Dinamis -->
  <div class="fase-napas-bar" id="faseNapasBar" role="region" aria-label="Tahapan Pernapasan">
    <!-- Diisi secara dinamis oleh JavaScript sesuai teknik terpilih -->
  </div>

  <!-- Lingkaran Pernapasan Interaktif dengan Gelombang Riak -->
  <div class="wadah-lingkaran-napas">
    <div class="efek-aura-napas" id="auraNapas" aria-hidden="true"></div>
    <div class="riak-lingkaran riak-1" aria-hidden="true"></div>
    <div class="riak-lingkaran riak-2" aria-hidden="true"></div>
    
    <div id="lingkaran" class="lingkaran" role="timer" aria-live="polite">
      <div class="lingkaran-konten">
        <span id="aba" class="aba-teks">Siap?</span>
        <span id="hitungDetik" class="hitung-detik" aria-hidden="true"></span>
        <span id="subAba" class="sub-aba-teks">Tekan mulai di bawah</span>
      </div>
    </div>
  </div>

  <!-- Informasi Putaran -->
  <div class="info-putaran" id="infoPutaran" hidden>
    Siklus selesai: <strong id="angkaPutaran">0</strong> putaran
  </div>

  <!-- Baris Tombol Aksi & Kontrol Zen -->
  <div class="baris-tombol-napas">
    <button id="btnNapas" class="tombol tombol-lebar">
      <span class="ikon-tombol-napas">▶</span>
      <span class="teks-tombol-napas">Mulai Latihan Napas</span>
    </button>
    <div class="toolbar-napas">
      <button type="button" class="btn-napas-tool" id="btnSuaraNapas" title="Lonceng mangkuk meditasi setiap pergantian fase">
        🔔 Lonceng: <strong id="labelSuaraNapas">Aktif</strong>
      </button>
      <button type="button" class="btn-napas-tool" id="btnModeZen" title="Masuk ke mode zen bebas gangguan">
        🧘 Mode Zen
      </button>
    </div>
  </div>

  <div class="kartu tips-napas" id="kotakTipsNapas">
    <strong>💡 Panduan Menenangkan Diri:</strong>
    <p id="deskripsiTeknik"><strong>Teknik 4-7-8</strong>: Tarik napas lembut melalui hidung (4s), tahan di dada dengan santai (7s), lalu hembuskan perlahan melalui mulut secara tuntas (8s). Lakukan 4 siklus untuk merasakan ketenangan yang mendalam.</p>
  </div>
</section>
<?php require 'includes/footer.php'; ?>
