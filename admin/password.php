<?php
require '../config/koneksi.php';
require '../includes/auth.php';
$base = '../'; $judul = 'Ganti password'; $pesan = ''; $error = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    cek_csrf();
    $lama = $_POST['lama'] ?? ''; $baru = $_POST['baru'] ?? ''; $ulang = $_POST['ulang'] ?? '';
    $s = $pdo->prepare("SELECT * FROM admin WHERE username = ?");
    $s->execute([$_SESSION['admin']]);
    $a = $s->fetch();
    if (!$a || !password_verify($lama, $a['password'])) {
        $error = 'Password lama salah.';
    } elseif (strlen($baru) < 8) {
        $error = 'Password baru minimal 8 karakter.';
    } elseif ($baru === $lama) {
        $error = 'Password baru harus berbeda dari yang lama.';
    } elseif ($baru !== $ulang) {
        $error = 'Konfirmasi password tidak sama.';
    } else {                                              // UPDATE
        $pdo->prepare("UPDATE admin SET password = ? WHERE id = ?")
            ->execute([password_hash($baru, PASSWORD_DEFAULT), $a['id']]);
        session_regenerate_id(true);
        $pwDefault = false;
        $pesan = 'Password berhasil diganti.';
    }
}
require '../includes/header.php';
?>
<section class="hero">
  <h1>Ganti password</h1>
  <?php if ($pesan): ?><div class="info"><?= e($pesan) ?></div><?php endif; ?>
  <?php if ($error): ?><div class="info salah"><?= e($error) ?></div><?php endif; ?>
  <form method="post" class="form">
    <?= csrf_field() ?>
    <label for="lama">Password lama</label>
    <input id="lama" name="lama" type="password" autocomplete="current-password" required>
    <label for="baru">Password baru (minimal 8 karakter)</label>
    <input id="baru" name="baru" type="password" autocomplete="new-password" minlength="8" required>
    <label for="ulang">Ulangi password baru</label>
    <input id="ulang" name="ulang" type="password" autocomplete="new-password" minlength="8" required>
    <button class="tombol">Simpan password</button>
  </form>
</section>
<?php require '../includes/footer.php'; ?>
