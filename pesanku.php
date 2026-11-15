<?php
require 'config/koneksi.php';
$judul = 'Pesanku';
$moods = $pdo->query("SELECT * FROM mood ORDER BY id")->fetchAll();
$kode = strtoupper(trim($_REQUEST['kode'] ?? ''));
$info = '';

$cari = function () use ($pdo, $kode) {
    $s = $pdo->prepare("SELECT * FROM pesan_harapan WHERE kode_edit = ?");
    $s->execute([$kode]);
    return $s->fetch();
};

if ($_SERVER['REQUEST_METHOD'] === 'POST' && $kode) {
    cek_csrf();
    if (($_POST['aksi'] ?? '') === 'hapus') {                      // DELETE
        $pdo->prepare("DELETE FROM pesan_harapan WHERE kode_edit = ?")->execute([$kode]);
        $info = 'Pesanmu sudah dihapus.'; $kode = '';
    } elseif (($_POST['aksi'] ?? '') === 'ubah') {                 // UPDATE (kembali pending)
        $isi = trim($_POST['isi'] ?? ''); $m = (int)$_POST['id_mood'];
        if (mb_strlen($isi) >= 5 && mb_strlen($isi) <= 300) {
            $pdo->prepare("UPDATE pesan_harapan SET isi=?, id_mood=?, status='pending' WHERE kode_edit=?")
                ->execute([$isi, $m, $kode]);
            $info = 'Pesan diperbarui dan menunggu persetujuan admin lagi.';
        } else { $info = 'Pesan harus 5 sampai 300 karakter.'; }
    }
}
$data = $kode ? $cari() : null;
if ($kode && !$data && !$info) $info = 'Kode tidak ditemukan.';

require 'includes/header.php';
?>
<section class="hero">
  <h1>Pesanku</h1>
  <?php if ($info): ?><div class="info"><?= e($info) ?></div><?php endif; ?>

  <?php if (!$data): ?>
  <form method="get" class="form">
    <label for="kode">Masukkan kode edit</label>
    <input id="kode" name="kode" maxlength="10" required>
    <button class="tombol">Cari pesan</button>
  </form>
  <?php else: ?>
  <form method="post" class="form">
    <?= csrf_field() ?>
    <input type="hidden" name="kode" value="<?= e($kode) ?>">
    <p>Status: <strong><?= e($data['status']) ?></strong></p>
    <label for="isi">Pesan</label>
    <textarea id="isi" name="isi" rows="3" maxlength="300" required><?= e($data['isi']) ?></textarea>
    <label for="id_mood">Perasaan</label>
    <select id="id_mood" name="id_mood">
      <?php foreach ($moods as $m): ?>
        <option value="<?= $m['id'] ?>" <?= $m['id'] == $data['id_mood'] ? 'selected' : '' ?>><?= e($m['nama']) ?></option>
      <?php endforeach; ?>
    </select>
    <button class="tombol" name="aksi" value="ubah">Simpan perubahan</button>
    <button class="tombol bahaya" name="aksi" value="hapus" onclick="return confirm('Hapus pesan ini?')">Hapus pesan</button>
  </form>
  <?php endif; ?>
</section>
<?php require 'includes/footer.php'; ?>
