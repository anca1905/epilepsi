<?php
if (session_status() === PHP_SESSION_NONE) { session_start(); }
/**
 * proses_diagnosa.php
 * ─────────────────────────────────────────────────────
 * Implementasi Metode Probabilitas Bayes sesuai skripsi.
 *
 * Algoritma:
 * Untuk setiap gejala Gj yang dipilih user:
 *   1. P(Gj) = Σi [ P(Gj|Pi) × P(Pi) ]
 *   2. P(Pi|Gj) = [ P(Gj|Pi) × P(Pi) ] / P(Gj)   [Teorema Bayes]
 *   3. val(Pi,Gj) = P(Pi|Gj) × P(Pi)
 *   4. PGj = Σi val(Pi,Gj)
 *
 * Untuk setiap penyakit Pi:
 *   5. Score(Pi) = Σj [ val(Pi,Gj) / PGj ]
 *                 (hanya untuk Gj di mana P(Gj|Pi) > 0)
 *   6. Total = Σi Score(Pi)
 *   7. Presentase(Pi) = Score(Pi) / Total
 */

require 'koneksi.php';

// ── Validasi input ────────────────────────────────────
if (!isset($_POST['id_pengguna']) || !isset($_POST['gejala']) || empty($_POST['gejala'])) {
    header("Location: form_pasien.php");
    exit();
}

$id_pengguna    = (int)$_POST['id_pengguna'];
$gejala_dipilih = array_map('strval', $_POST['gejala']); // array kode_gejala

// ── 1. Ambil data pasien ──────────────────────────────
$q_pasien = mysqli_query($conn, "SELECT * FROM pengguna WHERE id_pengguna = '$id_pengguna'");
$pasien   = mysqli_fetch_assoc($q_pasien);
if (!$pasien) {
    header("Location: form_pasien.php");
    exit();
}

// ── 2. Ambil semua penyakit + prior ──────────────────
$q_penyakit    = mysqli_query($conn, "SELECT * FROM penyakit ORDER BY kode_penyakit ASC");
$penyakit_list = [];
while ($p = mysqli_fetch_assoc($q_penyakit)) {
    $penyakit_list[$p['kode_penyakit']] = $p;
}

if (empty($penyakit_list)) {
    header("Location: diagnosa.php?id=$id_pengguna");
    exit();
}

// Hitung total prior
$total_prior = array_sum(array_column($penyakit_list, 'probabilitas_prior'));
if ($total_prior <= 0) {
    // Fallback: prior seragam jika semua bernilai 0
    $n = count($penyakit_list);
    foreach ($penyakit_list as $kode => $p) {
        $penyakit_list[$kode]['probabilitas_prior'] = 1 / $n;
    }
    $total_prior = 1.0;
} elseif (abs($total_prior - 1.0) > 0.0001) {
    // Normalisasi proporsional jika total tidak sama dengan 1
    // Contoh: admin memasukkan 0.45 + 0.55 = 1.0 → tidak perlu normalisasi
    // Contoh: 9 + 11 = 20 → normalisasi jadi 0.45 dan 0.55
    foreach ($penyakit_list as $kode => $p) {
        $penyakit_list[$kode]['probabilitas_prior'] = (float)$p['probabilitas_prior'] / $total_prior;
    }
    $total_prior = 1.0;
}

// ── 3. Ambil semua nilai basis untuk gejala terpilih ─
// basis_pengetahuan: kode_penyakit, kode_gejala, probabilitas
$kode_safe = implode("','", array_map(
    fn($g) => mysqli_real_escape_string($conn, $g),
    $gejala_dipilih
));

$q_basis = mysqli_query($conn,
    "SELECT kode_penyakit, kode_gejala, probabilitas
     FROM basis_pengetahuan
     WHERE kode_gejala IN ('$kode_safe')"
);

// Buat lookup: $basis[kode_gejala][kode_penyakit] = probabilitas
$basis = [];
while ($b = mysqli_fetch_assoc($q_basis)) {
    $basis[$b['kode_gejala']][$b['kode_penyakit']] = (float)$b['probabilitas'];
}

// ── 4. Hitung Bayes per gejala ────────────────────────
// Struktur: $val[kode_gejala][kode_penyakit] = val(Pi,Gj)
// $PGj[kode_gejala] = total nilai per gejala

$val_matrix = []; // val(Pi,Gj)
$PGj        = []; // Σi val(Pi,Gj) per gejala
$detail_per_gejala = []; // untuk ditampilkan di halaman hasil

foreach ($gejala_dipilih as $kode_gejala) {

    // Hitung P(Gj) = Σi [ P(Gj|Pi) × P(Pi) ]
    $PGj_raw = 0;
    foreach ($penyakit_list as $kode_penyakit => $p) {
        $prior      = (float)$p['probabilitas_prior'];
        $likelihood = $basis[$kode_gejala][$kode_penyakit] ?? 0;
        $PGj_raw   += $likelihood * $prior;
    }

    // Jika P(Gj) = 0, semua penyakit punya probabilitas 0 untuk gejala ini
    // Lewati gejala ini (tidak berkontribusi pada skor)
    if ($PGj_raw <= 0) {
        continue;
    }

    $detail_per_gejala[$kode_gejala] = [
        'PGj'     => $PGj_raw,
        'per_penyakit' => []
    ];

    $pg_sum = 0; // untuk PGj (sum of val)

    foreach ($penyakit_list as $kode_penyakit => $p) {
        $prior      = (float)$p['probabilitas_prior'];
        $likelihood = $basis[$kode_gejala][$kode_penyakit] ?? 0;

        // P(Pi|Gj) = P(Gj|Pi) × P(Pi) / P(Gj)
        $posterior = ($PGj_raw > 0) ? ($likelihood * $prior) / $PGj_raw : 0;

        // val(Pi,Gj) = P(Pi|Gj) × P(Pi)
        $val = $posterior * $prior;

        $val_matrix[$kode_gejala][$kode_penyakit] = $val;
        $pg_sum += $val;

        $detail_per_gejala[$kode_gejala]['per_penyakit'][$kode_penyakit] = [
            'likelihood' => $likelihood,
            'prior'      => $prior,
            'posterior'  => $posterior,
            'val'        => $val,
        ];
    }

    $PGj[$kode_gejala] = $pg_sum; // = PGj setelah dikalikan prior
}

// ── 5. Hitung Score tiap penyakit ────────────────────
// Score(Pi) = Σj [ val(Pi,Gj) / PGj ]
// Hanya untuk gejala di mana P(Gj|Pi) > 0 (likelihood > 0)

$score = [];
foreach ($penyakit_list as $kode_penyakit => $p) {
    $score[$kode_penyakit] = 0;

    foreach ($gejala_dipilih as $kode_gejala) {
        // Lewati jika gejala tidak memiliki distribusi (semua 0)
        if (!isset($PGj[$kode_gejala]) || $PGj[$kode_gejala] <= 0) {
            continue;
        }

        $likelihood = $basis[$kode_gejala][$kode_penyakit] ?? 0;

        // Hanya berkontribusi jika P(Gj|Pi) > 0
        if ($likelihood > 0) {
            $val = $val_matrix[$kode_gejala][$kode_penyakit] ?? 0;
            $score[$kode_penyakit] += $val / $PGj[$kode_gejala];
        }
    }
}

// ── 6. Normalisasi → presentase ──────────────────────
$total_score = array_sum($score);

$hasil = [];
foreach ($penyakit_list as $kode_penyakit => $p) {
    $presentase = ($total_score > 0) ? $score[$kode_penyakit] / $total_score : 0;
    $hasil[$kode_penyakit] = array_merge($p, [
        'score'      => $score[$kode_penyakit],
        'presentase' => $presentase,
    ]);
}

// Urutkan dari presentase tertinggi
uasort($hasil, fn($a, $b) => $b['presentase'] <=> $a['presentase']);

$diagnosa_utama = reset($hasil);

// ── 7. Simpan ke riwayat_diagnosa ────────────────────
$kode_simpan  = mysqli_real_escape_string($conn, $diagnosa_utama['kode_penyakit']);
$nilai_simpan = $diagnosa_utama['presentase'];
$tgl_simpan   = date('Y-m-d');

mysqli_query($conn,
    "INSERT INTO riwayat_diagnosa (id_pengguna, tanggal, kode_penyakit, nilai_bayes)
     VALUES ('$id_pengguna', '$tgl_simpan', '$kode_simpan', '$nilai_simpan')"
);

// ── 8. Ambil nama gejala yang dipilih ─────────────────
$q_gejala    = mysqli_query($conn,
    "SELECT * FROM gejala WHERE kode_gejala IN ('$kode_safe') ORDER BY kode_gejala ASC"
);
$gejala_data = [];
while ($g = mysqli_fetch_assoc($q_gejala)) {
    $gejala_data[$g['kode_gejala']] = $g;
}

// ─────────────────────────────────────────────────────
// TAMPILAN HASIL
// ─────────────────────────────────────────────────────
$page_title = 'Hasil Diagnosa';
if (isset($_SESSION['login_admin'])) {
    $active_page = 'diagnosa';
    require 'layout/head_admin.php';
    require 'layout/sidebar_admin.php';
    echo '<div class="admin-main"><div class="admin-topbar"><h1 class="page-heading"><i class="bi bi-clipboard2-pulse me-2 text-success"></i>Hasil Diagnosa</h1></div><div class="admin-content">';
} else {
    $active_nav = 'diagnosa';
    require 'layout/header_public.php';
}

$pct = round($diagnosa_utama['presentase'] * 100, 2);
$lvl = $pct >= 50 ? ['teks' => 'Kemungkinan Tinggi',  'kelas' => 'success', 'bg' => '#f0fdf4']
     : ($pct >= 30 ? ['teks' => 'Kemungkinan Sedang',  'kelas' => 'warning', 'bg' => '#fffbeb']
     :                ['teks' => 'Kemungkinan Rendah',  'kelas' => 'danger',  'bg' => '#fff1f2']);
?>

<div class="container py-5">
    <div class="row justify-content-center">
        <div class="col-md-10 col-lg-9">

            <!-- Breadcrumb -->
            <nav aria-label="breadcrumb" class="mb-3">
                <ol class="breadcrumb small">
                    <li class="breadcrumb-item"><a href="index.php" class="text-decoration-none text-success">Beranda</a></li>
                    <li class="breadcrumb-item active">Hasil Diagnosa</li>
                </ol>
            </nav>

            <!-- Step Indicator -->
            <div class="d-flex align-items-center gap-2 mb-4">
                <?php foreach ([1,2,3] as $step): ?>
                    <div class="d-flex align-items-center justify-content-center rounded-circle text-white fw-bold"
                         style="width:32px;height:32px;background:<?= $step < 3 ? '#6ee7b7;color:#064e3b' : 'var(--brand-primary)' ?>;font-size:0.85rem;">
                        <?= $step < 3 ? '<i class="bi bi-check-lg" style="font-size:1rem;"></i>' : '3' ?>
                    </div>
                    <?php if ($step < 3): ?>
                    <div class="flex-grow-1" style="height:3px;background:var(--brand-primary);border-radius:2px;"></div>
                    <?php endif; ?>
                <?php endforeach; ?>
            </div>
            <div class="d-flex justify-content-between small text-muted mb-4 px-1">
                <span class="fw-semibold text-success">Data Pasien ✓</span>
                <span class="fw-semibold text-success">Pilih Gejala ✓</span>
                <span class="fw-semibold text-success">Hasil Diagnosa</span>
            </div>

            <!-- ── Hasil Utama ───────────────────────── -->
            <div class="page-card mb-4">
                <div class="d-flex align-items-start gap-3 mb-4">
                    <div class="d-flex align-items-center justify-content-center rounded-circle text-white"
                         style="width:52px;height:52px;background:var(--brand-gradient);flex-shrink:0;font-size:1.4rem;">
                        <i class="bi bi-shield-check"></i>
                    </div>
                    <div>
                        <div class="page-title">Hasil Diagnosa Selesai</div>
                        <div class="page-subtitle mb-0">
                            Pasien: <strong><?= htmlspecialchars($pasien['nama_user']) ?></strong>
                            &bull; <?= $pasien['umur'] ?> Tahun
                            &bull; <?= date('d M Y') ?>
                        </div>
                    </div>
                </div>

                <!-- Diagnosa Utama -->
                <div class="p-4 rounded-3 mb-4 text-center" style="background:<?= $lvl['bg'] ?>;border:2px solid var(--bs-<?= $lvl['kelas'] ?>);">
                    <div class="text-muted mb-1" style="font-size:0.8rem;text-transform:uppercase;letter-spacing:0.8px;font-weight:600;">
                        Diagnosa Paling Mungkin
                    </div>
                    <div class="fw-bold mb-1" style="font-size:1.6rem;color:#0f172a;line-height:1.2;">
                        <?= htmlspecialchars($diagnosa_utama['nama_penyakit']) ?>
                    </div>
                    <span class="badge bg-<?= $lvl['kelas'] ?> mb-3" style="font-size:0.85rem;padding:6px 14px;">
                        <?= $lvl['teks'] ?>
                    </span>
                    <div class="d-flex align-items-center justify-content-center gap-4 mt-2">
                        <div>
                            <div style="font-size:3rem;font-weight:800;color:#0f172a;line-height:1;">
                                <?= number_format($pct, 2) ?>%
                            </div>
                            <div class="text-muted" style="font-size:0.78rem;">Presentasi Probabilitas</div>
                        </div>
                        <div style="flex:1;max-width:220px;">
                            <div class="progress mb-1" style="height:14px;border-radius:7px;">
                                <div class="progress-bar bg-<?= $lvl['kelas'] ?>"
                                     style="width:<?= $pct ?>%;border-radius:7px;"></div>
                            </div>
                            <div class="d-flex justify-content-between" style="font-size:0.72rem;color:#94a3b8;">
                                <span>0%</span><span>50%</span><span>100%</span>
                            </div>
                        </div>
                    </div>
                </div>

                <!-- Keterangan & Solusi -->
                <?php if ($diagnosa_utama['keterangan'] || $diagnosa_utama['solusi']): ?>
                <div class="row g-3 mb-4">
                    <?php if ($diagnosa_utama['keterangan']): ?>
                    <div class="col-md-6">
                        <div class="p-3 rounded-3 h-100" style="background:#f8fafc;border:1px solid #e2e8f0;">
                            <div class="fw-bold mb-2 d-flex align-items-center gap-2" style="font-size:0.9rem;">
                                <i class="bi bi-info-circle text-primary"></i>Keterangan Penyakit
                            </div>
                            <p class="text-muted mb-0" style="font-size:0.88rem;line-height:1.7;">
                                <?= nl2br(htmlspecialchars($diagnosa_utama['keterangan'])) ?>
                            </p>
                        </div>
                    </div>
                    <?php endif; ?>
                    <?php if ($diagnosa_utama['solusi']): ?>
                    <div class="col-md-6">
                        <div class="p-3 rounded-3 h-100" style="background:#f0fdf7;border:1px solid #bbf7d0;">
                            <div class="fw-bold mb-2 d-flex align-items-center gap-2" style="font-size:0.9rem;">
                                <i class="bi bi-patch-check text-success"></i>Penanganan Awal
                            </div>
                            <p class="text-muted mb-0" style="font-size:0.88rem;line-height:1.7;">
                                <?= nl2br(htmlspecialchars($diagnosa_utama['solusi'])) ?>
                            </p>
                        </div>
                    </div>
                    <?php endif; ?>
                </div>
                <?php endif; ?>

                <!-- Gejala yang Dipilih -->
                <div class="mb-4">
                    <div class="fw-semibold mb-2 d-flex align-items-center gap-2" style="font-size:0.9rem;">
                        <i class="bi bi-list-check text-success"></i>
                        Gejala yang Dilaporkan (<?= count($gejala_dipilih) ?> gejala)
                    </div>
                    <div class="d-flex flex-wrap gap-2">
                        <?php foreach ($gejala_dipilih as $kg): ?>
                        <span class="badge rounded-pill d-flex align-items-center gap-1"
                              style="background:rgba(26,127,90,0.08);color:var(--brand-primary);border:1px solid rgba(26,127,90,0.2);padding:6px 12px;font-weight:500;font-size:0.82rem;">
                            <i class="bi bi-check-circle-fill" style="font-size:0.75rem;"></i>
                            [<?= $kg ?>] <?= htmlspecialchars($gejala_data[$kg]['nama_gejala'] ?? $kg) ?>
                        </span>
                        <?php endforeach; ?>
                    </div>
                </div>

                <!-- Tombol Aksi -->
                <div class="d-flex gap-2">
                    <a href="index.php" class="btn btn-outline-secondary flex-shrink-0">
                        <i class="bi bi-house me-1"></i>Beranda
                    </a>
                    <a href="form_pasien.php" class="btn btn-brand flex-grow-1">
                        <i class="bi bi-arrow-repeat me-1"></i>Diagnosa Pasien Lain
                    </a>
                </div>
            </div>

            <!-- ── Tabel Perbandingan Semua Penyakit ── -->
            <div class="page-card mb-4">
                <h5 class="fw-bold mb-1" style="font-size:1rem;">
                    <i class="bi bi-bar-chart me-2 text-info"></i>Perbandingan Semua Penyakit
                </h5>
                <p class="text-muted mb-4" style="font-size:0.83rem;">
                    Nilai presentasi probabilitas Bayes untuk seluruh jenis penyakit dalam sistem.
                </p>
                <div class="table-responsive">
                    <table class="table align-middle mb-0">
                        <thead>
                            <tr>
                                <th style="width:5%;">No</th>
                                <th>Jenis Penyakit</th>
                                <th class="text-center" style="width:12%;">Prior P(Pi)</th>
                                <th class="text-center" style="width:12%;">Score</th>
                                <th class="text-center" style="width:14%;">Presentasi</th>
                                <th style="width:28%;">Grafik</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php $no = 1; foreach ($hasil as $h): ?>
                            <?php
                            $h_pct   = round($h['presentase'] * 100, 2);
                            $h_color = $h_pct >= 50 ? 'success' : ($h_pct >= 30 ? 'warning' : 'secondary');
                            $is_top  = $h['kode_penyakit'] === $diagnosa_utama['kode_penyakit'];
                            ?>
                            <tr class="<?= $is_top ? 'table-success' : '' ?>">
                                <td class="text-muted"><?= $no++ ?></td>
                                <td>
                                    <div class="fw-semibold" style="font-size:0.9rem;">
                                        <?= htmlspecialchars($h['nama_penyakit']) ?>
                                        <?php if ($is_top): ?>
                                        <span class="badge bg-success ms-1" style="font-size:0.7rem;">Terdiagnosa</span>
                                        <?php endif; ?>
                                    </div>
                                    <span class="badge rounded-pill"
                                          style="background:rgba(13,110,253,0.1);color:#0d6efd;font-size:0.7rem;">
                                        <?= $h['kode_penyakit'] ?>
                                    </span>
                                </td>
                                <td class="text-center">
                                    <span style="font-size:0.9rem;font-weight:600;">
                                        <?= number_format((float)$h['probabilitas_prior'], 2) ?>
                                    </span>
                                </td>
                                <td class="text-center">
                                    <span class="text-muted" style="font-size:0.85rem;">
                                        <?= number_format($h['score'], 4) ?>
                                    </span>
                                </td>
                                <td class="text-center fw-bold" style="font-size:1.05rem;">
                                    <?= number_format($h_pct, 2) ?>%
                                </td>
                                <td>
                                    <div class="progress" style="height:10px;border-radius:5px;">
                                        <div class="progress-bar bg-<?= $h_color ?>"
                                             style="width:<?= $h_pct ?>%;border-radius:5px;">
                                        </div>
                                    </div>
                                </td>
                            </tr>
                            <?php endforeach; ?>
                        </tbody>
                    </table>
                </div>
            </div>

            <!-- ── Tabel Detail Perhitungan (Transparansi) -->
            <div class="page-card mb-4">
                <div class="d-flex align-items-center justify-content-between mb-3">
                    <h5 class="fw-bold mb-0" style="font-size:1rem;">
                        <i class="bi bi-calculator me-2 text-secondary"></i>Detail Perhitungan Bayes
                    </h5>
                    <button class="btn btn-sm btn-outline-secondary" type="button"
                            data-bs-toggle="collapse" data-bs-target="#collapseDetail">
                        <i class="bi bi-chevron-down me-1"></i>Tampilkan
                    </button>
                </div>
                <div class="collapse" id="collapseDetail">
                    <p class="text-muted mb-3" style="font-size:0.83rem;">
                        Nilai P(Gj|Pi) × P(Pi) / PGj untuk setiap kombinasi gejala dan penyakit.
                    </p>

                    <?php if (!empty($detail_per_gejala)): ?>
                    <?php foreach ($detail_per_gejala as $kg => $detail): ?>
                    <?php $gejala_nama = $gejala_data[$kg]['nama_gejala'] ?? $kg; ?>
                    <div class="mb-3">
                        <div class="fw-semibold mb-1" style="font-size:0.88rem;">
                            [<?= $kg ?>] <?= htmlspecialchars($gejala_nama) ?>
                            <span class="text-muted fw-normal">
                                — P(Gj) = <?= number_format($detail['PGj'], 6) ?>
                            </span>
                        </div>
                        <div class="table-responsive">
                            <table class="table table-sm mb-0" style="font-size:0.8rem;">
                                <thead>
                                    <tr style="background:#f8fafc;">
                                        <th>Penyakit</th>
                                        <th class="text-center">P(Gj|Pi)</th>
                                        <th class="text-center">P(Pi)</th>
                                        <th class="text-center">P(Pi|Gj)</th>
                                        <th class="text-center">val(Pi,Gj)</th>
                                        <th class="text-center">val/PGj</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    <?php foreach ($penyakit_list as $kp => $p):
                                        $d = $detail['per_penyakit'][$kp] ?? ['likelihood'=>0,'prior'=>0,'posterior'=>0,'val'=>0];
                                        $contribution = ($detail['PGj'] > 0 && $d['likelihood'] > 0)
                                            ? $d['val'] / $detail['PGj'] : 0;
                                    ?>
                                    <tr class="<?= $d['likelihood'] > 0 ? '' : 'text-muted' ?>">
                                        <td><?= htmlspecialchars($p['nama_penyakit']) ?> (<?= $kp ?>)</td>
                                        <td class="text-center"><?= number_format($d['likelihood'], 4) ?></td>
                                        <td class="text-center"><?= number_format((float)$p['probabilitas_prior'], 2) ?></td>
                                        <td class="text-center"><?= number_format($d['posterior'], 4) ?></td>
                                        <td class="text-center"><?= number_format($d['val'], 6) ?></td>
                                        <td class="text-center fw-semibold <?= $d['likelihood'] > 0 ? 'text-success' : '' ?>">
                                            <?= number_format($contribution, 4) ?>
                                        </td>
                                    </tr>
                                    <?php endforeach; ?>
                                </tbody>
                            </table>
                        </div>
                    </div>
                    <?php endforeach; ?>
                    <?php endif; ?>

                    <!-- Rekap Score Akhir -->
                    <div class="p-3 rounded-3 mt-2" style="background:#f8fafc;border:1px solid #e2e8f0;">
                        <div class="fw-semibold mb-2" style="font-size:0.88rem;">Rekapitulasi Skor Akhir</div>
                        <table class="table table-sm mb-0" style="font-size:0.82rem;">
                            <thead>
                                <tr>
                                    <th>Penyakit</th>
                                    <th class="text-center">Score</th>
                                    <th class="text-center">Total</th>
                                    <th class="text-center">Presentasi</th>
                                </tr>
                            </thead>
                            <tbody>
                                <?php foreach ($hasil as $h): ?>
                                <tr class="<?= $h['kode_penyakit'] === $diagnosa_utama['kode_penyakit'] ? 'table-success fw-bold' : '' ?>">
                                    <td><?= htmlspecialchars($h['nama_penyakit']) ?></td>
                                    <td class="text-center"><?= number_format($h['score'], 4) ?></td>
                                    <td class="text-center"><?= number_format($total_score, 4) ?></td>
                                    <td class="text-center"><?= number_format($h['presentase'] * 100, 2) ?>%</td>
                                </tr>
                                <?php endforeach; ?>
                            </tbody>
                        </table>
                    </div>
                </div>
            </div>

            <!-- Peringatan Medis -->
            <div class="p-3 rounded-3" style="background:#fef9c3;border:1px solid #fde68a;">
                <div class="d-flex gap-2 align-items-start">
                    <i class="bi bi-exclamation-triangle-fill text-warning mt-1 flex-shrink-0"></i>
                    <p class="mb-0 text-dark" style="font-size:0.83rem;line-height:1.7;">
                        <strong>Peringatan:</strong> Hasil diagnosa ini merupakan estimasi awal menggunakan metode 
                        Probabilitas Bayes berdasarkan gejala yang diinputkan dan <strong>tidak menggantikan diagnosis medis profesional</strong>. 
                        Segera konsultasikan kondisi anak ke dokter atau fasilitas kesehatan terdekat.
                    </p>
                </div>
            </div>

        </div>
    </div>
</div>

<?php
if (isset($_SESSION['login_admin'])) {
    echo '</div></div></div><script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script></body></html>';
} else {
    require 'layout/footer_public.php';
}
?>
