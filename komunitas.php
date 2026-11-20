<?php
require 'config/koneksi.php';
$judul = 'Ruang Komunitas';
$kelas = 'komunitas';

$userAktif = pengguna_aktif();
$aliasBawaan = buat_alias_anonim();

// Filter topik & pencarian & urutan
$slugTopik = trim($_GET['topik'] ?? '');
$cari = trim($_GET['q'] ?? '');
$urut = $_GET['urut'] ?? 'terbaru';

$semuaTopik = $pdo->query("SELECT t.*, (SELECT COUNT(*) FROM komunitas_post WHERE id_topik = t.id) AS jumlah_post FROM komunitas_topik t ORDER BY t.urutan ASC")->fetchAll();

$kondisi = [];
$params = [];

if ($slugTopik !== '') {
    $kondisi[] = "t.slug = ?";
    $params[] = $slugTopik;
}

if ($cari !== '') {
    $kondisi[] = "(p.judul LIKE ? OR p.isi LIKE ?)";
    $params[] = '%' . $cari . '%';
    $params[] = '%' . $cari . '%';
}

$whereSql = $kondisi ? 'WHERE ' . implode(' AND ', $kondisi) : '';

$orderSql = "ORDER BY p.created_at DESC";
if ($urut === 'peluk') {
    $orderSql = "ORDER BY (p.reaksi_peluk + p.reaksi_semangat + p.reaksi_relate) DESC, p.created_at DESC";
}

$sql = "SELECT p.*, t.nama AS nama_topik, t.slug AS slug_topik,
        (SELECT COUNT(*) FROM komunitas_balasan WHERE id_post = p.id) AS jumlah_balasan
        FROM komunitas_post p
        JOIN komunitas_topik t ON t.id = p.id_topik
        $whereSql
        $orderSql";

$st = $pdo->prepare($sql);
$st->execute($params);
$daftarPost = $st->fetchAll();

// Pesan notifikasi dari query string
$pesanSukses = '';
$pesanError = '';
if (isset($_GET['pesan'])) {
    if ($_GET['pesan'] === 'post_sukses') $pesanSukses = 'Ceritamu berhasil dibagikan ke ruang komunitas.';
    if ($_GET['pesan'] === 'balasan_sukses') $pesanSukses = 'Pesan dukunganmu sudah terkirim.';
    if ($_GET['pesan'] === 'login_sukses') $pesanSukses = 'Berhasil masuk ke akunmu.';
    if ($_GET['pesan'] === 'daftar_sukses') $pesanSukses = 'Akunmu berhasil dibuat. Selamat datang di Release.It!';
    if ($_GET['pesan'] === 'logout_sukses') $pesanSukses = 'Kamu sudah keluar dan kembali ke mode anonim.';
}
if (isset($_GET['error'])) {
    if ($_GET['error'] === 'login_gagal') $pesanError = 'Username atau kata sandi tidak cocok.';
    elseif ($_GET['error'] === 'login_kosong') $pesanError = 'Silakan isi username dan kata sandi.';
    else $pesanError = htmlspecialchars($_GET['error'], ENT_QUOTES, 'UTF-8');
}

require 'includes/header.php';
?>

<section class="hero hero-komunitas">
  <div class="komunitas-intro">
    <h1>Ruang Komunitas</h1>
    <p>Lingkaran aman untuk saling berbagi, mendengarkan, dan menguatkan. Kamu bebas bersuara memakai akun atau sepenuhnya anonim.</p>
  </div>

  <!-- Status Partisipasi: Login atau Anonim -->
  <div class="kartu kartu-status-user">
    <div class="status-user-info">
      <?php if ($userAktif): ?>
        <div class="user-badge-lingkaran anggota" aria-hidden="true">👤</div>
        <div>
          <div class="label-status">Status Masuk</div>
          <div class="nama-user-aktif">
            <strong><?= e($userAktif['nama']) ?></strong> 
            <span class="tag-username">@<?= e($userAktif['username']) ?></span>
            <span class="badge-role">Anggota</span>
          </div>
          <p class="keterangan-status">Kamu terhubung dengan akunmu. Saat membagikan cerita atau membalas, kamu tetap bisa memilih opsi anonim.</p>
        </div>
      <?php else: ?>
        <div class="user-badge-lingkaran anonim" aria-hidden="true">🎭</div>
        <div>
          <div class="label-status">Status Partisipasi</div>
          <div class="nama-user-aktif">
            <strong>Mode Tamu Anonim</strong>
            <span class="badge-role anon">Bebas Akun</span>
          </div>
          <p class="keterangan-status">Kamu dapat membaca, bereaksi, dan menulis cerita tanpa perlu mendaftar. Identitas aslimu aman dan tidak terlihat.</p>
        </div>
      <?php endif; ?>
    </div>
    
    <div class="status-user-aksi">
      <button type="button" class="tombol" id="btnBukaTulis" aria-expanded="false" aria-controls="kotakTulisPost">
        ✏️ Tulis Cerita
      </button>
      <?php if ($userAktif): ?>
        <a href="komunitas_aksi.php?aksi=logout" class="tombol sekunder tombol-kecil">Keluar Akun</a>
      <?php else: ?>
        <button type="button" class="tombol sekunder tombol-kecil" id="btnBukaModalAuth">
          Masuk / Daftar
        </button>
      <?php endif; ?>
    </div>
  </div>

  <?php if ($pesanSukses): ?>
    <div class="info" role="status" aria-live="polite"><?= e($pesanSukses) ?></div>
  <?php endif; ?>
  <?php if ($pesanError): ?>
    <div class="info salah" role="alert"><?= e($pesanError) ?></div>
  <?php endif; ?>

  <!-- Formulir Tulis Cerita Baru (Bisa dilipat / dibuka) -->
  <div id="kotakTulisPost" class="kotak-tulis-post" hidden>
    <div class="kartu form-post-kartu">
      <div class="form-post-header">
        <h2>Bagikan Cerita di Komunitas</h2>
        <button type="button" class="tombol-tutup-form" id="btnTutupTulis" aria-label="Tutup formulir">✕</button>
      </div>
      <p class="form-post-petunjuk">Ruang ini dimoderasi dengan kasih. Hindari menyebut nama asli orang lain atau informasi sensitif.</p>

      <form action="komunitas_aksi.php" method="post" class="form form-komunitas">
        <?= csrf_field() ?>
        <input type="hidden" name="aksi" value="post_baru">
        <div class="jebakan" aria-hidden="true"><label>Jangan diisi<input name="website" tabindex="-1" autocomplete="off"></label></div>

        <div class="grup-input">
          <label for="post_topik">Pilih Ruang Topik</label>
          <select id="post_topik" name="id_topik" required>
            <option value="">Pilih topik yang sesuai</option>
            <?php foreach ($semuaTopik as $topik): ?>
              <option value="<?= $topik['id'] ?>" <?= $slugTopik === $topik['slug'] ? 'selected' : '' ?>>
                <?= e($topik['nama']) ?>
              </option>
            <?php endforeach; ?>
          </select>
        </div>

        <div class="grup-input">
          <label for="post_judul">Judul Cerita</label>
          <input type="text" id="post_judul" name="judul" maxlength="150" placeholder="Ringkasan singkat apa yang kamu rasakan..." required>
        </div>

        <div class="grup-input">
          <label for="post_isi">Isi Cerita / Keluh Kesah</label>
          <textarea id="post_isi" name="isi" rows="5" maxlength="2500" placeholder="Tuliskan dengan leluasa. Di sini tidak ada yang akan menghakimimu..." required></textarea>
        </div>

        <!-- Pemilihan Identitas Pengirim -->
        <fieldset class="grup-identitas-pengirim">
          <legend class="legend-identitas">Identitas Pengirim:</legend>
          
          <?php if ($userAktif): ?>
            <label class="opsi-radio">
              <input type="radio" name="is_anonim" value="0" checked onchange="toggleAliasBox(false)">
              <span>Kirim sebagai akun saya: <strong><?= e($userAktif['nama']) ?></strong></span>
            </label>
            <label class="opsi-radio">
              <input type="radio" name="is_anonim" value="1" onchange="toggleAliasBox(true)">
              <span>Kirim sebagai <strong>Anonim</strong> (sembunyikan nama akun)</span>
            </label>
          <?php else: ?>
            <input type="hidden" name="is_anonim" value="1">
            <div class="info-anonim-tamu">
              Kamu membagikan cerita sebagai pengunjung <strong>Anonim</strong>.
            </div>
          <?php endif; ?>

          <div id="kotakAliasAnonim" class="kotak-alias <?= $userAktif ? 'alias-tersembunyi' : '' ?>">
            <label for="alias_anonim">Nama Samaran yang Ditampilkan:</label>
            <div class="baris-alias">
              <input type="text" id="alias_anonim" name="alias_anonim" value="<?= e($aliasBawaan) ?>" maxlength="40">
              <button type="button" class="tombol sekunder tombol-kecil" id="btnAcakAlias" title="Acak nama samaran baru">
                🎲 Acak Nama
              </button>
            </div>
          </div>
        </fieldset>

        <div class="baris-tombol-form">
          <button type="submit" class="tombol">Bagikan ke Komunitas</button>
          <button type="button" class="tombol sekunder" id="btnBatalTulis">Batal</button>
        </div>
      </form>
    </div>
  </div>
</section>

<!-- Filter Topik & Pencarian -->
<section class="bagian-filter-komunitas">
  <div class="filter-topik-bar">
    <div class="label-filter-topik">Ruang Diskusi:</div>
    <div class="mood-list list-topik">
      <a class="chip <?= $slugTopik === '' ? 'aktif' : '' ?>" href="komunitas.php<?= $cari ? '?q=' . urlencode($cari) : '' ?>">
        Semua Cerita
      </a>
      <?php foreach ($semuaTopik as $topik): ?>
        <a class="chip <?= $slugTopik === $topik['slug'] ? 'aktif' : '' ?>" 
           href="?topik=<?= e($topik['slug']) ?><?= $cari ? '&q=' . urlencode($cari) : '' ?>">
          <?= e($topik['nama']) ?> (<?= (int)$topik['jumlah_post'] ?>)
        </a>
      <?php endforeach; ?>
    </div>
  </div>

  <div class="baris-kendali-post">
    <form method="get" class="form-cari-komunitas" role="search">
      <?php if ($slugTopik): ?><input type="hidden" name="topik" value="<?= e($slugTopik) ?>"><?php endif; ?>
      <label for="inputCari" class="visually-hidden">Cari cerita</label>
      <input type="search" id="inputCari" name="q" value="<?= e($cari) ?>" placeholder="Cari topik atau kata kunci..." class="input-cari">
      <button type="submit" class="tombol tombol-kecil">Cari</button>
      <?php if ($cari): ?>
        <a href="komunitas.php<?= $slugTopik ? '?topik=' . urlencode($slugTopik) : '' ?>" class="chip tombol-reset-cari">Reset</a>
      <?php endif; ?>
    </form>

    <div class="opsi-urutan">
      <span class="label-urutan">Urutkan:</span>
      <a href="?<?= http_build_query(array_merge($_GET, ['urut' => 'terbaru'])) ?>" class="chip <?= $urut !== 'peluk' ? 'aktif' : '' ?>">Terbaru</a>
      <a href="?<?= http_build_query(array_merge($_GET, ['urut' => 'peluk'])) ?>" class="chip <?= $urut === 'peluk' ? 'aktif' : '' ?>">Paling Didukung</a>
    </div>
  </div>
</section>

<!-- Daftar Postingan Komunitas -->
<section class="bagian-daftar-post">
  <?php if (empty($daftarPost)): ?>
    <div class="kartu kartu-kosong-komunitas">
      <div class="ikon-kosong" aria-hidden="true">🌌</div>
      <h2>Belum Ada Cerita di Ruang Ini</h2>
      <p>Jadilah orang pertama yang menuangkan isi hati atau menyalakan lentera harapan di sini.</p>
      <button type="button" class="tombol" onclick="bukaFormTulis()">Mulai Tulis Cerita Pertama</button>
    </div>
  <?php else: ?>
    <div class="feed-komunitas" role="feed" aria-label="Cerita komunitas">
      <?php foreach ($daftarPost as $p): ?>
        <?php
          // Cek apakah sudah pernah beri reaksi pada post ini
          $kunciSesi = hash('sha256', session_id() . ($_SERVER['REMOTE_ADDR'] ?? 'local'));
          $stReaksiUser = $pdo->prepare("SELECT tipe FROM komunitas_reaksi WHERE id_post = ? AND kunci_sesi = ?");
          $stReaksiUser->execute([$p['id'], $kunciSesi]);
          $reaksiSaya = $stReaksiUser->fetchAll(PDO::FETCH_COLUMN);

          // Ambil daftar balasan untuk post ini
          $stBalas = $pdo->prepare("SELECT * FROM komunitas_balasan WHERE id_post = ? ORDER BY created_at ASC");
          $stBalas->execute([$p['id']]);
          $balasanList = $stBalas->fetchAll();
        ?>
        <article class="kartu kartu-post-komunitas" id="post-<?= (int)$p['id'] ?>">
          <!-- Header Post -->
          <header class="header-post">
            <div class="info-penulis">
              <span class="avatar-penulis <?= $p['is_anonim'] ? 'avatar-anon' : 'avatar-user' ?>" aria-hidden="true">
                <?= $p['is_anonim'] ? '🎭' : '👤' ?>
              </span>
              <div>
                <div class="nama-penulis">
                  <strong><?= e($p['nama_pengirim']) ?></strong>
                  <?php if ($p['is_anonim']): ?>
                    <span class="lencana lencana-anon">Anonim</span>
                  <?php else: ?>
                    <span class="lencana lencana-anggota">Anggota</span>
                  <?php endif; ?>
                </div>
                <time datetime="<?= e($p['created_at']) ?>" class="waktu-post">
                  <?= e(waktu_lalu($p['created_at'])) ?>
                </time>
              </div>
            </div>
            <a href="?topik=<?= e($p['slug_topik']) ?>" class="tag-topik-post">
              <?= e($p['nama_topik']) ?>
            </a>
          </header>

          <!-- Konten Post -->
          <h2 class="judul-post"><?= e($p['judul']) ?></h2>
          <div class="isi-post">
            <p><?= nl2br(e($p['isi'])) ?></p>
          </div>

          <!-- Bar Interaksi Reaksi & Balasan -->
          <footer class="footer-post">
            <div class="grup-reaksi" data-post-id="<?= (int)$p['id'] ?>">
              <button type="button" 
                      class="btn-reaksi <?= in_array('peluk', $reaksiSaya, true) ? 'aktif' : '' ?>" 
                      data-tipe="peluk" 
                      title="Kirim pelukan hangat untuk pengirim">
                <span class="ikon-reaksi" aria-hidden="true">🫂</span>
                <span class="label-reaksi">Peluk Hangat</span>
                <span class="hitung-reaksi"><?= (int)$p['reaksi_peluk'] ?></span>
              </button>

              <button type="button" 
                      class="btn-reaksi <?= in_array('semangat', $reaksiSaya, true) ? 'aktif' : '' ?>" 
                      data-tipe="semangat" 
                      title="Kirim energi semangat">
                <span class="ikon-reaksi" aria-hidden="true">✨</span>
                <span class="label-reaksi">Kamu Kuat</span>
                <span class="hitung-reaksi"><?= (int)$p['reaksi_semangat'] ?></span>
              </button>

              <button type="button" 
                      class="btn-reaksi <?= in_array('relate', $reaksiSaya, true) ? 'aktif' : '' ?>" 
                      data-tipe="relate" 
                      title="Aku juga merasakan hal serupa">
                <span class="ikon-reaksi" aria-hidden="true">💙</span>
                <span class="label-reaksi">Relate</span>
                <span class="hitung-reaksi"><?= (int)$p['reaksi_relate'] ?></span>
              </button>
            </div>

            <button type="button" 
                    class="btn-buka-balasan" 
                    onclick="toggleBalasan(<?= (int)$p['id'] ?>)" 
                    aria-expanded="false" 
                    aria-controls="balasan-box-<?= (int)$p['id'] ?>">
              💬 Balasan (<span id="hitung-balasan-<?= (int)$p['id'] ?>"><?= count($balasanList) ?></span>)
            </button>
          </footer>

          <!-- Wadah Balasan / Diskusi -->
          <div id="balasan-box-<?= (int)$p['id'] ?>" class="wadah-balasan" hidden>
            <div class="daftar-balasan" id="daftar-balasan-<?= (int)$p['id'] ?>">
              <?php if (empty($balasanList)): ?>
                <div class="pesan-belum-ada-balasan">
                  Belum ada tanggapan untuk cerita ini. Tinggalkan sepatah kata yang menguatkan.
                </div>
              <?php else: ?>
                <?php foreach ($balasanList as $b): ?>
                  <div class="item-balasan">
                    <div class="header-balasan">
                      <span class="penulis-balasan">
                        <strong><?= e($b['nama_pengirim']) ?></strong>
                        <?php if ($b['is_anonim']): ?>
                          <span class="mini-lencana anon">Anonim</span>
                        <?php else: ?>
                          <span class="mini-lencana anggota">Anggota</span>
                        <?php endif; ?>
                      </span>
                      <time datetime="<?= e($b['created_at']) ?>" class="waktu-balasan">
                        <?= e(waktu_lalu($b['created_at'])) ?>
                      </time>
                    </div>
                    <div class="isi-balasan">
                      <?= nl2br(e($b['isi'])) ?>
                    </div>
                  </div>
                <?php endforeach; ?>
              <?php endif; ?>
            </div>

            <!-- Form Kirim Balasan -->
            <form action="komunitas_aksi.php" method="post" class="form-kirim-balasan" onsubmit="kirimBalasanAjax(event, <?= (int)$p['id'] ?>)">
              <?= csrf_field() ?>
              <input type="hidden" name="aksi" value="balas_post">
              <input type="hidden" name="id_post" value="<?= (int)$p['id'] ?>">
              
              <div class="baris-penulis-balasan">
                <?php if ($userAktif): ?>
                  <span class="label-membalas">Membalas sebagai: <strong><?= e($userAktif['nama']) ?></strong></span>
                  <label class="opsi-centang-anon">
                    <input type="checkbox" name="is_anonim" value="1">
                    <span>Balas secara anonim</span>
                  </label>
                <?php else: ?>
                  <input type="hidden" name="is_anonim" value="1">
                  <span class="label-membalas">Membalas sebagai: <strong>Anonim</strong></span>
                <?php endif; ?>
              </div>

              <div class="baris-input-balasan">
                <textarea name="isi" rows="2" maxlength="1000" placeholder="Tuliskan kata-kata penyemangat yang tulus..." required aria-label="Tulis balasan dukungan"></textarea>
                <button type="submit" class="tombol tombol-kecil">Kirim Dukungan</button>
              </div>
            </form>
          </div>
        </article>
      <?php endforeach; ?>
    </div>
  <?php endif; ?>
</section>

<!-- MODAL LOGIN & DAFTAR AKUN PENGGUNA -->
<div id="modalAuth" class="modal-latar" hidden role="dialog" aria-modal="true" aria-labelledby="judulModalAuth">
  <div class="modal-kotak">
    <div class="modal-header">
      <h2 id="judulModalAuth">Akun Komunitas Release.It</h2>
      <button type="button" class="tombol-tutup-modal" id="btnTutupModalAuth" aria-label="Tutup jendela login">✕</button>
    </div>

    <!-- Tab Navigasi Modal -->
    <div class="modal-tab-nav" role="tablist">
      <button type="button" class="tab-btn aktif" id="tabMasukBtn" role="tab" aria-selected="true" onclick="pindahTabAuth('masuk')">
        Masuk Akun
      </button>
      <button type="button" class="tab-btn" id="tabDaftarBtn" role="tab" aria-selected="false" onclick="pindahTabAuth('daftar')">
        Daftar Akun Baru
      </button>
    </div>

    <div class="modal-body">
      <!-- FORM LOGIN -->
      <form id="formMasuk" action="komunitas_aksi.php" method="post" class="form form-auth">
        <?= csrf_field() ?>
        <input type="hidden" name="aksi" value="login">
        
        <div class="grup-input">
          <label for="login_username">Username</label>
          <input type="text" id="login_username" name="username" autocomplete="username" placeholder="Masukkan username kamu" required>
        </div>

        <div class="grup-input">
          <label for="login_password">Kata Sandi</label>
          <input type="password" id="login_password" name="password" autocomplete="current-password" placeholder="Masukkan kata sandi" required>
        </div>

        <div class="baris-aksi-modal">
          <button type="submit" class="tombol">Masuk Sekarang</button>
        </div>
      </form>

      <!-- FORM DAFTAR -->
      <form id="formDaftar" action="komunitas_aksi.php" method="post" class="form form-auth" hidden>
        <?= csrf_field() ?>
        <input type="hidden" name="aksi" value="daftar">

        <div class="grup-input">
          <label for="daftar_nama">Nama Panggilan / Display Name</label>
          <input type="text" id="daftar_nama" name="nama" maxlength="50" placeholder="Contoh: Rian, Maya, Senja" required>
          <span class="petunjuk-input">Nama ini yang akan terlihat oleh anggota komunitas.</span>
        </div>

        <div class="grup-input">
          <label for="daftar_username">Username Akun</label>
          <input type="text" id="daftar_username" name="username" maxlength="30" placeholder="huruf kecil, angka, atau underscore" required autocomplete="username">
        </div>

        <div class="grup-input">
          <label for="daftar_password">Kata Sandi</label>
          <input type="password" id="daftar_password" name="password" minlength="6" placeholder="Minimal 6 karakter" required autocomplete="new-password">
        </div>

        <div class="baris-aksi-modal">
          <button type="submit" class="tombol">Daftar Akun Baru</button>
        </div>
      </form>

      <div class="catatan-privasi-modal">
        <p>🔒 <strong>Privasi Terjaga</strong>: Kami tidak meminta email, nomor telepon, atau data pribadi lainnya. Kamu juga bisa tetap menggunakan Release.It tanpa membuat akun sama sekali.</p>
      </div>
    </div>
  </div>
</div>

<script>
// Toggle panel form tulis cerita
function bukaFormTulis() {
  const box = document.getElementById('kotakTulisPost');
  const btn = document.getElementById('btnBukaTulis');
  if (box) {
    box.hidden = false;
    if (btn) btn.setAttribute('aria-expanded', 'true');
    box.scrollIntoView({ behavior: 'smooth', block: 'start' });
    const judul = document.getElementById('post_judul');
    if (judul) judul.focus();
  }
}
function tutupFormTulis() {
  const box = document.getElementById('kotakTulisPost');
  const btn = document.getElementById('btnBukaTulis');
  if (box) {
    box.hidden = true;
    if (btn) btn.setAttribute('aria-expanded', 'false');
  }
}

// Buka dan tutup modal otentikasi
function bukaModalAuth(tab = 'masuk') {
  const modal = document.getElementById('modalAuth');
  if (modal) {
    modal.hidden = false;
    pindahTabAuth(tab);
    const firstInput = modal.querySelector('input:not([type=hidden])');
    if (firstInput) firstInput.focus();
  }
}
function tutupModalAuth() {
  const modal = document.getElementById('modalAuth');
  if (modal) modal.hidden = true;
}

function pindahTabAuth(tab) {
  const fMasuk = document.getElementById('formMasuk');
  const fDaftar = document.getElementById('formDaftar');
  const bMasuk = document.getElementById('tabMasukBtn');
  const bDaftar = document.getElementById('tabDaftarBtn');
  if (tab === 'daftar') {
    fMasuk.hidden = true;
    fDaftar.hidden = false;
    bMasuk.classList.remove('aktif');
    bMasuk.setAttribute('aria-selected', 'false');
    bDaftar.classList.add('aktif');
    bDaftar.setAttribute('aria-selected', 'true');
  } else {
    fMasuk.hidden = false;
    fDaftar.hidden = true;
    bMasuk.classList.add('aktif');
    bMasuk.setAttribute('aria-selected', 'true');
    bDaftar.classList.remove('aktif');
    bDaftar.setAttribute('aria-selected', 'false');
  }
}

function toggleAliasBox(show) {
  const box = document.getElementById('kotakAliasAnonim');
  if (box) {
    if (show) box.classList.remove('alias-tersembunyi');
    else box.classList.add('alias-tersembunyi');
  }
}

function toggleBalasan(id) {
  const box = document.getElementById('balasan-box-' + id);
  if (box) {
    const isHidden = box.hidden;
    box.hidden = !isHidden;
    const btn = event.currentTarget;
    if (btn) btn.setAttribute('aria-expanded', isHidden ? 'true' : 'false');
  }
}

// Handler tombol acak nama samaran
const daftarKataDepan = ['Sobat', 'Teman', 'Jiwa', 'Pengelana', 'Bintang', 'Pohon', 'Awan', 'Ranting', 'Kembara', 'Hening'];
const daftarKataSifat = ['Teduh', 'Tenang', 'Hangat', 'Sabar', 'Bening', 'Lembut', 'Tegar', 'Damai', 'Malam', 'Hujan'];

document.addEventListener('DOMContentLoaded', () => {
  const btnBuka = document.getElementById('btnBukaTulis');
  if (btnBuka) btnBuka.addEventListener('click', bukaFormTulis);

  const btnTutup = document.getElementById('btnTutupTulis');
  if (btnTutup) btnTutup.addEventListener('click', tutupFormTulis);

  const btnBatal = document.getElementById('btnBatalTulis');
  if (btnBatal) btnBatal.addEventListener('click', tutupFormTulis);

  const btnBukaAuth = document.getElementById('btnBukaModalAuth');
  if (btnBukaAuth) btnBukaAuth.addEventListener('click', () => bukaModalAuth('masuk'));

  const btnTutupAuth = document.getElementById('btnTutupModalAuth');
  if (btnTutupAuth) btnTutupAuth.addEventListener('click', tutupModalAuth);

  const modalAuth = document.getElementById('modalAuth');
  if (modalAuth) {
    modalAuth.addEventListener('click', (e) => {
      if (e.target === modalAuth) tutupModalAuth();
    });
  }

  // Tutup modal dengan tombol Escape (Aksesibilitas R-32)
  document.addEventListener('keydown', (e) => {
    if (e.key === 'Escape') {
      tutupModalAuth();
      tutupFormTulis();
    }
  });

  const btnAcak = document.getElementById('btnAcakAlias');
  if (btnAcak) {
    btnAcak.addEventListener('click', () => {
      const d = daftarKataDepan[Math.floor(Math.random() * daftarKataDepan.length)];
      const s = daftarKataSifat[Math.floor(Math.random() * daftarKataSifat.length)];
      const k = Math.floor(Math.random() * 899 + 100);
      const aliasInput = document.getElementById('alias_anonim');
      if (aliasInput) aliasInput.value = d + ' ' + s + ' #' + k;
    });
  }

  // Interaktivitas Tombol Reaksi Komunitas via AJAX
  const csrfMeta = document.querySelector('meta[name="csrf"]');
  const csrfToken = csrfMeta ? csrfMeta.content : '';

  document.querySelectorAll('.btn-reaksi').forEach(btn => {
    btn.addEventListener('click', async () => {
      const parentGrup = btn.closest('.grup-reaksi');
      if (!parentGrup) return;
      const postId = parentGrup.dataset.postId;
      const tipe = btn.dataset.tipe;

      const formData = new FormData();
      formData.append('aksi', 'reaksi');
      formData.append('id_post', postId);
      formData.append('tipe', tipe);
      formData.append('csrf', csrfToken);

      try {
        const resp = await fetch('komunitas_aksi.php', {
          method: 'POST',
          headers: { 'X-Requested-With': 'XMLHttpRequest' },
          body: formData
        });
        const res = await resp.json();
        if (res.sukses) {
          if (res.status === 'ditambah') {
            btn.classList.add('aktif');
          } else {
            btn.classList.remove('aktif');
          }
          const pBtn = parentGrup.querySelector('[data-tipe="peluk"] .hitung-reaksi');
          const sBtn = parentGrup.querySelector('[data-tipe="semangat"] .hitung-reaksi');
          const rBtn = parentGrup.querySelector('[data-tipe="relate"] .hitung-reaksi');
          if (pBtn) pBtn.textContent = res.peluk;
          if (sBtn) sBtn.textContent = res.semangat;
          if (rBtn) rBtn.textContent = res.relate;
        }
      } catch (err) {
        console.error('Gagal mengirim reaksi:', err);
      }
    });
  });
});

// Kirim balasan lewat AJAX tanpa me-refresh halaman
async function kirimBalasanAjax(e, postId) {
  e.preventDefault();
  const form = e.target;
  const textarea = form.querySelector('textarea[name="isi"]');
  const tombolKirim = form.querySelector('button[type="submit"]');
  const isiTeks = textarea ? textarea.value.trim() : '';

  if (!isiTeks) return;

  tombolKirim.disabled = true;
  tombolKirim.textContent = 'Mengirim...';

  const formData = new FormData(form);

  try {
    const resp = await fetch('komunitas_aksi.php', {
      method: 'POST',
      headers: { 'X-Requested-With': 'XMLHttpRequest' },
      body: formData
    });
    const res = await resp.json();
    if (res.sukses && res.balasan) {
      const wadahDaftar = document.getElementById('daftar-balasan-' + postId);
      const pesanKosong = wadahDaftar.querySelector('.pesan-belum-ada-balasan');
      if (pesanKosong) pesanKosong.remove();

      const itemB = document.createElement('div');
      itemB.className = 'item-balasan animasi-muncul';
      const lencanaClass = res.balasan.is_anonim ? 'mini-lencana anon' : 'mini-lencana anggota';
      const lencanaTeks = res.balasan.is_anonim ? 'Anonim' : 'Anggota';

      itemB.innerHTML = `
        <div class="header-balasan">
          <span class="penulis-balasan">
            <strong>${escapeHtml(res.balasan.nama)}</strong>
            <span class="${lencanaClass}">${lencanaTeks}</span>
          </span>
          <time class="waktu-balasan">${escapeHtml(res.balasan.waktu)}</time>
        </div>
        <div class="isi-balasan">${escapeHtml(res.balasan.isi).replace(/\\n/g, '<br>')}</div>
      `;
      wadahDaftar.appendChild(itemB);

      textarea.value = '';
      const hitungBalasan = document.getElementById('hitung-balasan-' + postId);
      if (hitungBalasan) {
        hitungBalasan.textContent = parseInt(hitungBalasan.textContent || 0) + 1;
      }
    } else {
      alert(res.pesan || 'Gagal mengirim balasan.');
    }
  } catch (err) {
    alert('Terjadi kendala koneksi.');
  } finally {
    tombolKirim.disabled = false;
    tombolKirim.textContent = 'Kirim Dukungan';
  }
}

function escapeHtml(str) {
  return str.replace(/[&<>"']/g, function(m) {
    return { '&': '&amp;', '<': '&lt;', '>': '&gt;', '"': '&quot;', "'": '&#039;' }[m];
  });
}
</script>

<?php require 'includes/footer.php'; ?>
