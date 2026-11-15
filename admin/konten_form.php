<?php
require '../config/koneksi.php';
require '../includes/auth.php';
$base = '../'; $judul = 'Form konten'; $error = '';
$moods = $pdo->query("SELECT * FROM mood ORDER BY id")->fetchAll();
$id = (int)($_GET['id'] ?? 0);
$d = ['tipe' => 'kutipan', 'isi' => '', 'id_mood' => 0];
if ($id) {
    $s = $pdo->prepare("SELECT * FROM konten WHERE id = ?"); $s->execute([$id]);
    $d = $s->fetch() ?: die('Konten tidak ditemukan.');
}

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    cek_csrf();
    $d = ['tipe' => $_POST['tipe'] ?? '', 'isi' => trim($_POST['isi'] ?? ''), 'id_mood' => (int)($_POST['id_mood'] ?? 0)];
    if (!in_array($d['tipe'], ['kutipan','video','gambar'])) $error = 'Tipe tidak valid.';
    elseif ($d['isi'] === '' || !$d['id_mood']) $error = 'Isi dan mood wajib diisi.';
    elseif ($d['tipe'] === 'video' && !youtube_id($d['isi'])) $error = 'Link YouTube tidak dikenali.';
    elseif ($d['tipe'] === 'gambar' && !filter_var($d['isi'], FILTER_VALIDATE_URL)) $error = 'Link gambar tidak valid.';
    else {
        if ($id) {   // UPDATE
            $pdo->prepare("UPDATE konten SET tipe=?, isi=?, id_mood=? WHERE id=?")
                ->execute([$d['tipe'], $d['isi'], $d['id_mood'], $id]);
        } else {     // CREATE
            $pdo->prepare("INSERT INTO konten (tipe, isi, id_mood) VALUES (?,?,?)")
                ->execute([$d['tipe'], $d['isi'], $d['id_mood']]);
        }
        header('Location: index.php'); exit;
    }
}
require '../includes/header.php';
?>
<section class="hero">
  <h1><?= $id ? 'Ubah konten' : 'Tambah konten' ?></h1>
  <?php if ($error): ?><div class="info salah"><?= e($error) ?></div><?php endif; ?>
  <form method="post" class="form">
    <?= csrf_field() ?>
    <label for="tipe">Tipe</label>
    <select id="tipe" name="tipe">
      <?php foreach (['kutipan','video','gambar'] as $t): ?>
        <option value="<?= $t ?>" <?= $d['tipe'] === $t ? 'selected' : '' ?>><?= $t ?></option>
      <?php endforeach; ?>
    </select>
    <label for="isi">Isi (teks kutipan, link YouTube, atau link gambar)</label>
    <textarea id="isi" name="isi" rows="3" required><?= e($d['isi']) ?></textarea>
    <label for="id_mood">Mood</label>
    <select id="id_mood" name="id_mood" required>
      <option value="">Pilih mood</option>
      <?php foreach ($moods as $m): ?>
        <option value="<?= $m['id'] ?>" <?= $d['id_mood'] == $m['id'] ? 'selected' : '' ?>><?= e($m['nama']) ?></option>
      <?php endforeach; ?>
    </select>
    <button class="tombol">Simpan konten</button>
  </form>
</section>
<?php require '../includes/footer.php'; ?>
