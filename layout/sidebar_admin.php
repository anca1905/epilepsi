<?php
/**
 * layout/sidebar_admin.php
 * ─────────────────────────────────────────────
 * Sidebar navigasi untuk semua halaman ADMIN.
 *
 * Variabel yang harus di-set sebelum include:
 *   $active_page  (string) — Nama halaman aktif:
 *     'dashboard' | 'gejala' | 'penyakit' | 'basis' | 'laporan' | 'setting'
 */
$active_page = $active_page ?? '';

$nav_items = [
    ['id' => 'dashboard', 'href' => 'dashboard.php',    'icon' => 'bi-speedometer2',         'label' => 'Dashboard'],
    ['id' => 'diagnosa',  'href' => 'form_pasien.php',  'icon' => 'bi-clipboard2-pulse',     'label' => 'Diagnosa'],
    ['id' => 'pasien',    'href' => 'admin_pasien.php', 'icon' => 'bi-person-lines-fill',    'label' => 'Data Pasien'],
    ['id' => 'gejala',    'href' => 'admin_gejala.php',  'icon' => 'bi-file-earmark-medical', 'label' => 'Data Gejala'],
    ['id' => 'penyakit',  'href' => 'admin_penyakit.php','icon' => 'bi-virus',                'label' => 'Data Penyakit'],
    ['id' => 'basis',     'href' => 'admin_basis.php',   'icon' => 'bi-diagram-3',            'label' => 'Basis Pengetahuan'],
    ['id' => 'laporan',   'href' => 'admin_laporan.php', 'icon' => 'bi-graph-up-arrow',       'label' => 'Hasil Diagnosa'],
];
?>
<!-- SIDEBAR -->
<aside class="sidebar">
    <div class="sidebar-header">
        <div class="sidebar-brand">
            <i class="bi bi-heart-pulse-fill"></i>
            <div>
                EpilepsiCare
                <small>Panel Administrator</small>
            </div>
        </div>
    </div>

    <nav class="sidebar-nav">
        <div class="sidebar-nav-label">Menu Utama</div>
        <?php foreach ($nav_items as $item): ?>
            <a href="<?= $item['href'] ?>" class="<?= $active_page === $item['id'] ? 'active' : '' ?>">
                <i class="bi <?= $item['icon'] ?>"></i>
                <?= $item['label'] ?>
            </a>
        <?php endforeach; ?>

        <div class="sidebar-nav-label mt-2">Pengaturan</div>
        <a href="admin_setting.php" class="<?= $active_page === 'setting' ? 'active' : '' ?>">
            <i class="bi bi-gear-fill"></i>
            Setting Akun
        </a>
    </nav>

    <div class="sidebar-footer">
        <a href="logout.php" id="btn-logout">
            <i class="bi bi-box-arrow-right"></i>
            Keluar
        </a>
    </div>
</aside>
<!-- END SIDEBAR -->
