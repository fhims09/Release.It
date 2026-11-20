<?php
require 'config/koneksi.php';
$judul = 'Curhat';
$moods = $pdo->query("SELECT * FROM mood ORDER BY id")->fetchAll();
require 'includes/header.php';
?>
<section class="hero langit">
  <div class="header-curhat">
    <div class="status-rilis-bar">
      <span class="status-rilis-dot" aria-hidden="true"><span class="ping-dot"></span></span>
      <span id="sapaanWaktu" class="sapaan-waktu">Rilis siap mendengarkan</span>
      <span class="privasi-pill" title="Obrolan bersifat sementara dan tidak disimpan ke database Release.It">🔒 Rahasia &amp; Sementara</span>
    </div>
    <h1>Ceritakan saja. Rilis siap mendengarkan.</h1>
    <p class="deskripsi-curhat">
      <?php $aiAktif = is_file(__DIR__ . '/config/ai.php') && !empty((require __DIR__ . '/config/ai.php')['api_key']); ?>
      <?php if ($aiAktif): ?>
        Rilis adalah pendengar berbasis AI, bukan psikolog. Percakapan ini tidak disimpan di server Release.It, namun diteruskan ke penyedia AI untuk menyusun tanggapan yang hangat. Jangan mencantumkan nama lengkap, alamat, atau data privat lainnya.
      <?php else: ?>
        Percakapan ini bersifat sementara dan tidak disimpan di mana pun. Rilis adalah teman virtual yang hadir untuk menemanimu melepaskan penat tanpa penghakiman.
      <?php endif; ?>
    </p>
  </div>

  <div class="chat-wrapper">
    <div id="chat" class="chat" role="log" aria-live="polite" aria-label="Riwayat percakapan dengan Rilis"></div>
    <div class="chat-toolbar">
      <button type="button" class="btn-chat-toolbar" id="btnHapusChat" title="Mulai sesi obrolan baru">
        🗑️ Mulai Ulang
      </button>
      <button type="button" class="btn-chat-toolbar" id="btnSuaraChat" title="Bunyi notifikasi santai">
        🔔 Suara: <span id="labelSuaraChat">Aktif</span>
      </button>
    </div>
  </div>

  <div id="bantuan" class="bantuan" hidden role="alert">
    <h2>Kamu tidak sendirian dalam kesulitan ini.</h2>
    <p>Jika beban terasa terlalu berat dan kamu memikirkan untuk menyakiti diri, tolong hubungi bantuan profesional segera:</p>
    <ul>
      <li>Layanan Kesehatan Jiwa Kemenkes: <strong>119 ext. 8</strong></li>
      <li>Panggilan Darurat: <strong>112</strong></li>
      <li>Komunitas Peduli: <a href="https://www.intothelightid.org" target="_blank" rel="noopener">Into The Light Indonesia</a></li>
      <li>Bicarakan dengan orang terdekat, sahabat, atau konselor kampus yang kamu percaya.</li>
    </ul>
  </div>

  <div class="wadah-mood-cepat">
    <div class="label-mood-cepat">Bagaimana perasaanmu sekarang?</div>
    <div class="mood-list" id="cepat">
      <?php foreach ($moods as $m): ?>
        <button type="button" class="chip" data-teks="Aku lagi merasa <?= e(strtolower($m['nama'])) ?>">
          <?= e($m['nama']) ?>
        </button>
      <?php endforeach; ?>
    </div>
  </div>

  <form id="formChat" class="kirim">
    <div class="tulis">
      <textarea id="curhat" rows="2" maxlength="1000" placeholder="Tuliskan apa pun yang mengganjal di hatimu..." aria-label="Tulis pesan untuk Rilis"></textarea>
      <div id="melayang" aria-hidden="true"></div>
    </div>
    <div class="info-tulis-bawah">
      <span id="hitungKarakter" class="hitung-karakter">0 / 1000 karakter</span>
      <span class="hint-tulis">Tekan <strong>Enter</strong> untuk mengirim &bull; <strong>Shift+Enter</strong> baris baru</span>
    </div>
    <div class="baris-tombol-chat">
      <button class="tombol" type="submit">
        <span class="btn-ikon">💬</span> Kirim ke Rilis
      </button>
      <button class="tombol sekunder" type="button" id="btnLepas" title="Kata-katamu akan melayang dan menghilang tanpa dikirim atau disimpan">
        🍃 Lepaskan Tulisan
      </button>
    </div>
  </form>
</section>
<?php require 'includes/footer.php'; ?>
