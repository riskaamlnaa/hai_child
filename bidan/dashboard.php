<?php
$page_title = 'Dashboard';
require_once '../includes/header_bidan.php';
require_once '../config/database.php';

$database = new Database();
$db = $database->getConnection();

// Statistik
$total_anak = $db->query("SELECT COUNT(*) FROM ibu_anak")->fetchColumn();
$imun_lengkap = $db->query("SELECT COUNT(*) FROM imunisasi WHERE status='lengkap'")->fetchColumn();
$imun_total = $db->query("SELECT COUNT(*) FROM imunisasi")->fetchColumn();
$gizi_normal = $db->query("SELECT COUNT(*) FROM status_gizi WHERE status_gizi='normal'")->fetchColumn();

// Anak yang perlu imunisasi (belum lengkap)
$perlu_imunisasi = $db->query("SELECT ia.nama_anak, ia.nama_ibu, COUNT(i.id) as jumlah_imun 
                                FROM ibu_anak ia 
                                LEFT JOIN imunisasi i ON ia.id = i.ibu_anak_id 
                                GROUP BY ia.id 
                                HAVING jumlah_imun < 3 
                                LIMIT 5")->fetchAll();

// Data gizi terbaru
$gizi_terbaru = $db->query("SELECT ia.nama_anak, ia.nama_ibu, sg.status_gizi, sg.kategori, sg.tanggal_ukur, sg.berat_badan, sg.tinggi_badan
                            FROM status_gizi sg 
                            JOIN ibu_anak ia ON sg.ibu_anak_id = ia.id 
                            ORDER BY sg.tanggal_ukur DESC LIMIT 5")->fetchAll();
?>

<style>
    /* Welcome Section */
    .welcome-section {
        background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
        border-radius: 25px;
        padding: 35px;
        color: white;
        margin-bottom: 30px;
        box-shadow: 0 10px 40px rgba(102,126,234,0.3);
    }
    
    .welcome-section h2 {
        font-weight: 700;
        margin-bottom: 10px;
        font-size: 28px;
    }
    
    .welcome-section p {
        opacity: 0.9;
        font-size: 15px;
        margin: 0;
    }
    
    .welcome-date {
        background: rgba(255,255,255,0.2);
        padding: 8px 20px;
        border-radius: 20px;
        display: inline-block;
        margin-top: 15px;
        font-size: 14px;
    }
    
    /* Quick Stats */
    .quick-stat-card {
        background: white;
        border-radius: 20px;
        padding: 25px;
        text-align: center;
        box-shadow: 0 5px 20px rgba(0,0,0,0.08);
        transition: all 0.3s;
        border: none;
        position: relative;
        overflow: hidden;
    }
    
    .quick-stat-card::after {
        content: '';
        position: absolute;
        bottom: 0;
        left: 0;
        right: 0;
        height: 5px;
        background: var(--accent-color);
        transform: scaleX(0);
        transition: transform 0.3s;
    }
    
    .quick-stat-card:hover {
        transform: translateY(-8px);
        box-shadow: 0 15px 40px rgba(0,0,0,0.15);
    }
    
    .quick-stat-card:hover::after {
        transform: scaleX(1);
    }
    
    .quick-stat-card.stat-1 { --accent-color: #667eea; }
    .quick-stat-card.stat-2 { --accent-color: #28a745; }
    .quick-stat-card.stat-3 { --accent-color: #17a2b8; }
    .quick-stat-card.stat-4 { --accent-color: #fd7e14; }
    
    .stat-icon-wrapper {
        width: 70px;
        height: 70px;
        border-radius: 50%;
        display: flex;
        align-items: center;
        justify-content: center;
        margin: 0 auto 15px;
        font-size: 30px;
        background: var(--accent-color);
        color: white;
        box-shadow: 0 8px 20px rgba(0,0,0,0.15);
    }
    
    .stat-value {
        font-size: 32px;
        font-weight: 800;
        color: #333;
        margin-bottom: 5px;
    }
    
    .stat-label {
        font-size: 13px;
        color: #888;
        font-weight: 600;
    }
    
    /* Section Cards */
    .section-card {
        background: white;
        border-radius: 20px;
        padding: 25px;
        box-shadow: 0 5px 20px rgba(0,0,0,0.08);
        border: none;
        margin-bottom: 25px;
    }
    
    .section-header {
        display: flex;
        align-items: center;
        justify-content: space-between;
        margin-bottom: 20px;
        padding-bottom: 15px;
        border-bottom: 2px solid #f0f0f0;
    }
    
    .section-title {
        font-size: 18px;
        font-weight: 700;
        color: #333;
        margin: 0;
        display: flex;
        align-items: center;
        gap: 10px;
    }
    
    .section-title i {
        color: var(--section-color);
    }
    
    .section-card.perlu-imun { --section-color: #fd7e14; }
    .section-card.terbaru { --section-color: #17a2b8; }
    
    /* List Items */
    .list-item {
        padding: 15px;
        background: #f8f9fa;
        border-radius: 12px;
        margin-bottom: 10px;
        border-left: 4px solid var(--item-color);
        transition: all 0.3s;
    }
    
    .list-item:hover {
        background: #e9ecef;
        transform: translateX(5px);
    }
    
    .list-item.perlu { --item-color: #fd7e14; }
    .list-item.gizi { --item-color: #17a2b8; }
    
    .item-name {
        font-weight: 700;
        color: #333;
        margin-bottom: 3px;
    }
    
    .item-info {
        font-size: 13px;
        color: #666;
        margin-bottom: 8px;
    }
    
    .item-badge {
        display: inline-block;
        padding: 4px 12px;
        border-radius: 15px;
        font-size: 11px;
        font-weight: 600;
    }
    
    .badge-warning-light { background: #fff3cd; color: #856404; }
    .badge-success-light { background: #d4edda; color: #155724; }
    .badge-info-light { background: #d1ecf1; color: #0c5460; }
    
    /* Progress Circle */
    .progress-circle {
        width: 120px;
        height: 120px;
        border-radius: 50%;
        background: conic-gradient(#28a745 calc(var(--progress) * 1%), #e9ecef 0);
        display: flex;
        align-items: center;
        justify-content: center;
        margin: 0 auto;
        position: relative;
    }
    
    .progress-circle::before {
        content: '';
        position: absolute;
        width: 100px;
        height: 100px;
        background: white;
        border-radius: 50%;
    }
    
    .progress-text {
        position: relative;
        z-index: 1;
        text-align: center;
    }
    
    .progress-number {
        font-size: 24px;
        font-weight: 800;
        color: #28a745;
    }
    
    .progress-label {
        font-size: 11px;
        color: #888;
    }
    
    /* Quick Menu Grid */
    .quick-menu {
        display: grid;
        grid-template-columns: repeat(4, 1fr);
        gap: 15px;
        margin-top: 25px;
    }
    
    .menu-item {
        background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
        color: white;
        border-radius: 15px;
        padding: 20px 15px;
        text-align: center;
        text-decoration: none;
        transition: all 0.3s;
        box-shadow: 0 5px 15px rgba(102,126,234,0.3);
    }
    
    .menu-item:hover {
        transform: translateY(-5px) scale(1.05);
        box-shadow: 0 10px 30px rgba(102,126,234,0.4);
        color: white;
    }
    
    .menu-item i {
        font-size: 28px;
        margin-bottom: 10px;
        display: block;
    }
    
    .menu-item span {
        font-size: 13px;
        font-weight: 600;
    }
</style>

<!-- Welcome Section -->
<div class="welcome-section">
    <div class="row align-items-center">
        <div class="col-md-8">
            <h2>👋 Selamat Datang, <?php echo htmlspecialchars($_SESSION['nama']); ?>!</h2>
            <p>Semoga hari ini menyenangkan. Berikut ringkasan data kesehatan anak hari ini.</p>
            <div class="welcome-date">
                <i class="far fa-calendar me-2"></i><?php echo date('d F Y'); ?>
            </div>
        </div>
        <div class="col-md-4 text-center">
            <div class="progress-circle" style="--progress: <?php echo $imun_total > 0 ? ($imun_lengkap/$imun_total)*100 : 0; ?>">
                <div class="progress-text">
                    <div class="progress-number"><?php echo $imun_total > 0 ? round(($imun_lengkap/$imun_total)*100) : 0; ?>%</div>
                    <div class="progress-label">Cakupan</div>
                </div>
            </div>
        </div>
    </div>
</div>

<!-- Quick Stats -->
<div class="row g-4 mb-4">
    <div class="col-md-3 col-sm-6">
        <div class="quick-stat-card stat-1">
            <div class="stat-icon-wrapper">
                <i class="fas fa-users"></i>
            </div>
            <div class="stat-value"><?php echo $total_anak; ?></div>
            <div class="stat-label">Total Ibu & Anak</div>
        </div>
    </div>
    <div class="col-md-3 col-sm-6">
        <div class="quick-stat-card stat-2">
            <div class="stat-icon-wrapper">
                <i class="fas fa-check-circle"></i>
            </div>
            <div class="stat-value"><?php echo $imun_lengkap; ?></div>
            <div class="stat-label">Imunisasi Lengkap</div>
        </div>
    </div>
    <div class="col-md-3 col-sm-6">
        <div class="quick-stat-card stat-3">
            <div class="stat-icon-wrapper">
                <i class="fas fa-heartbeat"></i>
            </div>
            <div class="stat-value"><?php echo $gizi_normal; ?></div>
            <div class="stat-label">Gizi Normal</div>
        </div>
    </div>
    <div class="col-md-3 col-sm-6">
        <div class="quick-stat-card stat-4">
            <div class="stat-icon-wrapper">
                <i class="fas fa-clock"></i>
            </div>
            <div class="stat-value"><?php echo count($perlu_imunisasi); ?></div>
            <div class="stat-label">Perlu Imunisasi</div>
        </div>
    </div>
</div>

<div class="row">
    <!-- Perlu Imunisasi -->
    <div class="col-lg-6 mb-4">
        <div class="section-card perlu-imun">
            <div class="section-header">
                <h5 class="section-title">
                    <i class="fas fa-exclamation-triangle"></i>
                    Perlu Imunisasi
                </h5>
                <a href="data_ibu_anak.php" class="btn btn-sm btn-outline-primary">Lihat Semua</a>
            </div>
            
            <?php if(count($perlu_imunisasi) > 0): ?>
                <?php foreach($perlu_imunisasi as $item): ?>
                <div class="list-item perlu">
                    <div class="item-name"><?php echo htmlspecialchars($item['nama_anak']); ?></div>
                    <div class="item-info">
                        <i class="fas fa-user me-1"></i><?php echo htmlspecialchars($item['nama_ibu']); ?>
                        <span class="mx-2">•</span>
                        <i class="fas fa-syringe me-1"></i><?php echo $item['jumlah_imun']; ?> imunisasi
                    </div>
                    <span class="item-badge badge-warning-light">
                        <i class="fas fa-clock me-1"></i>Belum Lengkap
                    </span>
                </div>
                <?php endforeach; ?>
            <?php else: ?>
                <p class="text-muted text-center py-3"><i class="fas fa-check-circle text-success me-2"></i>Semua anak sudah imunisasi lengkap!</p>
            <?php endif; ?>
        </div>
    </div>
    
    <!-- Data Gizi Terbaru -->
    <div class="col-lg-6 mb-4">
        <div class="section-card terbaru">
            <div class="section-header">
                <h5 class="section-title">
                    <i class="fas fa-weight"></i>
                    Data Gizi Terbaru
                </h5>
                <a href="status_gizi.php" class="btn btn-sm btn-outline-primary">Lihat Semua</a>
            </div>
            
            <?php if(count($gizi_terbaru) > 0): ?>
                <?php foreach($gizi_terbaru as $item): 
                    $badge_class = match($item['status_gizi']) {
                        'normal' => 'badge-success-light',
                        'kurang' => 'badge-warning-light',
                        'lebih' => 'badge-danger',
                        default => 'badge-secondary'
                    };
                ?>
                <div class="list-item gizi">
                    <div class="item-name"><?php echo htmlspecialchars($item['nama_anak']); ?></div>
                    <div class="item-info">
                        <i class="fas fa-ruler-vertical me-1"></i><?php echo $item['tinggi_badan']; ?> cm
                        <span class="mx-2">•</span>
                        <i class="fas fa-weight me-1"></i><?php echo $item['berat_badan']; ?> kg
                    </div>
                    <span class="item-badge <?php echo $badge_class; ?>">
                        <?php echo ucfirst(str_replace('_', ' ', $item['kategori'])); ?>
                    </span>
                </div>
                <?php endforeach; ?>
            <?php else: ?>
                <p class="text-muted text-center py-3">Belum ada data gizi</p>
            <?php endif; ?>
        </div>
    </div>
</div>

<!-- Quick Menu -->
<div class="quick-menu">
    <a href="data_ibu_anak.php" class="menu-item">
        <i class="fas fa-users"></i>
        <span>Data Ibu & Anak</span>
    </a>
    <a href="tambah_imunisasi.php" class="menu-item">
        <i class="fas fa-syringe"></i>
        <span>Input Imunisasi</span>
    </a>
    <a href="tambah_gizi.php" class="menu-item">
        <i class="fas fa-weight"></i>
        <span>Input Gizi</span>
    </a>
    <a href="laporan.php" class="menu-item">
        <i class="fas fa-chart-bar"></i>
        <span>Laporan</span>
    </a>
</div>

<?php require_once '../includes/footer.php'; ?>