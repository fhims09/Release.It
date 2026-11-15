<?php
// Sesuaikan dengan XAMPP/Laragon kalian
$host = 'localhost'; $db = 'releaseit'; $user = 'root'; $pass = '';

session_start();
try {
    $pdo = new PDO("mysql:host=$host;dbname=$db;charset=utf8mb4", $user, $pass, [
        PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION,
        PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC,
    ]);
} catch (PDOException $e) {
    die('Koneksi database gagal. Cek config/koneksi.php dan pastikan database.sql sudah diimpor.');
}

function e($s) { return htmlspecialchars($s, ENT_QUOTES, 'UTF-8'); }

function csrf_token() {
    if (empty($_SESSION['csrf'])) $_SESSION['csrf'] = bin2hex(random_bytes(16));
    return $_SESSION['csrf'];
}
function csrf_field() {
    return '<input type="hidden" name="csrf" value="' . csrf_token() . '">';
}
function cek_csrf() {
    if (empty($_POST['csrf']) || !hash_equals($_SESSION['csrf'] ?? '', $_POST['csrf'])) {
        http_response_code(400); die('Permintaan tidak valid. Muat ulang halaman.');
    }
}
function youtube_id($url) {
    return preg_match('~(?:v=|youtu\.be/|embed/)([\w-]{11})~', $url, $m) ? $m[1] : null;
}
function tampil_konten($k) {
    if ($k['tipe'] === 'kutipan') return '<blockquote class="kartu">' . e($k['isi']) . '</blockquote>';
    if ($k['tipe'] === 'gambar')  return '<figure class="kartu"><img src="' . e($k['isi']) . '" alt="Gambar penenang" loading="lazy"></figure>';
    $id = youtube_id($k['isi']);
    return $id ? '<div class="kartu video"><iframe src="https://www.youtube-nocookie.com/embed/' . e($id) . '" title="Video penenang" allowfullscreen loading="lazy"></iframe></div>' : '';
}
