<?php
if (session_status() === PHP_SESSION_NONE) { session_start(); }
require 'koneksi.php';

if (isset($_POST['submit'])) {
    $nomor_input = trim($_POST['nomor_pasien']);
    
    // Ekstrak angka dari input (misal PSN-0005 menjadi 5)
    $id_pengguna = (int) preg_replace('/[^0-9]/', '', $nomor_input);
    
    if ($id_pengguna > 0) {
        $cek = mysqli_query($conn, "SELECT * FROM pengguna WHERE id_pengguna = $id_pengguna");
        if (mysqli_num_rows($cek) > 0) {
            header("Location: diagnosa.php?id=" . $id_pengguna);
            exit();
        } else {
            $error = "Nomor Pasien tidak ditemukan. Pastikan nomor benar atau mendaftar terlebih dahulu ke admin.";
        }
    } else {
        $error = "Format Nomor Pasien tidak valid.";
    }
}

// Ambil semua data pasien untuk fitur autocomplete datalist
$query_all_pasien = mysqli_query($conn, "SELECT id_pengguna, nama_user FROM pengguna ORDER BY id_pengguna DESC");

$page_title = 'Data Pasien';
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

            <!-- Load Select2 CSS -->
            <link href="https://cdn.jsdelivr.net/npm/select2@4.1.0-rc.0/dist/css/select2.min.css" rel="stylesheet" />
            <link href="https://cdn.jsdelivr.net/npm/select2-bootstrap-5-theme@1.3.0/dist/select2-bootstrap-5-theme.min.css" rel="stylesheet" />
            <style>
                .select2-container .select2-selection--single {
                    height: calc(3.5rem + 2px);
                    padding: 0.75rem 1rem;
                    font-size: 1.1rem;
                    border-radius: 0.375rem;
                    border: 1px solid #dee2e6;
                }
                .select2-container--bootstrap-5 .select2-selection--single .select2-selection__rendered {
                    padding: 0;
                    line-height: 1.8;
                }
                /* Fix Select2 within Input Group */
                .input-group > .select2-container--bootstrap-5 {
                    flex: 1 1 auto;
                    width: 1% !important;
                }
                .input-group > .select2-container--bootstrap-5 .select2-selection {
                    border-top-left-radius: 0;
                    border-bottom-left-radius: 0;
                }
            </style>

            <!-- Breadcrumb -->
            <nav aria-label="breadcrumb" class="mb-3">
                <ol class="breadcrumb small">
                    <li class="breadcrumb-item"><a href="index.php" class="text-decoration-none text-success">Beranda</a></li>
                    <li class="breadcrumb-item active">Data Pasien</li>
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

            <div class="page-card">
                <h4 class="page-title">
                    <i class="bi bi-upc-scan me-2 text-success"></i>Nomor Rekam Medis
                </h4>
                <p class="page-subtitle">Masukkan Nomor Pasien (Rekam Medis) untuk melanjutkan ke tahap diagnosa.</p>

                <?php if (isset($error)): ?>
                    <div class="alert alert-danger">
                        <i class="bi bi-exclamation-triangle-fill me-2"></i><?= $error ?>
                    </div>
                <?php endif; ?>

                <form action="" method="POST">
                    <div class="mb-4">
                        <label class="form-label fw-semibold" for="nomor_pasien">Nomor Pasien</label>
                        <div class="input-group input-group-lg">
                            <span class="input-group-text bg-light border-end-0">
                                <i class="bi bi-upc-scan text-muted"></i>
                            </span>
                            <select id="nomor_pasien" name="nomor_pasien" class="form-select" required>
                                <option value="">Pilih Pasien...</option>
                                <?php 
                                if($query_all_pasien):
                                    while($p = mysqli_fetch_assoc($query_all_pasien)):
                                        $psn = 'PSN-' . str_pad($p['id_pengguna'], 4, '0', STR_PAD_LEFT);
                                ?>
                                    <option value="<?= $psn ?>"><?= $psn ?> - <?= htmlspecialchars($p['nama_user']) ?></option>
                                <?php 
                                    endwhile;
                                endif; 
                                ?>
                            </select>
                        </div>
                        <div class="form-text mt-2"><i class="bi bi-info-circle me-1"></i> Ketik nama pasien untuk mencari. Jika belum terdaftar, silakan daftar melalui Admin.</div>
                    </div>
                    
                    <div class="d-flex gap-2">
                        <a href="index.php" class="btn btn-outline-secondary">
                            <i class="bi bi-arrow-left me-1"></i>Kembali
                        </a>
                        <button type="submit" name="submit" class="btn btn-brand flex-grow-1">
                            <i class="bi bi-arrow-right me-1"></i>Lanjut Pilih Gejala
                        </button>
                    </div>
                </form>
            </div>

        </div>
    </div>
</div>

<?php
$select2_script = '
<script src="https://code.jquery.com/jquery-3.7.0.min.js"></script>
<script src="https://cdn.jsdelivr.net/npm/select2@4.1.0-rc.0/dist/js/select2.min.js"></script>
<script>
$(document).ready(function() {
    $("#nomor_pasien").select2({
        theme: "bootstrap-5",
        placeholder: "Pilih Pasien...",
        allowClear: true,
        width: "100%"
    });
});
</script>
';

if (isset($_SESSION['login_admin'])) {
    echo '</div></div></div><script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>' . $select2_script . '</body></html>';
} else {
    require 'layout/footer_public.php';
    echo $select2_script;
}
?>