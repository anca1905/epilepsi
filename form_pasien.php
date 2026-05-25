<?php
require 'koneksi.php';

if (isset($_POST['submit'])) {
    $nama   = mysqli_real_escape_string($conn, $_POST['nama']);
    $umur   = mysqli_real_escape_string($conn, $_POST['umur']);
    $alamat = mysqli_real_escape_string($conn, $_POST['alamat']);
    $notelp = mysqli_real_escape_string($conn, $_POST['notelp']);

    $query = "INSERT INTO pengguna (nama_user, umur, alamat, no_telpon) 
              VALUES ('$nama', '$umur', '$alamat', '$notelp')";
              
    if (mysqli_query($conn, $query)) {
        $id_pengguna = mysqli_insert_id($conn);
        header("Location: diagnosa.php?id=" . $id_pengguna);
        exit();
    } else {
        $error = "Terjadi kesalahan: " . mysqli_error($conn);
    }
}

$page_title = 'Data Pasien';
$active_nav = 'diagnosa';
require 'layout/header_public.php';
?>

<div class="container py-5">
    <div class="row justify-content-center">
        <div class="col-md-8 col-lg-6">

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
                    <i class="bi bi-person-vcard me-2 text-success"></i>Isi Data Pasien
                </h4>
                <p class="page-subtitle">Lengkapi data di bawah sebelum memilih gejala yang dialami anak.</p>

                <?php if (isset($error)): ?>
                    <div class="alert alert-danger">
                        <i class="bi bi-exclamation-triangle-fill me-2"></i><?= $error ?>
                    </div>
                <?php endif; ?>

                <form action="" method="POST">
                    <div class="mb-3">
                        <label class="form-label" for="nama">Nama Anak / Pasien</label>
                        <div class="input-group">
                            <span class="input-group-text bg-light border-end-0">
                                <i class="bi bi-person text-muted"></i>
                            </span>
                            <input type="text" id="nama" name="nama" class="form-control border-start-0"
                                   placeholder="Masukkan nama lengkap" required>
                        </div>
                    </div>
                    <div class="mb-3">
                        <label class="form-label" for="umur">Umur (Tahun)</label>
                        <div class="input-group">
                            <span class="input-group-text bg-light border-end-0">
                                <i class="bi bi-calendar3 text-muted"></i>
                            </span>
                            <input type="number" id="umur" name="umur" class="form-control border-start-0"
                                   placeholder="Contoh: 5" min="0" max="17" required>
                        </div>
                    </div>
                    <div class="mb-3">
                        <label class="form-label" for="alamat">Alamat Lengkap</label>
                        <textarea id="alamat" name="alamat" class="form-control" rows="3"
                                  placeholder="Masukkan alamat..." required></textarea>
                    </div>
                    <div class="mb-4">
                        <label class="form-label" for="notelp">No. Telepon / WhatsApp Orang Tua</label>
                        <div class="input-group">
                            <span class="input-group-text bg-light border-end-0">
                                <i class="bi bi-telephone text-muted"></i>
                            </span>
                            <input type="text" id="notelp" name="notelp" class="form-control border-start-0"
                                   placeholder="Contoh: 08xxxxxxxxxx" required>
                        </div>
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

<?php require 'layout/footer_public.php'; ?>