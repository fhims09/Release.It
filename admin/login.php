<?php
require '../config/koneksi.php';
$base = '../'; $judul = 'Login admin'; $error = '';

// Pembatasan percobaan login: maksimal 5 kegagalan per IP dalam 15 menit
$pdo->exec("CREATE TABLE IF NOT EXISTS login_gagal (
    id INT AUTO_INCREMENT PRIMARY KEY, ip VARCHAR(45) NOT NULL, waktu DATETIME NOT NULL, INDEX (ip, waktu))");
$ip = $_SERVER['REMOTE_ADDR'] ?? '0';
$pdo->exec("DELETE FROM login_gagal WHERE waktu < NOW() - INTERVAL 1 DAY");
$s = $pdo->prepare("SELECT COUNT(*) FROM login_gagal WHERE ip = ? AND waktu > NOW() - INTERVAL 0 MINUTE");
$s->execute([$ip]);
$terkunci = (int)$s->fetchColumn() >= 5;

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    cek_csrf();
    if ($terkunci) {
        $error = 'Terlalu banyak percobaan gagal. Coba lagi dalam 15 menit.';
    } else {
        $s = $pdo->prepare("SELECT * FROM admin WHERE username = ?");
        $s->execute([trim($_POST['username'] ?? '')]);
        $a = $s->fetch();
        if ($a && password_verify($_POST['password'] ?? '', $a['password'])) {
            $pdo->prepare("DELETE FROM login_gagal WHERE ip = ?")->execute([$ip]);
            session_regenerate_id(true);
            $_SESSION['admin'] = $a['username'];
            header('Location: index.php'); exit;
        }
        $pdo->prepare("INSERT INTO login_gagal (ip, waktu) VALUES (?, NOW())")->execute([$ip]);
        $error = 'Username atau password salah.';
    }
}
require '../includes/header.php';
?>
<section class="hero">
  <h1>Masuk admin</h1>
  <?php if ($error): ?><div class="info salah"><?= e($error) ?></div><?php endif; ?>
  <form method="post" class="form">
    <?= csrf_field() ?>
    <label for="username">Username</label><input id="username" name="username" required>
    <label for="password">Password</label><input id="password" name="password" type="password" required>
    <button class="tombol">Masuk</button>
  </form>
</section>
<?php require '../includes/footer.php'; ?>
