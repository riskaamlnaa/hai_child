<?php
session_start();
require_once '../includes/functions.php';
require_once '../config/database.php';

// Cek login
if (!isset($_SESSION['user_id']) || $_SESSION['role'] !== 'ibu_anak') {
    header("Location: ../login.php");
    exit();
}

$page_title = 'Beranda';
$db = (new Database())->getConnection();
$user_id = $_SESSION['user_id'];

// Ambil data anak
$stmt = $db->prepare("SELECT * FROM ibu_anak WHERE user_id = ?");
$stmt->execute([$user_id]);
$anak = $stmt->fetch();

// Data imunisasi
$imun_total = 0;
$imun_lengkap = 0;
if ($anak) {
    $stmt = $db->prepare("SELECT COUNT(*) FROM imunisasi WHERE ibu_anak_id = ?");
    $stmt->execute([$anak['id']]);
    $imun_total = $stmt->fetchColumn();
    
    $stmt = $db->prepare("SELECT COUNT(*) FROM imunisasi WHERE ibu_anak_id = ? AND status = 'lengkap'");
    $stmt->execute([$anak['id']]);
    $imun_lengkap = $stmt->fetchColumn();
}

// Data gizi
$status_gizi_text = 'Belum Diukur';
$status_gizi_color = '#888';
$status_gizi_icon = 'fa-question';
if ($anak) {
    $stmt = $db->prepare("SELECT status_gizi, kategori FROM status_gizi WHERE ibu_anak_id = ? ORDER BY tanggal_ukur DESC LIMIT 1");
    $stmt->execute([$anak['id']]);
    $gizi = $stmt->fetch();
    if ($gizi) {
        if ($gizi['status_gizi'] == 'normal') {
            $status_gizi_text = 'Gizi Baik';
            $status_gizi_color = '#28a745';
            $status_gizi_icon = 'fa-smile';
        } elseif ($gizi['kategori'] == 'kurus') {
            $status_gizi_text = 'Kurang Gizi';
            $status_gizi_color = '#ffc107';
            $status_gizi_icon = 'fa-frown';
        } else {
            $status_gizi_text = 'Gizi Lebih';
            $status_gizi_color = '#dc3545';
            $status_gizi_icon = 'fa-tired';
        }
    }
}

// Hitung umur
$umur_bulan = '-';
if ($anak) {
    $birth = new DateTime($anak['tanggal_lahir_anak']);
    $now = new DateTime();
    $diff = $now->diff($birth);
    $umur_bulan = ($diff->y * 12) + $diff->m;
}
?>
<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?php echo $page_title; ?> - Hai Child</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <link href="https://fonts.googleapis.com/css2?family=Poppins:wght@300;400;500;600;700;800&display=swap" rel="stylesheet">
    <style>
        * { font-family: 'Poppins', sans-serif; }
        body { background: linear-gradient(180deg, #f5f7fa 0%, #e9ecef 100%); padding-bottom: 80px; }
        
        /* Header */
        .topbar {
            background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
            padding: 25px 20px 80px;
            color: white;
            border-radius: 0 0 30px 30px;
            box-shadow: 0 10px 30px rgba(102,126,234,0.3);
        }
        
        /* Welcome Card */
        .welcome-card {
            background: white;
            border-radius: 25px;
            margin: -60px 15px 20px;
            padding: 30px 20px;
            text-align: center;
            box-shadow: 0 10px 40px rgba(0,0,0,0.1);
            position: relative;
            z-index: 10;
        }
        
        .child-avatar {
            width: 100px;
            height: 100px;
            border-radius: 50%;
            background: linear-gradient(135deg, #667eea, #764ba2);
            display: flex;
            align-items: center;
            justify-content: center;
            margin: 0 auto 15px;
            font-size: 50px;
            color: white;
            box-shadow: 0 8px 25px rgba(102,126,234,0.4);
        }
        
        .child-name {
            font-size: 24px;
            font-weight: 700;
            color: #333;
            margin-bottom: 5px;
        }
        
        .child-info {
            display: flex;
            justify-content: center;
            gap: 15px;
            flex-wrap: wrap;
            margin-top: 10px;
        }
        
        .info-badge {
            background: #f0f2f5;
            padding: 6px 15px;
            border-radius: 20px;
            font-size: 12px;
            font-weight: 600;
            color: #666;
        }
        
        /* Stats Cards */
        .stats-row {
            display: grid;
            grid-template-columns: 1fr 1fr;
            gap: 15px;
            padding: 0 15px;
            margin-bottom: 25px;
        }
        
        .stat-card {
            background: white;
            border-radius: 20px;
            padding: 20px;
            box-shadow: 0 5px 20px rgba(0,0,0,0.08);
            transition: transform 0.3s;
        }
        
        .stat-card:hover {
            transform: translateY(-5px);
        }
        
        .stat-icon {
            width: 50px;
            height: 50px;
            border-radius: 15px;
            display: flex;
            align-items: center;
            justify-content: center;
            font-size: 24px;
            color: white;
            margin-bottom: 12px;
        }
        
        .stat-card.imun .stat-icon { background: linear-gradient(135deg, #ff6b9d, #ff9a9e); }
        .stat-card.gizi .stat-icon { background: linear-gradient(135deg, #4facfe, #00f2fe); }
        
        .stat-value {
            font-size: 28px;
            font-weight: 800;
            color: #333;
            margin-bottom: 3px;
        }
        
        .stat-label {
            font-size: 12px;
            color: #888;
            font-weight: 500;
        }
        
        /* Menu Grid */
        .menu-section {
            padding: 0 15px;
            margin-bottom: 25px;
        }
        
        .section-title {
            font-size: 18px;
            font-weight: 700;
            color: #333;
            margin-bottom: 15px;
            display: flex;
            align-items: center;
            gap: 8px;
        }
        
        .menu-grid {
            display: grid;
            grid-template-columns: repeat(3, 1fr);
            gap: 12px;
        }
        
        .menu-item {
            background: white;
            border-radius: 18px;
            padding: 20px 10px;
            text-align: center;
            text-decoration: none;
            color: #333;
            box-shadow: 0 5px 15px rgba(0,0,0,0.06);
            transition: all 0.3s;
        }
        
        .menu-item:hover {
            transform: translateY(-5px);
            box-shadow: 0 10px 25px rgba(0,0,0,0.12);
        }
        
        .menu-icon {
            width: 55px;
            height: 55px;
            border-radius: 16px;
            display: inline-flex;
            align-items: center;
            justify-content: center;
            font-size: 24px;
            color: white;
            margin-bottom: 10px;
        }
        
        .menu-item:nth-child(1) .menu-icon { background: linear-gradient(135deg, #ff6b9d, #ff9a9e); }
        .menu-item:nth-child(2) .menu-icon { background: linear-gradient(135deg, #4facfe, #00f2fe); }
        .menu-item:nth-child(3) .menu-icon { background: linear-gradient(135deg, #43e97b, #38f9d7); }
        .menu-item:nth-child(4) .menu-icon { background: linear-gradient(135deg, #fa709a, #fee140); }
        .menu-item:nth-child(5) .menu-icon { background: linear-gradient(135deg, #a18cd1, #fbc2eb); }
        .menu-item:nth-child(6) .menu-icon { background: linear-gradient(135deg, #ffecd2, #fcb69f); }
        
        .menu-title {
            font-size: 12px;
            font-weight: 600;
        }
        
        /* Bottom Nav */
        .bottom-nav {
            position: fixed;
            bottom: 0;
            left: 0;
            right: 0;
            background: white;
            padding: 12px 20px 20px;
            display: flex;
            justify-content: space-around;
            box-shadow: 0 -5px 20px rgba(0,0,0,0.1);
            border-radius: 25px 25px 0 0;
            z-index: 100;
        }
        
        .nav-item {
            text-align: center;
            color: #999;
            text-decoration: none;
            font-size: 11px;
            font-weight: 600;
            flex: 1;
        }
        
        .nav-item i {
            font-size: 22px;
            display: block;
            margin-bottom: 4px;
        }
        
        .nav-item.active {
            color: #667eea;
        }
        
        /* Progress Bar */
        .progress-container {
            background: white;
            border-radius: 15px;
            padding: 20px;
            margin: 0 15px 25px;
            box-shadow: 0 5px 15px rgba(0,0,0,0.06);
        }
        
        .progress-label {
            display: flex;
            justify-content: space-between;
            margin-bottom: 10px;
            font-size: 13px;
            font-weight: 600;
        }
        
        .progress-bar {
            height: 10px;
            background: #e9ecef;
            border-radius: 10px;
            overflow: hidden;
        }
        
        .progress-fill {
            height: 100%;
            background: linear-gradient(90deg, #667eea, #764ba2);
            border-radius: 10px;
            transition: width 0.5s;
        }
    </style>
</head>
<body>
    <!-- Top Bar -->
    <div class="topbar">
        <div class="d-flex justify-content-between align-items-center">
            <div>
                <h4 class="mb-0"><i class="fas fa-baby-carriage me-2"></i>Hai Child</h4>
                <small>Panel Ibu</small>
            </div>
            <div class="d-flex align-items-center gap-3">
                <span class="d-none d-sm-block"><?php echo htmlspecialchars($_SESSION['nama']); ?></span>
                <a href="../logout.php" class="text-white"><i class="fas fa-sign-out-alt fa-lg"></i></a>
            </div>
        </div>
    </div>

    <div class="container" style="max-width: 600px;">
        <!-- Welcome Card -->
        <div class="welcome-card">
            <div class="child-avatar">
                <?php echo $anak && $anak['jenis_kelamin'] == 'L' ? '👦' : '👧'; ?>
            </div>
            <h2 class="child-name"><?php echo $anak ? htmlspecialchars($anak['nama_anak']) : 'Selamat Datang!'; ?></h2>
            <?php if ($anak): ?>
            <div class="child-info">
                <span class="info-badge">🎂 <?php echo $umur_bulan; ?> Bulan</span>
                <span class="info-badge"><?php echo $anak['jenis_kelamin'] == 'L' ? '👦 Laki-laki' : '👧 Perempuan'; ?></span>
            </div>
            <?php else: ?>
            <p class="text-muted small">Data anak belum tersedia. Hubungi bidan untuk pendaftaran.</p>
            <?php endif; ?>
        </div>

        <!-- Stats -->
        <div class="stats-row">
            <div class="stat-card imun">
                <div class="stat-icon"><i class="fas fa-syringe"></i></div>
                <div class="stat-value"><?php echo $imun_lengkap; ?> / <?php echo $imun_total; ?></div>
                <div class="stat-label">Imunisasi Lengkap</div>
            </div>
            <div class="stat-card gizi">
                <div class="stat-icon"><i class="fas fa-<?php echo $status_gizi_icon; ?>"></i></div>
                <div class="stat-value" style="font-size: 20px; color: <?php echo $status_gizi_color; ?>;"><?php echo $status_gizi_text; ?></div>
                <div class="stat-label">Status Gizi</div>
            </div>
        </div>

        <!-- Progress Imunisasi -->
        <?php if ($anak): ?>
        <div class="progress-container">
            <div class="progress-label">
                <span><i class="fas fa-chart-line me-2"></i>Progress Imunisasi</span>
                <span><?php echo $imun_total > 0 ? round(($imun_lengkap/$imun_total)*100) : 0; ?>%</span>
            </div>
            <div class="progress-bar">
                <div class="progress-fill" style="width: <?php echo $imun_total > 0 ? ($imun_lengkap/$imun_total)*100 : 0; ?>%"></div>
            </div>
        </div>
        <?php endif; ?>

        <!-- Menu Grid -->
        <div class="menu-section">
            <h4 class="section-title"><i class="fas fa-th-large text-primary"></i>Menu Layanan</h4>
            <div class="menu-grid">
                <a href="riwayat_imunisasi.php" class="menu-item">
                    <div class="menu-icon"><i class="fas fa-syringe"></i></div>
                    <div class="menu-title">Imunisasi</div>
                </a>
                <a href="status_gizi.php" class="menu-item">
                    <div class="menu-icon"><i class="fas fa-weight"></i></div>
                    <div class="menu-title">Gizi</div>
                </a>
                <a href="edukasi.php" class="menu-item">
                    <div class="menu-icon"><i class="fas fa-book-open"></i></div>
                    <div class="menu-title">Edukasi</div>
                </a>
                <a href="kategori.php" class="menu-item">
                    <div class="menu-icon"><i class="fas fa-th-large"></i></div>
                    <div class="menu-title">Kategori</div>
                </a>
                <a href="cari.php" class="menu-item">
                    <div class="menu-icon"><i class="fas fa-search"></i></div>
                    <div class="menu-title">Cari</div>
                </a>
                <a href="profil.php" class="menu-item">
                    <div class="menu-icon"><i class="fas fa-user-circle"></i></div>
                    <div class="menu-title">Profil</div>
                </a>
            </div>
        </div>
    </div>

    <!-- Bottom Navigation -->
    <div class="bottom-nav">
        <a href="dashboard.php" class="nav-item active">
            <i class="fas fa-home"></i>
            <span>Beranda</span>
        </a>
        <a href="kategori.php" class="nav-item">
            <i class="fas fa-th-large"></i>
            <span>Kategori</span>
        </a>
        <a href="cari.php" class="nav-item">
            <i class="fas fa-search"></i>
            <span>Cari</span>
        </a>
        <a href="profil.php" class="nav-item">
            <i class="fas fa-user"></i>
            <span>Profil</span>
        </a>
    </div>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>