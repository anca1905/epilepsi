<?php
/**
 * layout/header_public.php
 * ─────────────────────────────────────────────
 * Komponen Header untuk halaman PUBLIK.
 * 
 * Variabel yang harus di-set sebelum include:
 *   $page_title  (string) — Judul tab browser
 *   $active_nav  (string) — 'home' | 'diagnosa' | ''
 */
$page_title = $page_title ?? 'EpilepsiCare';
$active_nav = $active_nav ?? '';
?>
<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?= htmlspecialchars($page_title) ?> — EpilepsiCare</title>
    <meta name="description" content="Sistem Pakar Diagnosa Dini Epilepsi pada Anak menggunakan Metode Probabilitas Bayes">
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Plus+Jakarta+Sans:wght@400;500;600;700;800&display=swap" rel="stylesheet">
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.0/font/bootstrap-icons.css">
    <link rel="stylesheet" href="assets/style.css">
</head>
<body class="public-page">

<nav class="navbar navbar-expand-lg navbar-public">
    <div class="container">
        <a class="navbar-brand-logo" href="index.php">
            <i class="bi bi-heart-pulse-fill"></i> EpilepsiCare
        </a>
        <button class="navbar-toggler" type="button" 
                data-bs-toggle="collapse" data-bs-target="#navbarPublic"
                aria-controls="navbarPublic" aria-expanded="false" aria-label="Toggle navigation">
            <span class="navbar-toggler-icon"></span>
        </button>
        <div class="collapse navbar-collapse justify-content-end" id="navbarPublic">
            <ul class="navbar-nav align-items-center gap-1">
                <li class="nav-item">
                    <a class="nav-link <?= $active_nav === 'home' ? 'active fw-semibold' : '' ?>" href="index.php">
                        <i class="bi bi-house me-1"></i>Beranda
                    </a>
                </li>
                <li class="nav-item">
                    <a class="nav-link <?= $active_nav === 'diagnosa' ? 'active fw-semibold' : '' ?>" href="form_pasien.php">
                        <i class="bi bi-clipboard2-pulse me-1"></i>Mulai Diagnosa
                    </a>
                </li>
                <li class="nav-item ms-2">
                    <a class="btn-nav-login nav-link" href="login_admin.php">
                        <i class="bi bi-shield-lock me-1"></i>Login Admin
                    </a>
                </li>
            </ul>
        </div>
    </div>
</nav>
