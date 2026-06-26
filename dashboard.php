<?php
session_start();
require 'koneksi.php';

if (!isset($_SESSION['login_admin'])) {
    header("Location: login_admin.php");
    exit();
}

$count_gejala   = mysqli_num_rows(mysqli_query($conn, "SELECT * FROM gejala"));
$count_penyakit = mysqli_num_rows(mysqli_query($conn, "SELECT * FROM penyakit"));
$count_pasien   = mysqli_num_rows(mysqli_query($conn, "SELECT * FROM pengguna"));
$count_riwayat  = mysqli_num_rows(mysqli_query($conn, "SELECT * FROM riwayat_diagnosa"));

$page_title  = 'Dashboard';
$active_page = 'dashboard';
require 'layout/head_admin.php';
require 'layout/sidebar_admin.php';
?>

<!-- MAIN CONTENT -->
<div class="admin-main">

    <!-- Topbar -->
    <div class="admin-topbar">
        <h1 class="page-heading"><i class="bi bi-speedometer2 me-2 text-success"></i>Dashboard</h1>
        <div class="topbar-right">
            <span class="date-chip">
                <i class="bi bi-calendar3 me-1"></i><?= date('d M Y') ?>
            </span>
            <div class="user-chip">
                <div class="avatar"><?= strtoupper(substr($_SESSION['username'], 0, 1)) ?></div>
                <?= htmlspecialchars($_SESSION['username']) ?>
            </div>
        </div>
    </div>

    <div class="admin-content">

        <!-- Welcome Banner -->
        <div class="p-4 rounded-3 mb-4 text-white"
             style="background: var(--brand-gradient);position:relative;overflow:hidden;">
            <div style="position:relative;z-index:1;">
                <h5 class="fw-bold mb-1">
                    Selamat Datang, <?= htmlspecialchars($_SESSION['username']) ?>! 👋
                </h5>
                <p class="mb-0 opacity-90" style="font-size:0.9rem;">
                    Sistem Pakar Diagnosa Dini Epilepsi Pada Anak — Panel Administrator
                </p>
            </div>
        </div>

        <!-- Stat Cards -->
        <div class="row g-3 mb-4">
            <div class="col-sm-6 col-xl-3">
                <div class="stat-card">
                    <div>
                        <div class="stat-label">Data Gejala</div>
                        <div class="stat-value"><?= $count_gejala ?></div>
                    </div>
                    <div class="stat-icon bg-success bg-opacity-10 text-success">
                        <i class="bi bi-file-earmark-medical"></i>
                    </div>
                </div>
            </div>
            <div class="col-sm-6 col-xl-3">
                <div class="stat-card">
                    <div>
                        <div class="stat-label">Data Penyakit</div>
                        <div class="stat-value"><?= $count_penyakit ?></div>
                    </div>
                    <div class="stat-icon bg-primary bg-opacity-10 text-primary">
                        <i class="bi bi-virus"></i>
                    </div>
                </div>
            </div>
            <div class="col-sm-6 col-xl-3">
                <div class="stat-card">
                    <div>
                        <div class="stat-label">Total Pasien</div>
                        <div class="stat-value"><?= $count_pasien ?></div>
                    </div>
                    <div class="stat-icon bg-warning bg-opacity-10 text-warning">
                        <i class="bi bi-people"></i>
                    </div>
                </div>
            </div>
            <div class="col-sm-6 col-xl-3">
                <div class="stat-card">
                    <div>
                        <div class="stat-label">Laporan Diagnosa</div>
                        <div class="stat-value"><?= $count_riwayat ?></div>
                    </div>
                    <div class="stat-icon bg-danger bg-opacity-10 text-danger">
                        <i class="bi bi-graph-up-arrow"></i>
                    </div>
                </div>
            </div>
        </div>

        <!-- Quick Links & Info -->
        <div class="row g-3">
            <div class="col-lg-8">
                <div class="card-admin p-4">
                    <h6 class="fw-bold text-dark mb-3 d-flex align-items-center gap-2">
                        <i class="bi bi-info-circle text-primary"></i> Tentang Sistem
                    </h6>
                    <p class="text-muted mb-0" style="font-size:0.92rem;line-height:1.75;">
                        Sistem ini mengimplementasikan metode <strong>Probabilitas Bayes</strong> untuk menghitung 
                        tingkat kemungkinan seorang anak mengalami jenis epilepsi tertentu berdasarkan tanda-tanda 
                        atau gejala klinis yang diinputkan oleh orang tua maupun tenaga kesehatan. Gunakan menu 
                        navigasi di sebelah kiri untuk mengelola basis pengetahuan sistem.
                    </p>
                </div>
            </div>
            <div class="col-lg-4">
                <div class="card-admin p-4">
                    <h6 class="fw-bold text-dark mb-3 d-flex align-items-center gap-2">
                        <i class="bi bi-grid-3x3-gap text-success"></i> Menu Cepat
                    </h6>
                    <div class="d-flex flex-column gap-2">
                        <a href="form_pasien.php" class="btn btn-outline-warning btn-sm text-start">
                            <i class="bi bi-clipboard2-pulse me-2"></i>Mulai Diagnosa
                        </a>
                        <a href="admin_gejala.php" class="btn btn-outline-success btn-sm text-start">
                            <i class="bi bi-file-earmark-medical me-2"></i>Kelola Gejala
                        </a>
                        <a href="admin_penyakit.php" class="btn btn-outline-primary btn-sm text-start">
                            <i class="bi bi-virus me-2"></i>Kelola Penyakit
                        </a>
                        <a href="admin_laporan.php" class="btn btn-outline-danger btn-sm text-start">
                            <i class="bi bi-graph-up-arrow me-2"></i>Lihat Laporan
                        </a>
                    </div>
                </div>
            </div>
        </div>

    </div><!-- /.admin-content -->
</div><!-- /.admin-main -->
</div><!-- /.admin-wrapper -->

<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>