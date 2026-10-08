<?php
require_once __DIR__ . '/../config/helpers.php';
$flash = flash_get();
?>
<!DOCTYPE html>
<html lang="id">
<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width, initial-scale=1">
  <title><?= e($pageTitle ?? 'Inventaris') ?> — Inventaris Barang</title>
  <link rel="stylesheet" href="assets/style.css">
</head>
<body>
<header class="topbar">
  <div class="container">
    <a class="brand" href="index.php">📦 Inventaris Barang</a>
    <span class="sub">Tugas Rutin 8 — Pemrograman Web</span>
  </div>
</header>
<main class="container">
<?php if ($flash): ?>
  <div class="alert alert-<?= e($flash['type']) ?>" role="alert"><?= e($flash['message']) ?></div>
<?php endif; ?>
