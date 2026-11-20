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
<section class="hero hero-pesanku">
  <div class="header-pesanku">
    <h1>Kelola Pesanku</h1>
    <p>Gunakan kode rahasia yang kamu dapatkan saat mengirim pesan di Dinding Harapan untuk menyunting isi kalimat atau menghapusnya.</p>
  </div>

  <?php if ($info): ?><div class="info" role="status"><?= e($info) ?></div><?php endif; ?>

  <div class="kartu kartu-kelola-pesan">
    <?php if (!$data): ?>
    <form method="get" class="form form-cari-kode">
      <div class="grup-input">
        <label for="kode">Masukkan 6-Karakter Kode Edit</label>
        <div class="baris-input-kode">
          <input id="kode" name="kode" maxlength="10" placeholder="Contoh: 4F2A1B" value="<?= e($kode) ?>" required autocomplete="off" style="text-transform:uppercase; font-family:ui-monospace, monospace; font-size:1.15rem; letter-spacing:0.1em;">
          <button class="tombol">
            <span>🔍</span> Cari Pesan
          </button>
        </div>
      </div>
      <p class="petunjuk-kode">Kode ini unik dan tidak disimpan dengan identitasmu agar privasimu tetap terjaga 100%.</p>
    </form>
    <?php else: ?>
    <form method="post" class="form form-edit-pesan">
      <?= csrf_field() ?>
      <input type="hidden" name="kode" value="<?= e($kode) ?>">

      <div class="status-pesanku-bar">
        <span>Kode: <strong class="kode"><?= e($kode) ?></strong></span>
        <span>Status Kurasi: 
          <?php if ($data['status'] === 'disetujui'): ?>
            <span class="badge-status-pesan hijau">✓ Tayang di Dinding</span>
          <?php elseif ($data['status'] === 'pending'): ?>
            <span class="badge-status-pesan kuning">⏳ Menunggu Moderasi</span>
          <?php else: ?>
            <span class="badge-status-pesan merah">✕ Ditolak</span>
          <?php endif; ?>
        </span>
      </div>

      <div class="grup-input">
        <label for="isi">Kalimat Harapan</label>
        <textarea id="isi" name="isi" rows="3" maxlength="300" required><?= e($data['isi']) ?></textarea>
        <div class="counter-karakter-dinding">
          <span id="counterDinding">0</span> / 300 karakter
        </div>
      </div>

      <div class="grup-input">
        <label for="id_mood">Ditujukan Untuk Yang Sedang Merasa</label>
        <select id="id_mood" name="id_mood">
          <?php foreach ($moods as $m): ?>
            <option value="<?= $m['id'] ?>" <?= $m['id'] == $data['id_mood'] ? 'selected' : '' ?>><?= e($m['nama']) ?></option>
          <?php endforeach; ?>
        </select>
      </div>

      <div class="baris-tombol-edit-pesan">
        <button class="tombol" name="aksi" value="ubah">
          <span>💾</span> Simpan Perubahan
        </button>
        <button class="tombol bahaya" name="aksi" value="hapus" onclick="return confirm('Apakah kamu yakin ingin menghapus pesan ini secara permanen?')">
          <span>🗑️</span> Hapus Pesan
        </button>
        <a href="pesanku.php" class="tombol sekunder">Cari Kode Lain</a>
      </div>
    </form>
    <?php endif; ?>
  </div>
</section>
<?php require 'includes/footer.php'; ?>
