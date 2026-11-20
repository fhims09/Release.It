<?php 
$base = $base ?? ''; 
$halamanSekarang = basename($_SERVER['PHP_SELF'] ?? '');
$userNav = function_exists('pengguna_aktif') ? pengguna_aktif() : null;
?>
<!DOCTYPE html>
<html lang="id">
<head>
<meta charset="UTF-8">
<meta name="viewport" content="width=device-width, initial-scale=1">
<meta name="csrf" content="<?= e(csrf_token()) ?>">
<title><?= e($judul ?? 'Release.It') ?> | Release.It</title>
<link rel="preconnect" href="https://fonts.googleapis.com">
<link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
<link href="https://fonts.googleapis.com/css2?family=Fraunces:ital,opsz,wght@0,9..144,600;0,9..144,700;1,9..144,400&family=Plus+Jakarta+Sans:wght@400;500;600;700;800&display=swap" rel="stylesheet">
<link rel="stylesheet" href="<?= $base ?>assets/style.css">
</head>
<body class="<?= e($kelas ?? ($base ? 'admin' : '')) ?>">
<div id="scrollProgress" class="scroll-progress" aria-hidden="true"></div>

<header class="atas">
  <div class="atas-wadah">
    <div class="atas-kiri">
      <a class="logo" href="<?= $base ?>index.php" aria-label="Beranda Release.It">
        <span class="logo-titik" aria-hidden="true">✦</span> Release<span class="logo-aksen">.It</span>
      </a>
      <span class="motto-header">Ruang Teduh Mahasiswa</span>
    </div>

    <div class="atas-aksi-nav">
      <!-- Tombol Suasana Teduh (Ambient Audio) -->
      <?php if ($base === ''): ?>
      <button type="button" class="btn-ambience" id="btnAmbience" aria-label="Buka Pemutar Suara Teduh" title="Suara Alam & Relaksasi">
        <span class="ambience-ikon" aria-hidden="true">🎧</span>
        <span class="ambience-label">Suara Teduh</span>
        <span class="ambience-status-dot" id="ambienceDot" aria-hidden="true"></span>
      </button>
      <?php endif; ?>

      <!-- Tombol Mobile Menu Toggle -->
      <button type="button" class="btn-menu-toggle" id="btnMenuToggle" aria-label="Buka menu navigasi" aria-expanded="false" aria-controls="navUtama">
        <span class="bar-toggle"></span>
        <span class="bar-toggle"></span>
        <span class="bar-toggle"></span>
      </button>
    </div>

    <nav id="navUtama" aria-label="Navigasi Utama">
      <?php if ($base === ''): ?>
        <a href="index.php" class="<?= $halamanSekarang === 'index.php' ? 'aktif' : '' ?>" <?= $halamanSekarang === 'index.php' ? 'aria-current="page"' : '' ?>>
          <span class="nav-ikon" aria-hidden="true">💬</span> Curhat
        </a>
        <a href="napas.php" class="<?= $halamanSekarang === 'napas.php' ? 'aktif' : '' ?>" <?= $halamanSekarang === 'napas.php' ? 'aria-current="page"' : '' ?>>
          <span class="nav-ikon" aria-hidden="true">🍃</span> Napas
        </a>
        <a href="dinding.php" class="<?= $halamanSekarang === 'dinding.php' ? 'aktif' : '' ?>" <?= $halamanSekarang === 'dinding.php' ? 'aria-current="page"' : '' ?>>
          <span class="nav-ikon" aria-hidden="true">✨</span> Dinding Harapan
        </a>
        <a href="komunitas.php" class="<?= $halamanSekarang === 'komunitas.php' ? 'aktif' : '' ?>" <?= $halamanSekarang === 'komunitas.php' ? 'aria-current="page"' : '' ?>>
          <span class="nav-ikon" aria-hidden="true">👥</span> Komunitas
        </a>
        
        <div class="nav-user-item">
          <?php if ($userNav): ?>
            <a href="komunitas.php" class="nav-user-badge aktif-user" title="Masuk sebagai <?= e($userNav['nama']) ?>">
              <span class="user-ikon" aria-hidden="true">👤</span>
              <span class="user-teks"><?= e($userNav['nama']) ?></span>
            </a>
          <?php else: ?>
            <a href="komunitas.php" class="nav-user-badge" title="Ruang Komunitas: Masuk atau Anonim">
              <span class="user-ikon" aria-hidden="true">🎭</span>
              <span class="user-teks">Mode Anonim</span>
            </a>
          <?php endif; ?>
        </div>
      <?php else: ?>
        <a href="index.php" class="<?= $halamanSekarang === 'index.php' ? 'aktif' : '' ?>">Konten</a>
        <a href="bot.php" class="<?= $halamanSekarang === 'bot.php' ? 'aktif' : '' ?>">Respons bot</a>
        <a href="pesan.php" class="<?= $halamanSekarang === 'pesan.php' ? 'aktif' : '' ?>">Moderasi pesan</a>
        <a href="password.php" class="<?= $halamanSekarang === 'password.php' ? 'aktif' : '' ?>">Ganti password</a>
        <a href="logout.php">Keluar</a>
      <?php endif; ?>
    </nav>
  </div>
</header>

<!-- Modal Ambient Sound Player -->
<?php if ($base === ''): ?>
<div id="ambienceModal" class="ambience-modal" hidden role="dialog" aria-modal="true" aria-labelledby="judulAmbience">
  <div class="ambience-box">
    <div class="ambience-header">
      <div class="ambience-title-group">
        <span class="ikon-zen" aria-hidden="true">🌊</span>
        <h3 id="judulAmbience">Suasana Teduh</h3>
      </div>
      <button type="button" class="btn-tutup-modal" id="btnTutupAmbience" aria-label="Tutup pemutar suara">&times;</button>
    </div>
    <p class="ambience-desc">Bunyi alami yang disintesis di browsermu untuk meredakan kecemasan dan menemani saat membaca atau menulis.</p>
    
    <div class="ambience-options">
      <button type="button" class="ambience-chip aktif" data-sound="rain">
        <span class="chip-icon">🌧️</span>
        <span class="chip-text">Hujan Tenang</span>
      </button>
      <button type="button" class="ambience-chip" data-sound="waves">
        <span class="chip-icon">🌊</span>
        <span class="chip-text">Ombak Laut</span>
      </button>
      <button type="button" class="ambience-chip" data-sound="wind">
        <span class="chip-icon">🍃</span>
        <span class="chip-text">Semilir Angin</span>
      </button>
      <button type="button" class="ambience-chip" data-sound="brown">
        <span class="chip-icon">☕</span>
        <span class="chip-text">Brown Noise</span>
      </button>
    </div>

    <div class="ambience-controls">
      <div class="volume-slider-group">
        <label for="volumeAmbience" class="volume-label">
          <span id="volumeIkon">🔊</span> Volume: <span id="volumeAngka">40%</span>
        </label>
        <input type="range" id="volumeAmbience" min="0" max="100" value="40" aria-label="Volume Suara Relaksasi">
      </div>
      <div class="ambience-actions">
        <button type="button" class="tombol" id="btnPlayAmbience">
          <span id="playIcon">▶</span> <span id="playText">Putar Suasana</span>
        </button>
      </div>
    </div>
  </div>
</div>
<?php endif; ?>

<!-- Wadah Toast Notifikasi Global -->
<div id="toastContainer" class="toast-container" aria-live="polite" aria-atomic="true"></div>
<main>
<?php if (!empty($pwDefault)): ?>
<div class="info salah">Password admin masih bawaan. <a href="password.php">Ganti sekarang</a>.</div>
<?php endif; ?>
