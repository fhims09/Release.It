<?php
/**
 * Helper dan Schema untuk Room Komunitas Release.It
 * Menangani inisialisasi tabel, manajemen pengguna, postingan, balasan, dan reaksi.
 */

function init_komunitas_db($pdo) {
    // 1. Tabel Pengguna (untuk anggota yang ingin mendaftar dan login)
    $pdo->exec("CREATE TABLE IF NOT EXISTS pengguna (
        id INT AUTO_INCREMENT PRIMARY KEY,
        username VARCHAR(50) NOT NULL UNIQUE,
        nama VARCHAR(100) NOT NULL,
        password VARCHAR(255) NOT NULL,
        avatar VARCHAR(50) DEFAULT 'user',
        created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
    ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci");

    // 2. Tabel Topik / Kategori Komunitas
    $pdo->exec("CREATE TABLE IF NOT EXISTS komunitas_topik (
        id INT AUTO_INCREMENT PRIMARY KEY,
        nama VARCHAR(50) NOT NULL,
        slug VARCHAR(50) NOT NULL UNIQUE,
        deskripsi VARCHAR(255) NOT NULL,
        urutan INT DEFAULT 0
    ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci");

    // 3. Tabel Postingan Komunitas
    $pdo->exec("CREATE TABLE IF NOT EXISTS komunitas_post (
        id INT AUTO_INCREMENT PRIMARY KEY,
        id_topik INT NOT NULL,
        id_pengguna INT NULL,
        nama_pengirim VARCHAR(100) NOT NULL,
        is_anonim TINYINT(1) DEFAULT 0,
        judul VARCHAR(200) NOT NULL,
        isi TEXT NOT NULL,
        reaksi_peluk INT DEFAULT 0,
        reaksi_semangat INT DEFAULT 0,
        reaksi_relate INT DEFAULT 0,
        created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
        INDEX idx_topik (id_topik),
        INDEX idx_waktu (created_at)
    ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci");

    // 4. Tabel Balasan / Komentar Komunitas
    $pdo->exec("CREATE TABLE IF NOT EXISTS komunitas_balasan (
        id INT AUTO_INCREMENT PRIMARY KEY,
        id_post INT NOT NULL,
        id_pengguna INT NULL,
        nama_pengirim VARCHAR(100) NOT NULL,
        is_anonim TINYINT(1) DEFAULT 0,
        isi TEXT NOT NULL,
        created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
        INDEX idx_post (id_post),
        INDEX idx_waktu (created_at)
    ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci");

    // 5. Tabel Log Reaksi (Anti double-vote berlebihan per sesi / IP)
    $pdo->exec("CREATE TABLE IF NOT EXISTS komunitas_reaksi (
        id INT AUTO_INCREMENT PRIMARY KEY,
        id_post INT NOT NULL,
        tipe VARCHAR(20) NOT NULL,
        kunci_sesi VARCHAR(64) NOT NULL,
        created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
        UNIQUE KEY uq_reaksi (id_post, tipe, kunci_sesi)
    ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci");

    // Isi topik bawaan jika masih kosong
    $cekTopik = $pdo->query("SELECT COUNT(*) FROM komunitas_topik")->fetchColumn();
    if ($cekTopik == 0) {
        $topikAwal = [
            ['Ruang Bebas Curhat', 'bebas', 'Ceritakan apa saja yang mengganjal di hatimu hari ini.', 1],
            ['Keluh Kesah Kuliah', 'kuliah', 'Tugas menumpuk, skripsi, ujian, atau dinamika dosen.', 2],
            ['Hati dan Relasi', 'relasi', 'Dinamika pertemanan, keluarga, asmara, dan kesepian.', 3],
            ['Overthinking dan Cemas', 'cemas', 'Pikiran yang berputar tentang masa depan dan rasa ragu.', 4],
            ['Kemenangan Kecil', 'syukur', 'Hal-hal kecil yang berhasil kamu lalui hari ini.', 5]
        ];
        $st = $pdo->prepare("INSERT INTO komunitas_topik (nama, slug, deskripsi, urutan) VALUES (?, ?, ?, ?)");
        foreach ($topikAwal as $t) {
            $st->execute($t);
        }
    }

    // Isi contoh postingan pembuka yang hangat jika masih kosong
    $cekPost = $pdo->query("SELECT COUNT(*) FROM komunitas_post")->fetchColumn();
    if ($cekPost == 0) {
        $stPost = $pdo->prepare("INSERT INTO komunitas_post (id_topik, id_pengguna, nama_pengirim, is_anonim, judul, isi, reaksi_peluk, reaksi_semangat, reaksi_relate, created_at) VALUES (?, NULL, ?, ?, ?, ?, ?, ?, ?, ?)");
        
        $stPost->execute([
            1,
            'Sobat Teduh',
            1,
            'Selamat datang di Ruang Komunitas Release.It',
            'Ruang ini hadir untuk kita saling mendengarkan tanpa prasangka. Kamu boleh bercerita memakai akunmu atau sepenuhnya anonim. Tidak ada cerita yang terlalu sepele atau tidak berharga. Bila hari ini terasa berat, tarik napas sejenak, kamu aman di sini.',
            12,
            18,
            9,
            date('Y-m-d H:i:s', strtotime('-2 hours'))
        ]);

        $stPost->execute([
            2,
            'Pejuang Skripsi',
            1,
            'Hari ini rasanya ingin menyerah dengan bab 4',
            'Sudah revisi ketiga kalinya dan dosen pembimbing belum memberi kepastian. Rasanya lelah sekali melihat teman-teman lain sudah seminar hasil. Tapi tadi sore aku paksakan jalan kaki sebentar dan minum teh hangat. Satu hari demi satu hari dulu.',
            15,
            14,
            21,
            date('Y-m-d H:i:s', strtotime('-45 minutes'))
        ]);
    }
}

// Cek apakah ada pengguna yang sedang login
function pengguna_aktif() {
    if (!empty($_SESSION['pengguna'])) {
        return $_SESSION['pengguna'];
    }
    return null;
}

// Generate alias acak yang hangat untuk pengunjung anonim
function buat_alias_anonim() {
    $kataDepan = ['Sobat', 'Teman', 'Jiwa', 'Pengelana', 'Bintang', 'Pohon', 'Awan', 'Ranting', 'Kembara', 'Hening'];
    $kataSifat = ['Teduh', 'Tenang', 'Hangat', 'Sabar', 'Bening', 'Lembut', 'Tegar', 'Damai', 'Malam', 'Hujan'];
    $depan = $kataDepan[array_rand($kataDepan)];
    $sifat = $kataSifat[array_rand($kataSifat)];
    $kode = substr(bin2hex(random_bytes(2)), 0, 3);
    return $depan . ' ' . $sifat . ' #' . strtoupper($kode);
}

// Format waktu relatif yang ramah bahasa Indonesia
function waktu_lalu($datetime) {
    $waktu = strtotime($datetime);
    $selisih = time() - $waktu;
    if ($selisih < 60) {
        return 'baru saja';
    } elseif ($selisih < 3600) {
        $m = floor($selisih / 60);
        return $m . ' menit yang lalu';
    } elseif ($selisih < 86400) {
        $j = floor($selisih / 3600);
        return $j . ' jam yang lalu';
    } elseif ($selisih < 604800) {
        $h = floor($selisih / 86400);
        return $h . ' hari yang lalu';
    } else {
        return date('d M Y', $waktu);
    }
}
