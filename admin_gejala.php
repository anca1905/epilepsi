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
    $kode = mysqli_real_escape_string($conn, $_POST['kode_gejala']);
    $nama = mysqli_real_escape_string($conn, $_POST['nama_gejala']);

    $cek = mysqli_query($conn, "SELECT * FROM gejala WHERE kode_gejala = '$kode'");
    if (mysqli_num_rows($cek) > 0) {
        $pesan = "<div class='alert alert-warning'><i class='bi bi-exclamation-triangle me-2'></i>Kode Gejala <strong>$kode</strong> sudah digunakan!</div>";
    } else {
        $insert = mysqli_query($conn, "INSERT INTO gejala (kode_gejala, nama_gejala) VALUES ('$kode', '$nama')");
        if ($insert) {
            $pesan = "<div class='alert alert-success'><i class='bi bi-check-circle me-2'></i>Data gejala berhasil ditambahkan!</div>";
        } else {
            $pesan = "<div class='alert alert-danger'><i class='bi bi-x-circle me-2'></i>Gagal menambah data: " . mysqli_error($conn) . "</div>";
        }
    }
}

// 2. PROSES EDIT DATA
if (isset($_POST['edit'])) {
    $kode_lama = mysqli_real_escape_string($conn, $_POST['kode_lama']);
    $kode_baru = mysqli_real_escape_string($conn, $_POST['kode_gejala']);
    $nama      = mysqli_real_escape_string($conn, $_POST['nama_gejala']);

    $update = mysqli_query($conn, "UPDATE gejala SET kode_gejala = '$kode_baru', nama_gejala = '$nama' WHERE kode_gejala = '$kode_lama'");
    if ($update) {
        $pesan = "<div class='alert alert-success'><i class='bi bi-check-circle me-2'></i>Data gejala berhasil diperbarui!</div>";
    } else {
        $pesan = "<div class='alert alert-danger'><i class='bi bi-x-circle me-2'></i>Gagal memperbarui data: " . mysqli_error($conn) . "</div>";
    }
}

// 3. PROSES HAPUS DATA
if (isset($_GET['hapus'])) {
    $kode_hapus = mysqli_real_escape_string($conn, $_GET['hapus']);
    $hapus = mysqli_query($conn, "DELETE FROM gejala WHERE kode_gejala = '$kode_hapus'");
    if ($hapus) {
        header("Location: admin_gejala.php?pesan=hapus_sukses");
        exit();
    } else {
        $pesan = "<div class='alert alert-danger'><i class='bi bi-x-circle me-2'></i>Gagal menghapus! Pastikan data ini tidak sedang digunakan di Basis Pengetahuan.</div>";
    }
}

if (isset($_GET['pesan']) && $_GET['pesan'] == 'hapus_sukses') {
    $pesan = "<div class='alert alert-success'><i class='bi bi-check-circle me-2'></i>Data gejala berhasil dihapus!</div>";
}

$query_gejala = mysqli_query($conn, "SELECT * FROM gejala ORDER BY kode_gejala ASC");

$page_title  = 'Data Gejala';
$active_page = 'gejala';
require 'layout/head_admin.php';
require 'layout/sidebar_admin.php';
?>

<!-- MAIN CONTENT -->
<div class="admin-main">

    <!-- Topbar -->
    <div class="admin-topbar">
        <h1 class="page-heading"><i class="bi bi-file-earmark-medical me-2 text-success"></i>Data Gejala</h1>
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
                <h2 class="fw-bold mb-0" style="font-size:1.35rem;">Manajemen Data Gejala</h2>
                <p class="text-muted mb-0" style="font-size:0.85rem;">Kelola daftar gejala yang digunakan dalam proses diagnosa.</p>
            </div>
            <button type="button" class="btn btn-brand" data-bs-toggle="modal" data-bs-target="#modalTambah">
                <i class="bi bi-plus-lg me-1"></i>Tambah Gejala
            </button>
        </div>

        <?= $pesan ?>

        <div class="card-admin p-4">
            <div class="table-responsive">
                <table class="table table-hover align-middle mb-0">
                    <thead>
                        <tr>
                            <th class="text-center" style="width:5%;">No</th>
                            <th style="width:15%;">Kode Gejala</th>
                            <th>Nama Gejala</th>
                            <th class="text-center" style="width:120px;">Aksi</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php 
                        $no = 1;
                        if (mysqli_num_rows($query_gejala) > 0):
                            while ($row = mysqli_fetch_assoc($query_gejala)):
                        ?>
                        <tr>
                            <td class="text-center text-muted"><?= $no++ ?></td>
                            <td>
                                <span class="badge rounded-pill" 
                                      style="background:rgba(26,127,90,0.1);color:var(--brand-primary);font-weight:700;font-size:0.8rem;padding:5px 10px;">
                                    <?= $row['kode_gejala'] ?>
                                </span>
                            </td>
                            <td><?= htmlspecialchars($row['nama_gejala']) ?></td>
                            <td class="text-center">
                                <button class="btn btn-sm btn-outline-primary me-1"
                                        data-bs-toggle="modal" data-bs-target="#modalEdit<?= $row['kode_gejala'] ?>"
                                        title="Edit">
                                    <i class="bi bi-pencil"></i>
                                </button>
                                <a href="admin_gejala.php?hapus=<?= $row['kode_gejala'] ?>"
                                   class="btn btn-sm btn-outline-danger"
                                   onclick="return confirm('Yakin ingin menghapus gejala ini?')"
                                   title="Hapus">
                                    <i class="bi bi-trash"></i>
                                </a>
                            </td>
                        </tr>

                        <!-- Modal Edit -->
                        <div class="modal fade" id="modalEdit<?= $row['kode_gejala'] ?>" tabindex="-1" aria-hidden="true">
                            <div class="modal-dialog modal-dialog-centered">
                                <div class="modal-content">
                                    <div class="modal-header" style="background:var(--brand-gradient);">
                                        <h5 class="modal-title text-white">
                                            <i class="bi bi-pencil-square me-2"></i>Edit Gejala
                                        </h5>
                                        <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal"></button>
                                    </div>
                                    <form action="" method="POST">
                                        <div class="modal-body p-4">
                                            <input type="hidden" name="kode_lama" value="<?= $row['kode_gejala'] ?>">
                                            <div class="mb-3">
                                                <label class="form-label">Kode Gejala</label>
                                                <input type="text" name="kode_gejala" class="form-control"
                                                       value="<?= $row['kode_gejala'] ?>" required>
                                            </div>
                                            <div class="mb-2">
                                                <label class="form-label">Nama Gejala</label>
                                                <input type="text" name="nama_gejala" class="form-control"
                                                       value="<?= htmlspecialchars($row['nama_gejala']) ?>" required>
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
                            <td colspan="4" class="text-center py-5 text-muted">
                                <i class="bi bi-inbox fs-3 d-block mb-2"></i>
                                Data gejala belum tersedia.
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
                    <i class="bi bi-plus-circle me-2"></i>Tambah Gejala Baru
                </h5>
                <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal"></button>
            </div>
            <form action="" method="POST">
                <div class="modal-body p-4">
                    <div class="mb-3">
                        <label class="form-label">Kode Gejala</label>
                        <input type="text" name="kode_gejala" class="form-control" placeholder="Contoh: G01" required>
                    </div>
                    <div class="mb-2">
                        <label class="form-label">Nama Gejala</label>
                        <input type="text" name="nama_gejala" class="form-control" placeholder="Contoh: Hilangnya kesadaran" required>
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