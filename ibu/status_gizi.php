<?php
$page_title = 'Status Gizi';
require_once '../includes/header_ibu.php';
require_once '../config/database.php';

$db = (new Database())->getConnection();
$user_id = $_SESSION['user_id'];

// Ambil data anak
$stmt = $db->prepare("SELECT * FROM ibu_anak WHERE user_id = ?");
$stmt->execute([$user_id]);
$anak = $stmt->fetch();

// Data gizi
$gizi = [];
$latest = null;
$chart_data = [];

if ($anak) {
    $stmt = $db->prepare("SELECT * FROM status_gizi WHERE ibu_anak_id = ? ORDER BY tanggal_ukur DESC");
    $stmt->execute([$anak['id']]);
    $gizi = $stmt->fetchAll();
    
    if (count($gizi) > 0) {
        $latest = $gizi[0]; // Data terbaru
        
        // Siapkan data untuk chart (5 data terakhir, urut ascending untuk chart)
        $chart_subset = array_slice(array_reverse($gizi), 0, 5);
        foreach ($chart_subset as $g) {
            $chart_data[] = [
                'label' => date('M/y', strtotime($g['tanggal_ukur'])),
                'bb' => $g['berat_badan'],
                'tb' => $g['tinggi_badan']
            ];
        }
    }
}

// Hitung umur anak
$umur_bulan = '-';
if ($anak) {
    $birth = new DateTime($anak['tanggal_lahir_anak']);
    $now = new DateTime();
    $diff = $now->diff($birth);
    $umur_bulan = ($diff->y * 12) + $diff->m;
}

// Estimasi berat & tinggi ideal (WHO simplified)
$ideal_bb = $umur_bulan != '-' ? (
    $umur_bulan <= 12 ? 3 + ($umur_bulan * 0.5) : 
    ($umur_bulan <= 24 ? 9 + (($umur_bulan - 12) * 0.25) : 12 + (($umur_bulan - 24) * 0.2))
) : 0;

$ideal_tb = $umur_bulan != '-' ? (50 + ($umur_bulan * 2.5)) : 0;
?>

<style>
    .gizi-container { padding: 20px 15px 100px; max-width: 600px; margin: 0 auto; }
    
    /* Status Card */
    .status-card { background: white; border-radius: 20px; padding: 25px; text-align: center; margin-bottom: 20px; box-shadow: 0 5px 20px rgba(0,0,0,0.08); }
    .status-icon { width: 80px; height: 80px; border-radius: 50%; display: inline-flex; align-items: center; justify-content: center; font-size: 35px; margin-bottom: 15px; }
    .status-icon.normal { background: linear-gradient(135deg, #28a745, #34ce57); color: white; }
    .status-icon.kurang { background: linear-gradient(135deg, #ffc107, #ffcd39); color: #333; }
    .status-icon.lebih { background: linear-gradient(135deg, #dc3545, #e4606d); color: white; }
    .status-text { font-size: 22px; font-weight: 800; margin: 0 0 5px; }
    .status-kategori { font-size: 14px; color: #666; margin: 0; }
    .status-date { font-size: 12px; color: #999; margin-top: 10px; }
    
    /* Detail Grid */
    .detail-grid { display: grid; grid-template-columns: 1fr 1fr; gap: 12px; margin-bottom: 20px; }
    .detail-box { background: white; border-radius: 15px; padding: 15px; text-align: center; box-shadow: 0 3px 10px rgba(0,0,0,0.06); }
    .detail-value { font-size: 20px; font-weight: 800; color: #333; margin: 5px 0 2px; }
    .detail-label { font-size: 11px; color: #888; font-weight: 600; }
    
    /* Chart */
    .chart-card { background: white; border-radius: 15px; padding: 15px; margin-bottom: 20px; box-shadow: 0 3px 10px rgba(0,0,0,0.06); }
    .chart-title { font-size: 14px; font-weight: 700; color: #333; margin: 0 0 15px; text-align: center; }
    .chart-placeholder { height: 150px; background: #f8f9fa; border-radius: 10px; display: flex; align-items: center; justify-content: center; color: #999; font-size: 13px; }
    
    /* Riwayat Table */
    .section-title { font-size: 16px; font-weight: 700; color: #333; margin: 25px 0 12px; display: flex; align-items: center; gap: 8px; }
    .riwayat-table { background: white; border-radius: 15px; overflow: hidden; box-shadow: 0 3px 10px rgba(0,0,0,0.06); }
    .riwayat-table table { width: 100%; border-collapse: collapse; }
    .riwayat-table th { background: #f8f9fa; padding: 12px 15px; font-size: 11px; font-weight: 700; color: #666; text-transform: uppercase; text-align: left; }
    .riwayat-table td { padding: 12px 15px; font-size: 13px; border-bottom: 1px solid #f0f0f0; }
    .riwayat-table tr:last-child td { border-bottom: none; }
    .badge-gizi { font-size: 10px; padding: 4px 8px; border-radius: 10px; font-weight: 600; }
    .badge-gizi.normal { background: #d4edda; color: #155724; }
    .badge-gizi.kurang { background: #fff3cd; color: #856404; }
    .badge-gizi.lebih { background: #f8d7da; color: #721c24; }
    
    /* Info Box */
    .info-box { background: linear-gradient(135deg, #e3f2fd, #bbdefb); border-radius: 15px; padding: 15px; margin-top: 20px; font-size: 13px; color: #1565c0; }
    .info-box strong { color: #0d47a1; }
    
    .empty-state { text-align: center; padding: 30px 20px; color: #999; }
    .empty-state i { font-size: 40px; opacity: 0.3; margin-bottom: 10px; }
    .empty-state p { margin: 0; font-size: 13px; }
</style>

<div class="gizi-container">
    <!-- Status Card -->
    <?php if ($latest): ?>
    <div class="status-card">
        <div class="status-icon <?php echo $latest['status_gizi']; ?>">
            <?php 
            echo match($latest['status_gizi']) {
                'normal' => '✅',
                'kurang' => '⚠️',
                'lebih' => '🔶',
                default => '❓'
            };
            ?>
        </div>
        <h3 class="status-text"><?php echo ucfirst($latest['status_gizi']); ?></h3>
        <p class="status-kategori"><?php echo ucfirst(str_replace('_', ' ', $latest['kategori'])); ?></p>
        <p class="status-date">
            <i class="far fa-calendar me-1"></i>Diukur: <?php echo date('d M Y', strtotime($latest['tanggal_ukur'])); ?>
        </p>
    </div>
    
    <!-- Detail Grid -->
    <div class="detail-grid">
        <div class="detail-box">
            <div style="font-size:24px; margin-bottom:5px;">⚖️</div>
            <div class="detail-value"><?php echo $latest['berat_badan']; ?> kg</div>
            <div class="detail-label">Berat Badan</div>
            <small style="color:#999; font-size:10px;">Ideal: ~<?php echo number_format($ideal_bb, 1); ?> kg</small>
        </div>
        <div class="detail-box">
            <div style="font-size:24px; margin-bottom:5px;">📏</div>
            <div class="detail-value"><?php echo $latest['tinggi_badan']; ?> cm</div>
            <div class="detail-label">Tinggi Badan</div>
            <small style="color:#999; font-size:10px;">Ideal: ~<?php echo number_format($ideal_tb, 1); ?> cm</small>
        </div>
    </div>
    
    <!-- Chart Placeholder (bisa dikembangkan dengan Chart.js) -->
    <?php if (count($chart_data) >= 2): ?>
    <div class="chart-card">
        <h5 class="chart-title">📈 Tren Berat Badan (5 Pengukuran Terakhir)</h5>
        <div class="chart-placeholder">
            <!-- Bisa diganti dengan canvas Chart.js -->
            <div style="text-align:center;">
                <i class="fas fa-chart-line fa-2x mb-2 opacity-50"></i><br>
                <small>Grafik tersedia untuk 2+ data</small>
            </div>
        </div>
    </div>
    <?php endif; ?>
    
    <?php else: ?>
    <!-- Empty State -->
    <div class="empty-state">
        <i class="fas fa-weight"></i>
        <p><strong>Belum ada data gizi</strong></p>
        <p style="margin-top:5px;">Status gizi akan muncul setelah bidan melakukan pengukuran.</p>
    </div>
    <?php endif; ?>
    
    <!-- Riwayat Table -->
    <h4 class="section-title"><i class="fas fa-history text-info"></i>Riwayat Pengukuran</h4>
    
    <?php if (count($gizi) > 0): ?>
    <div class="riwayat-table">
        <table>
            <thead>
                <tr>
                    <th>Tanggal</th>
                    <th>BB</th>
                    <th>TB</th>
                    <th>Status</th>
                </tr>
            </thead>
            <tbody>
                <?php foreach ($gizi as $row): ?>
                <tr>
                    <td><?php echo date('d/m', strtotime($row['tanggal_ukur'])); ?></td>
                    <td><strong><?php echo $row['berat_badan']; ?></strong> kg</td>
                    <td><?php echo $row['tinggi_badan']; ?> cm</td>
                    <td>
                        <span class="badge-gizi <?php echo $row['status_gizi']; ?>">
                            <?php echo ucfirst($row['status_gizi']); ?>
                        </span>
                    </td>
                </tr>
                <?php endforeach; ?>
            </tbody>
        </table>
    </div>
    <?php else: ?>
    <p class="text-muted small text-center">Tidak ada riwayat pengukuran.</p>
    <?php endif; ?>
    
    <!-- Info Box -->
    <div class="info-box">
        <i class="fas fa-lightbulb me-2"></i>
        <strong>Tips:</strong> Pantau berat & tinggi anak secara rutin di Posyandu setiap bulan. 
        Gizi baik mendukung tumbuh kembang optimal! 🌱
    </div>
    
    <!-- Quick Action -->
    <?php if ($anak): ?>
    <div style="text-align:center; margin-top:25px;">
        <small class="text-muted d-block mb-2">Butuh input data gizi baru?</small>
        <a href="../bidan/tambah_gizi.php?id=<?php echo $anak['id']; ?>" 
           style="background: linear-gradient(135deg, #667eea, #764ba2); color: white; padding: 12px 25px; border-radius: 25px; text-decoration: none; font-weight: 600; font-size: 14px; display: inline-block;">
            <i class="fas fa-plus me-2"></i>Minta Bidan Input
        </a>
    </div>
    <?php endif; ?>
</div>

<?php require_once '../includes/footer.php'; ?>