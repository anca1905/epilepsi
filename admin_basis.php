<?php
session_start();
require 'koneksi.php';

if (!isset($_SESSION['login_admin'])) {
    header("Location: login_admin.php");
    exit();
}

$pesan = "";

// Ambil data untuk filter & dropdown
$filter_penyakit = isset($_GET['filter_penyakit']) ? mysqli_real_escape_string($conn, $_GET['filter_penyakit']) : '';

// Aturan P(G|P) di-hardcode ke dalam kodingan
$hardcoded_basis = [
    'P01' => [
        'G01' => 1, 'G02' => 0, 'G03' => 0.1, 'G04' => 0, 'G05' => 0.5,
        'G06' => 0.3, 'G07' => 0.2, 'G08' => 0, 'G09' => 0, 'G10' => 0.4,
        'G11' => 0.1, 'G12' => 0.1, 'G13' => 0.3, 'G14' => 0.3, 'G15' => 0,
        'G16' => 0.2, 'G17' => 0
    ],
    'P02' => [
        'G01' => 1, 'G02' => 1, 'G03' => 0.2, 'G04' => 0.1, 'G05' => 0.2,
        'G06' => 0.2, 'G07' => 0, 'G08' => 0.1, 'G09' => 0.1, 'G10' => 0,
        'G11' => 0.3, 'G12' => 0.3, 'G13' => 0, 'G14' => 0, 'G15' => 0,
        'G16' => 0, 'G17' => 0.3
    ]
];

// Ambil semua penyakit dan gejala dari database untuk keperluan nama
$query_penyakit = mysqli_query($conn, "SELECT * FROM penyakit ORDER BY kode_penyakit ASC");
$penyakit_data = [];
$penyakit_list = [];
while ($p = mysqli_fetch_assoc($query_penyakit)) {
    $penyakit_data[$p['kode_penyakit']] = $p['nama_penyakit'];
    $penyakit_list[] = $p;
}

$query_gejala = mysqli_query($conn, "SELECT * FROM gejala ORDER BY kode_gejala ASC");
$gejala_data = [];
while ($g = mysqli_fetch_assoc($query_gejala)) {
    $gejala_data[$g['kode_gejala']] = $g['nama_gejala'];
}

// Buat array data untuk ditampilkan dan summary
$basis_list = [];
$summary_map = [];
$total_basis = 0;

foreach ($hardcoded_basis as $kp => $gejalas) {
    if (!isset($summary_map[$kp])) {
        $summary_map[$kp] = ['total' => 0];
    }
    foreach ($gejalas as $kg => $prob) {
        $total_basis++;
        $summary_map[$kp]['total']++;
        if ($filter_penyakit === '' || $filter_penyakit === $kp) {
            $basis_list[] = [
                'kode_penyakit' => $kp,
                'kode_gejala'   => $kg,
                'nama_penyakit' => $penyakit_data[$kp] ?? $kp,
                'nama_gejala'   => $gejala_data[$kg] ?? $kg,
                'probabilitas'  => $prob
            ];
        }
    }
}

$page_title  = 'Basis Pengetahuan';
$active_page = 'basis';
require 'layout/head_admin.php';
require 'layout/sidebar_admin.php';
?>

<!-- MAIN CONTENT -->
<div class="admin-main">

    <!-- Topbar -->
    <div class="admin-topbar">
        <h1 class="page-heading">
            <i class="bi bi-diagram-3 me-2 text-info"></i>Basis Pengetahuan
        </h1>
        <div class="topbar-right">
            <span class="date-chip"><i class="bi bi-calendar3 me-1"></i><?= date('d M Y') ?></span>
            <div class="user-chip">
                <div class="avatar"><?= strtoupper(substr($_SESSION['username'], 0, 1)) ?></div>
                <?= htmlspecialchars($_SESSION['username']) ?>
            </div>
        </div>
    </div>

    <div class="admin-content">

        <!-- Header -->
        <div class="mb-4">
            <h2 class="fw-bold mb-0" style="font-size:1.35rem;">Daftar Basis Pengetahuan</h2>
            <p class="text-muted mb-0" style="font-size:0.85rem;">
                Relasi antara penyakit dan gejala.
            </p>
        </div>

        <!-- Summary Cards -->
        <?php

        ?>
        <?php if (!empty($penyakit_list)): ?>
        <div class="row g-3 mb-4">
            <?php foreach ($penyakit_list as $p): ?>
            <div class="col-sm-6 col-xl-3">
                <a href="admin_basis.php?filter_penyakit=<?= $p['kode_penyakit'] ?>"
                   class="d-block text-decoration-none">
                    <div class="stat-card <?= $filter_penyakit === $p['kode_penyakit'] ? 'border border-2 border-primary' : '' ?>"
                         style="cursor:pointer;">
                        <div>
                            <div class="stat-label"><?= htmlspecialchars($p['nama_penyakit']) ?></div>
                            <div class="stat-value" style="font-size:1.6rem;">
                                <?= isset($summary_map[$p['kode_penyakit']]) ? $summary_map[$p['kode_penyakit']]['total'] : 0 ?>
                            </div>
                            <div style="font-size:0.75rem;color:#94a3b8;">
                                Gejala Terkait
                            </div>
                        </div>
                        <div class="stat-icon bg-info bg-opacity-10 text-info">
                            <i class="bi bi-diagram-3"></i>
                        </div>
                    </div>
                </a>
            </div>
            <?php endforeach; ?>
        </div>
        <?php endif; ?>

        <!-- Filter & Table -->
        <div class="card-admin p-4">

            <!-- Filter Bar -->
            <div class="d-flex align-items-center justify-content-between mb-3 gap-2 flex-wrap">
                <div class="d-flex align-items-center gap-2">
                    <i class="bi bi-funnel text-muted"></i>
                    <span class="fw-semibold" style="font-size:0.9rem;">Filter Penyakit:</span>
                    <div class="d-flex gap-1 flex-wrap">
                        <a href="admin_basis.php"
                           class="btn btn-sm <?= $filter_penyakit === '' ? 'btn-dark' : 'btn-outline-secondary' ?>">
                            Semua
                        </a>
                        <?php foreach ($penyakit_list as $p): ?>
                        <a href="admin_basis.php?filter_penyakit=<?= $p['kode_penyakit'] ?>"
                           class="btn btn-sm <?= $filter_penyakit === $p['kode_penyakit'] ? 'btn-primary' : 'btn-outline-secondary' ?>">
                            <?= $p['kode_penyakit'] ?>
                        </a>
                        <?php endforeach; ?>
                    </div>
                </div>
                <span class="badge bg-secondary" style="font-size:0.8rem;">
                    Total: <?= $total_basis ?> aturan
                </span>
            </div>

            <div class="table-responsive">
                <table class="table table-hover align-middle mb-0">
                    <thead>
                        <tr>
                            <th class="text-center" style="width:10%;">No</th>
                            <th style="width:40%;">Penyakit</th>
                            <th style="width:50%;">Gejala</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php
                        $no = 1;
                        if (!empty($basis_list)):
                            foreach ($basis_list as $row):
                        ?>
                        <tr>
                            <td class="text-center text-muted"><?= $no++ ?></td>
                            <td>
                                <div class="fw-semibold" style="font-size:0.9rem;">
                                    <?= htmlspecialchars($row['nama_penyakit'] ?? $row['kode_penyakit']) ?>
                                </div>
                                <span class="badge rounded-pill"
                                      style="background:rgba(13,110,253,0.1);color:#0d6efd;font-size:0.72rem;">
                                    <?= $row['kode_penyakit'] ?>
                                </span>
                            </td>
                            <td>
                                <div style="font-size:0.9rem;">
                                    <?= htmlspecialchars($row['nama_gejala'] ?? $row['kode_gejala']) ?>
                                </div>
                                <span class="badge rounded-pill"
                                      style="background:rgba(26,127,90,0.1);color:var(--brand-primary);font-size:0.72rem;">
                                    <?= $row['kode_gejala'] ?>
                                </span>
                            </td>
                        </tr>

                        <?php endforeach; else: ?>
                        <tr>
                            <td colspan="3" class="text-center py-5 text-muted">
                                <i class="bi bi-inbox fs-2 d-block mb-2"></i>
                                <?= $filter_penyakit ? "Belum ada aturan untuk penyakit <strong>$filter_penyakit</strong>." : 'Basis pengetahuan masih kosong.' ?>
                            </td>
                        </tr>
                        <?php endif; ?>
                    </tbody>
                </table>
            </div>
        </div>

    </div><!-- /.admin-content -->
</div><!-- /.admin-main -->
</div><!-- /.admin-wrapper -->



<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>
