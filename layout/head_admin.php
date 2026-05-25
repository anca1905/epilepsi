<?php
/**
 * layout/head_admin.php
 * ─────────────────────────────────────────────
 * HTML <head> untuk semua halaman ADMIN.
 *
 * Variabel yang harus di-set sebelum include:
 *   $page_title  (string) — Judul tab browser
 */
$page_title = $page_title ?? 'Panel Admin';
?>
<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?= htmlspecialchars($page_title) ?> — EpilepsiCare Admin</title>
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Plus+Jakarta+Sans:wght@400;500;600;700;800&display=swap" rel="stylesheet">
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.0/font/bootstrap-icons.css">
    <link rel="stylesheet" href="assets/style.css">
</head>
<body class="admin-page">
<div class="admin-wrapper">
