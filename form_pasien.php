<?php
if (session_status() === PHP_SESSION_NONE) { session_start(); }
require 'koneksi.php';

// Endpoint AJAX: cari pasien berdasarkan nama
if (isset($_GET['ajax_cari']) && !empty($_GET['q'])) {
    $q = mysqli_real_escape_string($conn, trim($_GET['q']));
    $hasil = mysqli_query($conn, "SELECT id_pengguna, nama_user, umur, no_telpon FROM pengguna WHERE nama_user LIKE '%$q%' ORDER BY nama_user ASC LIMIT 10");
    $data = [];
    while ($r = mysqli_fetch_assoc($hasil)) {
        $data[] = [
            'id'     => $r['id_pengguna'],
            'nomor'  => 'PSN-' . str_pad($r['id_pengguna'], 4, '0', STR_PAD_LEFT),
            'nama'   => $r['nama_user'],
            'umur'   => $r['umur'],
            'telpon' => $r['no_telpon'],
        ];
    }
    header('Content-Type: application/json');
    echo json_encode($data);
    exit();
}

$pesan_sukses = '';
$pesan_error  = '';

// PROSES: Daftar pasien baru lalu langsung ke diagnosa
if (isset($_POST['daftar_baru'])) {
    $nama    = mysqli_real_escape_string($conn, trim($_POST['nama_user']));
    $umur    = (int)$_POST['umur'];
    $telpon  = mysqli_real_escape_string($conn, trim($_POST['no_telpon']));
    $alamat  = mysqli_real_escape_string($conn, trim($_POST['alamat']));

    if (empty($nama) || $umur <= 0) {
        $pesan_error = "Nama dan umur wajib diisi.";
    } else {
        // Cek apakah nama sudah ada (exact match, case insensitive)
        $cek_duplikat = mysqli_query($conn, "SELECT id_pengguna FROM pengguna WHERE LOWER(nama_user) = LOWER('$nama') LIMIT 1");
        if (mysqli_num_rows($cek_duplikat) > 0) {
            $existing = mysqli_fetch_assoc($cek_duplikat);
            $pesan_error = "Nama <strong>" . htmlspecialchars($nama) . "</strong> sudah terdaftar dengan nomor <strong>PSN-" . str_pad($existing['id_pengguna'], 4, '0', STR_PAD_LEFT) . "</strong>. Silakan pilih nama dari daftar saran jika ini pasien yang sama.";
        } else {
            $insert = mysqli_query($conn, "INSERT INTO pengguna (nama_user, umur, no_telpon, alamat) VALUES ('$nama', '$umur', '$telpon', '$alamat')");
            if ($insert) {
                $new_id = mysqli_insert_id($conn);
                header("Location: diagnosa.php?id=" . $new_id);
                exit();
            } else {
                $pesan_error = "Gagal menyimpan data. Silakan coba lagi.";
            }
        }
    }
}

// PROSES: Pasien lama (pilih dari autocomplete) → langsung ke diagnosa
if (isset($_POST['pilih_existing'])) {
    $id_pengguna = (int)$_POST['id_existing'];
    if ($id_pengguna > 0) {
        $cek = mysqli_query($conn, "SELECT id_pengguna FROM pengguna WHERE id_pengguna = $id_pengguna");
        if (mysqli_num_rows($cek) > 0) {
            header("Location: diagnosa.php?id=" . $id_pengguna);
            exit();
        } else {
            $pesan_error = "Pasien tidak ditemukan.";
        }
    } else {
        $pesan_error = "Data pasien tidak valid.";
    }
}

$page_title = 'Daftar Pasien';
if (isset($_SESSION['login_admin'])) {
    $active_page = 'diagnosa';
    require 'layout/head_admin.php';
    require 'layout/sidebar_admin.php';
    echo '<div class="admin-main"><div class="admin-topbar"><h1 class="page-heading"><i class="bi bi-person-vcard me-2 text-success"></i>Data Pasien</h1></div><div class="admin-content">';
} else {
    $active_nav = 'diagnosa';
    require 'layout/header_public.php';
}
?>

<div class="container py-5">
    <div class="row justify-content-center">
        <div class="col-md-8 col-lg-6">

            <!-- Breadcrumb -->
            <nav aria-label="breadcrumb" class="mb-3">
                <ol class="breadcrumb small">
                    <li class="breadcrumb-item"><a href="index.php" class="text-decoration-none text-success">Beranda</a></li>
                    <li class="breadcrumb-item active">Daftar Pasien</li>
                </ol>
            </nav>

            <!-- Step Indicator -->
            <div class="d-flex align-items-center gap-2 mb-4">
                <div class="d-flex align-items-center justify-content-center rounded-circle text-white fw-bold"
                     style="width:32px;height:32px;background:var(--brand-primary);font-size:0.85rem;">1</div>
                <div class="flex-grow-1" style="height:3px;background:var(--brand-primary);border-radius:2px;"></div>
                <div class="d-flex align-items-center justify-content-center rounded-circle fw-bold"
                     style="width:32px;height:32px;background:#e2e8f0;color:#94a3b8;font-size:0.85rem;">2</div>
                <div class="flex-grow-1" style="height:3px;background:#e2e8f0;border-radius:2px;"></div>
                <div class="d-flex align-items-center justify-content-center rounded-circle fw-bold"
                     style="width:32px;height:32px;background:#e2e8f0;color:#94a3b8;font-size:0.85rem;">3</div>
            </div>
            <div class="d-flex justify-content-between small text-muted mb-4 px-1">
                <span class="fw-semibold text-success">Data Pasien</span>
                <span>Pilih Gejala</span>
                <span>Hasil Diagnosa</span>
            </div>

            <!-- Pesan Error -->
            <?php if ($pesan_error): ?>
                <div class="alert alert-danger alert-dismissible fade show" role="alert">
                    <i class="bi bi-exclamation-triangle-fill me-2"></i><?= $pesan_error ?>
                    <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
                </div>
            <?php endif; ?>

            <!-- FORM DAFTAR -->
            <div class="page-card">
                <h4 class="page-title">
                    <i class="bi bi-person-plus-fill me-2 text-success"></i>Form Pendaftaran Pasien
                </h4>
                <p class="page-subtitle">Isi data pasien untuk memulai diagnosa. Jika nama sudah pernah terdaftar, pilih dari saran yang muncul.</p>

                <!-- ===================== FORM PASIEN BARU ===================== -->
                <form action="" method="POST" id="formDaftar" autocomplete="off">
                    <!-- NAMA (dengan autocomplete) -->
                    <div class="mb-3 position-relative">
                        <label class="form-label fw-semibold" for="nama_user">
                            Nama Lengkap Pasien <span class="text-danger">*</span>
                        </label>
                        <div class="input-group">
                            <span class="input-group-text bg-light border-end-0">
                                <i class="bi bi-person text-muted"></i>
                            </span>
                            <input type="text" id="nama_user" name="nama_user"
                                   class="form-control form-control-lg border-start-0 ps-0"
                                   placeholder="Ketik nama lengkap pasien..."
                                   value="<?= isset($_POST['nama_user']) ? htmlspecialchars($_POST['nama_user']) : '' ?>"
                                   required>
                        </div>
                        <!-- Dropdown saran autocomplete -->
                        <div id="autocompleteDrop" class="autocomplete-drop shadow-sm border rounded-3 bg-white d-none"
                             style="position:absolute;top:100%;left:0;right:0;z-index:1000;max-height:240px;overflow-y:auto;">
                        </div>
                        <div id="namaWarning" class="d-none mt-2">
                            <div class="alert alert-warning py-2 px-3 mb-0 small">
                                <i class="bi bi-exclamation-circle me-1"></i>
                                <span id="namaWarningText"></span>
                            </div>
                        </div>
                    </div>

                    <!-- UMUR -->
                    <div class="mb-3">
                        <label class="form-label fw-semibold" for="umur">
                            Umur (Tahun) <span class="text-danger">*</span>
                        </label>
                        <div class="input-group">
                            <span class="input-group-text bg-light border-end-0">
                                <i class="bi bi-calendar2 text-muted"></i>
                            </span>
                            <input type="number" id="umur" name="umur" min="0" max="120"
                                   class="form-control form-control-lg border-start-0 ps-0"
                                   placeholder="Contoh: 8"
                                   value="<?= isset($_POST['umur']) ? htmlspecialchars($_POST['umur']) : '' ?>"
                                   required>
                        </div>
                    </div>

                    <!-- NO TELEPON -->
                    <div class="mb-3">
                        <label class="form-label fw-semibold" for="no_telpon">
                            No. Telepon / WhatsApp
                        </label>
                        <div class="input-group">
                            <span class="input-group-text bg-light border-end-0">
                                <i class="bi bi-telephone text-muted"></i>
                            </span>
                            <input type="text" id="no_telpon" name="no_telpon"
                                   class="form-control form-control-lg border-start-0 ps-0"
                                   placeholder="Contoh: 08123456789"
                                   value="<?= isset($_POST['no_telpon']) ? htmlspecialchars($_POST['no_telpon']) : '' ?>">
                        </div>
                    </div>

                    <!-- ALAMAT -->
                    <div class="mb-4">
                        <label class="form-label fw-semibold" for="alamat">Alamat Lengkap</label>
                        <textarea id="alamat" name="alamat" class="form-control" rows="3"
                                  placeholder="Masukkan alamat lengkap pasien..."><?= isset($_POST['alamat']) ? htmlspecialchars($_POST['alamat']) : '' ?></textarea>
                    </div>

                    <div class="d-flex gap-2">
                        <a href="index.php" class="btn btn-outline-secondary">
                            <i class="bi bi-arrow-left me-1"></i>Kembali
                        </a>
                        <button type="submit" name="daftar_baru" class="btn btn-brand flex-grow-1" id="btnDaftar">
                            <i class="bi bi-arrow-right me-1"></i>Daftar & Pilih Gejala
                        </button>
                    </div>
                </form>

                <!-- Form hidden untuk memilih pasien yang sudah ada -->
                <form action="" method="POST" id="formExisting">
                    <input type="hidden" name="id_existing" id="id_existing_val">
                    <input type="hidden" name="pilih_existing" value="1">
                </form>
            </div>

        </div>
    </div>
</div>

<style>
.autocomplete-drop .ac-item {
    padding: 10px 14px;
    cursor: pointer;
    border-bottom: 1px solid #f1f5f9;
    transition: background 0.15s;
}
.autocomplete-drop .ac-item:last-child { border-bottom: none; }
.autocomplete-drop .ac-item:hover,
.autocomplete-drop .ac-item.active { background: #f0fdf4; }
.autocomplete-drop .ac-item .ac-nama { font-weight: 600; font-size: 0.95rem; color: #1e293b; }
.autocomplete-drop .ac-item .ac-info { font-size: 0.8rem; color: #64748b; }
.autocomplete-drop .ac-item .ac-badge {
    font-size: 0.75rem;
    background: rgba(16,185,129,0.12);
    color: #059669;
    border-radius: 100px;
    padding: 2px 8px;
    font-weight: 600;
}
.autocomplete-drop .ac-header {
    padding: 6px 14px;
    font-size: 0.75rem;
    font-weight: 600;
    color: #94a3b8;
    text-transform: uppercase;
    letter-spacing: 0.05em;
    background: #f8fafc;
    border-bottom: 1px solid #e2e8f0;
}
</style>

<script>
(function() {
    const inputNama  = document.getElementById('nama_user');
    const drop       = document.getElementById('autocompleteDrop');
    const warning    = document.getElementById('namaWarning');
    const warningTxt = document.getElementById('namaWarningText');
    const btnDaftar  = document.getElementById('btnDaftar');

    let debounceTimer = null;
    let activeIdx = -1;
    let results   = [];

    inputNama.addEventListener('input', function () {
        const q = this.value.trim();
        clearTimeout(debounceTimer);
        hideWarning();

        if (q.length < 2) {
            hideDrop();
            return;
        }

        debounceTimer = setTimeout(() => {
            fetch('form_pasien.php?ajax_cari=1&q=' + encodeURIComponent(q))
                .then(r => r.json())
                .then(data => {
                    results = data;
                    renderDrop(data, q);
                });
        }, 250);
    });

    function renderDrop(data, q) {
        if (data.length === 0) {
            hideDrop();
            return;
        }

        let html = '<div class="ac-header">Pasien Sudah Terdaftar — Klik untuk Lanjutkan</div>';
        data.forEach((item, i) => {
            const namaHL = item.nama.replace(new RegExp('(' + escapeReg(q) + ')', 'gi'), '<mark>$1</mark>');
            html += `<div class="ac-item" data-idx="${i}">
                <div class="d-flex align-items-center justify-content-between gap-2">
                    <div>
                        <div class="ac-nama">${namaHL}</div>
                        <div class="ac-info">${item.umur} thn &nbsp;|&nbsp; ${item.telpon || '-'}</div>
                    </div>
                    <span class="ac-badge">${item.nomor}</span>
                </div>
            </div>`;
        });

        drop.innerHTML = html;
        drop.classList.remove('d-none');
        activeIdx = -1;

        // Klik item → pakai pasien existing
        drop.querySelectorAll('.ac-item').forEach(el => {
            el.addEventListener('click', function () {
                const idx  = parseInt(this.dataset.idx);
                const item = results[idx];
                pilihExisting(item);
            });
        });
    }

    function pilihExisting(item) {
        // Set form dan submit
        document.getElementById('id_existing_val').value = item.id;
        hideDrop();
        showWarning(`Melanjutkan sebagai pasien <strong>${item.nama}</strong> (${item.nomor})...`);
        setTimeout(() => {
            document.getElementById('formExisting').submit();
        }, 600);
    }

    function hideDrop() {
        drop.classList.add('d-none');
        drop.innerHTML = '';
        activeIdx = -1;
    }

    function showWarning(msg) {
        warningTxt.innerHTML = msg;
        warning.classList.remove('d-none');
    }

    function hideWarning() {
        warning.classList.add('d-none');
    }

    // Keyboard navigation
    inputNama.addEventListener('keydown', function (e) {
        const items = drop.querySelectorAll('.ac-item');
        if (drop.classList.contains('d-none') || items.length === 0) return;

        if (e.key === 'ArrowDown') {
            e.preventDefault();
            activeIdx = Math.min(activeIdx + 1, items.length - 1);
            updateActive(items);
        } else if (e.key === 'ArrowUp') {
            e.preventDefault();
            activeIdx = Math.max(activeIdx - 1, -1);
            updateActive(items);
        } else if (e.key === 'Enter' && activeIdx >= 0) {
            e.preventDefault();
            pilihExisting(results[activeIdx]);
        } else if (e.key === 'Escape') {
            hideDrop();
        }
    });

    function updateActive(items) {
        items.forEach((el, i) => {
            el.classList.toggle('active', i === activeIdx);
        });
    }

    // Tutup dropdown saat klik di luar
    document.addEventListener('click', function (e) {
        if (!inputNama.contains(e.target) && !drop.contains(e.target)) {
            hideDrop();
        }
    });

    function escapeReg(str) {
        return str.replace(/[.*+?^${}()|[\]\\]/g, '\\$&');
    }
})();
</script>

<?php
if (isset($_SESSION['login_admin'])) {
    echo '</div></div></div><script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script></body></html>';
} else {
    require 'layout/footer_public.php';
}
?>