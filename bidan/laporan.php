<?php
$page_title = 'Laporan & Statistik';
require_once '../includes/header_bidan.php';
require_once '../config/database.php';

$database = new Database();
$db = $database->getConnection();

// ========== DATA STATISTIK UTAMA ==========
$stats = [
    'total_anak' => $db->query("SELECT COUNT(*) FROM ibu_anak")->fetchColumn(),
    'total_imun' => $db->query("SELECT COUNT(*) FROM imunisasi")->fetchColumn(),
    'imun_lengkap' => $db->query("SELECT COUNT(*) FROM imunisasi WHERE status='lengkap'")->fetchColumn(),
    'total_gizi' => $db->query("SELECT COUNT(*) FROM status_gizi")->fetchColumn(),
];

// ========== DATA GIZI PER KATEGORI ==========
$gizi_data = $db->query("SELECT kategori, COUNT(*) as jml FROM status_gizi GROUP BY kategori")->fetchAll(PDO::FETCH_ASSOC);

// ========== DATA IMUNISASI PER JENIS ==========
$imun_data = $db->query("SELECT jenis_imunisasi, COUNT(*) as jml, SUM(CASE WHEN status='lengkap' THEN 1 ELSE 0 END) as lengkap FROM imunisasi GROUP BY jenis_imunisasi")->fetchAll(PDO::FETCH_ASSOC);

// ========== DATA PER PUSKESMAS ==========
$puskes_data = $db->query("SELECT puskesmas, COUNT(DISTINCT ia.id) as total FROM ibu_anak ia GROUP BY puskesmas ORDER BY total DESC")->fetchAll(PDO::FETCH_ASSOC);

// ========== FILTER LAPORAN ==========
$periode = $_GET['periode'] ?? 'bulan_ini';
$tgl_mulai = date('Y-m-01');
$tgl_selesai = date('Y-m-t');

if ($periode == 'minggu_ini') {
    $tgl_mulai = date('Y-m-d', strtotime('monday this week'));
    $tgl_selesai = date('Y-m-d', strtotime('sunday this week'));
} elseif ($periode == 'tahun_ini') {
    $tgl_mulai = date('Y-01-01');
    $tgl_selesai = date('Y-12-31');
} elseif ($periode == 'custom' && !empty($_GET['tgl_mulai']) && !empty($_GET['tgl_selesai'])) {
    $tgl_mulai = $_GET['tgl_mulai'];
    $tgl_selesai = $_GET['tgl_selesai'];
}
?>

<style>
    /* Style untuk Card Statistik */
    .stat-card { border-radius: 15px; padding: 20px; color: white; position: relative; overflow: hidden; }
    .stat-card::after { content: ''; position: absolute; top: -50%; right: -20%; width: 150px; height: 150px; background: rgba(255,255,255,0.1); border-radius: 50%; }
    .stat-card.total { background: linear-gradient(135deg, #667eea, #764ba2); }
    .stat-card.imun { background: linear-gradient(135deg, #28a745, #34ce57); }
    .stat-card.gizi { background: linear-gradient(135deg, #17a2b8, #20c997); }
    .stat-card.puskes { background: linear-gradient(135deg, #fd7e14, #fdcb6e); }
    .stat-number { font-size: 32px; font-weight: 800; margin: 10px 0 5px; position: relative; z-index: 1; }
    .stat-label { font-size: 13px; opacity: 0.95; position: relative; z-index: 1; }
    
    /* Style untuk Chart Container */
    .chart-container { 
        background: white; 
        border-radius: 15px; 
        padding: 20px; 
        margin-bottom: 20px; 
        box-shadow: 0 2px 10px rgba(0,0,0,0.05); 
        /* Batasi tinggi maksimum container */
        max-height: 400px; 
    }
    .chart-wrapper {
        position: relative;
        /* Atur tinggi grafik di sini (sedikit lebih kecil dari sebelumnya) */
        height: 250px !important; 
        width: 100%;
    }
    
    .table-report th { font-weight: 600; font-size: 12px; text-transform: uppercase; color: #666; background: #f8f9fa; }
    .badge-gizi { font-size: 11px; padding: 4px 10px; border-radius: 20px; font-weight: 600; }
    
    @media print {
        .no-print { display: none !important; }
        .card { box-shadow: none !important; border: 1px solid #ddd !important; }
        body { background: white !important; }
    }
</style>

<!-- Header Laporan -->
<div class="d-flex justify-content-between align-items-center mb-4 no-print">
    <div>
        <h3 class="fw-bold mb-1"><i class="fas fa-file-alt text-primary me-2"></i>Laporan & Statistik</h3>
        <p class="text-muted mb-0">Periode: <strong><?php echo date('d M Y', strtotime($tgl_mulai)); ?> - <?php echo date('d M Y', strtotime($tgl_selesai)); ?></strong></p>
    </div>
    <div class="d-flex gap-2">
        <form method="GET" class="d-flex gap-2 align-items-center">
            <select name="periode" class="form-select form-select-sm" style="width:auto;" onchange="this.form.submit()">
                <option value="minggu_ini" <?php echo $periode=='minggu_ini'?'selected':''; ?>>Minggu Ini</option>
                <option value="bulan_ini" <?php echo $periode=='bulan_ini'?'selected':''; ?>>Bulan Ini</option>
                <option value="tahun_ini" <?php echo $periode=='tahun_ini'?'selected':''; ?>>Tahun Ini</option>
                <option value="custom" <?php echo $periode=='custom'?'selected':''; ?>>Custom</option>
            </select>
            <?php if($periode == 'custom'): ?>
            <input type="date" name="tgl_mulai" class="form-control form-control-sm" value="<?php echo $tgl_mulai; ?>" style="width:auto;">
            <input type="date" name="tgl_selesai" class="form-control form-control-sm" value="<?php echo $tgl_selesai; ?>" style="width:auto;">
            <button type="submit" class="btn btn-primary btn-sm">Filter</button>
            <?php endif; ?>
        </form>
        <button onclick="window.print()" class="btn btn-outline-primary btn-sm">
            <i class="fas fa-print me-1"></i> Cetak
        </button>
    </div>
</div>

<!-- Statistik Cards -->
<div class="row g-3 mb-4">
    <div class="col-md-3 col-6">
        <div class="stat-card total">
            <i class="fas fa-baby fa-2x mb-2"></i>
            <div class="stat-number"><?php echo $stats['total_anak']; ?></div>
            <div class="stat-label">Total Balita Terdaftar</div>
        </div>
    </div>
    <div class="col-md-3 col-6">
        <div class="stat-card imun">
            <i class="fas fa-syringe fa-2x mb-2"></i>
            <div class="stat-number"><?php echo $stats['imun_lengkap']; ?> / <?php echo $stats['total_imun']; ?></div>
            <div class="stat-label">Imunisasi Lengkap</div>
            <small class="d-block mt-1" style="font-size:11px; opacity:0.9;">
                <?php echo $stats['total_imun']>0 ? round(($stats['imun_lengkap']/$stats['total_imun'])*100,1) : 0; ?>% Cakupan
            </small>
        </div>
    </div>
    <div class="col-md-3 col-6">
        <div class="stat-card gizi">
            <i class="fas fa-weight fa-2x mb-2"></i>
            <div class="stat-number"><?php echo $stats['total_gizi']; ?></div>
            <div class="stat-label">Data Gizi Terinput</div>
        </div>
    </div>
    <div class="col-md-3 col-6">
        <div class="stat-card puskes">
            <i class="fas fa-hospital fa-2x mb-2"></i>
            <div class="stat-number"><?php echo count($puskes_data); ?></div>
            <div class="stat-label">Puskesmas Aktif</div>
        </div>
    </div>
</div>

<!-- Charts Section -->
<div class="row">
    <!-- Chart Gizi (Doughnut) -->
    <div class="col-lg-6 mb-4">
        <div class="chart-container">
            <h6 class="fw-bold mb-3"><i class="fas fa-chart-pie me-2 text-info"></i>Distribusi Status Gizi</h6>
            <div class="chart-wrapper">
                <canvas id="chartGizi"></canvas>
            </div>
        </div>
    </div>
    
    <!-- Chart Imunisasi (Bar) -->
    <div class="col-lg-6 mb-4">
        <div class="chart-container">
            <h6 class="fw-bold mb-3"><i class="fas fa-chart-bar me-2 text-primary"></i>Cakupan Imunisasi per Jenis</h6>
            <div class="chart-wrapper">
                <canvas id="chartImun"></canvas>
            </div>
        </div>
    </div>
</div>

<!-- Tabel Puskesmas -->
<div class="card content-card border-0 shadow-sm mb-4">
    <div class="card-header bg-white py-3">
        <h6 class="fw-bold mb-0"><i class="fas fa-map-marker-alt me-2 text-success"></i>Data per Puskesmas</h6>
    </div>
    <div class="card-body p-0">
        <div class="table-responsive">
            <table class="table table-report mb-0">
                <thead>
                    <tr>
                        <th class="ps-4">Puskesmas</th>
                        <th class="text-center">Total Anak</th>
                    </tr>
                </thead>
                <tbody>
                    <?php foreach($puskes_data as $p): ?>
                    <tr>
                        <td class="ps-4 fw-bold"><?php echo htmlspecialchars($p['puskesmas']); ?></td>
                        <td class="text-center"><span class="badge bg-primary rounded-pill"><?php echo $p['total']; ?></span></td>
                    </tr>
                    <?php endforeach; ?>
                </tbody>
            </table>
        </div>
    </div>
</div>

<!-- Tabel Detail Gizi Terbaru -->
<div class="card content-card border-0 shadow-sm">
    <div class="card-header bg-white py-3 d-flex justify-content-between align-items-center">
        <h6 class="fw-bold mb-0"><i class="fas fa-list me-2 text-warning"></i>10 Data Gizi Terbaru</h6>
        <a href="status_gizi.php" class="btn btn-sm btn-outline-primary">Lihat Semua</a>
    </div>
    <div class="card-body p-0">
        <div class="table-responsive">
            <table class="table table-report mb-0">
                <thead>
                    <tr>
                        <th class="ps-4">Nama Anak</th>
                        <th>Tanggal</th>
                        <th>BB/TB</th>
                        <th>Status</th>
                        <th>Kategori</th>
                    </tr>
                </thead>
                <tbody>
                    <?php
                    $latest_gizi = $db->query("SELECT sg.*, ia.nama_anak FROM status_gizi sg JOIN ibu_anak ia ON sg.ibu_anak_id = ia.id ORDER BY sg.tanggal_ukur DESC LIMIT 10")->fetchAll();
                    foreach($latest_gizi as $g):
                        $badge = match($g['status_gizi']) {
                            'normal' => 'success',
                            'kurang' => 'warning',
                            'lebih' => 'danger',
                            default => 'secondary'
                        };
                    ?>
                    <tr>
                        <td class="ps-4 fw-bold"><?php echo htmlspecialchars($g['nama_anak']); ?></td>
                        <td><?php echo date('d/m/Y', strtotime($g['tanggal_ukur'])); ?></td>
                        <td><?php echo $g['berat_badan']; ?>kg / <?php echo $g['tinggi_badan']; ?>cm</td>
                        <td><span class="badge bg-<?php echo $badge; ?> badge-gizi"><?php echo ucfirst($g['status_gizi']); ?></span></td>
                        <td><?php echo ucfirst(str_replace('_', ' ', $g['kategori'])); ?></td>
                    </tr>
                    <?php endforeach; ?>
                </tbody>
            </table>
        </div>
    </div>
</div>

<!-- Load Chart.js -->
<script src="https://cdn.jsdelivr.net/npm/chart.js"></script>

<script>
    // --- 1. CHART GIZI ---
    const ctxGizi = document.getElementById('chartGizi').getContext('2d');
    new Chart(ctxGizi, {
        type: 'doughnut',
        data: {
            labels: <?php echo json_encode(array_map(fn($d) => ucfirst($d['kategori'] ?? 'Lainnya'), $gizi_data)); ?>,
            datasets: [{
                data: <?php echo json_encode(array_map(fn($d) => $d['jml'], $gizi_data)); ?>,
                backgroundColor: ['#28a745', '#ffc107', '#dc3545', '#6c757d'],
                borderWidth: 1,
                borderColor: '#fff'
            }]
        },
        options: {
            responsive: true,
            maintainAspectRatio: false, // PENTING: Agar mengikuti ukuran container CSS
            plugins: { 
                legend: { position: 'right', labels: { boxWidth: 15, font: { size: 11 } } },
                title: { display: false }
            }
        }
    });

    // --- 2. CHART IMUNISASI ---
    const ctxImun = document.getElementById('chartImun').getContext('2d');
    new Chart(ctxImun, {
        type: 'bar',
        data: {
            labels: <?php echo json_encode(array_map(fn($d) => $d['jenis_imunisasi'], array_slice($imun_data, 0, 6))); ?>,
            datasets: [{
                label: 'Jumlah',
                data: <?php echo json_encode(array_map(fn($d) => $d['jml'], array_slice($imun_data, 0, 6))); ?>,
                backgroundColor: 'rgba(102, 126, 234, 0.7)',
                borderColor: 'rgba(102, 126, 234, 1)',
                borderWidth: 1,
                borderRadius: 4,
                maxBarThickness: 40 // Batasi lebar batang
            }]
        },
        options: {
            responsive: true,
            maintainAspectRatio: false, // PENTING: Agar mengikuti ukuran container CSS
            scales: { 
                y: { beginAtZero: true, ticks: { stepSize: 1, font: { size: 10 } } },
                x: { ticks: { font: { size: 10 } } }
            },
            plugins: { 
                legend: { display: false }
            }
        }
    });
</script>

<?php require_once '../includes/footer.php'; ?>