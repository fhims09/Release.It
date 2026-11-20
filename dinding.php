<?php
require 'config/koneksi.php';
$judul = 'Dinding Harapan';
$moods = $pdo->query("SELECT * FROM mood ORDER BY id")->fetchAll();
$error = '';

// CREATE: kirim pesan (masuk status pending, menunggu moderasi admin)
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    cek_csrf();
    $isi = trim($_POST['isi'] ?? '');
    $id_mood = (int)($_POST['id_mood'] ?? 0);
    // Anti-spam: kolom jebakan untuk bot, maksimal 5 pesan per jam, jeda 30 detik
    if (!empty($_POST['website'])) { header('Location: dinding.php'); exit; }
    $sekarang = time();
    $kirim = array_values(array_filter($_SESSION['kirim'] ?? [], function ($w) use ($sekarang) { return $sekarang - $w < 3600; }));
    if (count($kirim) >= 5) {
        $error = 'Kamu sudah mengirim 5 pesan dalam satu jam. Coba lagi nanti ya.';
    } elseif ($kirim && $sekarang - end($kirim) < 30) {
        $error = 'Tunggu sebentar sebelum mengirim pesan berikutnya.';
    } elseif (mb_strlen($isi) < 5 || mb_strlen($isi) > 300) {
        $error = 'Pesan harus 5 sampai 300 karakter.';
    } elseif (!$id_mood) {
        $error = 'Pilih perasaan yang ingin kamu semangati.';
    } else {
        $kode = strtoupper(bin2hex(random_bytes(3)));
        $pdo->prepare("INSERT INTO pesan_harapan (isi, id_mood, kode_edit) VALUES (?,?,?)")
            ->execute([$isi, $id_mood, $kode]);
        $kirim[] = $sekarang;
        $_SESSION['kirim'] = $kirim;
        $_SESSION['kode_baru'] = $kode;
        header('Location: dinding.php'); exit;
    }
}
$kode_baru = $_SESSION['kode_baru'] ?? null;
unset($_SESSION['kode_baru']);

// READ: hanya pesan yang sudah disetujui, bisa difilter mood
$f = (int)($_GET['mood'] ?? 0);
$sql = "SELECT p.*, m.nama AS mood FROM pesan_harapan p JOIN mood m ON m.id = p.id_mood WHERE p.status='disetujui'";
$par = [];
if ($f) { $sql .= " AND p.id_mood = ?"; $par[] = $f; }
$s = $pdo->prepare($sql . " ORDER BY p.created_at DESC");
$s->execute($par);
$pesan = $s->fetchAll();

require 'includes/header.php';
?>
<section class="hero hero-dinding">
  <h1>Dinding Harapan</h1>
  <p>Tuliskan satu kalimat semangat hangat untuk teman sebaya yang mungkin sedang merasa sendiri. Jangan cantumkan nama lengkap atau detail berbahaya.</p>

  <?php if ($kode_baru): ?>
    <div class="info info-sukses-kode" role="status">
      <div class="info-kode-teks">
        Pesanmu terkirim dan sedang menunggu kurasi. Simpan kode ini untuk mengubah atau menghapus pesanmu sewaktu-waktu:
      </div>
      <div class="baris-salin-kode">
        <strong class="kode" id="kodeBaruTeks"><?= e($kode_baru) ?></strong>
        <button type="button" class="tombol sekunder tombol-kecil" id="btnSalinKode" data-kode="<?= e($kode_baru) ?>">
          📋 Salin Kode
        </button>
      </div>
    </div>
  <?php endif; ?>
  <?php if ($error): ?><div class="info salah" role="alert"><?= e($error) ?></div><?php endif; ?>

  <div class="kartu form-dinding-wrapper">
    <div class="form-dinding-header">
      <span class="ikon-dinding-header" aria-hidden="true">💌</span>
      <h2>Gantungkan Pesanmu</h2>
    </div>
    <form method="post" class="form form-dinding" id="formDinding">
      <?= csrf_field() ?>
      <div class="jebakan" aria-hidden="true"><label>Jangan diisi<input name="website" tabindex="-1" autocomplete="off"></label></div>
      
      <div class="grup-input">
        <label for="isi">Kalimat Penguat Semangat</label>
        <textarea id="isi" name="isi" rows="3" maxlength="300" placeholder="Tuliskan kata-kata yang ingin kamu dengar saat harimu berat..." required><?= e($_POST['isi'] ?? '') ?></textarea>
        <div class="counter-karakter-dinding">
          <span id="counterDinding">0</span> / 300 karakter
        </div>
      </div>

      <div class="grup-input">
        <label for="id_mood">Ditujukan Untuk Yang Sedang Merasa</label>
        <select id="id_mood" name="id_mood" required>
          <option value="">Pilih perasaan</option>
          <?php foreach ($moods as $m): ?>
            <option value="<?= $m['id'] ?>" <?= isset($_POST['id_mood']) && $_POST['id_mood'] == $m['id'] ? 'selected' : '' ?>><?= e($m['nama']) ?></option>
          <?php endforeach; ?>
        </select>
      </div>

      <div class="baris-tombol-dinding">
        <button class="tombol" type="submit">
          <span>✨</span> Gantungkan Pesan
        </button>
        <a href="pesanku.php" class="link-kelola-pesan">Ubah atau hapus pesan saya (pakai kode)</a>
      </div>
    </form>
  </div>
</section>

<section class="bagian-galeri-dinding">
  <div class="header-galeri-dinding">
    <h2>Pesan Hangat Dari Sesama</h2>
    <p>Filter berdasarkan perasaan atau cari kata penyemangat yang kamu butuhkan:</p>
  </div>

  <div class="toolbar-galeri-dinding">
    <!-- Live Search Bar -->
    <div class="kotak-cari-dinding">
      <span class="ikon-cari" aria-hidden="true">🔍</span>
      <input type="search" id="cariHarapan" placeholder="Cari kata penyemangat di sini..." aria-label="Cari kata penyemangat" class="input-cari-dinding">
    </div>

    <div class="mood-list list-filter-dinding">
      <a class="chip <?= !$f ? 'aktif' : '' ?>" href="dinding.php">Semua Perasaan</a>
      <?php foreach ($moods as $m): ?>
        <a class="chip <?= $f === (int)$m['id'] ? 'aktif' : '' ?>" href="?mood=<?= $m['id'] ?>"><?= e($m['nama']) ?></a>
      <?php endforeach; ?>
    </div>
  </div>

  <div class="galeri galeri-dinding" id="galeriHarapan">
    <?php foreach ($pesan as $p): ?>
      <blockquote class="kartu kartu-harapan" data-id="<?= $p['id'] ?>" data-mood="<?= e(strtolower($p['mood'])) ?>" data-isi="<?= e(strtolower($p['isi'])) ?>">
        <div class="kartu-harapan-top">
          <span class="pin-harapan" aria-hidden="true">✨</span>
          <span class="tag-mood-harapan"><?= e($p['mood']) ?></span>
        </div>
        <p class="isi-harapan">&ldquo;<?= e($p['isi']) ?>&rdquo;</p>
        <footer class="footer-harapan">
          <span class="tujuan-harapan">Untuk yang sedang merasa <strong><?= e(strtolower($p['mood'])) ?></strong></span>
          <div class="aksi-kartu-harapan">
            <button type="button" class="btn-peluk-harapan" data-id="<?= $p['id'] ?>" title="Kirim pelukan hangat">
              <span class="ikon-peluk">🫂</span>
              <span class="angka-peluk">0</span>
            </button>
            <button type="button" class="btn-salin-harapan" data-isi="<?= e($p['isi']) ?>" title="Salin kata ini">
              <span class="ikon-salin">📋</span>
            </button>
          </div>
        </footer>
      </blockquote>
    <?php endforeach; ?>
    <?php if (!$pesan): ?>
      <div class="kartu kartu-kosong" id="pesanKosongDinding">
        <p>Belum ada pesan harapan untuk perasaan ini. Jadilah orang pertama yang menuliskan kehangatan.</p>
      </div>
    <?php else: ?>
      <div class="kartu kartu-kosong" id="pesanTidakDitemukan" hidden>
        <p>Tidak ada pesan yang sesuai dengan pencarianmu.</p>
      </div>
    <?php endif; ?>
  </div>
</section>
<?php require 'includes/footer.php'; ?>
