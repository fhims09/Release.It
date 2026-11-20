<?php
require 'config/koneksi.php';

$isAjax = (!empty($_SERVER['HTTP_X_REQUESTED_WITH']) && strtolower($_SERVER['HTTP_X_REQUESTED_WITH']) === 'xmlhttprequest')
    || (isset($_SERVER['CONTENT_TYPE']) && strpos($_SERVER['CONTENT_TYPE'], 'application/json') !== false);

function respon_json($data, $kode = 200) {
    http_response_code($kode);
    header('Content-Type: application/json; charset=utf-8');
    echo json_encode($data);
    exit;
}

$aksi = $_POST['aksi'] ?? $_GET['aksi'] ?? '';

// 1. LOGOUT
if ($aksi === 'logout') {
    unset($_SESSION['pengguna']);
    if ($isAjax) {
        respon_json(['sukses' => true, 'pesan' => 'Berhasil keluar dari akun.']);
    }
    header('Location: komunitas.php?pesan=logout_sukses');
    exit;
}

// 2. LOGIN PENGGUNA
if ($aksi === 'login') {
    cek_csrf();
    $username = strtolower(trim($_POST['username'] ?? ''));
    $password = $_POST['password'] ?? '';

    if (empty($username) || empty($password)) {
        if ($isAjax) respon_json(['sukses' => false, 'pesan' => 'Username dan password wajib diisi.'], 400);
        header('Location: komunitas.php?error=login_kosong');
        exit;
    }

    $st = $pdo->prepare("SELECT * FROM pengguna WHERE username = ?");
    $st->execute([$username]);
    $user = $st->fetch();

    if ($user && password_verify($password, $user['password'])) {
        $_SESSION['pengguna'] = [
            'id' => (int)$user['id'],
            'username' => $user['username'],
            'nama' => $user['nama']
        ];
        if ($isAjax) respon_json(['sukses' => true, 'pesan' => 'Selamat datang kembali, ' . $user['nama'] . '!', 'user' => $_SESSION['pengguna']]);
        header('Location: komunitas.php?pesan=login_sukses');
        exit;
    } else {
        if ($isAjax) respon_json(['sukses' => false, 'pesan' => 'Username atau password tidak sesuai.'], 401);
        header('Location: komunitas.php?error=login_gagal');
        exit;
    }
}

// 3. DAFTAR AKUN BARU
if ($aksi === 'daftar') {
    cek_csrf();
    $username = strtolower(trim($_POST['username'] ?? ''));
    $nama = trim($_POST['nama'] ?? '');
    $password = $_POST['password'] ?? '';

    if (mb_strlen($username) < 3 || mb_strlen($username) > 30 || !preg_match('/^[a-z0-9_]+$/', $username)) {
        $msg = 'Username minimal 3 karakter (hanya huruf kecil, angka, dan underscore).';
        if ($isAjax) respon_json(['sukses' => false, 'pesan' => $msg], 400);
        header('Location: komunitas.php?error=' . urlencode($msg));
        exit;
    }

    if (mb_strlen($nama) < 2 || mb_strlen($nama) > 50) {
        $msg = 'Nama panggilan 2 sampai 50 karakter.';
        if ($isAjax) respon_json(['sukses' => false, 'pesan' => $msg], 400);
        header('Location: komunitas.php?error=' . urlencode($msg));
        exit;
    }

    if (strlen($password) < 6) {
        $msg = 'Kata sandi minimal 6 karakter demi keamanan akunmu.';
        if ($isAjax) respon_json(['sukses' => false, 'pesan' => $msg], 400);
        header('Location: komunitas.php?error=' . urlencode($msg));
        exit;
    }

    // Cek duplikasi username
    $stCek = $pdo->prepare("SELECT id FROM pengguna WHERE username = ?");
    $stCek->execute([$username]);
    if ($stCek->fetch()) {
        $msg = 'Username ini sudah digunakan, silakan pilih username lain.';
        if ($isAjax) respon_json(['sukses' => false, 'pesan' => $msg], 409);
        header('Location: komunitas.php?error=' . urlencode($msg));
        exit;
    }

    $hash = password_hash($password, PASSWORD_DEFAULT);
    $stInsert = $pdo->prepare("INSERT INTO pengguna (username, nama, password) VALUES (?, ?, ?)");
    $stInsert->execute([$username, $nama, $hash]);
    $newId = (int)$pdo->lastInsertId();

    $_SESSION['pengguna'] = [
        'id' => $newId,
        'username' => $username,
        'nama' => $nama
    ];

    if ($isAjax) respon_json(['sukses' => true, 'pesan' => 'Pendaftaran berhasil. Selamat bergabung, ' . $nama . '!', 'user' => $_SESSION['pengguna']]);
    header('Location: komunitas.php?pesan=daftar_sukses');
    exit;
}

// 4. BUAT POSTINGAN BARU
if ($aksi === 'post_baru') {
    cek_csrf();

    // Anti-bot jebakan
    if (!empty($_POST['website'])) {
        header('Location: komunitas.php');
        exit;
    }

    // Rate limiting
    $sekarang = time();
    $waktuKirim = array_values(array_filter($_SESSION['komunitas_kirim'] ?? [], function ($w) use ($sekarang) {
        return ($sekarang - $w) < 3600;
    }));
    if (count($waktuKirim) >= 10) {
        $msg = 'Kamu sudah membagikan banyak pesan dalam 1 jam terakhir. Istirahat sejenak ya.';
        if ($isAjax) respon_json(['sukses' => false, 'pesan' => $msg], 429);
        header('Location: komunitas.php?error=' . urlencode($msg));
        exit;
    }

    $id_topik = (int)($_POST['id_topik'] ?? 0);
    $judul = trim($_POST['judul'] ?? '');
    $isi = trim($_POST['isi'] ?? '');
    $mau_anonim = !empty($_POST['is_anonim']);
    $alias_input = trim($_POST['alias_anonim'] ?? '');

    if (!$id_topik) {
        $msg = 'Silakan pilih ruang topik cerita terlebih dahulu.';
        if ($isAjax) respon_json(['sukses' => false, 'pesan' => $msg], 400);
        header('Location: komunitas.php?error=' . urlencode($msg));
        exit;
    }

    if (mb_strlen($judul) < 3 || mb_strlen($judul) > 150) {
        $msg = 'Judul cerita 3 sampai 150 karakter.';
        if ($isAjax) respon_json(['sukses' => false, 'pesan' => $msg], 400);
        header('Location: komunitas.php?error=' . urlencode($msg));
        exit;
    }

    if (mb_strlen($isi) < 5 || mb_strlen($isi) > 2500) {
        $msg = 'Isi cerita minimal 5 karakter dan maksimal 2500 karakter.';
        if ($isAjax) respon_json(['sukses' => false, 'pesan' => $msg], 400);
        header('Location: komunitas.php?error=' . urlencode($msg));
        exit;
    }

    $userAktif = pengguna_aktif();
    $id_pengguna = null;
    $is_anonim = 1;
    $nama_pengirim = '';

    if ($userAktif && !$mau_anonim) {
        // Kirim dengan nama akun
        $id_pengguna = $userAktif['id'];
        $is_anonim = 0;
        $nama_pengirim = $userAktif['nama'];
    } else {
        // Kirim sebagai anonim
        $is_anonim = 1;
        $id_pengguna = null;
        if (!empty($alias_input) && mb_strlen($alias_input) <= 40) {
            $nama_pengirim = $alias_input;
        } else {
            $nama_pengirim = buat_alias_anonim();
        }
    }

    $st = $pdo->prepare("INSERT INTO komunitas_post (id_topik, id_pengguna, nama_pengirim, is_anonim, judul, isi) VALUES (?, ?, ?, ?, ?, ?)");
    $st->execute([$id_topik, $id_pengguna, $nama_pengirim, $is_anonim, $judul, $isi]);
    $newPostId = (int)$pdo->lastInsertId();

    $waktuKirim[] = $sekarang;
    $_SESSION['komunitas_kirim'] = $waktuKirim;

    if ($isAjax) {
        respon_json([
            'sukses' => true,
            'pesan' => 'Ceritamu berhasil dibagikan ke ruang komunitas.',
            'post_id' => $newPostId
        ]);
    }
    header('Location: komunitas.php?pesan=post_sukses#post-' . $newPostId);
    exit;
}

// 5. BUAT BALASAN / DUKUNGAN PADA POSTINGAN
if ($aksi === 'balas_post') {
    cek_csrf();

    $id_post = (int)($_POST['id_post'] ?? 0);
    $isi = trim($_POST['isi'] ?? '');
    $mau_anonim = !empty($_POST['is_anonim']);
    $alias_input = trim($_POST['alias_anonim'] ?? '');

    if (!$id_post || mb_strlen($isi) < 2 || mb_strlen($isi) > 1000) {
        $msg = 'Balasan harus berisi 2 sampai 1000 karakter.';
        if ($isAjax) respon_json(['sukses' => false, 'pesan' => $msg], 400);
        header('Location: komunitas.php?error=' . urlencode($msg));
        exit;
    }

    // Pastikan post tujuan ada
    $cekPost = $pdo->prepare("SELECT id FROM komunitas_post WHERE id = ?");
    $cekPost->execute([$id_post]);
    if (!$cekPost->fetch()) {
        $msg = 'Postingan tidak ditemukan.';
        if ($isAjax) respon_json(['sukses' => false, 'pesan' => $msg], 404);
        header('Location: komunitas.php?error=' . urlencode($msg));
        exit;
    }

    $userAktif = pengguna_aktif();
    $id_pengguna = null;
    $is_anonim = 1;
    $nama_pengirim = '';

    if ($userAktif && !$mau_anonim) {
        $id_pengguna = $userAktif['id'];
        $is_anonim = 0;
        $nama_pengirim = $userAktif['nama'];
    } else {
        $is_anonim = 1;
        $id_pengguna = null;
        if (!empty($alias_input) && mb_strlen($alias_input) <= 40) {
            $nama_pengirim = $alias_input;
        } else {
            $nama_pengirim = buat_alias_anonim();
        }
    }

    $st = $pdo->prepare("INSERT INTO komunitas_balasan (id_post, id_pengguna, nama_pengirim, is_anonim, isi) VALUES (?, ?, ?, ?, ?)");
    $st->execute([$id_post, $id_pengguna, $nama_pengirim, $is_anonim, $isi]);
    $newReplyId = (int)$pdo->lastInsertId();

    if ($isAjax) {
        respon_json([
            'sukses' => true,
            'pesan' => 'Pesan dukungan berhasil dikirimkan.',
            'balasan' => [
                'id' => $newReplyId,
                'nama' => $nama_pengirim,
                'is_anonim' => $is_anonim,
                'isi' => $isi,
                'waktu' => 'baru saja'
            ]
        ]);
    }
    header('Location: komunitas.php?pesan=balasan_sukses#post-' . $id_post);
    exit;
}

// 6. KIRIM REAKSI (PELUK, SEMANGAT, RELATE)
if ($aksi === 'reaksi') {
    cek_csrf();

    $id_post = (int)($_POST['id_post'] ?? 0);
    $tipe = $_POST['tipe'] ?? '';
    $daftarTipe = ['peluk', 'semangat', 'relate'];

    if (!$id_post || !in_array($tipe, $daftarTipe, true)) {
        respon_json(['sukses' => false, 'pesan' => 'Data reaksi tidak valid.'], 400);
    }

    // Gunakan hash sesi untuk mencegah spamming reaksi berulang kali
    $kunciSesi = hash('sha256', session_id() . ($_SERVER['REMOTE_ADDR'] ?? 'local'));

    // Cek apakah sudah pernah bereaksi dengan tipe yang sama
    $cek = $pdo->prepare("SELECT id FROM komunitas_reaksi WHERE id_post = ? AND tipe = ? AND kunci_sesi = ?");
    $cek->execute([$id_post, $tipe, $kunciSesi]);

    $kolom = 'reaksi_' . $tipe;
    $sudahPernah = $cek->fetch();

    if ($sudahPernah) {
        // Batalkan reaksi (toggle off)
        $pdo->prepare("DELETE FROM komunitas_reaksi WHERE id = ?")->execute([$sudahPernah['id']]);
        $pdo->prepare("UPDATE komunitas_post SET $kolom = GREATEST(0, $kolom - 1) WHERE id = ?")->execute([$id_post]);
        $status = 'dihapus';
    } else {
        // Tambahkan reaksi
        $pdo->prepare("INSERT INTO komunitas_reaksi (id_post, tipe, kunci_sesi) VALUES (?, ?, ?)")->execute([$id_post, $tipe, $kunciSesi]);
        $pdo->prepare("UPDATE komunitas_post SET $kolom = $kolom + 1 WHERE id = ?")->execute([$id_post]);
        $status = 'ditambah';
    }

    // Ambil angka reaksi terbaru
    $stHitung = $pdo->prepare("SELECT reaksi_peluk, reaksi_semangat, reaksi_relate FROM komunitas_post WHERE id = ?");
    $stHitung->execute([$id_post]);
    $hasil = $stHitung->fetch();

    respon_json([
        'sukses' => true,
        'status' => $status,
        'peluk' => (int)($hasil['reaksi_peluk'] ?? 0),
        'semangat' => (int)($hasil['reaksi_semangat'] ?? 0),
        'relate' => (int)($hasil['reaksi_relate'] ?? 0)
    ]);
}

// Aksi tidak dikenal
respon_json(['sukses' => false, 'pesan' => 'Aksi tidak ditemukan.'], 404);
