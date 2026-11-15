<?php
require '../config/koneksi.php';
require '../includes/auth.php';
$base = '../'; $judul = 'Form respons bot'; $error = '';
$moods = $pdo->query("SELECT * FROM mood ORDER BY id")->fetchAll();
$id = (int)($_GET['id'] ?? 0);
$d = ['kata_kunci' => '', 'balasan' => '', 'id_mood' => null];
if ($id) {
    $s = $pdo->prepare("SELECT * FROM respons_bot WHERE id = ?"); $s->execute([$id]);
    $d = $s->fetch() ?: die('Respons tidak ditemukan.');
}
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    cek_csrf();
    $d = ['kata_kunci' => trim($_POST['kata_kunci'] ?? ''), 'balasan' => trim($_POST['balasan'] ?? ''),
          'id_mood' => (int)($_POST['id_mood'] ?? 0) ?: null];
    if ($d['kata_kunci'] === '' || mb_strlen($d['kata_kunci']) > 255) $error = 'Kata kunci wajib diisi (maks 255 karakter).';
    elseif (mb_strlen($d['balasan']) < 5) $error = 'Balasan terlalu pendek.';
    else {
        if ($id) $pdo->prepare("UPDATE respons_bot SET kata_kunci=?, balasan=?, id_mood=? WHERE id=?")
                     ->execute([$d['kata_kunci'], $d['balasan'], $d['id_mood'], $id]);          // UPDATE
        else     $pdo->prepare("INSERT INTO respons_bot (kata_kunci, balasan, id_mood) VALUES (?,?,?)")
                     ->execute([$d['kata_kunci'], $d['balasan'], $d['id_mood']]);               // CREATE
        header('Location: bot.php'); exit;
    }
}
require '../includes/header.php';
?>
<section class="hero">
  <h1><?= $id ? 'Ubah respons' : 'Tambah respons' ?></h1>
  <?php if ($error): ?><div class="info salah"><?= e($error) ?></div><?php endif; ?>
  <form method="post" class="form">
    <?= csrf_field() ?>
    <label for="kata_kunci">Kata kunci (pisahkan dengan koma, atau * untuk cadangan)</label>
    <input id="kata_kunci" name="kata_kunci" value="<?= e($d['kata_kunci']) ?>" required>
    <label for="balasan">Balasan Rilis</label>
    <textarea id="balasan" name="balasan" rows="4" required><?= e($d['balasan']) ?></textarea>
    <label for="id_mood">Lampirkan konten dari mood (opsional)</label>
    <select id="id_mood" name="id_mood">
      <option value="">Tanpa lampiran</option>
      <?php foreach ($moods as $m): ?>
        <option value="<?= $m['id'] ?>" <?= $d['id_mood'] == $m['id'] ? 'selected' : '' ?>><?= e($m['nama']) ?></option>
      <?php endforeach; ?>
    </select>
    <button class="tombol">Simpan respons</button>
  </form>
</section>
<?php require '../includes/footer.php'; ?>
