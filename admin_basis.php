<?php
session_start();
require 'koneksi.php';

if (!isset($_SESSION['login_admin'])) {
    header("Location: login_admin.php");
    exit();
}

$pesan = "";

// 1. PROSES TAMBAH
if (isset($_POST['tambah'])) {
    $kode_penyakit = mysqli_real_escape_string($conn, $_POST['kode_penyakit']);
    $kode_gejala   = mysqli_real_escape_string($conn, $_POST['kode_gejala']);
    $pasien_terkena = isset($_POST['pasien_terkena']) ? (float)$_POST['pasien_terkena'] : 0;
    $total_kasus    = isset($_POST['total_kasus']) ? (float)$_POST['total_kasus'] : 1;
    $probabilitas_val = $total_kasus > 0 ? $pasien_terkena / $total_kasus : 0;
    $probabilitas  = number_format($probabilitas_val, 4, '.', '');

    // Cek apakah kombinasi sudah ada
    $cek = mysqli_query($conn, "SELECT * FROM basis_pengetahuan 
                                WHERE kode_penyakit = '$kode_penyakit' 
                                AND kode_gejala = '$kode_gejala'");
    if (mysqli_num_rows($cek) > 0) {
        $pesan = "<div class='alert alert-warning'><i class='bi bi-exclamation-triangle me-2'></i>
                  Kombinasi penyakit <strong>$kode_penyakit</strong> dan gejala <strong>$kode_gejala</strong> sudah ada!</div>";
    } else {
        $insert = mysqli_query($conn, "INSERT INTO basis_pengetahuan (kode_penyakit, kode_gejala, probabilitas) 
                                       VALUES ('$kode_penyakit', '$kode_gejala', '$probabilitas')");
        if ($insert) {
            $pesan = "<div class='alert alert-success'><i class='bi bi-check-circle me-2'></i>Data basis pengetahuan berhasil ditambahkan!</div>";
        } else {
            $pesan = "<div class='alert alert-danger'><i class='bi bi-x-circle me-2'></i>Gagal menambah data: " . mysqli_error($conn) . "</div>";
        }
    }
}

// 2. PROSES EDIT
if (isset($_POST['edit'])) {
    $id_basis     = mysqli_real_escape_string($conn, $_POST['id_basis']);
    $pasien_terkena = isset($_POST['pasien_terkena']) ? (float)$_POST['pasien_terkena'] : 0;
    $total_kasus    = isset($_POST['total_kasus']) ? (float)$_POST['total_kasus'] : 1;
    $probabilitas_val = $total_kasus > 0 ? $pasien_terkena / $total_kasus : 0;
    $probabilitas  = number_format($probabilitas_val, 4, '.', '');

    $update = mysqli_query($conn, "UPDATE basis_pengetahuan SET probabilitas = '$probabilitas' WHERE id_basis = '$id_basis'");
    if ($update) {
        $pesan = "<div class='alert alert-success'><i class='bi bi-check-circle me-2'></i>Nilai P(G|P) berhasil diperbarui!</div>";
    } else {
        $pesan = "<div class='alert alert-danger'><i class='bi bi-x-circle me-2'></i>Gagal memperbarui: " . mysqli_error($conn) . "</div>";
    }
}

// 3. PROSES HAPUS
if (isset($_GET['hapus'])) {
    $id_hapus = (int)$_GET['hapus'];
    $hapus    = mysqli_query($conn, "DELETE FROM basis_pengetahuan WHERE id_basis = '$id_hapus'");
    if ($hapus) {
        header("Location: admin_basis.php?pesan=hapus_sukses");
        exit();
    } else {
        $pesan = "<div class='alert alert-danger'><i class='bi bi-x-circle me-2'></i>Gagal menghapus data!</div>";
    }
}

if (isset($_GET['pesan']) && $_GET['pesan'] == 'hapus_sukses') {
    $pesan = "<div class='alert alert-success'><i class='bi bi-check-circle me-2'></i>Data basis pengetahuan berhasil dihapus!</div>";
}

// Ambil data untuk filter & dropdown
$filter_penyakit = isset($_GET['filter_penyakit']) ? mysqli_real_escape_string($conn, $_GET['filter_penyakit']) : '';

$where = $filter_penyakit ? "WHERE b.kode_penyakit = '$filter_penyakit'" : "";

$query_basis = mysqli_query($conn, "
    SELECT b.*, p.nama_penyakit, g.nama_gejala 
    FROM basis_pengetahuan b
    LEFT JOIN penyakit p ON b.kode_penyakit = p.kode_penyakit
    LEFT JOIN gejala g   ON b.kode_gejala   = g.kode_gejala
    $where
    ORDER BY b.kode_penyakit ASC, b.kode_gejala ASC
");

$query_penyakit = mysqli_query($conn, "SELECT * FROM penyakit ORDER BY kode_penyakit ASC");
$query_gejala   = mysqli_query($conn, "SELECT * FROM gejala   ORDER BY kode_gejala ASC");

// Hitung jumlah basis per penyakit
$query_summary = mysqli_query($conn, "
    SELECT kode_penyakit, COUNT(*) as total, AVG(probabilitas) as avg_prob 
    FROM basis_pengetahuan 
    GROUP BY kode_penyakit
");

// Total data
$total_basis = mysqli_num_rows(mysqli_query($conn, "SELECT * FROM basis_pengetahuan"));

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

        <!-- Header + Action -->
        <div class="d-flex justify-content-between align-items-start mb-4">
            <div>
                <h2 class="fw-bold mb-0" style="font-size:1.35rem;">Manajemen Basis Pengetahuan</h2>
                <p class="text-muted mb-0" style="font-size:0.85rem;">
                    Kelola nilai P(Gejala | Penyakit) untuk metode Naive Bayes.
                </p>
            </div>
            <button type="button" class="btn btn-brand" data-bs-toggle="modal" data-bs-target="#modalTambah">
                <i class="bi bi-plus-lg me-1"></i>Tambah Aturan
            </button>
        </div>

        <?= $pesan ?>

        <!-- Summary Cards -->
        <?php
        // Reset pointer penyakit untuk digunakan di summary
        $penyakit_list = [];
        $res_p = mysqli_query($conn, "SELECT * FROM penyakit ORDER BY kode_penyakit ASC");
        while ($rp = mysqli_fetch_assoc($res_p)) $penyakit_list[] = $rp;

        $summary_map = [];
        while ($s = mysqli_fetch_assoc($query_summary)) {
            $summary_map[$s['kode_penyakit']] = $s;
        }
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
                                Avg: <?= isset($summary_map[$p['kode_penyakit']]) 
                                    ? number_format($summary_map[$p['kode_penyakit']]['avg_prob'], 4) 
                                    : '—' ?>
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
                            <th class="text-center" style="width:5%;">No</th>
                            <th style="width:22%;">Penyakit</th>
                            <th style="width:35%;">Gejala</th>
                            <th class="text-center" style="width:18%;">Nilai P(G|P)</th>
                            <th class="text-center" style="width:10%;">Visual</th>
                            <th class="text-center" style="width:10%;">Aksi</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php
                        $no = 1;
                        if (mysqli_num_rows($query_basis) > 0):
                            while ($row = mysqli_fetch_assoc($query_basis)):
                                $pct = min(100, round($row['probabilitas'] * 100));
                                $color = $pct >= 70 ? 'success' : ($pct >= 40 ? 'warning' : 'danger');
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
                            <td class="text-center">
                                <span class="fw-bold" style="font-size:1.05rem;color:#0f172a;">
                                    <?= number_format($row['probabilitas'], 4) ?>
                                </span>
                                <div class="text-muted" style="font-size:0.75rem;"><?= $pct ?>%</div>
                            </td>
                            <td class="text-center">
                                <div class="progress" style="height:8px;border-radius:4px;min-width:70px;">
                                    <div class="progress-bar bg-<?= $color ?>"
                                         style="width:<?= $pct ?>%;border-radius:4px;"></div>
                                </div>
                            </td>
                            <td class="text-center">
                                <button class="btn btn-sm btn-outline-primary me-1"
                                        data-bs-toggle="modal"
                                        data-bs-target="#modalEdit<?= $row['id_basis'] ?>"
                                        title="Edit Nilai P(G|P)">
                                    <i class="bi bi-pencil"></i>
                                </button>
                                <a href="admin_basis.php?hapus=<?= $row['id_basis'] ?><?= $filter_penyakit ? '&filter_penyakit=' . $filter_penyakit : '' ?>"
                                   class="btn btn-sm btn-outline-danger"
                                   onclick="return confirm('Yakin ingin menghapus aturan ini?')"
                                   title="Hapus">
                                    <i class="bi bi-trash"></i>
                                </a>
                            </td>
                        </tr>

                        <!-- Modal Edit -->
                        <div class="modal fade" id="modalEdit<?= $row['id_basis'] ?>" tabindex="-1" aria-hidden="true">
                            <div class="modal-dialog modal-dialog-centered">
                                <div class="modal-content">
                                    <div class="modal-header" style="background:var(--brand-gradient);">
                                        <h5 class="modal-title text-white">
                                            <i class="bi bi-pencil-square me-2"></i>Edit Nilai P(G|P)
                                        </h5>
                                        <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal"></button>
                                    </div>
                                    <form action="" method="POST">
                                        <div class="modal-body p-4">
                                            <input type="hidden" name="id_basis" value="<?= $row['id_basis'] ?>">
                                            <div class="p-3 rounded-3 mb-3" style="background:#f8fafc;">
                                                <div class="row g-2 text-sm">
                                                    <div class="col-6">
                                                        <div class="text-muted" style="font-size:0.78rem;">Penyakit</div>
                                                        <div class="fw-semibold" style="font-size:0.9rem;">
                                                            <?= htmlspecialchars($row['nama_penyakit'] ?? $row['kode_penyakit']) ?>
                                                        </div>
                                                    </div>
                                                    <div class="col-6">
                                                        <div class="text-muted" style="font-size:0.78rem;">Gejala</div>
                                                        <div class="fw-semibold" style="font-size:0.9rem;">
                                                            <?= htmlspecialchars($row['nama_gejala'] ?? $row['kode_gejala']) ?>
                                                        </div>
                                                    </div>
                                                </div>
                                            </div>
                                            <div class="row mb-2">
                                                <div class="col-md-6">
                                                    <label class="form-label">Jumlah Pasien Terkena Gejala</label>
                                                    <input type="number" name="pasien_terkena" class="form-control"
                                                           min="0" placeholder="Contoh: 10" required>
                                                </div>
                                                <div class="col-md-6">
                                                    <label class="form-label">Jumlah Total Kasus Penyakit</label>
                                                    <input type="number" name="total_kasus" class="form-control"
                                                           min="1" placeholder="Contoh: 20" required>
                                                </div>
                                            </div>
                                            <div class="form-text mb-2">
                                                <i class="bi bi-info-circle me-1"></i>
                                                Nilai P(G|P) saat ini: <strong><?= number_format($row['probabilitas'], 4) ?></strong>. 
                                                Isi jumlah pasien dan total kasus untuk menghitung nilai baru.
                                            </div>
                                        </div>
                                        <div class="modal-footer">
                                            <button type="button" class="btn btn-outline-secondary" data-bs-dismiss="modal">Batal</button>
                                            <button type="submit" name="edit" class="btn btn-brand">
                                                <i class="bi bi-save me-1"></i>Simpan
                                            </button>
                                        </div>
                                    </form>
                                </div>
                            </div>
                        </div>

                        <?php endwhile; else: ?>
                        <tr>
                            <td colspan="6" class="text-center py-5 text-muted">
                                <i class="bi bi-inbox fs-2 d-block mb-2"></i>
                                <?= $filter_penyakit ? "Belum ada aturan untuk penyakit <strong>$filter_penyakit</strong>." : 'Basis pengetahuan masih kosong.' ?>
                                <br>
                                <button type="button" class="btn btn-brand btn-sm mt-3"
                                        data-bs-toggle="modal" data-bs-target="#modalTambah">
                                    <i class="bi bi-plus me-1"></i>Tambah Aturan Pertama
                                </button>
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

<!-- Modal Tambah -->
<div class="modal fade" id="modalTambah" tabindex="-1" aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered">
        <div class="modal-content">
            <div class="modal-header" style="background:var(--brand-gradient);">
                <h5 class="modal-title text-white">
                    <i class="bi bi-plus-circle me-2"></i>Tambah Aturan Basis Pengetahuan
                </h5>
                <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal"></button>
            </div>
            <form action="" method="POST">
                <div class="modal-body p-4">
                    <div class="mb-3">
                        <label class="form-label">Penyakit</label>
                        <select name="kode_penyakit" class="form-select" required>
                            <option value="" disabled selected>— Pilih Penyakit —</option>
                            <?php
                            $res_p2 = mysqli_query($conn, "SELECT * FROM penyakit ORDER BY kode_penyakit ASC");
                            while ($p = mysqli_fetch_assoc($res_p2)):
                            ?>
                            <option value="<?= $p['kode_penyakit'] ?>"
                                <?= $filter_penyakit === $p['kode_penyakit'] ? 'selected' : '' ?>>
                                [<?= $p['kode_penyakit'] ?>] <?= htmlspecialchars($p['nama_penyakit']) ?>
                            </option>
                            <?php endwhile; ?>
                        </select>
                    </div>
                    <div class="mb-3">
                        <label class="form-label">Gejala</label>
                        <select name="kode_gejala" class="form-select" required>
                            <option value="" disabled selected>— Pilih Gejala —</option>
                            <?php
                            $res_g2 = mysqli_query($conn, "SELECT * FROM gejala ORDER BY kode_gejala ASC");
                            while ($g = mysqli_fetch_assoc($res_g2)):
                            ?>
                            <option value="<?= $g['kode_gejala'] ?>">
                                [<?= $g['kode_gejala'] ?>] <?= htmlspecialchars($g['nama_gejala']) ?>
                            </option>
                            <?php endwhile; ?>
                        </select>
                    </div>
                    <div class="row mb-2">
                        <div class="col-md-6">
                            <label class="form-label">Jumlah Pasien Terkena Gejala</label>
                            <input type="number" name="pasien_terkena" class="form-control"
                                   min="0" placeholder="Contoh: 10" required>
                        </div>
                        <div class="col-md-6">
                            <label class="form-label">Jumlah Total Kasus Penyakit</label>
                            <input type="number" name="total_kasus" class="form-control"
                                   min="1" placeholder="Contoh: 20" required>
                        </div>
                    </div>
                    <div class="form-text mb-2">
                        <i class="bi bi-info-circle me-1"></i>
                        Nilai P(G|P) akan dihitung otomatis (Pasien Terkena / Total Kasus).
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-outline-secondary" data-bs-dismiss="modal">Batal</button>
                    <button type="submit" name="tambah" class="btn btn-brand">
                        <i class="bi bi-save me-1"></i>Simpan Data
                    </button>
                </div>
            </form>
        </div>
    </div>
</div>

<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>
