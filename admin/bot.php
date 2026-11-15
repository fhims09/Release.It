<?php
require '../config/koneksi.php';
require '../includes/auth.php';
$base = '../'; $judul = 'Respons bot';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {          // DELETE
    cek_csrf();
    $pdo->prepare("DELETE FROM respons_bot WHERE id = ?")->execute([(int)$_POST['hapus']]);
    header('Location: bot.php'); exit;
}
$q = trim($_GET['q'] ?? '');                          // READ + cari
$s = $pdo->prepare("SELECT r.*, m.nama AS mood FROM respons_bot r LEFT JOIN mood m ON m.id = r.id_mood
                    WHERE r.kata_kunci LIKE ? OR r.balasan LIKE ? ORDER BY r.id DESC");
$s->execute(["%$q%", "%$q%"]);
$rows = $s->fetchAll();
require '../includes/header.php';
?>
<section>
  <h1>Respons chatbot</h1>
  <p>Rilis memilih balasan dari kata kunci pesan pengguna. Kata kunci <strong>*</strong> dipakai sebagai balasan cadangan.</p>
  <form method="get" class="baris">
    <input name="q" placeholder="Cari kata kunci atau balasan" value="<?= e($q) ?>">
    <button class="tombol kecil">Cari</button>
    <a class="tombol kecil" href="bot_form.php">Tambah respons</a>
  </form>
  <div class="gulir"><table>
    <tr><th>Kata kunci</th><th>Balasan</th><th>Mood</th><th>Aksi</th></tr>
    <?php foreach ($rows as $r): ?>
    <tr>
      <td><?= e($r['kata_kunci']) ?></td>
      <td><?= e(mb_strimwidth($r['balasan'], 0, 90, '...')) ?></td>
      <td><?= e($r['mood'] ?? '-') ?></td>
      <td>
        <a href="bot_form.php?id=<?= $r['id'] ?>">Ubah</a>
        <form method="post" class="inline" onsubmit="return confirm('Hapus respons ini?')">
          <?= csrf_field() ?><button name="hapus" value="<?= $r['id'] ?>" class="link-bahaya">Hapus</button>
        </form>
      </td>
    </tr>
    <?php endforeach; ?>
    <?php if (!$rows): ?><tr><td colspan="4">Belum ada respons yang cocok.</td></tr><?php endif; ?>
  </table></div>
</section>
<?php require '../includes/footer.php'; ?>
