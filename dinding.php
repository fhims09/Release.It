<?php
require 'config/koneksi.php';
$judul = 'Dinding harapan';
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
<section class="hero">
  <h1>Dinding harapan</h1>
  <p>Tulis satu kalimat semangat untuk orang asing yang mungkin sedang butuh. Jangan sebut nama atau metode menyakiti diri.</p>

  <?php if ($kode_baru): ?>
    <div class="info">Terkirim, menunggu persetujuan admin. Simpan kode ini untuk mengubah atau menghapus pesanmu:
      <strong class="kode"><?= e($kode_baru) ?></strong></div>
  <?php endif; ?>
  <?php if ($error): ?><div class="info salah"><?= e($error) ?></div><?php endif; ?>

  <form method="post" class="form">
    <?= csrf_field() ?>
    <div class="jebakan" aria-hidden="true"><label>Jangan diisi<input name="website" tabindex="-1" autocomplete="off"></label></div>
    <label for="isi">Pesan semangat</label>
    <textarea id="isi" name="isi" rows="3" maxlength="300" required><?= e($_POST['isi'] ?? '') ?></textarea>
    <label for="id_mood">Untuk yang sedang merasa</label>
    <select id="id_mood" name="id_mood" required>
      <option value="">Pilih perasaan</option>
      <?php foreach ($moods as $m): ?><option value="<?= $m['id'] ?>"><?= e($m['nama']) ?></option><?php endforeach; ?>
    </select>
    <button class="tombol">Kirim pesan</button>
  </form>
  <p><a href="pesanku.php">Ubah atau hapus pesanku (pakai kode)</a></p>
</section>

<section>
  <div class="mood-list">
    <a class="chip <?= !$f ? 'aktif' : '' ?>" href="dinding.php">Semua</a>
    <?php foreach ($moods as $m): ?>
      <a class="chip <?= $f === (int)$m['id'] ? 'aktif' : '' ?>" href="?mood=<?= $m['id'] ?>"><?= e($m['nama']) ?></a>
    <?php endforeach; ?>
  </div>
  <div class="galeri">
    <?php foreach ($pesan as $p): ?>
      <blockquote class="kartu"><?= e($p['isi']) ?><footer>untuk yang sedang <?= e(strtolower($p['mood'])) ?></footer></blockquote>
    <?php endforeach; ?>
    <?php if (!$pesan): ?><p>Belum ada pesan di sini. Jadilah yang pertama.</p><?php endif; ?>
  </div>
</section>
<?php require 'includes/footer.php'; ?>
