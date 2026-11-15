<?php
require '../config/koneksi.php';
require '../includes/auth.php';
$base = '../'; $judul = 'Moderasi pesan';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    cek_csrf();
    $pid = (int)$_POST['id'];
    if ($_POST['aksi'] === 'setuju') {          // UPDATE status
        $pdo->prepare("UPDATE pesan_harapan SET status='disetujui' WHERE id=?")->execute([$pid]);
    } else {                                     // DELETE
        $pdo->prepare("DELETE FROM pesan_harapan WHERE id=?")->execute([$pid]);
    }
    header('Location: pesan.php'); exit;
}
$rows = $pdo->query("SELECT p.*, m.nama AS mood FROM pesan_harapan p JOIN mood m ON m.id=p.id_mood
                     ORDER BY p.status='pending' DESC, p.created_at DESC")->fetchAll();
require '../includes/header.php';
?>
<section>
  <h1>Moderasi pesan</h1>
  <p>Periksa tiap pesan. Tolak yang menyindir, memicu, atau memuat cara menyakiti diri.</p>
  <div class="gulir">
  <table>
    <tr><th>Pesan</th><th>Mood</th><th>Status</th><th>Aksi</th></tr>
    <?php foreach ($rows as $r): ?>
    <tr>
      <td><?= e($r['isi']) ?></td><td><?= e($r['mood']) ?></td><td><?= e($r['status']) ?></td>
      <td>
        <form method="post" class="inline">
          <?= csrf_field() ?><input type="hidden" name="id" value="<?= $r['id'] ?>">
          <?php if ($r['status'] === 'pending'): ?><button name="aksi" value="setuju" class="link">Setujui</button><?php endif; ?>
          <button name="aksi" value="hapus" class="link-bahaya" onclick="return confirm('Hapus pesan ini?')">Hapus</button>
        </form>
      </td>
    </tr>
    <?php endforeach; ?>
    <?php if (!$rows): ?><tr><td colspan="4">Belum ada pesan masuk.</td></tr><?php endif; ?>
  </table>
  </div>
</section>
<?php require '../includes/footer.php'; ?>
