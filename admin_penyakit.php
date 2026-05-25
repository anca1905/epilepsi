<?php
session_start();
require 'koneksi.php';

if (!isset($_SESSION['login_admin'])) {
    header("Location: login_admin.php");
    exit();
}

$pesan = "";

// 1. PROSES TAMBAH DATA
if (isset($_POST['tambah'])) {
    $kode       = mysqli_real_escape_string($conn, $_POST['kode_penyakit']);
    $nama       = mysqli_real_escape_string($conn, $_POST['nama_penyakit']);
    $keterangan = mysqli_real_escape_string($conn, $_POST['keterangan']);
    $solusi     = mysqli_real_escape_string($conn, $_POST['solusi']);
    $prior      = mysqli_real_escape_string($conn, $_POST['probabilitas_prior']);

    $cek = mysqli_query($conn, "SELECT * FROM penyakit WHERE kode_penyakit = '$kode'");
    if (mysqli_num_rows($cek) > 0) {
        $pesan = "<div class='alert alert-warning'><i class='bi bi-exclamation-triangle me-2'></i>Kode Penyakit <strong>$kode</strong> sudah digunakan!</div>";
    } else {
        $insert = mysqli_query($conn, "INSERT INTO penyakit (kode_penyakit, nama_penyakit, keterangan, solusi, probabilitas_prior) VALUES ('$kode', '$nama', '$keterangan', '$solusi', '$prior')");
        if ($insert) {
            $pesan = "<div class='alert alert-success'><i class='bi bi-check-circle me-2'></i>Data penyakit berhasil ditambahkan!</div>";
        } else {
            $pesan = "<div class='alert alert-danger'><i class='bi bi-x-circle me-2'></i>Gagal menambah data: " . mysqli_error($conn) . "</div>";
        }
    }
}

// 2. PROSES EDIT DATA
if (isset($_POST['edit'])) {
    $kode_lama  = mysqli_real_escape_string($conn, $_POST['kode_lama']);
    $kode_baru  = mysqli_real_escape_string($conn, $_POST['kode_penyakit']);
    $nama       = mysqli_real_escape_string($conn, $_POST['nama_penyakit']);
    $keterangan = mysqli_real_escape_string($conn, $_POST['keterangan']);
    $solusi     = mysqli_real_escape_string($conn, $_POST['solusi']);
    $prior      = mysqli_real_escape_string($conn, $_POST['probabilitas_prior']);

    $update = mysqli_query($conn, "UPDATE penyakit SET kode_penyakit = '$kode_baru', nama_penyakit = '$nama', keterangan = '$keterangan', solusi = '$solusi', probabilitas_prior = '$prior' WHERE kode_penyakit = '$kode_lama'");
    if ($update) {
        $pesan = "<div class='alert alert-success'><i class='bi bi-check-circle me-2'></i>Data penyakit berhasil diperbarui!</div>";
    } else {
        $pesan = "<div class='alert alert-danger'><i class='bi bi-x-circle me-2'></i>Gagal memperbarui data: " . mysqli_error($conn) . "</div>";
    }
}

// 3. PROSES HAPUS DATA
if (isset($_GET['hapus'])) {
    $kode_hapus = mysqli_real_escape_string($conn, $_GET['hapus']);
    $hapus = mysqli_query($conn, "DELETE FROM penyakit WHERE kode_penyakit = '$kode_hapus'");
    if ($hapus) {
        header("Location: admin_penyakit.php?pesan=hapus_sukses");
        exit();
    } else {
        $pesan = "<div class='alert alert-danger'><i class='bi bi-x-circle me-2'></i>Gagal menghapus! Pastikan data tidak sedang digunakan di tabel lain.</div>";
    }
}

if (isset($_GET['pesan']) && $_GET['pesan'] == 'hapus_sukses') {
    $pesan = "<div class='alert alert-success'><i class='bi bi-check-circle me-2'></i>Data penyakit berhasil dihapus!</div>";
}

$query_penyakit = mysqli_query($conn, "SELECT * FROM penyakit ORDER BY kode_penyakit ASC");

$page_title  = 'Data Penyakit';
$active_page = 'penyakit';
require 'layout/head_admin.php';
require 'layout/sidebar_admin.php';
?>

<!-- MAIN CONTENT -->
<div class="admin-main">

    <!-- Topbar -->
    <div class="admin-topbar">
        <h1 class="page-heading"><i class="bi bi-virus me-2 text-primary"></i>Data Penyakit</h1>
        <div class="topbar-right">
            <span class="date-chip"><i class="bi bi-calendar3 me-1"></i><?= date('d M Y') ?></span>
            <div class="user-chip">
                <div class="avatar"><?= strtoupper(substr($_SESSION['username'], 0, 1)) ?></div>
                <?= htmlspecialchars($_SESSION['username']) ?>
            </div>
        </div>
    </div>

    <div class="admin-content">

        <!-- Header Actions -->
        <div class="d-flex justify-content-between align-items-center mb-4">
            <div>
                <h2 class="fw-bold mb-0" style="font-size:1.35rem;">Manajemen Data Penyakit</h2>
                <p class="text-muted mb-0" style="font-size:0.85rem;">Kelola daftar jenis penyakit epilepsi beserta keterangan dan solusinya.</p>
            </div>
            <button type="button" class="btn btn-brand" data-bs-toggle="modal" data-bs-target="#modalTambah">
                <i class="bi bi-plus-lg me-1"></i>Tambah Penyakit
            </button>
        </div>

        <?= $pesan ?>

        <div class="card-admin p-4">
            <div class="table-responsive">
                <table class="table table-hover align-middle mb-0">
                    <thead>
                        <tr>
                            <th class="text-center" style="width:5%;">No</th>
                            <th style="width:9%;">Kode</th>
                            <th style="width:18%;">Nama Penyakit</th>
                            <th style="width:20%;">Keterangan</th>
                            <th style="width:20%;">Solusi Penanganan</th>
                            <th class="text-center" style="width:10%;">Prior P(Pi)</th>
                            <th class="text-center" style="width:13%;">Aksi</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php
                        $no = 1;
                        if (mysqli_num_rows($query_penyakit) > 0):
                            while ($row = mysqli_fetch_assoc($query_penyakit)):
                        ?>
                        <tr>
                            <td class="text-center text-muted"><?= $no++ ?></td>
                            <td>
                                <span class="badge rounded-pill"
                                      style="background:rgba(13,110,253,0.1);color:#0d6efd;font-weight:700;font-size:0.8rem;padding:5px 10px;">
                                    <?= $row['kode_penyakit'] ?>
                                </span>
                            </td>
                            <td class="fw-semibold"><?= htmlspecialchars($row['nama_penyakit']) ?></td>
                            <td>
                                <div class="text-muted" style="font-size:0.87rem;display:-webkit-box;-webkit-line-clamp:2;-webkit-box-orient:vertical;overflow:hidden;">
                                    <?= htmlspecialchars($row['keterangan']) ?>
                                </div>
                            </td>
                            <td>
                                <div class="text-muted" style="font-size:0.87rem;display:-webkit-box;-webkit-line-clamp:2;-webkit-box-orient:vertical;overflow:hidden;">
                                    <?= htmlspecialchars($row['solusi']) ?>
                                </div>
                            </td>
                            <td class="text-center">
                                <span class="badge rounded-pill fw-bold" style="background:rgba(6,78,59,0.1);color:#065f46;font-size:0.82rem;padding:5px 10px;">
                                    <?= number_format((float)$row['probabilitas_prior'], 2) ?>
                                </span>
                            </td>
                            <td class="text-center">
                                <button class="btn btn-sm btn-outline-primary me-1"
                                        data-bs-toggle="modal" data-bs-target="#modalEdit<?= $row['kode_penyakit'] ?>"
                                        title="Edit">
                                    <i class="bi bi-pencil"></i>
                                </button>
                                <a href="admin_penyakit.php?hapus=<?= $row['kode_penyakit'] ?>"
                                   class="btn btn-sm btn-outline-danger"
                                   onclick="return confirm('Yakin ingin menghapus penyakit ini?')"
                                   title="Hapus">
                                    <i class="bi bi-trash"></i>
                                </a>
                            </td>
                        </tr>

                        <!-- Modal Edit -->
                        <div class="modal fade" id="modalEdit<?= $row['kode_penyakit'] ?>" tabindex="-1" aria-hidden="true">
                            <div class="modal-dialog modal-lg modal-dialog-centered">
                                <div class="modal-content">
                                    <div class="modal-header" style="background:var(--brand-gradient);">
                                        <h5 class="modal-title text-white">
                                            <i class="bi bi-pencil-square me-2"></i>Edit Penyakit
                                        </h5>
                                        <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal"></button>
                                    </div>
                                    <form action="" method="POST">
                                        <div class="modal-body p-4">
                                            <input type="hidden" name="kode_lama" value="<?= $row['kode_penyakit'] ?>">
                                            <div class="row">
                                                <div class="col-md-4 mb-3">
                                                    <label class="form-label">Kode Penyakit</label>
                                                    <input type="text" name="kode_penyakit" class="form-control"
                                                           value="<?= $row['kode_penyakit'] ?>" required>
                                                </div>
                                                <div class="col-md-8 mb-3">
                                                    <label class="form-label">Nama Penyakit</label>
                                                    <input type="text" name="nama_penyakit" class="form-control"
                                                           value="<?= htmlspecialchars($row['nama_penyakit']) ?>" required>
                                                </div>
                                            </div>
                                            <div class="mb-3">
                                                <label class="form-label">Keterangan / Deskripsi</label>
                                                <textarea name="keterangan" class="form-control" rows="3" required><?= htmlspecialchars($row['keterangan']) ?></textarea>
                                            </div>
                                            <div class="mb-3">
                                                <label class="form-label">Solusi Penanganan Awal</label>
                                                <textarea name="solusi" class="form-control" rows="3" required><?= htmlspecialchars($row['solusi']) ?></textarea>
                                            </div>
                                            <div class="mb-2">
                                                <label class="form-label d-flex align-items-center gap-1">
                                                    Probabilitas Prior P(Pi)
                                                    <span class="badge bg-info bg-opacity-10 text-info" style="font-size:0.7rem;font-weight:500;">Untuk Bayes</span>
                                                </label>
                                                <input type="number" name="probabilitas_prior" class="form-control"
                                                       value="<?= $row['probabilitas_prior'] ?>"
                                                       step="0.01" min="0" max="1" required>
                                                <div class="form-text"><i class="bi bi-info-circle me-1"></i>Nilai antara 0–1. Total semua penyakit harus = 1.</div>
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
                                <i class="bi bi-inbox fs-3 d-block mb-2"></i>
                                Data penyakit belum tersedia.
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
    <div class="modal-dialog modal-lg modal-dialog-centered">
        <div class="modal-content">
            <div class="modal-header" style="background:var(--brand-gradient);">
                <h5 class="modal-title text-white">
                    <i class="bi bi-plus-circle me-2"></i>Tambah Penyakit Baru
                </h5>
                <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal"></button>
            </div>
            <form action="" method="POST">
                <div class="modal-body p-4">
                    <div class="row">
                        <div class="col-md-4 mb-3">
                            <label class="form-label">Kode Penyakit</label>
                            <input type="text" name="kode_penyakit" class="form-control" placeholder="Contoh: P01" required>
                        </div>
                        <div class="col-md-8 mb-3">
                            <label class="form-label">Nama Penyakit</label>
                            <input type="text" name="nama_penyakit" class="form-control" placeholder="Contoh: Epilepsi Parsial Sederhana" required>
                        </div>
                    </div>
                    <div class="mb-3">
                        <label class="form-label">Keterangan / Deskripsi</label>
                        <textarea name="keterangan" class="form-control" rows="3" placeholder="Masukkan penjelasan terkait penyakit ini..." required></textarea>
                    </div>
                    <div class="mb-3">
                        <label class="form-label">Solusi Penanganan Awal</label>
                        <textarea name="solusi" class="form-control" rows="3" placeholder="Contoh: Amankan lingkungan sekitar anak..." required></textarea>
                    </div>
                    <div class="mb-2">
                        <label class="form-label d-flex align-items-center gap-1">
                            Probabilitas Prior P(Pi)
                            <span class="badge bg-info bg-opacity-10 text-info" style="font-size:0.7rem;font-weight:500;">Untuk Bayes</span>
                        </label>
                        <input type="number" name="probabilitas_prior" class="form-control"
                               step="0.01" min="0" max="1" placeholder="Contoh: 0.50" required>
                        <div class="form-text"><i class="bi bi-info-circle me-1"></i>Nilai antara 0–1. Total semua penyakit sebaiknya = 1.</div>
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