CREATE DATABASE IF NOT EXISTS releaseit CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci;
USE releaseit;

CREATE TABLE mood (
  id INT AUTO_INCREMENT PRIMARY KEY,
  nama VARCHAR(30) NOT NULL
);

CREATE TABLE konten (
  id INT AUTO_INCREMENT PRIMARY KEY,
  tipe ENUM('kutipan','video','gambar') NOT NULL,
  isi TEXT NOT NULL,              -- teks kutipan / link YouTube / link gambar
  id_mood INT NOT NULL,
  created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
  FOREIGN KEY (id_mood) REFERENCES mood(id)
);

CREATE TABLE pesan_harapan (
  id INT AUTO_INCREMENT PRIMARY KEY,
  isi VARCHAR(300) NOT NULL,
  id_mood INT NOT NULL,
  kode_edit VARCHAR(10) NOT NULL UNIQUE,
  status ENUM('pending','disetujui') DEFAULT 'pending',
  created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
  FOREIGN KEY (id_mood) REFERENCES mood(id)
);

CREATE TABLE admin (
  id INT AUTO_INCREMENT PRIMARY KEY,
  username VARCHAR(30) NOT NULL UNIQUE,
  password VARCHAR(255) NOT NULL
);

INSERT INTO mood (nama) VALUES ('Capek'),('Cemas'),('Sedih'),('Marah'),('Kosong');

-- Login awal: username admin, password "password" (SEGERA GANTI!)
INSERT INTO admin (username, password) VALUES
('admin', '$2y$10$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llC/.og/at2.uheWG/igi');

INSERT INTO konten (tipe, isi, id_mood) VALUES
('kutipan','Kamu tidak harus kuat setiap hari. Istirahat juga bagian dari berjuang.',1),
('kutipan','Perasaan cemas itu nyata, tapi bukan ramalan. Tarik napas, satu langkah kecil saja dulu.',2),
('kutipan','Boleh menangis. Air mata itu cara tubuh meletakkan beban sebentar.',3),
('kutipan','Marah itu tanda ada batasmu yang terinjak. Beri ia ruang, lalu lepaskan pelan-pelan.',4),
('kutipan','Kosong bukan berarti tidak berharga. Kadang hati hanya butuh waktu untuk terisi lagi.',5);

-- Respons chatbot (dikelola admin lewat CRUD). kata_kunci dipisah koma; '*' = balasan cadangan
CREATE TABLE respons_bot (
  id INT AUTO_INCREMENT PRIMARY KEY,
  kata_kunci VARCHAR(255) NOT NULL,
  balasan TEXT NOT NULL,
  id_mood INT NULL,               -- jika diisi, bot melampirkan 1 konten dari mood ini
  FOREIGN KEY (id_mood) REFERENCES mood(id) ON DELETE SET NULL
);

INSERT INTO respons_bot (kata_kunci, balasan, id_mood) VALUES
('halo,hai,hi,selamat pagi,selamat malam,selamat siang','Hai, aku Rilis. Di sini kamu boleh cerita apa saja, tanpa dinilai. Hari ini rasanya bagaimana?',NULL),
('capek,lelah,letih,capai,burnout,kewalahan','Kedengarannya kamu sudah menanggung banyak dan tubuh serta pikiranmu butuh jeda. Capek itu valid. Bagian mana yang paling melelahkan akhir-akhir ini?',1),
('cemas,khawatir,takut,panik,overthinking,gelisah,deg-degan','Cemas memang melelahkan, apalagi saat pikiran terus berputar. Kamu aman untuk menceritakannya di sini. Apa yang paling kamu khawatirkan sekarang?',2),
('sedih,nangis,menangis,kecewa,patah hati,terluka','Terima kasih sudah mau cerita. Rasa sedih itu nyata dan boleh dirasakan. Mau ceritakan apa yang membuatmu merasa begini?',3),
('marah,kesal,emosi,benci,muak,jengkel','Wajar kalau kamu marah. Biasanya ada hal penting yang terasa tidak adil atau tidak dihargai. Apa yang baru saja terjadi?',4),
('kosong,hampa,mati rasa,kesepian,sendirian','Perasaan kosong itu berat, apalagi kalau terasa sendirian. Kamu tidak harus menjelaskannya dengan sempurna. Sudah berapa lama perasaan ini ada?',5),
('stres,stress,tugas,skripsi,kuliah,deadline,dosen,ujian,nilai','Tekanan kuliah bisa menumpuk sampai terasa menyesakkan. Kamu tidak sedang lemah, kamu sedang memikul banyak hal. Mana yang paling mendesak buatmu sekarang?',2),
('keluarga,orang tua,ortu,ayah,ibu,teman,pacar,sahabat','Hubungan dengan orang terdekat memang bisa paling menyentuh perasaan. Aku mendengarkan. Apa yang terjadi dengan mereka?',NULL),
('terima kasih,makasih,thanks','Sama-sama. Kamu sudah berani menuliskan perasaanmu, itu langkah yang tidak kecil. Aku tetap di sini kalau mau lanjut cerita.',NULL),
('*','Aku mendengarkan. Boleh ceritakan sedikit lebih banyak?',NULL),
('*','Terima kasih sudah membagikannya. Pelan-pelan saja, tidak ada yang terburu-buru di sini.',NULL),
('*','Aku mengerti ini tidak mudah. Bagian mana yang paling terasa berat buatmu?',NULL);

-- Pembatasan percobaan login admin (tabel ini juga dibuat otomatis oleh admin/login.php)
CREATE TABLE IF NOT EXISTS login_gagal (
  id INT AUTO_INCREMENT PRIMARY KEY,
  ip VARCHAR(45) NOT NULL,
  waktu DATETIME NOT NULL,
  INDEX (ip, waktu)
);

-- Konten kutipan tambahan
INSERT INTO konten (tipe, isi, id_mood) VALUES
('kutipan','Istirahat bukan hadiah setelah semuanya selesai. Istirahat adalah bagian dari perjalanan itu sendiri.',1),
('kutipan','Kamu boleh berhenti sebentar untuk bernapas. Tugas yang menunggu tidak akan menghilang, tapi tenagamu layak dipulihkan.',1),
('kutipan','Tidak semua hari harus produktif. Hari ini kamu bertahan, dan itu sudah cukup.',1),
('kutipan','Lelah tandanya kamu sudah berusaha. Beri dirimu izin untuk mengisi ulang tenaga.',1),
('kutipan','Pikiran yang berisik bukan berarti ramalan. Coba kembali ke satu hal yang bisa kamu rasakan sekarang: napas, kursi, suara di sekitarmu.',2),
('kutipan','Kamu tidak perlu menyelesaikan seluruh masa depan malam ini. Cukup langkah kecil berikutnya.',2),
('kutipan','Rasa cemas mengajakmu berjaga. Kamu boleh bilang padanya, terima kasih, tapi sekarang kita aman.',2),
('kutipan','Tarik napas pelan, hembuskan lebih pelan. Satu menit saja sudah cukup untuk memulai.',2),
('kutipan','Sedih tidak harus buru-buru pergi. Biarkan ia duduk sebentar, lalu kamu bisa memilih kapan melangkah lagi.',3),
('kutipan','Tidak apa-apa tidak baik-baik saja hari ini. Besok boleh berbeda, dan kamu tidak harus menunggunya sendirian.',3),
('kutipan','Air mata bukan tanda kalah. Itu cara hati merapikan bebannya.',3),
('kutipan','Menceritakan perasaan kepada orang yang kamu percaya bukan merepotkan. Itu cara kita saling menjaga.',3),
('kutipan','Marah menunjukkan ada hal penting bagimu yang terasa tidak adil. Dengarkan pesannya, lalu pilih cara yang tidak melukai dirimu.',4),
('kutipan','Sebelum menjawab, tarik napas tiga kali. Kamu boleh menunda reaksi sampai kepalamu lebih dingin.',4),
('kutipan','Menuliskan semua kekesalan lalu melepaskannya bisa membuat dada terasa lebih longgar.',4),
('kutipan','Kamu boleh marah dan tetap menjadi orang baik. Perasaan dan tindakan adalah dua hal yang berbeda.',4),
('kutipan','Perasaan kosong tidak mengurangi nilaimu. Kadang hati sedang istirahat panjang setelah terlalu lama menahan.',5),
('kutipan','Mulailah dari hal yang sangat kecil hari ini: minum air, buka jendela, atau kirim pesan singkat ke seseorang.',5),
('kutipan','Kamu tidak harus merasakan sesuatu dengan sempurna. Hadir di hari ini saja sudah cukup.',5),
('kutipan','Jika rasa kosong ini terasa lama dan berat, bercerita kepada konselor atau orang tepercaya adalah langkah yang berani.',5);


-- ==========================================================
-- MODUL ROOM KOMUNITAS (LOGIN & ANONIM)
-- ==========================================================

CREATE TABLE IF NOT EXISTS pengguna (
  id INT AUTO_INCREMENT PRIMARY KEY,
  username VARCHAR(50) NOT NULL UNIQUE,
  nama VARCHAR(100) NOT NULL,
  password VARCHAR(255) NOT NULL,
  avatar VARCHAR(50) DEFAULT 'user',
  created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
);

CREATE TABLE IF NOT EXISTS komunitas_topik (
  id INT AUTO_INCREMENT PRIMARY KEY,
  nama VARCHAR(50) NOT NULL,
  slug VARCHAR(50) NOT NULL UNIQUE,
  deskripsi VARCHAR(255) NOT NULL,
  urutan INT DEFAULT 0
);

CREATE TABLE IF NOT EXISTS komunitas_post (
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
  INDEX idx_waktu (created_at),
  FOREIGN KEY (id_topik) REFERENCES komunitas_topik(id) ON DELETE CASCADE,
  FOREIGN KEY (id_pengguna) REFERENCES pengguna(id) ON DELETE SET NULL
);

CREATE TABLE IF NOT EXISTS komunitas_balasan (
  id INT AUTO_INCREMENT PRIMARY KEY,
  id_post INT NOT NULL,
  id_pengguna INT NULL,
  nama_pengirim VARCHAR(100) NOT NULL,
  is_anonim TINYINT(1) DEFAULT 0,
  isi TEXT NOT NULL,
  created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
  INDEX idx_post (id_post),
  INDEX idx_waktu (created_at),
  FOREIGN KEY (id_post) REFERENCES komunitas_post(id) ON DELETE CASCADE,
  FOREIGN KEY (id_pengguna) REFERENCES pengguna(id) ON DELETE SET NULL
);

CREATE TABLE IF NOT EXISTS komunitas_reaksi (
  id INT AUTO_INCREMENT PRIMARY KEY,
  id_post INT NOT NULL,
  tipe VARCHAR(20) NOT NULL,
  kunci_sesi VARCHAR(64) NOT NULL,
  created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
  UNIQUE KEY uq_reaksi (id_post, tipe, kunci_sesi),
  FOREIGN KEY (id_post) REFERENCES komunitas_post(id) ON DELETE CASCADE
);

INSERT INTO komunitas_topik (nama, slug, deskripsi, urutan) VALUES
('Ruang Bebas Curhat', 'bebas', 'Ceritakan apa saja yang mengganjal di hatimu hari ini.', 1),
('Keluh Kesah Kuliah', 'kuliah', 'Tugas menumpuk, skripsi, ujian, atau dinamika dosen.', 2),
('Hati dan Relasi', 'relasi', 'Dinamika pertemanan, keluarga, asmara, dan kesepian.', 3),
('Overthinking dan Cemas', 'cemas', 'Pikiran yang berputar tentang masa depan dan rasa ragu.', 4),
('Kemenangan Kecil', 'syukur', 'Hal-hal kecil yang berhasil kamu lalui hari ini.', 5)
ON DUPLICATE KEY UPDATE nama=VALUES(nama);
