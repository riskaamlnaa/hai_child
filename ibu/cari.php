<?php
$page_title = 'Pencarian';
require_once '../includes/header_ibu.php';
require_once '../config/database.php';

$db = (new Database())->getConnection();
$user_id = $_SESSION['user_id'];

// Ambil ID anak
$stmt = $db->prepare("SELECT id, nama_anak FROM ibu_anak WHERE user_id = ?");
$stmt->execute([$user_id]);
$anak = $stmt->fetch();
$anak_id = $anak ? $anak['id'] : 0;

// Ambil keyword dari URL
$keyword = isset($_GET['q']) ? trim($_GET['q']) : '';
$hasil = [];

// Proses pencarian jika ada keyword
if (!empty($keyword) && $anak_id) {
    // 1. CARI DI IMUNISASI
    try {
        $stmt = $db->prepare("SELECT jenis_imunisasi, tanggal_imunisasi, status, keterangan 
                              FROM imunisasi 
                              WHERE ibu_anak_id = ? 
                              AND (jenis_imunisasi LIKE ? OR keterangan LIKE ?)
                              ORDER BY tanggal_imunisasi DESC");
        $stmt->execute([$anak_id, "%$keyword%", "%$keyword%"]);
        $imunisasi = $stmt->fetchAll();
        
        foreach ($imunisasi as $row) {
            $hasil[] = [
                'tipe' => 'imunisasi',
                'label' => 'Riwayat Imunisasi',
                'judul' => $row['jenis_imunisasi'],
                'deskripsi' => 'Status: ' . ucfirst($row['status']) . ' - ' . date('d M Y', strtotime($row['tanggal_imunisasi'])),
                'icon' => 'fa-syringe',
                'warna' => '#ff6b9d',
                'link' => 'riwayat_imunisasi.php'
            ];
        }
    } catch(Exception $e) {
        // Abaikan jika error
    }
    
    // 2. CARI DI STATUS GIZI
    try {
        $stmt = $db->prepare("SELECT kategori, berat_badan, tinggi_badan, tanggal_ukur 
                              FROM status_gizi 
                              WHERE ibu_anak_id = ? 
                              AND (kategori LIKE ?)
                              ORDER BY tanggal_ukur DESC");
        $stmt->execute([$anak_id, "%$keyword%"]);
        $gizi = $stmt->fetchAll();
        
        foreach ($gizi as $row) {
            $hasil[] = [
                'tipe' => 'gizi',
                'label' => 'Status Gizi',
                'judul' => 'Pengukuran - ' . ucfirst($row['kategori']),
                'deskripsi' => $row['berat_badan'] . ' kg / ' . $row['tinggi_badan'] . ' cm - ' . date('d M Y', strtotime($row['tanggal_ukur'])),
                'icon' => 'fa-weight',
                'warna' => '#4facfe',
                'link' => 'status_gizi.php'
            ];
        }
    } catch(Exception $e) {
        // Abaikan jika error
    }
    
    // 3. ARTIKEL EDUKASI (Data Statis)
    $edukasi = [
        [
            'judul' => 'Jadwal Imunisasi Dasar Lengkap',
            'deskripsi' => 'Hepatitis B, BCG, Polio, DPT-HB-Hib, dan Campak/MR sesuai usia anak.',
            'keyword' => 'imunisasi jadwal vaksin dasar campak polio bcg dpt',
            'icon' => 'fa-book-medical',
            'warna' => '#43e97b',
            'link' => '#'
        ],
        [
            'judul' => 'Imunisasi Campak MR',
            'deskripsi' => 'Diberikan pada usia 9 bulan untuk mencegah campak dan rubella.',
            'keyword' => 'campak mr 9 bulan imunisasi',
            'icon' => 'fa-syringe',
            'warna' => '#43e97b',
            'link' => '#'
        ],
        [
            'judul' => 'Status Gizi Normal',
            'deskripsi' => 'Anak dengan gizi baik memiliki berat badan ideal dan aktif.',
            'keyword' => 'gizi normal berat badan ideal sehat',
            'icon' => 'fa-weight',
            'warna' => '#43e97b',
            'link' => '#'
        ],
        [
            'judul' => 'MPASI Usia 6 Bulan',
            'deskripsi' => 'Mulai MPASI dengan tekstur lumat, tingkatkan bertahap.',
            'keyword' => 'mpasi asi 6 bulan makan bayi',
            'icon' => 'fa-utensils',
            'warna' => '#43e97b',
            'link' => '#'
        ],
        [
            'judul' => 'Imunisasi Polio',
            'deskripsi' => 'Diberikan 4 kali untuk mencegah kelumpuhan.',
            'keyword' => 'polio imunisasi tetes lumpuh',
            'icon' => 'fa-syringe',
            'warna' => '#43e97b',
            'link' => '#'
        ]
    ];
    
    foreach ($edukasi as $art) {
        if (stripos($art['keyword'], strtolower($keyword)) !== false || 
            stripos($art['judul'], $keyword) !== false) {
            $hasil[] = [
                'tipe' => 'edukasi',
                'label' => 'Artikel Edukasi',
                'judul' => $art['judul'],
                'deskripsi' => $art['deskripsi'],
                'icon' => $art['icon'],
                'warna' => $art['warna'],
                'link' => '#'
            ];
        }
    }
}
?>

<style>
    .search-wrapper {
        max-width: 800px;
        margin: 0 auto;
        padding: 30px 20px 100px;
    }
    
    .search-box-main {
        background: white;
        border-radius: 20px;
        padding: 25px;
        box-shadow: 0 10px 40px rgba(0,0,0,0.1);
        margin-bottom: 30px;
    }
    
    .search-form-main {
        display: flex;
        gap: 10px;
    }
    
    .search-input-main {
        flex: 1;
        padding: 15px 20px;
        border: 2px solid #e0e0e0;
        border-radius: 12px;
        font-size: 15px;
        outline: none;
        transition: all 0.3s;
    }
    
    .search-input-main:focus {
        border-color: #667eea;
        box-shadow: 0 0 0 3px rgba(102,126,234,0.1);
    }
    
    .search-btn-main {
        padding: 15px 30px;
        background: linear-gradient(135deg, #667eea, #764ba2);
        color: white;
        border: none;
        border-radius: 12px;
        font-weight: 700;
        cursor: pointer;
        transition: transform 0.2s;
    }
    
    .search-btn-main:hover {
        transform: translateY(-2px);
    }
    
    .result-header {
        margin-bottom: 20px;
        padding: 15px;
        background: #e3f2fd;
        border-radius: 10px;
        border-left: 4px solid #2196f3;
    }
    
    .result-item {
        background: white;
        border-radius: 15px;
        padding: 20px;
        margin-bottom: 15px;
        box-shadow: 0 3px 15px rgba(0,0,0,0.05);
        border-left: 5px solid var(--item-color);
        transition: all 0.3s;
        display: block;
        text-decoration: none;
        color: inherit;
    }
    
    .result-item:hover {
        transform: translateX(5px);
        box-shadow: 0 5px 25px rgba(0,0,0,0.1);
        background: #f8f9fa;
    }
    
    .result-icon-box {
        width: 50px;
        height: 50px;
        border-radius: 12px;
        background: var(--item-color);
        color: white;
        display: inline-flex;
        align-items: center;
        justify-content: center;
        font-size: 24px;
        margin-right: 15px;
        float: left;
    }
    
    .result-content-box {
        overflow: hidden;
    }
    
    .result-label {
        font-size: 11px;
        font-weight: 700;
        color: var(--item-color);
        text-transform: uppercase;
        margin-bottom: 5px;
    }
    
    .result-title {
        font-size: 16px;
        font-weight: 700;
        color: #333;
        margin: 0 0 5px 0;
    }
    
    .result-desc {
        font-size: 14px;
        color: #666;
        margin: 0;
    }
    
    .empty-box {
        text-align: center;
        padding: 60px 20px;
        color: #999;
    }
    
    .empty-box i {
        font-size: 80px;
        opacity: 0.2;
        margin-bottom: 20px;
    }
    
    .popular-box {
        margin-top: 40px;
        text-align: center;
    }
    
    .popular-label {
        font-size: 13px;
        font-weight: 700;
        color: #666;
        margin-bottom: 15px;
        display: block;
    }
    
    .tag-box {
        display: inline-block;
        background: #f5f5f5;
        padding: 8px 16px;
        border-radius: 20px;
        font-size: 13px;
        color: #667eea;
        text-decoration: none;
        margin: 4px;
        transition: all 0.3s;
        font-weight: 600;
    }
    
    .tag-box:hover {
        background: #667eea;
        color: white;
        transform: translateY(-2px);
    }
</style>

<div class="search-wrapper">
    <div class="search-box-main">
        <form method="GET" class="search-form-main">
            <input type="text" 
                   name="q" 
                   class="search-input-main" 
                   placeholder="Cari: imunisasi, gizi, campak, mpasi..." 
                   value="<?php echo htmlspecialchars($keyword); ?>"
                   required>
            <button type="submit" class="search-btn-main">
                <i class="fas fa-search"></i> Cari
            </button>
        </form>
    </div>

    <?php if (!empty($keyword)): ?>
        <div class="result-header">
            <strong>🔍 Hasil pencarian untuk: "<?php echo htmlspecialchars($keyword); ?>"</strong>
            <span class="badge bg-primary ms-2"><?php echo count($hasil); ?> ditemukan</span>
        </div>
        
        <?php if (count($hasil) > 0): ?>
            <?php foreach ($hasil as $item): ?>
            <a href="<?php echo $item['link']; ?>" class="result-item" style="--item-color: <?php echo $item['warna']; ?>;">
                <div class="result-icon-box">
                    <i class="fas <?php echo $item['icon']; ?>"></i>
                </div>
                <div class="result-content-box">
                    <div class="result-label"><?php echo $item['label']; ?></div>
                    <h4 class="result-title"><?php echo $item['judul']; ?></h4>
                    <p class="result-desc"><?php echo $item['deskripsi']; ?></p>
                </div>
                <div style="clear: both;"></div>
            </a>
            <?php endforeach; ?>
        <?php else: ?>
            <div class="empty-box">
                <i class="fas fa-search"></i>
                <h4>Tidak ada hasil ditemukan</h4>
                <p>Coba kata kunci lain: imunisasi, gizi, campak, polio, mpasi</p>
            </div>
        <?php endif; ?>
    <?php else: ?>
        <div class="empty-box">
            <i class="fas fa-search"></i>
            <h4>Mau cari apa hari ini?</h4>
            <p>Cari riwayat imunisasi anak atau artikel kesehatan</p>
        </div>
        
        <div class="popular-box">
            <span class="popular-label">🔥 Pencarian Populer:</span>
            <a href="?q=imunisasi" class="tag-box">Imunisasi</a>
            <a href="?q=gizi" class="tag-box">Status Gizi</a>
            <a href="?q=campak" class="tag-box">Campak</a>
            <a href="?q=polio" class="tag-box">Polio</a>
            <a href="?q=mpasi" class="tag-box">MPASI</a>
        </div>
    <?php endif; ?>
</div>

<?php require_once '../includes/footer.php'; ?>