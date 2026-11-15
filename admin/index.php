<?php
require '../config/koneksi.php';
require '../includes/auth.php';
$base = '../'; $judul = 'Kelola konten';

// DELETE
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    cek_csrf();
    $pdo->prepare("DELETE FROM konten WHERE id = ?")->execute([(int)$_POST['hapus']]);
    header('Location: index.php'); exit;
}

// READ + cari + filter tipe
$q = trim($_GET['q'] ?? ''); $tipe = $_GET['tipe'] ?? '';
$sql = "SELECT k.*, m.nama AS mood FROM konten k JOIN mood m ON m.id = k.id_mood WHERE 1";
$par = [];
if ($q !== '') { $sql .= " AND k.isi LIKE ?"; $par[] = "%$q%"; }
if (in_array($tipe, ['kutipan','video','gambar'])) { $sql .= " AND k.tipe = ?"; $par[] = $tipe; }
$s = $pdo->prepare($sql . " ORDER BY k.id DESC");
$s->execute($par);
$rows = $s->fetchAll();
require '../includes/header.php';
?>
<section>
  <h1>Kelola konten</h1>
  <form method="get" class="baris">
    <input name="q" placeholder="Cari isi konten" value="<?= e($q) ?>">
    <select name="tipe">
      <option value="">Semua tipe</option>
      <?php foreach (['kutipan','video','gambar'] as $t): ?>
        <option value="<?= $t ?>" <?= $tipe === $t ? 'selected' : '' ?>><?= $t ?></option>
      <?php endforeach; ?>
    </select>
    <button class="tombol kecil">Cari</button>
    <a class="tombol kecil" href="konten_form.php">Tambah konten</a>
  </form>
  <div class="gulir">
  <table>
    <tr><th>Tipe</th><th>Isi</th><th>Mood</th><th>Aksi</th></tr>
    <?php foreach ($rows as $r): ?>
    <tr>
      <td><?= e($r['tipe']) ?></td>
      <td><?= e(mb_strimwidth($r['isi'], 0, 80, '...')) ?></td>
      <td><?= e($r['mood']) ?></td>
      <td>
        <a href="konten_form.php?id=<?= $r['id'] ?>">Ubah</a>
        <form method="post" class="inline" onsubmit="return confirm('Hapus konten ini?')">
          <?= csrf_field() ?><button name="hapus" value="<?= $r['id'] ?>" class="link-bahaya">Hapus</button>
        </form>
      </td>
    </tr>
    <?php endforeach; ?>
    <?php if (!$rows): ?><tr><td colspan="4">Belum ada konten yang cocok.</td></tr><?php endif; ?>
  </table>
  </div>
</section>
<?php require '../includes/footer.php'; ?>
