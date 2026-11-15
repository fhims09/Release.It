<?php $base = $base ?? ''; ?>
<!DOCTYPE html>
<html lang="id">
<head>
<meta charset="UTF-8">
<meta name="viewport" content="width=device-width, initial-scale=1">
<meta name="csrf" content="<?= e(csrf_token()) ?>">
<title><?= e($judul ?? 'Release.It') ?> | Release.It</title>
<link rel="preconnect" href="https://fonts.googleapis.com">
<link href="https://fonts.googleapis.com/css2?family=Lora:wght@500;700&family=Nunito+Sans:wght@400;600&display=swap" rel="stylesheet">
<link rel="stylesheet" href="<?= $base ?>assets/style.css">
</head>
<body class="<?= e($kelas ?? ($base ? 'admin' : '')) ?>">
<header class="atas">
  <a class="logo" href="<?= $base ?>index.php">Release.It</a>
  <nav>
    <?php if ($base === ''): ?>
      <a href="index.php">Curhat</a>
      <a href="napas.php">Napas</a>
      <a href="dinding.php">Dinding harapan</a>
    <?php else: ?>
      <a href="index.php">Konten</a>
      <a href="bot.php">Respons bot</a>
      <a href="pesan.php">Moderasi pesan</a>
      <a href="password.php">Ganti password</a>
      <a href="logout.php">Keluar</a>
    <?php endif; ?>
  </nav>
</header>
<main>
<?php if (!empty($pwDefault)): ?>
<div class="info salah">Password admin masih bawaan. <a href="password.php">Ganti sekarang</a>.</div>
<?php endif; ?>
