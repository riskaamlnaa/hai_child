<?php
$page_title = 'Kategori Layanan';
require_once '../includes/header_ibu.php';
require_once '../config/database.php';

$db = (new Database())->getConnection();
$user_id = $_SESSION['user_id'];

// Ambil data anak untuk statistik
$stmt = $db->prepare("SELECT id FROM ibu_anak WHERE user_id = ?");
$stmt->execute([$user_id]);
$anak = $stmt->fetch();

// Hitung jumlah imunisasi
$imun_count = 0;
if ($anak) {
    $stmt = $db->prepare("SELECT COUNT(*) FROM imunisasi WHERE ibu_anak_id = ? AND status = 'lengkap'");
    $stmt->execute([$anak['id']]);
    $imun_count = $stmt->fetchColumn();
}
?>

<style>
    * { font-family: 'Poppins', sans-serif; }
    
    .kategori-wrapper {
        padding: 40px 15px 100px;
        max-width: 900px;
        margin: 0 auto;
    }
    
    .page-header {
        text-align: center;
        margin-bottom: 40px;
        margin-top: 20px;
        padding-top: 20px;
    }
    
    .page-header h2 {
        color: #333;
        font-weight: 800;
        font-size: 28px;
        margin-bottom: 8px;
    }
    
    .page-header p {
        color: #888;
        font-size: 14px;
    }
    
    .kategori-grid {
        display: grid;
        grid-template-columns: repeat(2, 1fr);
        gap: 20px;
        margin-bottom: 30px;
    }
    
    .kategori-card {
        background: white;
        border-radius: 24px;
        padding: 35px 25px;
        text-align: center;
        text-decoration: none;
        box-shadow: 0 8px 30px rgba(0,0,0,0.08);
        transition: all 0.3s ease;
        position: relative;
        overflow: hidden;
        border: 2px solid transparent;
    }
    
    .kategori-card::before {
        content: '';
        position: absolute;
        top: 0;
        left: 0;
        right: 0;
        height: 6px;
        background: linear-gradient(90deg, var(--primary), var(--secondary));
        transform: scaleX(0);
        transition: transform 0.3s ease;
    }
    
    .kategori-card:hover {
        transform: translateY(-10px);
        box-shadow: 0 15px 50px rgba(0,0,0,0.15);
        border-color: var(--primary);
    }
    
    .kategori-card:hover::before {
        transform: scaleX(1);
    }
    
    .kategori-card.imunisasi { --primary: #ff6b9d; --secondary: #ff9a9e; }
    .kategori-card.gizi { --primary: #4facfe; --secondary: #00f2fe; }
    .kategori-card.edukasi { --primary: #43e97b; --secondary: #38f9d7; }
    .kategori-card.profil { --primary: #fa709a; --secondary: #fee140; }
    
    .kategori-icon {
        width: 90px;
        height: 90px;
        margin: 0 auto 20px;
        border-radius: 50%;
        display: flex;
        align-items: center;
        justify-content: center;
        font-size: 40px;
        background: linear-gradient(135deg, var(--primary), var(--secondary));
        color: white;
        box-shadow: 0 10px 30px rgba(0,0,0,0.2);
        transition: transform 0.3s;
    }
    
    .kategori-card:hover .kategori-icon {
        transform: scale(1.1) rotate(5deg);
    }
    
    .kategori-title {
        font-size: 20px;
        font-weight: 700;
        color: #333;
        margin-bottom: 10px;
    }
    
    .kategori-desc {
        font-size: 13px;
        color: #888;
        line-height: 1.6;
    }
    
    .kategori-badge {
        position: absolute;
        top: 15px;
        right: 15px;
        background: #ff6b6b;
        color: white;
        width: 28px;
        height: 28px;
        border-radius: 50%;
        display: flex;
        align-items: center;
        justify-content: center;
        font-size: 12px;
        font-weight: 700;
        box-shadow: 0 3px 10px rgba(255,107,107,0.4);
    }
    
    .tips-section {
        background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
        border-radius: 24px;
        padding: 30px;
        color: white;
        box-shadow: 0 10px 40px rgba(102,126,234,0.3);
        margin-top: 20px;
    }
    
    .tips-header {
        display: flex;
        align-items: center;
        gap: 12px;
        margin-bottom: 20px;
    }
    
    .tips-header i {
        font-size: 28px;
    }
    
    .tips-header h3 {
        font-weight: 700;
        margin: 0;
        font-size: 20px;
    }
    
    .tips-list {
        list-style: none;
        padding: 0;
        margin: 0;
    }
    
    .tips-list li {
        padding: 12px 0;
        border-bottom: 1px solid rgba(255,255,255,0.2);
        font-size: 14px;
        display: flex;
        align-items: center;
        gap: 12px;
    }
    
    .tips-list li:last-child {
        border-bottom: none;
    }
    
    .tips-list i {
        font-size: 16px;
        color: #ffd93d;
    }
    
    .quick-actions {
        margin-top: 20px;
        display: flex;
        gap: 10px;
        flex-wrap: wrap;
    }
    
    .quick-btn {
        flex: 1;
        min-width: 120px;
        background: rgba(255,255,255,0.2);
        border: 2px solid rgba(255,255,255,0.3);
        color: white;
        padding: 12px 20px;
        border-radius: 15px;
        text-decoration: none;
        text-align: center;
        font-weight: 600;
        font-size: 13px;
        transition: all 0.3s;
    }
    
    .quick-btn:hover {
        background: white;
        color: #667eea;
        transform: translateY(-2px);
    }
    
    @media (max-width: 600px) {
        .kategori-grid {
            grid-template-columns: 1fr;
        }
    }
</style>

<div class="kategori-wrapper">
    <div class="page-header">
        <h2><i class="fas fa-th-large me-2"></i>Kategori Layanan</h2>
        <p>Pilih layanan yang ingin Anda akses</p>
    </div>
    
    <div class="kategori-grid">
        <!-- Imunisasi -->
        <a href="riwayat_imunisasi.php" class="kategori-card imunisasi">
            <?php if ($imun_count > 0): ?>
            <div class="kategori-badge"><?php echo $imun_count; ?></div>
            <?php endif; ?>
            <div class="kategori-icon">
                <i class="fas fa-syringe"></i>
            </div>
            <h3 class="kategori-title">Imunisasi</h3>
            <p class="kategori-desc">Jadwal & riwayat imunisasi si kecil</p>
        </a>
        
        <!-- Status Gizi -->
        <a href="status_gizi.php" class="kategori-card gizi">
            <div class="kategori-icon">
                <i class="fas fa-weight"></i>
            </div>
            <h3 class="kategori-title">Status Gizi</h3>
            <p class="kategori-desc">Pantau pertumbuhan balita</p>
        </a>
        
        <!-- Edukasi -->
        <a href="edukasi.php" class="kategori-card edukasi">
            <div class="kategori-icon">
                <i class="fas fa-book-open"></i>
            </div>
            <h3 class="kategori-title">Edukasi</h3>
            <p class="kategori-desc">Informasi kesehatan anak</p>
        </a>
        
        <!-- Profil -->
        <a href="profil.php" class="kategori-card profil">
            <div class="kategori-icon">
                <i class="fas fa-user-circle"></i>
            </div>
            <h3 class="kategori-title">Profil</h3>
            <p class="kategori-desc">Pengaturan akun</p>
        </a>
    </div>
    
    <!-- Tips Section -->
    <div class="tips-section">
        <div class="tips-header">
            <i class="fas fa-lightbulb"></i>
            <h3>Tips Hari Ini</h3>
        </div>
        <ul class="tips-list">
            <li>
                <i class="fas fa-check-circle"></i>
                <span>Berikan ASI eksklusif selama 6 bulan pertama untuk kekebalan tubuh optimal</span>
            </li>
            <li>
                <i class="fas fa-check-circle"></i>
                <span>Imunisasi lengkap sesuai jadwal untuk mencegah penyakit berbahaya</span>
            </li>
            <li>
                <i class="fas fa-check-circle"></i>
                <span>Pantau berat dan tinggi badan rutin setiap bulan di Posyandu</span>
            </li>
        </ul>
        <div class="quick-actions">
            <a href="riwayat_imunisasi.php" class="quick-btn">
                <i class="fas fa-syringe me-1"></i> Lihat Imunisasi
            </a>
            <a href="status_gizi.php" class="quick-btn">
                <i class="fas fa-weight me-1"></i> Cek Gizi
            </a>
        </div>
    </div>
</div>

<?php require_once '../includes/footer.php'; ?>