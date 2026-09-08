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
    $nama       = mysqli_real_escape_string($conn, $_POST['nama_user']);
    $umur       = (int)$_POST['umur'];
    $alamat     = mysqli_real_escape_string($conn, $_POST['alamat']);
    $no_telpon  = mysqli_real_escape_string($conn, $_POST['no_telpon']);

    $insert = mysqli_query($conn, "INSERT INTO pengguna (nama_user, umur, alamat, no_telpon) VALUES ('$nama', '$umur', '$alamat', '$no_telpon')");
    if ($insert) {
        $pesan = "<div class='alert alert-success'><i class='bi bi-check-circle me-2'></i>Data pasien berhasil ditambahkan!</div>";
    } else {
        $pesan = "<div class='alert alert-danger'><i class='bi bi-x-circle me-2'></i>Gagal menambah data: " . mysqli_error($conn) . "</div>";
    }
}

// 2. PROSES EDIT DATA
if (isset($_POST['edit'])) {
    $id_pengguna = (int)$_POST['id_pengguna'];
    $nama        = mysqli_real_escape_string($conn, $_POST['nama_user']);
    $umur        = (int)$_POST['umur'];
    $alamat      = mysqli_real_escape_string($conn, $_POST['alamat']);
    $no_telpon   = mysqli_real_escape_string($conn, $_POST['no_telpon']);

    $update = mysqli_query($conn, "UPDATE pengguna SET nama_user = '$nama', umur = '$umur', alamat = '$alamat', no_telpon = '$no_telpon' WHERE id_pengguna = $id_pengguna");
    if ($update) {
        $pesan = "<div class='alert alert-success'><i class='bi bi-check-circle me-2'></i>Data pasien berhasil diperbarui!</div>";
    } else {
        $pesan = "<div class='alert alert-danger'><i class='bi bi-x-circle me-2'></i>Gagal memperbarui data: " . mysqli_error($conn) . "</div>";
    }
}

// 3. PROSES HAPUS DATA
if (isset($_GET['hapus'])) {
    $id_hapus = (int)$_GET['hapus'];
    // Hapus hasil terkait (jika tidak ada cascade)
    mysqli_query($conn, "DELETE FROM riwayat_diagnosa WHERE id_pengguna = $id_hapus");

    $hapus = mysqli_query($conn, "DELETE FROM pengguna WHERE id_pengguna = $id_hapus");
    if ($hapus) {
        header("Location: admin_pasien.php?pesan=hapus_sukses");
        exit();
    } else {
        $pesan = "<div class='alert alert-danger'><i class='bi bi-x-circle me-2'></i>Gagal menghapus! Pasien mungkin masih memiliki riwayat diagnosa.</div>";
    }
}

if (isset($_GET['pesan']) && $_GET['pesan'] == 'hapus_sukses') {
    $pesan = "<div class='alert alert-success'><i class='bi bi-check-circle me-2'></i>Data pasien berhasil dihapus!</div>";
}

$query_pasien = mysqli_query($conn, "SELECT * FROM pengguna ORDER BY id_pengguna DESC");

$page_title  = 'Data Pasien';
$active_page = 'pasien';
require 'layout/head_admin.php';
require 'layout/sidebar_admin.php';
?>

<!-- MAIN CONTENT -->
<div class="admin-main">
    <div class="admin-topbar">
        <h1 class="page-heading"><i class="bi bi-person-lines-fill me-2 text-primary"></i>Data Pasien</h1>
        <div class="topbar-right">
            <span class="date-chip"><i class="bi bi-calendar3 me-1"></i><?= date('d M Y') ?></span>
            <div class="user-chip">
                <div class="avatar"><?= strtoupper(substr($_SESSION['username'], 0, 1)) ?></div>
                <?= htmlspecialchars($_SESSION['username']) ?>
            </div>
        </div>
    </div>

    <div class="admin-content">
        <div class="d-flex justify-content-between align-items-center mb-4">
            <div>
                <h2 class="fw-bold mb-0" style="font-size:1.35rem;">Manajemen Data Pasien</h2>
                <p class="text-muted mb-0" style="font-size:0.85rem;">Daftar pasien dan rekam medis yang terdaftar dalam sistem.</p>
            </div>
            <button type="button" class="btn btn-brand" data-bs-toggle="modal" data-bs-target="#modalTambah">
                <i class="bi bi-plus-lg me-1"></i>Tambah Pasien
            </button>
        </div>

        <?= $pesan ?>

        <div class="card-admin p-4">
            <div class="table-responsive">
                <table class="table table-hover align-middle mb-0">
                    <thead>
                        <tr>
                            <th class="text-center" style="width:5%;">No</th>
                            <th style="width:15%;">Nomor Pasien</th>
                            <th style="width:25%;">Nama Pasien</th>
                            <th style="width:10%;">Umur</th>
                            <th style="width:20%;">No Telpon</th>
                            <th style="width:15%;">Alamat</th>
                            <th class="text-center" style="width:10%;">Aksi</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php
                        $no = 1;
                        if (mysqli_num_rows($query_pasien) > 0):
                            while ($row = mysqli_fetch_assoc($query_pasien)):
                                $nomor_pasien = 'PSN-' . str_pad($row['id_pengguna'], 4, '0', STR_PAD_LEFT);
                        ?>
                                <tr>
                                    <td class="text-center text-muted"><?= $no++ ?></td>
                                    <td>
                                        <span class="badge rounded-pill" style="background:rgba(13,110,253,0.1);color:#0d6efd;font-weight:700;font-size:0.85rem;padding:6px 12px;">
                                            <?= $nomor_pasien ?>
                                        </span>
                                    </td>
                                    <td class="fw-semibold"><?= htmlspecialchars($row['nama_user']) ?></td>
                                    <td><?= htmlspecialchars($row['umur']) ?> Thn</td>
                                    <td><?= htmlspecialchars($row['no_telpon'] ?: '-') ?></td>
                                    <td>
                                        <div class="text-muted" style="font-size:0.87rem;display:-webkit-box;-webkit-line-clamp:2;-webkit-box-orient:vertical;overflow:hidden;">
                                            <?= htmlspecialchars($row['alamat'] ?: '-') ?>
                                        </div>
                                    </td>
                                    <td class="text-center">
                                        <button class="btn btn-sm btn-outline-primary me-1"
                                            data-bs-toggle="modal" data-bs-target="#modalEdit<?= $row['id_pengguna'] ?>"
                                            title="Edit">
                                            <i class="bi bi-pencil"></i>
                                        </button>
                                        <a href="admin_pasien.php?hapus=<?= $row['id_pengguna'] ?>"
                                            class="btn btn-sm btn-outline-danger"
                                            onclick="return confirm('Yakin ingin menghapus data pasien ini? (Mungkin akan menghapus riwayat diagnosa juga)')"
                                            title="Hapus">
                                            <i class="bi bi-trash"></i>
                                        </a>
                                    </td>
                                </tr>

                                <!-- Modal Edit -->
                                <div class="modal fade" id="modalEdit<?= $row['id_pengguna'] ?>" tabindex="-1" aria-hidden="true">
                                    <div class="modal-dialog modal-dialog-centered">
                                        <div class="modal-content">
                                            <div class="modal-header" style="background:var(--brand-gradient);">
                                                <h5 class="modal-title text-white">
                                                    <i class="bi bi-pencil-square me-2"></i>Edit Data Pasien
                                                </h5>
                                                <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal"></button>
                                            </div>
                                            <form action="" method="POST">
                                                <div class="modal-body p-4 text-start">
                                                    <input type="hidden" name="id_pengguna" value="<?= $row['id_pengguna'] ?>">
                                                    <div class="mb-3">
                                                        <label class="form-label">Nama Pasien</label>
                                                        <input type="text" name="nama_user" class="form-control" value="<?= htmlspecialchars($row['nama_user']) ?>" required>
                                                    </div>
                                                    <div class="mb-3">
                                                        <label class="form-label">Umur (Tahun)</label>
                                                        <input type="number" name="umur" class="form-control" value="<?= htmlspecialchars($row['umur']) ?>" required>
                                                    </div>
                                                    <div class="mb-3">
                                                        <label class="form-label">No. Telepon / WhatsApp</label>
                                                        <input type="text" name="no_telpon" class="form-control" value="<?= htmlspecialchars($row['no_telpon']) ?>" required>
                                                    </div>
                                                    <div class="mb-3">
                                                        <label class="form-label">Alamat Lengkap</label>
                                                        <textarea name="alamat" class="form-control" rows="3" required><?= htmlspecialchars($row['alamat']) ?></textarea>
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
                            <?php endwhile;
                        else: ?>
                            <tr>
                                <td colspan="7" class="text-center py-5 text-muted">
                                    <i class="bi bi-inbox fs-3 d-block mb-2"></i>
                                    Belum ada data pasien.
                                </td>
                            </tr>
                        <?php endif; ?>
                    </tbody>
                </table>
            </div>
        </div>
    </div>
</div>
</div>

<!-- Modal Tambah -->
<div class="modal fade" id="modalTambah" tabindex="-1" aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered">
        <div class="modal-content">
            <div class="modal-header" style="background:var(--brand-gradient);">
                <h5 class="modal-title text-white">
                    <i class="bi bi-person-plus me-2"></i>Tambah Pasien Baru
                </h5>
                <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal"></button>
            </div>
            <form action="" method="POST">
                <div class="modal-body p-4 text-start">
                    <div class="mb-3 position-relative">
                        <label class="form-label">Nama Pasien</label>
                        <input type="text" id="tambahNamaInput" name="nama_user" class="form-control"
                            placeholder="Masukkan nama lengkap pasien" autocomplete="off" required>
                        <!-- Autocomplete dropdown admin -->
                        <div id="adminAutocompleteDrop"
                            class="border rounded-3 bg-white shadow-sm d-none"
                            style="position:absolute;top:100%;left:0;right:0;z-index:1055;max-height:220px;overflow-y:auto;">
                        </div>
                        <div id="adminNamaWarning" class="mt-2 d-none">
                            <div class="alert alert-warning py-2 px-3 mb-0 small">
                                <i class="bi bi-exclamation-circle me-1"></i>
                                <span id="adminNamaWarningText"></span>
                            </div>
                        </div>
                    </div>
                    <div class="mb-3">
                        <label class="form-label">Umur (Tahun)</label>
                        <input type="number" name="umur" class="form-control" placeholder="Contoh: 5" required>
                    </div>
                    <div class="mb-3">
                        <label class="form-label">No. Telepon / WhatsApp</label>
                        <input type="text" name="no_telpon" class="form-control" placeholder="Contoh: 08123456789" required>
                    </div>
                    <div class="mb-3">
                        <label class="form-label">Alamat Lengkap</label>
                        <textarea name="alamat" class="form-control" rows="3" placeholder="Masukkan alamat lengkap..." required></textarea>
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
<style>
    #adminAutocompleteDrop .ac-item {
        padding: 10px 14px;
        cursor: pointer;
        border-bottom: 1px solid #f1f5f9;
        transition: background .15s;
    }

    #adminAutocompleteDrop .ac-item:last-child {
        border-bottom: none;
    }

    #adminAutocompleteDrop .ac-item:hover {
        background: #fff8e1;
    }

    #adminAutocompleteDrop .ac-header {
        padding: 6px 14px;
        font-size: .75rem;
        font-weight: 600;
        color: #94a3b8;
        text-transform: uppercase;
        letter-spacing: .05em;
        background: #f8fafc;
        border-bottom: 1px solid #e2e8f0;
    }

    #adminAutocompleteDrop .ac-badge {
        font-size: .75rem;
        background: rgba(234, 179, 8, .15);
        color: #92400e;
        border-radius: 100px;
        padding: 2px 8px;
        font-weight: 600;
    }
</style>
<script>
    (function() {
        const input = document.getElementById('tambahNamaInput');
        const drop = document.getElementById('adminAutocompleteDrop');
        const warning = document.getElementById('adminNamaWarning');
        const warnTxt = document.getElementById('adminNamaWarningText');
        let timer = null;

        if (!input) return;

        // Reset saat modal ditutup
        document.getElementById('modalTambah').addEventListener('hidden.bs.modal', function() {
            input.value = '';
            hideDrop();
            hideWarn();
        });

        input.addEventListener('input', function() {
            const q = this.value.trim();
            clearTimeout(timer);
            hideDrop();
            hideWarn();
            if (q.length < 2) return;
            timer = setTimeout(() => {
                fetch('form_pasien.php?ajax_cari=1&q=' + encodeURIComponent(q))
                    .then(r => r.json())
                    .then(data => {
                        if (data.length === 0) return;
                        let html = '<div class="ac-header">Nama serupa sudah terdaftar</div>';
                        data.forEach(item => {
                            const hl = item.nama.replace(new RegExp('(' + q.replace(/[.*+?^${}()|[\]\\]/g, '\\$&') + ')', 'gi'), '<mark>$1</mark>');
                            html += `<div class="ac-item d-flex justify-content-between align-items-center">
                            <div><div style="font-weight:600;font-size:.9rem">${hl}</div><div style="font-size:.8rem;color:#64748b">${item.umur} thn | ${item.telpon||'-'}</div></div>
                            <span class="ac-badge">${item.nomor}</span>
                        </div>`;
                        });
                        drop.innerHTML = html;
                        drop.classList.remove('d-none');
                        warnTxt.innerHTML = `Ditemukan <strong>${data.length}</strong> nama serupa. Pastikan bukan duplikasi sebelum menyimpan.`;
                        warning.classList.remove('d-none');
                    });
            }, 300);
        });

        document.addEventListener('click', function(e) {
            if (!input.contains(e.target) && !drop.contains(e.target)) hideDrop();
        });

        function hideDrop() {
            drop.classList.add('d-none');
            drop.innerHTML = '';
        }

        function hideWarn() {
            warning.classList.add('d-none');
        }
    })();
</script>
</body>

</html>