<?php
session_start();
require 'koneksi.php';

if (!isset($_SESSION['login_admin'])) {
    header("Location: login_admin.php");
    exit();
}

$pesan = "";

// ── 1. TAMBAH ATURAN
if (isset($_POST['tambah'])) {
    $kode_penyakit = mysqli_real_escape_string($conn, $_POST['kode_penyakit']);
    $kode_gejala   = mysqli_real_escape_string($conn, $_POST['kode_gejala']);
    $probabilitas  = (float)$_POST['probabilitas'];

    // Cek duplikat
    $cek = mysqli_query($conn, "SELECT id_basis FROM basis_pengetahuan WHERE kode_penyakit='$kode_penyakit' AND kode_gejala='$kode_gejala'");
    if (mysqli_num_rows($cek) > 0) {
        $pesan = "<div class='alert alert-warning'><i class='bi bi-exclamation-circle me-2'></i>Aturan untuk kombinasi <strong>$kode_penyakit</strong> + <strong>$kode_gejala</strong> sudah ada. Gunakan fitur Edit.</div>";
    } else {
        $ins = mysqli_query($conn, "INSERT INTO basis_pengetahuan (kode_penyakit, kode_gejala, probabilitas) VALUES ('$kode_penyakit','$kode_gejala','$probabilitas')");
        $pesan = $ins
            ? "<div class='alert alert-success'><i class='bi bi-check-circle me-2'></i>Aturan berhasil ditambahkan!</div>"
            : "<div class='alert alert-danger'>Gagal: " . mysqli_error($conn) . "</div>";
    }
}

// ── 2. EDIT ATURAN
if (isset($_POST['edit'])) {
    $id_basis     = (int)$_POST['id_basis'];
    $probabilitas = (float)$_POST['probabilitas'];
    $upd = mysqli_query($conn, "UPDATE basis_pengetahuan SET probabilitas='$probabilitas' WHERE id_basis=$id_basis");
    $pesan = $upd
        ? "<div class='alert alert-success'><i class='bi bi-check-circle me-2'></i>Probabilitas berhasil diperbarui!</div>"
        : "<div class='alert alert-danger'>Gagal: " . mysqli_error($conn) . "</div>";
}

// ── 3. HAPUS ATURAN
if (isset($_GET['hapus'])) {
    $id_hapus = (int)$_GET['hapus'];
    $del = mysqli_query($conn, "DELETE FROM basis_pengetahuan WHERE id_basis=$id_hapus");
    if ($del) {
        header("Location: admin_basis.php?pesan=hapus_sukses");
        exit();
    } else {
        $pesan = "<div class='alert alert-danger'>Gagal menghapus.</div>";
    }
}
if (isset($_GET['pesan']) && $_GET['pesan'] == 'hapus_sukses') {
    $pesan = "<div class='alert alert-success'><i class='bi bi-check-circle me-2'></i>Aturan berhasil dihapus!</div>";
}

// ── Ambil data filter
$filter_penyakit = isset($_GET['filter_penyakit']) ? mysqli_real_escape_string($conn, $_GET['filter_penyakit']) : '';

// ── Ambil semua penyakit & gejala untuk dropdown
$query_penyakit = mysqli_query($conn, "SELECT * FROM penyakit ORDER BY kode_penyakit ASC");
$penyakit_data  = [];
$penyakit_list  = [];
while ($p = mysqli_fetch_assoc($query_penyakit)) {
    $penyakit_data[$p['kode_penyakit']] = $p['nama_penyakit'];
    $penyakit_list[] = $p;
}

$query_gejala = mysqli_query($conn, "SELECT * FROM gejala ORDER BY kode_gejala ASC");
$gejala_data  = [];
$gejala_list  = [];
while ($g = mysqli_fetch_assoc($query_gejala)) {
    $gejala_data[$g['kode_gejala']] = $g['nama_gejala'];
    $gejala_list[] = $g;
}

// ── Ambil basis pengetahuan dari DB + filter
$where = $filter_penyakit ? "WHERE b.kode_penyakit='$filter_penyakit'" : '';
$query_basis = mysqli_query(
    $conn,
    "SELECT b.*, p.nama_penyakit, g.nama_gejala
     FROM basis_pengetahuan b
     LEFT JOIN penyakit p ON b.kode_penyakit = p.kode_penyakit
     LEFT JOIN gejala g ON b.kode_gejala = g.kode_gejala
     $where
     ORDER BY b.kode_penyakit ASC, b.kode_gejala ASC"
);
$basis_list = [];
while ($b = mysqli_fetch_assoc($query_basis)) {
    $basis_list[] = $b;
}

// ── Summary per penyakit (untuk cards)
$summary_query = mysqli_query($conn, "SELECT kode_penyakit, COUNT(*) as total FROM basis_pengetahuan GROUP BY kode_penyakit");
$summary_map   = [];
$total_basis   = 0;
while ($s = mysqli_fetch_assoc($summary_query)) {
    $summary_map[$s['kode_penyakit']] = $s['total'];
    $total_basis += $s['total'];
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
        <div class="d-flex justify-content-between align-items-center mb-4">
            <div>
                <h2 class="fw-bold mb-0" style="font-size:1.35rem;">Nilai P(G|P) — Basis Pengetahuan</h2>
                <p class="text-muted mb-0" style="font-size:0.85rem;">
                    Probabilitas kemunculan gejala untuk setiap penyakit. Data ini digunakan langsung oleh mesin diagnosa Bayes.
                </p>
            </div>
            <button type="button" class="btn btn-brand" data-bs-toggle="modal" data-bs-target="#modalTambah">
                <i class="bi bi-plus-lg me-1"></i>Tambah Aturan
            </button>
        </div>

        <?= $pesan ?>

        <!-- Summary Cards -->
        <?php if (!empty($penyakit_list)): ?>
            <div class="row g-3 mb-4">
                <?php foreach ($penyakit_list as $p): ?>
                    <div class="col-sm-6 col-xl-4">
                        <a href="admin_basis.php?filter_penyakit=<?= $p['kode_penyakit'] ?>"
                            class="d-block text-decoration-none">
                            <div class="stat-card <?= $filter_penyakit === $p['kode_penyakit'] ? 'border border-2 border-primary' : '' ?>"
                                style="cursor:pointer;">
                                <div>
                                    <div class="stat-label"><?= htmlspecialchars($p['nama_penyakit']) ?></div>
                                    <div class="stat-value" style="font-size:1.6rem;">
                                        <?= $summary_map[$p['kode_penyakit']] ?? 0 ?>
                                    </div>
                                    <div style="font-size:0.75rem;color:#94a3b8;">Aturan Gejala</div>
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
                <div class="d-flex align-items-center gap-2 flex-wrap">
                    <i class="bi bi-funnel text-muted"></i>
                    <span class="fw-semibold" style="font-size:0.9rem;">Filter:</span>
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
                <span class="badge bg-secondary" style="font-size:0.8rem;">
                    Total: <?= $total_basis ?> aturan
                </span>
            </div>

            <div class="table-responsive">
                <table class="table table-hover align-middle mb-0">
                    <thead>
                        <tr>
                            <th class="text-center" style="width:5%;">No</th>
                            <th style="width:28%;">Penyakit</th>
                            <th style="width:35%;">Gejala</th>
                            <th class="text-center" style="width:15%;">P(G|P)</th>
                            <th class="text-center" style="width:17%;">Aksi</th>
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
                                    <td class="text-center">
                                        <span class="fw-bold" style="font-size:1rem;color:#0f172a;">
                                            <?= number_format((float)$row['probabilitas'], 4) ?>
                                        </span>
                                    </td>
                                    <td class="text-center">
                                        <button class="btn btn-sm btn-outline-primary me-1"
                                            data-bs-toggle="modal"
                                            data-bs-target="#modalEdit<?= $row['id_basis'] ?>"
                                            title="Edit Probabilitas">
                                            <i class="bi bi-pencil"></i>
                                        </button>
                                        <a href="admin_basis.php?hapus=<?= $row['id_basis'] ?>"
                                            class="btn btn-sm btn-outline-danger"
                                            onclick="return confirm('Hapus aturan <?= $row['kode_penyakit'] ?> + <?= $row['kode_gejala'] ?>?')"
                                            title="Hapus">
                                            <i class="bi bi-trash"></i>
                                        </a>
                                    </td>
                                </tr>

                                <!-- Modal Edit per baris -->
                                <div class="modal fade" id="modalEdit<?= $row['id_basis'] ?>" tabindex="-1" aria-hidden="true">
                                    <div class="modal-dialog modal-dialog-centered modal-sm">
                                        <div class="modal-content">
                                            <div class="modal-header" style="background:var(--brand-gradient);">
                                                <h5 class="modal-title text-white" style="font-size:0.95rem;">
                                                    <i class="bi bi-pencil-square me-2"></i>Edit Probabilitas
                                                </h5>
                                                <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal"></button>
                                            </div>
                                            <form action="" method="POST">
                                                <div class="modal-body p-4 text-start">
                                                    <input type="hidden" name="id_basis" value="<?= $row['id_basis'] ?>">
                                                    <div class="mb-2 small text-muted">
                                                        <strong><?= htmlspecialchars($row['kode_penyakit']) ?></strong> →
                                                        <?= htmlspecialchars($row['kode_gejala']) ?>
                                                        <br><?= htmlspecialchars($row['nama_penyakit'] ?? '') ?>
                                                        – <?= htmlspecialchars($row['nama_gejala'] ?? '') ?>
                                                    </div>
                                                    <div class="mb-3">
                                                        <label class="form-label fw-semibold">Nilai P(G|P)</label>
                                                        <input type="number" name="probabilitas" class="form-control"
                                                            step="0.0001" min="0" max="1"
                                                            value="<?= $row['probabilitas'] ?>" required>
                                                        <div class="form-text">Nilai antara 0 dan 1</div>
                                                    </div>
                                                </div>
                                                <div class="modal-footer">
                                                    <button type="button" class="btn btn-outline-secondary btn-sm" data-bs-dismiss="modal">Batal</button>
                                                    <button type="submit" name="edit" class="btn btn-brand btn-sm">
                                                        <i class="bi bi-save me-1"></i>Simpan
                                                    </button>
                                                </div>
                                            </form>
                                        </div>
                                    </div>
                                </div>

                            <?php endforeach;
                        else: ?>
                            <tr>
                                <td colspan="5" class="text-center py-5 text-muted">
                                    <i class="bi bi-inbox fs-2 d-block mb-2"></i>
                                    <?= $filter_penyakit ? "Belum ada aturan untuk penyakit <strong>$filter_penyakit</strong>." : 'Basis pengetahuan masih kosong. Klik <strong>Tambah Aturan</strong> untuk mulai mengisi.' ?>
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

<!-- Modal Tambah Aturan -->
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
                <div class="modal-body p-4 text-start">
                    <p class="text-muted small mb-3">
                        Tentukan nilai probabilitas P(G|P) — seberapa besar kemungkinan gejala ini muncul
                        pada penyakit tersebut. Nilai antara <strong>0</strong> (tidak mungkin) sampai <strong>1</strong> (pasti).
                    </p>
                    <div class="mb-3">
                        <label class="form-label fw-semibold">Penyakit</label>
                        <select name="kode_penyakit" class="form-select" required>
                            <option value="">— Pilih Penyakit —</option>
                            <?php foreach ($penyakit_list as $p): ?>
                                <option value="<?= $p['kode_penyakit'] ?>">
                                    <?= $p['kode_penyakit'] ?> — <?= htmlspecialchars($p['nama_penyakit']) ?>
                                </option>
                            <?php endforeach; ?>
                        </select>
                    </div>
                    <div class="mb-3">
                        <label class="form-label fw-semibold">Gejala</label>
                        <select name="kode_gejala" class="form-select" required>
                            <option value="">— Pilih Gejala —</option>
                            <?php foreach ($gejala_list as $g): ?>
                                <option value="<?= $g['kode_gejala'] ?>">
                                    <?= $g['kode_gejala'] ?> — <?= htmlspecialchars($g['nama_gejala']) ?>
                                </option>
                            <?php endforeach; ?>
                        </select>
                    </div>
                    <div class="mb-3">
                        <label class="form-label fw-semibold">Nilai Probabilitas P(G|P)</label>
                        <input type="number" name="probabilitas" class="form-control"
                            step="0.0001" min="0" max="1" placeholder="Contoh: 0.7500" required>
                        <div class="form-text">Masukkan nilai antara 0.0000 dan 1.0000</div>
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-outline-secondary" data-bs-dismiss="modal">Batal</button>
                    <button type="submit" name="tambah" class="btn btn-brand">
                        <i class="bi bi-save me-1"></i>Simpan Aturan
                    </button>
                </div>
            </form>
        </div>
    </div>
</div>

<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
</body>

</html>