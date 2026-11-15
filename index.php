<?php
require 'config/koneksi.php';
$judul = 'Curhat';
$moods = $pdo->query("SELECT * FROM mood ORDER BY id")->fetchAll();
require 'includes/header.php';
?>
<section class="hero langit">
  <h1>Ceritakan saja. Rilis mendengarkan.</h1>
  <p><?php $aiAktif = is_file(__DIR__ . '/config/ai.php') && !empty((require __DIR__ . '/config/ai.php')['api_key']); ?>
  <?php if ($aiAktif): ?>
    Rilis adalah AI, bukan psikolog. Obrolan tidak disimpan di server Release.It, tetapi pesanmu dikirim ke penyedia layanan AI untuk membuat balasan. Jangan menulis nama lengkap, alamat, atau data pribadi lainnya.
  <?php else: ?>
    Obrolan ini tidak disimpan di mana pun. Rilis adalah program sederhana, bukan psikolog.
  <?php endif; ?></p>

  <div id="chat" class="chat" role="log" aria-live="polite"></div>

  <div id="bantuan" class="bantuan" hidden>
    <h2>Kamu tidak sendirian.</h2>
    <ul>
      <li>Hubungi <strong>119 ext. 8</strong> (layanan kesehatan jiwa Kemenkes)</li>
      <li>Darurat: <strong>112</strong></li>
      <li>Info dan komunitas: <a href="https://www.intothelightid.org" target="_blank" rel="noopener">Into The Light Indonesia</a></li>
      <li>Ceritakan ke keluarga, teman, atau dosen wali yang kamu percaya, hari ini juga</li>
    </ul>
  </div>

  <div class="mood-list" id="cepat">
    <?php foreach ($moods as $m): ?>
      <button type="button" class="chip" data-teks="Aku lagi merasa <?= e(strtolower($m['nama'])) ?>"><?= e($m['nama']) ?></button>
    <?php endforeach; ?>
  </div>
  <p class="petunjuk-chat">Kamu juga bisa memilih perasaan di atas, atau tulis apa saja yang sedang kamu pikirkan.</p>

  <form id="formChat" class="kirim">
    <div class="tulis">
      <textarea id="curhat" rows="2" maxlength="1000" placeholder="Tulis perasaanmu di sini..." aria-label="Pesan"></textarea>
      <div id="melayang" aria-hidden="true"></div>
    </div>
    <div>
      <button class="tombol" type="submit">Kirim</button>
      <button class="tombol sekunder" type="button" id="btnLepas">Lepaskan tulisan</button>
    </div>
  </form>
</section>
<?php require 'includes/footer.php'; ?>
