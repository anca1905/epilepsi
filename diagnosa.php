<?php
if (session_status() === PHP_SESSION_NONE) { session_start(); }
require 'koneksi.php';

if (!isset($_GET['id']) || empty($_GET['id'])) {
    header("Location: form_pasien.php");
    exit();
}

$id_pengguna  = (int)$_GET['id'];
$query_pasien = mysqli_query($conn, "SELECT * FROM pengguna WHERE id_pengguna = '$id_pengguna'");
$pasien       = mysqli_fetch_assoc($query_pasien);

if (!$pasien) {
    header("Location: form_pasien.php");
    exit();
}

$query_gejala = mysqli_query($conn, "SELECT * FROM gejala ORDER BY kode_gejala ASC");

$page_title = 'Pilih Gejala';
if (isset($_SESSION['login_admin'])) {
    $active_page = 'diagnosa';
    require 'layout/head_admin.php';
    require 'layout/sidebar_admin.php';
    echo '<div class="admin-main"><div class="admin-topbar"><h1 class="page-heading"><i class="bi bi-clipboard2-pulse me-2 text-success"></i>Pilih Gejala</h1></div><div class="admin-content">';
} else {
    $active_nav = 'diagnosa';
    require 'layout/header_public.php';
}
?>

<div class="container py-5">
    <div class="row justify-content-center">
        <div class="col-md-9 col-lg-8">

            <!-- Breadcrumb -->
            <nav aria-label="breadcrumb" class="mb-3">
                <ol class="breadcrumb small">
                    <li class="breadcrumb-item"><a href="index.php" class="text-decoration-none text-success">Beranda</a></li>
                    <li class="breadcrumb-item"><a href="form_pasien.php" class="text-decoration-none text-success">Data Pasien</a></li>
                    <li class="breadcrumb-item active">Pilih Gejala</li>
                </ol>
            </nav>

            <!-- Step Indicator -->
            <div class="d-flex align-items-center gap-2 mb-4">
                <div class="d-flex align-items-center justify-content-center rounded-circle text-white fw-bold"
                     style="width:32px;height:32px;background:#6ee7b7;color:#064e3b;font-size:0.85rem;">
                    <i class="bi bi-check-lg" style="font-size:1rem;"></i>
                </div>
                <div class="flex-grow-1" style="height:3px;background:var(--brand-primary);border-radius:2px;"></div>
                <div class="d-flex align-items-center justify-content-center rounded-circle text-white fw-bold"
                     style="width:32px;height:32px;background:var(--brand-primary);font-size:0.85rem;">2</div>
                <div class="flex-grow-1" style="height:3px;background:#e2e8f0;border-radius:2px;"></div>
                <div class="d-flex align-items-center justify-content-center rounded-circle fw-bold"
                     style="width:32px;height:32px;background:#e2e8f0;color:#94a3b8;font-size:0.85rem;">3</div>
            </div>
            <div class="d-flex justify-content-between small text-muted mb-4 px-1">
                <span class="fw-semibold text-success">Data Pasien ✓</span>
                <span class="fw-semibold text-success">Pilih Gejala</span>
                <span>Hasil Diagnosa</span>
            </div>

            <div class="page-card">
                <h4 class="page-title">
                    <i class="bi bi-clipboard2-pulse me-2 text-success"></i>Pilih Gejala yang Dialami
                </h4>
                <p class="page-subtitle">Centang semua gejala yang sesuai dengan kondisi anak.</p>

                <!-- Info Pasien -->
                <div class="d-flex align-items-center gap-3 p-3 rounded-3 mb-4"
                     style="background:#f0fdf7;border:1.5px solid #bbf7d0;">
                    <div class="d-flex align-items-center justify-content-center rounded-circle text-white"
                         style="width:40px;height:40px;background:var(--brand-gradient);flex-shrink:0;">
                        <i class="bi bi-person-fill"></i>
                    </div>
                    <div>
                        <div class="fw-bold text-dark" style="font-size:0.95rem;">
                            <?= htmlspecialchars($pasien['nama_user']) ?>
                        </div>
                        <div class="text-muted" style="font-size:0.82rem;">
                            <?= $pasien['umur'] ?> Tahun &bull; <?= htmlspecialchars($pasien['alamat']) ?>
                        </div>
                    </div>
                </div>

                <form action="proses_diagnosa.php" method="POST">
                    <input type="hidden" name="id_pengguna" value="<?= $id_pengguna ?>">

                    <div class="mb-1 d-flex justify-content-between align-items-center">
                        <label class="form-label mb-0">Daftar Gejala</label>
                        <button type="button" id="selectAll" class="btn btn-sm btn-outline-secondary py-1 px-2" style="font-size:0.78rem;">
                            <i class="bi bi-check-all me-1"></i>Pilih Semua
                        </button>
                    </div>

                    <div class="border rounded-3 mb-4" style="max-height:380px;overflow-y:auto;">
                        <?php if (mysqli_num_rows($query_gejala) > 0): ?>
                            <?php while ($row = mysqli_fetch_assoc($query_gejala)): ?>
                                <div class="gejala-item form-check">
                                    <input class="form-check-input gejala-check" type="checkbox"
                                           name="gejala[]" value="<?= $row['kode_gejala'] ?>"
                                           id="gejala_<?= $row['kode_gejala'] ?>">
                                    <label class="form-check-label d-flex align-items-center gap-2"
                                           for="gejala_<?= $row['kode_gejala'] ?>">
                                        <span class="badge bg-secondary" style="font-size:0.7rem;font-weight:600;min-width:36px;">
                                            <?= $row['kode_gejala'] ?>
                                        </span>
                                        <?= htmlspecialchars($row['nama_gejala']) ?>
                                    </label>
                                </div>
                            <?php endwhile; ?>
                        <?php else: ?>
                            <div class="text-center text-muted py-4">
                                <i class="bi bi-exclamation-circle fs-3 d-block mb-2 text-warning"></i>
                                Data gejala belum tersedia. Silakan hubungi admin.
                            </div>
                        <?php endif; ?>
                    </div>

                    <div id="infoGejala" class="small text-muted mb-3">
                        <i class="bi bi-info-circle me-1"></i>
                        <span id="countGejala">0</span> gejala dipilih
                    </div>

                    <div class="d-flex gap-2">
                        <a href="form_pasien.php" class="btn btn-outline-secondary">
                            <i class="bi bi-arrow-left me-1"></i>Kembali
                        </a>
                        <button type="submit" name="proses" class="btn btn-brand flex-grow-1">
                            <i class="bi bi-cpu me-1"></i>Proses Diagnosa
                        </button>
                    </div>
                </form>
            </div>

        </div>
    </div>
</div>

<script>
    // Counter gejala yang dipilih
    const checkboxes = document.querySelectorAll('.gejala-check');
    const counter    = document.getElementById('countGejala');
    const selectBtn  = document.getElementById('selectAll');
    let allSelected  = false;

    function updateCount() {
        const n = document.querySelectorAll('.gejala-check:checked').length;
        counter.textContent = n;
    }

    checkboxes.forEach(cb => cb.addEventListener('change', updateCount));

    selectBtn.addEventListener('click', () => {
        allSelected = !allSelected;
        checkboxes.forEach(cb => cb.checked = allSelected);
        selectBtn.innerHTML = allSelected
            ? '<i class="bi bi-x-circle me-1"></i>Batal Semua'
            : '<i class="bi bi-check-all me-1"></i>Pilih Semua';
        updateCount();
    });
</script>

<?php
if (isset($_SESSION['login_admin'])) {
    echo '</div></div></div><script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script></body></html>';
} else {
    require 'layout/footer_public.php';
}
?>