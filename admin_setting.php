<?php
session_start();
require 'koneksi.php';

if (!isset($_SESSION['login_admin'])) {
    header("Location: login_admin.php");
    exit();
}

$id_admin = $_SESSION['id_admin'];
$pesan    = "";

$query_admin = mysqli_query($conn, "SELECT * FROM admin WHERE id_admin = '$id_admin'");
$data_admin  = mysqli_fetch_assoc($query_admin);

if (isset($_POST['update'])) {
    $username_baru = mysqli_real_escape_string($conn, $_POST['username']);
    $password_baru = mysqli_real_escape_string($conn, $_POST['password']);
    
    if (!empty($password_baru)) {
        $password_md5 = md5($password_baru);
        $update = mysqli_query($conn, "UPDATE admin SET username = '$username_baru', password = '$password_md5' WHERE id_admin = '$id_admin'");
    } else {
        $update = mysqli_query($conn, "UPDATE admin SET username = '$username_baru' WHERE id_admin = '$id_admin'");
    }

    if ($update) {
        $_SESSION['username']    = $username_baru;
        $data_admin['username']  = $username_baru;
        $pesan = "<div class='alert alert-success'><i class='bi bi-check-circle me-2'></i>Data pengaturan berhasil diperbarui!</div>";
    } else {
        $pesan = "<div class='alert alert-danger'><i class='bi bi-x-circle me-2'></i>Gagal memperbarui data: " . mysqli_error($conn) . "</div>";
    }
}

$page_title  = 'Setting Akun';
$active_page = 'setting';
require 'layout/head_admin.php';
require 'layout/sidebar_admin.php';
?>

<!-- MAIN CONTENT -->
<div class="admin-main">

    <!-- Topbar -->
    <div class="admin-topbar">
        <h1 class="page-heading"><i class="bi bi-gear-fill me-2 text-secondary"></i>Setting Akun</h1>
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
            <h2 class="fw-bold mb-0" style="font-size:1.35rem;">Pengaturan Akun Admin</h2>
            <p class="text-muted mb-0" style="font-size:0.85rem;">Perbarui username dan password untuk akun administrator.</p>
        </div>

        <div class="row">
            <div class="col-md-7 col-lg-5">
                <div class="card-admin p-4">

                    <!-- Profile Header -->
                    <div class="d-flex align-items-center gap-3 mb-4 pb-3 border-bottom">
                        <div class="d-flex align-items-center justify-content-center rounded-circle text-white fw-bold"
                             style="width:52px;height:52px;background:var(--brand-gradient);font-size:1.3rem;flex-shrink:0;">
                            <?= strtoupper(substr($data_admin['username'], 0, 1)) ?>
                        </div>
                        <div>
                            <div class="fw-bold" style="font-size:1rem;"><?= htmlspecialchars($data_admin['username']) ?></div>
                            <div class="text-muted" style="font-size:0.82rem;">
                                <i class="bi bi-shield-check me-1 text-success"></i>Administrator
                            </div>
                        </div>
                    </div>

                    <?= $pesan ?>

                    <form action="" method="POST">
                        <div class="mb-3">
                            <label class="form-label" for="usernameInput">Username Admin</label>
                            <div class="input-group">
                                <span class="input-group-text bg-light border-end-0">
                                    <i class="bi bi-person text-muted"></i>
                                </span>
                                <input type="text" id="usernameInput" name="username" class="form-control border-start-0"
                                       value="<?= htmlspecialchars($data_admin['username']) ?>" required>
                            </div>
                        </div>
                        <div class="mb-4">
                            <label class="form-label" for="passwordInput">Password Baru</label>
                            <div class="input-group">
                                <span class="input-group-text bg-light border-end-0">
                                    <i class="bi bi-lock text-muted"></i>
                                </span>
                                <input type="password" id="passwordInput" name="password" class="form-control border-start-0"
                                       placeholder="Kosongkan jika tidak ingin diubah">
                                <button type="button" class="input-group-text bg-light border-start-0"
                                        id="togglePwd" style="cursor:pointer;">
                                    <i class="bi bi-eye text-muted" id="pwdIcon"></i>
                                </button>
                            </div>
                            <div class="form-text">
                                <i class="bi bi-info-circle me-1"></i>Biarkan kosong jika tidak ingin mengganti password.
                            </div>
                        </div>
                        <button type="submit" name="update" class="btn btn-brand w-100">
                            <i class="bi bi-save me-2"></i>Simpan Perubahan
                        </button>
                    </form>
                </div>
            </div>

            <!-- Info Card -->
            <div class="col-md-5 col-lg-4 mt-3 mt-md-0">
                <div class="card-admin p-4 h-100" style="border-left:4px solid var(--brand-primary);">
                    <h6 class="fw-bold mb-3 d-flex align-items-center gap-2">
                        <i class="bi bi-shield-lock text-success"></i>Tips Keamanan
                    </h6>
                    <ul class="list-unstyled text-muted mb-0" style="font-size:0.88rem;line-height:2;">
                        <li><i class="bi bi-dot text-success fs-5 me-1"></i>Gunakan password minimal 8 karakter</li>
                        <li><i class="bi bi-dot text-success fs-5 me-1"></i>Kombinasikan huruf, angka &amp; simbol</li>
                        <li><i class="bi bi-dot text-success fs-5 me-1"></i>Jangan bagikan password ke siapapun</li>
                        <li><i class="bi bi-dot text-success fs-5 me-1"></i>Ganti password secara berkala</li>
                    </ul>
                </div>
            </div>
        </div>

    </div><!-- /.admin-content -->
</div><!-- /.admin-main -->
</div><!-- /.admin-wrapper -->

<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
<script>
    document.getElementById('togglePwd').addEventListener('click', function() {
        const pwd  = document.getElementById('passwordInput');
        const icon = document.getElementById('pwdIcon');
        if (pwd.type === 'password') {
            pwd.type = 'text';
            icon.classList.replace('bi-eye', 'bi-eye-slash');
        } else {
            pwd.type = 'password';
            icon.classList.replace('bi-eye-slash', 'bi-eye');
        }
    });
</script>
</body>
</html>