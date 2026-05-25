<?php
session_start();
require 'koneksi.php';

if (isset($_SESSION['login_admin'])) {
    header("Location: dashboard.php");
    exit();
}

if (isset($_POST['login'])) {
    $username     = mysqli_real_escape_string($conn, $_POST['username']);
    $password     = mysqli_real_escape_string($conn, $_POST['password']);
    $password_md5 = md5($password);
    
    $query  = "SELECT * FROM admin WHERE username = '$username' AND password = '$password_md5'";
    $result = mysqli_query($conn, $query);
    
    if (mysqli_num_rows($result) === 1) {
        $row = mysqli_fetch_assoc($result);
        $_SESSION['login_admin'] = true;
        $_SESSION['id_admin']    = $row['id_admin'];
        $_SESSION['username']    = $row['username'];
        header("Location: dashboard.php");
        exit();
    } else {
        $error = "Username atau Password salah!";
    }
}
?>
<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Login Admin — EpilepsiCare</title>
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Plus+Jakarta+Sans:wght@400;500;600;700;800&display=swap" rel="stylesheet">
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.0/font/bootstrap-icons.css">
    <link rel="stylesheet" href="assets/style.css">
</head>
<body class="login-page">

<div class="container">
    <div class="row justify-content-center">
        <div class="col-12">
            <div class="login-card">
                <!-- Logo -->
                <div class="login-logo">
                    <i class="bi bi-heart-pulse-fill"></i>
                </div>
                <h4 class="text-center fw-bold text-dark mb-1">EpilepsiCare</h4>
                <p class="text-center text-muted mb-4" style="font-size:0.9rem;">
                    Masuk ke Panel Administrator
                </p>

                <?php if (isset($error)): ?>
                    <div class="alert alert-danger d-flex align-items-center gap-2 py-2">
                        <i class="bi bi-exclamation-triangle-fill flex-shrink-0"></i>
                        <span><?= $error ?></span>
                    </div>
                <?php endif; ?>

                <form action="" method="POST">
                    <div class="mb-3">
                        <label class="form-label" for="username">Username</label>
                        <div class="input-group">
                            <span class="input-group-text bg-light border-end-0">
                                <i class="bi bi-person text-muted"></i>
                            </span>
                            <input type="text" id="username" name="username" class="form-control border-start-0"
                                   placeholder="Masukkan username" required autocomplete="off">
                        </div>
                    </div>
                    <div class="mb-4">
                        <label class="form-label" for="password">Password</label>
                        <div class="input-group">
                            <span class="input-group-text bg-light border-end-0">
                                <i class="bi bi-lock text-muted"></i>
                            </span>
                            <input type="password" id="password" name="password" class="form-control border-start-0"
                                   placeholder="••••••••" required>
                            <button type="button" class="input-group-text bg-light border-start-0"
                                    id="togglePwd" style="cursor:pointer;">
                                <i class="bi bi-eye text-muted" id="pwdIcon"></i>
                            </button>
                        </div>
                    </div>
                    <div class="d-grid gap-2">
                        <button type="submit" name="login" class="btn btn-brand btn-lg fw-bold">
                            <i class="bi bi-box-arrow-in-right me-2"></i>Masuk
                        </button>
                        <a href="index.php" class="btn btn-outline-secondary btn-sm">
                            <i class="bi bi-arrow-left me-1"></i>Kembali ke Beranda
                        </a>
                    </div>
                </form>
            </div>
        </div>
    </div>
</div>

<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
<script>
    document.getElementById('togglePwd').addEventListener('click', function() {
        const pwd  = document.getElementById('password');
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