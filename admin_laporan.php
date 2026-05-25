<?php
session_start();
require 'koneksi.php';

if (!isset($_SESSION['login_admin'])) {
    header("Location: login_admin.php");
    exit();
}

$pesan = "";

// PROSES HAPUS
if (isset($_GET['hapus'])) {
    $id_hapus = (int)$_GET['hapus'];
    $hapus    = mysqli_query($conn, "DELETE FROM riwayat_diagnosa WHERE id_diagnosa = '$id_hapus'");
    if ($hapus) {
        header("Location: admin_laporan.php?pesan=hapus_sukses");
        exit();
    } else {
        $pesan = "<div class='alert alert-danger'><i class='bi bi-x-circle me-2'></i>Gagal menghapus data!</div>";
    }
}

if (isset($_GET['pesan']) && $_GET['pesan'] == 'hapus_sukses') {
    $pesan = "<div class='alert alert-success'><i class='bi bi-check-circle me-2'></i>Data laporan berhasil dihapus!</div>";
}

// Filter & Pencarian
$filter_tanggal = isset($_GET['tanggal']) ? mysqli_real_escape_string($conn, $_GET['tanggal']) : '';
$search_nama    = isset($_GET['search'])  ? mysqli_real_escape_string($conn, $_GET['search'])  : '';

$where_parts = [];
if ($filter_tanggal) $where_parts[] = "r.tanggal = '$filter_tanggal'";
if ($search_nama)    $where_parts[] = "u.nama_user LIKE '%$search_nama%'";
$where = $where_parts ? "WHERE " . implode(" AND ", $where_parts) : "";

// Query laporan dengan JOIN
$query_laporan = mysqli_query($conn, "
    SELECT r.*, u.nama_user, u.umur, u.no_telpon, p.nama_penyakit
    FROM riwayat_diagnosa r
    LEFT JOIN pengguna p_user ON r.id_pengguna = p_user.id_pengguna
    LEFT JOIN pengguna u      ON r.id_pengguna = u.id_pengguna
    LEFT JOIN penyakit p      ON r.kode_penyakit = p.kode_penyakit
    $where
    ORDER BY r.tanggal DESC, r.id_diagnosa DESC
");

// Statistik ringkasan
$total_laporan  = mysqli_num_rows(mysqli_query($conn, "SELECT id_diagnosa FROM riwayat_diagnosa"));
$total_pasien   = mysqli_num_rows(mysqli_query($conn, "SELECT DISTINCT id_pengguna FROM riwayat_diagnosa"));
$total_hari_ini = mysqli_num_rows(mysqli_query($conn, "SELECT id_diagnosa FROM riwayat_diagnosa WHERE tanggal = CURDATE()"));

// Penyakit terbanyak terdiagnosa
$query_top = mysqli_query($conn, "
    SELECT r.kode_penyakit, p.nama_penyakit, COUNT(*) as total
    FROM riwayat_diagnosa r
    LEFT JOIN penyakit p ON r.kode_penyakit = p.kode_penyakit
    GROUP BY r.kode_penyakit
    ORDER BY total DESC LIMIT 1
");
$top_penyakit = mysqli_fetch_assoc($query_top);

$page_title  = 'Hasil Diagnosa';
$active_page = 'laporan';
require 'layout/head_admin.php';
require 'layout/sidebar_admin.php';
?>

<!-- MAIN CONTENT -->
<div class="admin-main">

    <!-- Topbar -->
    <div class="admin-topbar">
        <h1 class="page-heading">
            <i class="bi bi-graph-up-arrow me-2 text-danger"></i>Hasil Diagnosa
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

        <div class="mb-4">
            <h2 class="fw-bold mb-0" style="font-size:1.35rem;">Laporan Riwayat Diagnosa</h2>
            <p class="text-muted mb-0" style="font-size:0.85rem;">
                Seluruh hasil diagnosa pasien yang telah diproses oleh sistem pakar.
            </p>
        </div>

        <?= $pesan ?>

        <!-- Stat Cards -->
        <div class="row g-3 mb-4">
            <div class="col-sm-6 col-xl-3">
                <div class="stat-card">
                    <div>
                        <div class="stat-label">Total Diagnosa</div>
                        <div class="stat-value"><?= $total_laporan ?></div>
                    </div>
                    <div class="stat-icon bg-primary bg-opacity-10 text-primary">
                        <i class="bi bi-clipboard2-pulse"></i>
                    </div>
                </div>
            </div>
            <div class="col-sm-6 col-xl-3">
                <div class="stat-card">
                    <div>
                        <div class="stat-label">Total Pasien Unik</div>
                        <div class="stat-value"><?= $total_pasien ?></div>
                    </div>
                    <div class="stat-icon bg-success bg-opacity-10 text-success">
                        <i class="bi bi-people"></i>
                    </div>
                </div>
            </div>
            <div class="col-sm-6 col-xl-3">
                <div class="stat-card">
                    <div>
                        <div class="stat-label">Diagnosa Hari Ini</div>
                        <div class="stat-value"><?= $total_hari_ini ?></div>
                    </div>
                    <div class="stat-icon bg-warning bg-opacity-10 text-warning">
                        <i class="bi bi-calendar-check"></i>
                    </div>
                </div>
            </div>
            <div class="col-sm-6 col-xl-3">
                <div class="stat-card">
                    <div>
                        <div class="stat-label">Penyakit Terbanyak</div>
                        <div style="font-size:0.9rem;font-weight:700;color:#0f172a;line-height:1.2;margin-top:4px;">
                            <?= $top_penyakit ? htmlspecialchars($top_penyakit['nama_penyakit'] ?? '—') : '—' ?>
                        </div>
                        <?php if ($top_penyakit): ?>
                        <div style="font-size:0.75rem;color:#94a3b8;"><?= $top_penyakit['total'] ?> kasus</div>
                        <?php endif; ?>
                    </div>
                    <div class="stat-icon bg-danger bg-opacity-10 text-danger">
                        <i class="bi bi-virus"></i>
                    </div>
                </div>
            </div>
        </div>

        <!-- Filter & Search -->
        <div class="card-admin p-4">
            <form method="GET" action="admin_laporan.php" class="row g-2 align-items-end mb-4">
                <div class="col-md-4">
                    <label class="form-label mb-1" style="font-size:0.82rem;">Cari Nama Pasien</label>
                    <div class="input-group">
                        <span class="input-group-text bg-light border-end-0">
                            <i class="bi bi-search text-muted"></i>
                        </span>
                        <input type="text" name="search" class="form-control border-start-0"
                               placeholder="Cari nama..." value="<?= htmlspecialchars($search_nama) ?>">
                    </div>
                </div>
                <div class="col-md-3">
                    <label class="form-label mb-1" style="font-size:0.82rem;">Filter Tanggal</label>
                    <input type="date" name="tanggal" class="form-control"
                           value="<?= htmlspecialchars($filter_tanggal) ?>">
                </div>
                <div class="col-md-auto d-flex gap-2">
                    <button type="submit" class="btn btn-brand">
                        <i class="bi bi-filter me-1"></i>Filter
                    </button>
                    <?php if ($filter_tanggal || $search_nama): ?>
                    <a href="admin_laporan.php" class="btn btn-outline-secondary">
                        <i class="bi bi-x me-1"></i>Reset
                    </a>
                    <?php endif; ?>
                </div>
                <?php if ($filter_tanggal || $search_nama): ?>
                <div class="col-12">
                    <div class="d-flex align-items-center gap-2">
                        <span class="text-muted" style="font-size:0.83rem;">Hasil filter:</span>
                        <?php if ($search_nama): ?>
                        <span class="badge bg-primary bg-opacity-10 text-primary">
                            Nama: <?= htmlspecialchars($search_nama) ?>
                        </span>
                        <?php endif; ?>
                        <?php if ($filter_tanggal): ?>
                        <span class="badge bg-warning bg-opacity-10 text-warning">
                            Tanggal: <?= date('d M Y', strtotime($filter_tanggal)) ?>
                        </span>
                        <?php endif; ?>
                        <span class="text-muted" style="font-size:0.83rem;">
                            — <?= mysqli_num_rows($query_laporan) ?> data ditemukan
                        </span>
                    </div>
                </div>
                <?php endif; ?>
            </form>

            <div class="table-responsive">
                <table class="table table-hover align-middle mb-0">
                    <thead>
                        <tr>
                            <th class="text-center" style="width:5%;">No</th>
                            <th style="width:10%;">Tanggal</th>
                            <th style="width:20%;">Nama Pasien</th>
                            <th class="text-center" style="width:7%;">Umur</th>
                            <th style="width:20%;">Hasil Diagnosa</th>
                            <th class="text-center" style="width:16%;">Nilai Bayes</th>
                            <th class="text-center" style="width:10%;">Tingkat</th>
                            <th class="text-center" style="width:12%;">Aksi</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php
                        $no = 1;
                        if (mysqli_num_rows($query_laporan) > 0):
                            while ($row = mysqli_fetch_assoc($query_laporan)):
                                $nilai = (float)$row['nilai_bayes'];
                                // Tentukan tingkat kepercayaan
                                if ($nilai >= 0.7) {
                                    $level = ['label' => 'Tinggi',   'class' => 'success'];
                                } elseif ($nilai >= 0.4) {
                                    $level = ['label' => 'Sedang',   'class' => 'warning'];
                                } else {
                                    $level = ['label' => 'Rendah',   'class' => 'danger'];
                                }
                        ?>
                        <tr>
                            <td class="text-center text-muted"><?= $no++ ?></td>
                            <td>
                                <div class="fw-semibold" style="font-size:0.88rem;">
                                    <?= date('d M Y', strtotime($row['tanggal'])) ?>
                                </div>
                            </td>
                            <td>
                                <div class="d-flex align-items-center gap-2">
                                    <div class="d-flex align-items-center justify-content-center rounded-circle text-white"
                                         style="width:30px;height:30px;background:var(--brand-gradient);font-size:0.75rem;font-weight:700;flex-shrink:0;">
                                        <?= strtoupper(substr($row['nama_user'] ?? 'P', 0, 1)) ?>
                                    </div>
                                    <div>
                                        <div class="fw-semibold" style="font-size:0.88rem;">
                                            <?= htmlspecialchars($row['nama_user'] ?? '—') ?>
                                        </div>
                                        <div class="text-muted" style="font-size:0.75rem;">
                                            <?= $row['no_telpon'] ?? '—' ?>
                                        </div>
                                    </div>
                                </div>
                            </td>
                            <td class="text-center">
                                <span class="badge bg-light text-dark border" style="font-size:0.82rem;">
                                    <?= $row['umur'] ?? '—' ?> thn
                                </span>
                            </td>
                            <td>
                                <div class="fw-semibold" style="font-size:0.9rem;">
                                    <?= htmlspecialchars($row['nama_penyakit'] ?? '—') ?>
                                </div>
                                <span class="badge rounded-pill"
                                      style="background:rgba(13,110,253,0.1);color:#0d6efd;font-size:0.72rem;">
                                    <?= $row['kode_penyakit'] ?>
                                </span>
                            </td>
                            <td class="text-center">
                                <div class="fw-bold" style="font-size:1rem;color:#0f172a;">
                                    <?= number_format($nilai * 100, 2) ?>%
                                </div>
                                <div class="text-muted" style="font-size:0.75rem;">
                                    <?= number_format($nilai, 6) ?>
                                </div>
                            </td>
                            <td class="text-center">
                                <span class="badge bg-<?= $level['class'] ?> bg-opacity-15 text-<?= $level['class'] ?>"
                                      style="font-weight:700;font-size:0.78rem;padding:5px 10px;border:1px solid currentColor;">
                                    <?= $level['label'] ?>
                                </span>
                            </td>
                            <td class="text-center">
                                <button class="btn btn-sm btn-outline-info me-1"
                                        data-bs-toggle="modal"
                                        data-bs-target="#modalDetail<?= $row['id_diagnosa'] ?>"
                                        title="Lihat Detail">
                                    <i class="bi bi-eye"></i>
                                </button>
                                <a href="admin_laporan.php?hapus=<?= $row['id_diagnosa'] ?><?= $filter_tanggal ? '&tanggal='.$filter_tanggal : '' ?><?= $search_nama ? '&search='.$search_nama : '' ?>"
                                   class="btn btn-sm btn-outline-danger"
                                   onclick="return confirm('Yakin ingin menghapus laporan ini?')"
                                   title="Hapus">
                                    <i class="bi bi-trash"></i>
                                </a>
                            </td>
                        </tr>

                        <!-- Modal Detail -->
                        <div class="modal fade" id="modalDetail<?= $row['id_diagnosa'] ?>" tabindex="-1" aria-hidden="true">
                            <div class="modal-dialog modal-dialog-centered">
                                <div class="modal-content">
                                    <div class="modal-header" style="background:var(--brand-gradient);">
                                        <h5 class="modal-title text-white">
                                            <i class="bi bi-clipboard2-pulse me-2"></i>Detail Hasil Diagnosa
                                        </h5>
                                        <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal"></button>
                                    </div>
                                    <div class="modal-body p-4">

                                        <!-- Info Pasien -->
                                        <div class="p-3 rounded-3 mb-3" style="background:#f0fdf7;border:1.5px solid #bbf7d0;">
                                            <div class="d-flex align-items-center gap-3">
                                                <div class="d-flex align-items-center justify-content-center rounded-circle text-white fw-bold"
                                                     style="width:44px;height:44px;background:var(--brand-gradient);font-size:1.1rem;flex-shrink:0;">
                                                    <?= strtoupper(substr($row['nama_user'] ?? 'P', 0, 1)) ?>
                                                </div>
                                                <div>
                                                    <div class="fw-bold"><?= htmlspecialchars($row['nama_user'] ?? '—') ?></div>
                                                    <div class="text-muted" style="font-size:0.82rem;">
                                                        <?= $row['umur'] ?? '—' ?> Tahun
                                                        <?php if ($row['no_telpon']): ?>
                                                        &bull; <?= $row['no_telpon'] ?>
                                                        <?php endif; ?>
                                                    </div>
                                                </div>
                                            </div>
                                        </div>

                                        <div class="row g-3">
                                            <div class="col-6">
                                                <div class="text-muted" style="font-size:0.78rem;">Tanggal Diagnosa</div>
                                                <div class="fw-semibold">
                                                    <?= date('d M Y', strtotime($row['tanggal'])) ?>
                                                </div>
                                            </div>
                                            <div class="col-6">
                                                <div class="text-muted" style="font-size:0.78rem;">ID Diagnosa</div>
                                                <div class="fw-semibold">#<?= str_pad($row['id_diagnosa'], 4, '0', STR_PAD_LEFT) ?></div>
                                            </div>
                                            <div class="col-12">
                                                <div class="text-muted" style="font-size:0.78rem;">Hasil Diagnosa</div>
                                                <div class="fw-bold fs-6">
                                                    <?= htmlspecialchars($row['nama_penyakit'] ?? '—') ?>
                                                    <span class="badge bg-primary ms-1" style="font-size:0.75rem;">
                                                        <?= $row['kode_penyakit'] ?>
                                                    </span>
                                                </div>
                                            </div>
                                            <div class="col-12">
                                                <div class="text-muted mb-2" style="font-size:0.78rem;">Nilai Probabilitas Bayes</div>
                                                <div class="d-flex align-items-center gap-3">
                                                    <div class="fw-bold" style="font-size:1.8rem;color:#0f172a;line-height:1;">
                                                        <?= number_format($nilai * 100, 2) ?>%
                                                    </div>
                                                    <div>
                                                        <span class="badge bg-<?= $level['class'] ?>"
                                                              style="font-size:0.82rem;padding:6px 12px;">
                                                            Kepercayaan <?= $level['label'] ?>
                                                        </span>
                                                        <div class="text-muted mt-1" style="font-size:0.75rem;">
                                                            Nilai: <?= number_format($nilai, 8) ?>
                                                        </div>
                                                    </div>
                                                </div>
                                                <div class="progress mt-2" style="height:10px;border-radius:5px;">
                                                    <div class="progress-bar bg-<?= $level['class'] ?>"
                                                         style="width:<?= min(100, $nilai * 100) ?>%;border-radius:5px;">
                                                    </div>
                                                </div>
                                            </div>
                                        </div>
                                    </div>
                                    <div class="modal-footer">
                                        <button type="button" class="btn btn-outline-secondary" data-bs-dismiss="modal">Tutup</button>
                                    </div>
                                </div>
                            </div>
                        </div>

                        <?php endwhile; else: ?>
                        <tr>
                            <td colspan="8" class="text-center py-5 text-muted">
                                <i class="bi bi-clipboard2-x fs-2 d-block mb-2"></i>
                                <?php if ($filter_tanggal || $search_nama): ?>
                                    Tidak ada data yang cocok dengan filter yang dipilih.
                                    <br><a href="admin_laporan.php" class="btn btn-sm btn-outline-secondary mt-2">Reset Filter</a>
                                <?php else: ?>
                                    Belum ada riwayat diagnosa.
                                <?php endif; ?>
                            </td>
                        </tr>
                        <?php endif; ?>
                    </tbody>
                </table>
            </div>

        </div><!-- /.card-admin -->

    </div><!-- /.admin-content -->
</div><!-- /.admin-main -->
</div><!-- /.admin-wrapper -->

<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>
