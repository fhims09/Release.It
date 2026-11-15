<?php
if (empty($_SESSION['admin'])) { header('Location: login.php'); exit; }

// Peringatan jika admin masih memakai password bawaan dari database.sql
$s = $pdo->prepare("SELECT password FROM admin WHERE username = ?");
$s->execute([$_SESSION['admin']]);
$h = $s->fetchColumn();
$pwDefault = $h && password_verify('password', $h);
