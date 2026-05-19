<?php 
$page_title = 'Kategori'; 
require_once '../includes/header_ibu.php'; 
?>

<style>
.kategori-container {
    max-width: 800px;
    margin: 0 auto;
    padding: 20px 15px 100px 15px;
}

.page-header {
    background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
    border-radius: 20px;
    padding: 30px 25px;
    color: white;
    margin-bottom: 25px;
    text-align: center;
    box-shadow: 0 10px 30px rgba(102, 126, 234, 0.3);
}

.page-header h3 {
    margin: 0 0 10px 0;
    font-size: 26px;
}

.page-header p {
    margin: 0;
    opacity: 0.95;
    font-size: 15px;
}

/* Category Sections */
.category-section {
    margin-bottom: 30px;
}

.section-title {
    font-size: 18px;
    font-weight: bold;
    color: #333;
    margin-bottom: 15px;
    padding-left: 10px;
    border-left: 4px solid #667eea;
}

/* Feature Cards Grid */
.feature-grid {
    display: grid;
    grid-template-columns: repeat(auto-fit, minmax(250px, 1fr));
    gap: 15px;
}

.feature-card {
    background: white;
    border-radius: 15px;
    padding: 25px 20px;
    text-decoration: none;
    color: #333;
    box-shadow: 0 5px 15px rgba(0,0,0,0.08);
    transition: all 0.3s;
    display: flex;
    align-items: flex-start;
}

.feature-card:hover {
    transform: translateY(-5px);
    box-shadow: 0 10px 25px rgba(0,0,0,0.15);
}

.feature-icon {
    width: 60px;
    height: 60px;
    border-radius: 15px;
    display: flex;
    align-items: center;
    justify-content: center;
    margin-right: 15px;
    font-size: 28px;
    color: white;
    flex-shrink: 0;
}

.feature-content {
    flex: 1;
}

.feature-content h5 {
    margin: 0 0 8px 0;
    font-size: 16px;
    color: #333;
}

.feature-content p {
    margin: 0;
    font-size: 13px;
    color: #888;
    line-height: 1.6;
}

/* Info Boxes */
.info-box {
    background: white;
    border-radius: 15px;
    padding: 25px;
    margin-bottom: 20px;
    box-shadow: 0 5px 15px rgba(0,0,0,0.08);
}

.info-box-header {
    display: flex;
    align-items: center;
    margin-bottom: 15px;
}

.info-box-icon {
    width: 50px;
    height: 50px;
    border-radius: 12px;
    display: flex;
    align-items: center;
    justify-content: center;
    margin-right: 15px;
    color: white;
    font-size: 24px;
}

.info-box h4 {
    margin: 0;
    font-size: 18px;
    color: #333;
}

.info-box-content {
    color: #666;
    line-height: 1.8;
    font-size: 14px;
}

.info-box-content ul {
    margin: 10px 0;
    padding-left: 20px;
}

.info-box-content li {
    margin-bottom: 8px;
}

/* Tips Box */
.tips-box {
    background: linear-gradient(135deg, #fff5f5 0%, #ffe0e0 100%);
    border-left: 4px solid #f5576c;
    border-radius: 10px;
    padding: 20px;
    margin-top: 25px;
}

.tips-box h5 {
    color: #f5576c;
    margin: 0 0 10px 0;
    font-size: 16px;
}

.tips-box p {
    margin: 0;
    color: #555;
    line-height: 1.7;
}
</style>

<div class="kategori-container">
    <!-- Page Header -->
    <div class="page-header">
        <h3><i class="fas fa-th-large me-2"></i>Kategori Layanan</h3>
        <p>Pilih kategori untuk melihat informasi dan layanan kesehatan anak</p>
    </div>

    <!-- Kesehatan Anak -->
    <div class="category-section">
        <div class="section-title">🏥 Kesehatan Anak</div>
        <div class="feature-grid">
            <a href="riwayat_imunisasi.php" class="feature-card">
                <div class="feature-icon" style="background: linear-gradient(135deg, #f093fb 0%, #f5576c 100%);">
                    <i class="fas fa-syringe"></i>
                </div>
                <div class="feature-content">
                    <h5>Imunisasi</h5>
                    <p>Lihat jadwal dan riwayat imunisasi anak Anda</p>
                </div>
            </a>
            <a href="status_gizi.php" class="feature-card">
                <div class="feature-icon" style="background: linear-gradient(135deg, #4facfe 0%, #00f2fe 100%);">
                    <i class="fas fa-weight"></i>
                </div>
                <div class="feature-content">
                    <h5>Status Gizi</h5>
                    <p>Pantau pertumbuhan dan status gizi balita</p>
                </div>
            </a>
            <a href="#" class="feature-card">
                <div class="feature-icon" style="background: linear-gradient(135deg, #43e97b 0%, #38f9d7 100%);">
                    <i class="fas fa-stethoscope"></i>
                </div>
                <div class="feature-content">
                    <h5>Periksa Kesehatan</h5>
                    <p>Jadwal pemeriksaan rutin di Puskesmas</p>
                </div>
            </a>
        </div>
    </div>

    <!-- Edukasi & Informasi -->
    <div class="category-section">
        <div class="section-title">📚 Edukasi & Informasi</div>
        
        <div class="info-box">
            <div class="info-box-header">
                <div class="info-box-icon" style="background: linear-gradient(135deg, #fa709a 0%, #fee140 100%);">
                    <i class="fas fa-book-open"></i>
                </div>
                <h4>Edukasi Kesehatan</h4>
            </div>
            <div class="info-box-content">
                <p>Informasi penting tentang kesehatan ibu dan anak:</p>
                <ul>
                    <li><strong>Imunisasi Dasar:</strong> Hepatitis B, BCG, Polio, DPT, Campak</li>
                    <li><strong>Gizi Seimbang:</strong> ASI, MP-ASI, makanan bergizi</li>
                    <li><strong>Tumbuh Kembang:</strong> Stimulasi dan monitoring</li>
                    <li><strong>Kesehatan Ibu:</strong> Perawatan pasca melahirkan</li>
                </ul>
                <a href="edukasi.php" class="btn" style="background: linear-gradient(135deg, #667eea 0%, #764ba2 100%); color: white; padding: 10px 25px; border-radius: 20px; text-decoration: none; display: inline-block; margin-top: 10px;">
                    <i class="fas fa-arrow-right me-2"></i>Lihat Selengkapnya
                </a>
            </div>
        </div>

        <div class="info-box">
            <div class="info-box-header">
                <div class="info-box-icon" style="background: linear-gradient(135deg, #30cfd0 0%, #330867 100%);">
                    <i class="fas fa-calendar-alt"></i>
                </div>
                <h4>Jadwal Imunisasi</h4>
            </div>
            <div class="info-box-content">
                <p><strong>Usia 0-12 Bulan:</strong></p>
                <ul>
                    <li>0-7 hari: Hepatitis B-0</li>
                    <li>1 bulan: BCG, Polio-1</li>
                    <li>2 bulan: DPT-HB-Hib-1, Polio-2</li>
                    <li>3 bulan: DPT-HB-Hib-2, Polio-3</li>
                    <li>4 bulan: DPT-HB-Hib-3, Polio-4, IPV</li>
                    <li>9 bulan: Campak/MR</li>
                </ul>
            </div>
        </div>
    </div>

    <!-- Layanan Tambahan -->
    <div class="category-section">
        <div class="section-title">🌟 Layanan Tambahan</div>
        <div class="feature-grid">
            <a href="cari.php" class="feature-card">
                <div class="feature-icon" style="background: linear-gradient(135deg, #a8edea 0%, #fed6e3 100%);">
                    <i class="fas fa-search"></i>
                </div>
                <div class="feature-content">
                    <h5>Pencarian</h5>
                    <p>Cari informasi imunisasi dan gizi</p>
                </div>
            </a>
            <a href="profil.php" class="feature-card">
                <div class="feature-icon" style="background: linear-gradient(135deg, #ffecd2 0%, #fcb69f 100%);">
                    <i class="fas fa-user-circle"></i>
                </div>
                <div class="feature-content">
                    <h5>Profil Saya</h5>
                    <p>Kelola akun dan pengaturan</p>
                </div>
            </a>
        </div>
    </div>

    <!-- Tips Box -->
    <div class="tips-box">
        <h5><i class="fas fa-lightbulb me-2"></i>Tips Kesehatan</h5>
        <p>
            <strong>Berikan ASI Eksklusif</strong> selama 6 bulan pertama untuk kekebalan tubuh optimal. 
            Lanjutkan dengan MP-ASI bergizi dan imunisasi lengkap sesuai jadwal. 
            Rutin periksa ke Posyandu/Puskesmas untuk memantau tumbuh kembang anak.
        </p>
    </div>
</div>

<!-- Bottom Navigation -->
<div style="position: fixed; bottom: 0; left: 0; right: 0; background: #ffffff; display: flex; justify-content: space-around; padding: 12px 0 18px 0; box-shadow: 0 -4px 12px rgba(0,0,0,0.1); z-index: 9999; border-top: 1px solid #eee;">
    <a href="dashboard.php" style="text-align: center; color: #888; text-decoration: none; font-size: 12px; flex: 1;">
        <i class="fas fa-home" style="font-size: 22px; display: block; margin-bottom: 4px;"></i>
        <span>Beranda</span>
    </a>
    <a href="kategori.php" style="text-align: center; color: #667eea; text-decoration: none; font-size: 12px; flex: 1;">
        <i class="fas fa-th-large" style="font-size: 22px; display: block; margin-bottom: 4px;"></i>
        <span style="font-weight: bold;">Kategori</span>
    </a>
    <a href="cari.php" style="text-align: center; color: #888; text-decoration: none; font-size: 12px; flex: 1;">
        <i class="fas fa-search" style="font-size: 22px; display: block; margin-bottom: 4px;"></i>
        <span>Cari</span>
    </a>
    <a href="profil.php" style="text-align: center; color: #888; text-decoration: none; font-size: 12px; flex: 1;">
        <i class="fas fa-user" style="font-size: 22px; display: block; margin-bottom: 4px;"></i>
        <span>Profil</span>
    </a>
</div>

<?php require_once '../includes/footer.php'; ?>