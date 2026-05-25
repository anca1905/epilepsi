<?php
$page_title = 'Beranda';
$active_nav = 'home';
require 'layout/header_public.php';
?>

<!-- ── Hero Section ─────────────────────────────────── -->
<section class="hero-section">
    <div class="container text-center position-relative">
        <span class="hero-badge">
            <i class="bi bi-award-fill"></i>
            Menggunakan Metode Probabilitas Bayes
        </span>
        <h1 class="fw-bold mb-3">
            Deteksi Dini Epilepsi<br>pada Anak
        </h1>
        <p class="lead">
            Kenali gejala lebih awal dengan sistem pakar cerdas. Dapatkan persentase 
            kemungkinan penyakit dan panduan penanganan awal secara cepat &amp; akurat.
        </p>
        <div class="d-flex gap-3 justify-content-center flex-wrap">
            <a href="form_pasien.php" class="btn-hero-primary">
                <i class="bi bi-clipboard2-pulse me-2"></i>Mulai Diagnosa Sekarang
            </a>
            <a href="#fitur" class="btn-hero-secondary">
                <i class="bi bi-info-circle me-2"></i>Pelajari Lebih Lanjut
            </a>
        </div>
    </div>
</section>

<!-- ── Stats Bar ────────────────────────────────────── -->
<div class="bg-white border-bottom">
    <div class="container py-4">
        <div class="row g-3 text-center">
            <div class="col-4">
                <div class="fw-800 fs-4 text-success fw-bold">100%</div>
                <div class="text-muted small">Gratis &amp; Terbuka</div>
            </div>
            <div class="col-4 border-start border-end">
                <div class="fw-800 fs-4 text-primary fw-bold">Bayes</div>
                <div class="text-muted small">Metode Probabilistik</div>
            </div>
            <div class="col-4">
                <div class="fw-800 fs-4 text-warning fw-bold">Cepat</div>
                <div class="text-muted small">Hasil Real-time</div>
            </div>
        </div>
    </div>
</div>

<!-- ── Feature Cards ────────────────────────────────── -->
<section id="fitur" class="container py-5 my-2">
    <div class="text-center mb-5">
        <h2 class="fw-bold fs-3 mb-2">Mengapa Menggunakan EpilepsiCare?</h2>
        <p class="text-muted" style="max-width:500px;margin:0 auto;">
            Dirancang khusus untuk membantu orang tua dan tenaga kesehatan mendeteksi epilepsi anak sejak dini.
        </p>
    </div>
    <div class="row g-4">
        <div class="col-md-4">
            <div class="card feature-card h-100 p-4">
                <div class="feature-icon bg-success bg-opacity-10 text-success">
                    <i class="bi bi-graph-up-arrow"></i>
                </div>
                <h5 class="fw-bold mb-2">Akurat &amp; Terukur</h5>
                <p class="text-muted mb-0">
                    Menggunakan algoritma Probabilitas Bayes berdasarkan pengetahuan dari dokter spesialis epilepsi anak.
                </p>
            </div>
        </div>
        <div class="col-md-4">
            <div class="card feature-card h-100 p-4">
                <div class="feature-icon bg-primary bg-opacity-10 text-primary">
                    <i class="bi bi-lightning-charge-fill"></i>
                </div>
                <h5 class="fw-bold mb-2">Cepat &amp; Mudah</h5>
                <p class="text-muted mb-0">
                    Cukup pilih gejala yang dialami anak — sistem memproses data dan memberikan hasil secara real-time.
                </p>
            </div>
        </div>
        <div class="col-md-4">
            <div class="card feature-card h-100 p-4">
                <div class="feature-icon bg-warning bg-opacity-10 text-warning">
                    <i class="bi bi-patch-check-fill"></i>
                </div>
                <h5 class="fw-bold mb-2">Solusi Awal</h5>
                <p class="text-muted mb-0">
                    Memberikan saran penanganan pertama sebelum Anda merujuk anak ke fasilitas kesehatan terdekat.
                </p>
            </div>
        </div>
    </div>
</section>

<!-- ── CTA Section ──────────────────────────────────── -->
<section class="container pb-5 mb-3">
    <div class="p-5 rounded-4 text-center text-white" style="background: var(--brand-gradient);">
        <h3 class="fw-bold mb-2">Siap Melakukan Diagnosa?</h3>
        <p class="mb-4 opacity-90">Prosesnya mudah dan hanya butuh beberapa menit.</p>
        <a href="form_pasien.php" class="btn-hero-primary">
            <i class="bi bi-arrow-right-circle me-2"></i>Mulai Sekarang
        </a>
    </div>
</section>

<?php require 'layout/footer_public.php'; ?>