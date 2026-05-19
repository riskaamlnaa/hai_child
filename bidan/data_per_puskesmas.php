<?php
$page_title = 'Data Per Puskesmas';
require_once '../includes/header_bidan.php';
require_once '../config/database.php';

$database = new Database();
$db = $database->getConnection();

// Daftar 5 Puskesmas Banjarmasin Timur
$puskesmas_list = [
    'Puskesmas Sungai Jingah',
    'Puskesmas Pemurus Dalam',
    'Puskesmas Basirih',
    'Puskesmas Belitung Selatan',
    'Puskesmas Belitung Utara'
];

// Filter Puskesmas
$selected_puskesmas = $_GET['puskesmas'] ?? 'all';

// Query berdasarkan filter
if ($selected_puskesmas == 'all') {
    $where_clause = "1=1";
    $params = [];
} else {
    $where_clause = "ia.puskesmas = ?";
    $params = [$selected_puskesmas];
}

// Ambil semua data dengan join
$query = "SELECT ia.*, u.nama_lengkap as nama_ibu, u.email, u.no_hp
          FROM ibu_anak ia
          LEFT JOIN users u ON ia.user_id = u.id
          WHERE $where_clause
          ORDER BY ia.puskesmas, ia.nama_anak";

$stmt = $db->prepare($query);
$stmt->execute($params);
$data = $stmt->fetchAll(PDO::FETCH_ASSOC);

// Group data by puskesmas
$data_grouped = [];
foreach ($data as $row) {
    $puskesmas = $row['puskesmas'] ?? 'Lainnya';
    if (!isset($data_grouped[$puskesmas])) {
        $data_grouped[$puskesmas] = [];
    }
    $data_grouped[$puskesmas][] = $row;
}

// Statistik per Puskesmas
$stats_per_puskesmas = [];
foreach ($puskesmas_list as $puskesmas) {
    $stmt = $db->prepare("SELECT 
        COUNT(*) as total_anak,
        COUNT(DISTINCT user_id) as total_ibu,
        SUM(CASE WHEN jk.status_gizi = 'normal' THEN 1 ELSE 0 END) as gizi_normal
        FROM ibu_anak ia
        LEFT JOIN status_gizi jk ON ia.id = jk.ibu_anak_id
        WHERE ia.puskesmas = ?
        GROUP BY ia.puskesmas");
    $stmt->execute([$puskesmas]);
    $stat = $stmt->fetch(PDO::FETCH_ASSOC);
    
    $stats_per_puskesmas[$puskesmas] = [
        'total_anak' => $stat['total_anak'] ?? 0,
        'total_ibu' => $stat['total_ibu'] ?? 0,
        'gizi_normal' => $stat['gizi_normal'] ?? 0
    ];
}
?>

<style>
    .puskesmas-filter {
        background: white;
        border-radius: 15px;
        padding: 20px;
        margin-bottom: 25px;
        box-shadow: 0 5px 15px rgba(0,0,0,0.08);
    }
    .puskesmas-tabs {
        display: flex;
        gap: 10px;
        flex-wrap: wrap;
        margin-top: 15px;
    }
    .puskesmas-tab {
        padding: 10px 20px;
        background: #f0f0f0;
        border-radius: 25px;
        text-decoration: none;
        color: #666;
        font-weight: 600;
        font-size: 13px;
        transition: all 0.3s;
    }
    .puskesmas-tab:hover, .puskesmas-tab.active {
        background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
        color: white;
        transform: translateY(-2px);
    }
    .stats-grid {
        display: grid;
        grid-template-columns: repeat(auto-fit, minmax(250px, 1fr));
        gap: 15px;
        margin-bottom: 25px;
    }
    .puskesmas-card {
        background: white;
        border-radius: 15px;
        padding: 20px;
        box-shadow: 0 5px 15px rgba(0,0,0,0.08);
        border-left: 5px solid #667eea;
    }
    .puskesmas-card h4 {
        margin: 0 0 15px 0;
        color: #333;
        font-size: 16px;
    }
    .stat-row {
        display: flex;
        justify-content: space-between;
        padding: 8px 0;
        border-bottom: 1px solid #f0f0f0;
    }
    .stat-row:last-child {
        border-bottom: none;
    }
    .stat-label {
        color: #888;
        font-size: 13px;
    }
    .stat-value {
        font-weight: 700;
        color: #667eea;
    }
    .data-section {
        background: white;
        border-radius: 15px;
        padding: 20px;
        margin-bottom: 20px;
        box-shadow: 0 5px 15px rgba(0,0,0,0.08);
    }
    .data-section h3 {
        margin: 0 0 15px 0;
        color: #667eea;
        font-size: 18px;
        border-bottom: 2px solid #f0f0f0;
        padding-bottom: 10px;
    }
</style>

<div class="puskesmas-filter">
    <h4><i class="fas fa-filter me-2"></i>Filter Data Puskesmas</h4>
    <div class="puskesmas-tabs">
        <a href="?puskesmas=all" class="puskesmas-tab <?php echo $selected_puskesmas == 'all' ? 'active' : ''; ?>">
            <i class="fas fa-th me-1"></i>Semua Puskesmas
        </a>
        <?php foreach ($puskesmas_list as $puskesmas): ?>
            <a href="?puskesmas=<?php echo urlencode($puskesmas); ?>" 
               class="puskesmas-tab <?php echo $selected_puskesmas == $puskesmas ? 'active' : ''; ?>">
                <?php echo htmlspecialchars($puskesmas); ?>
            </a>
        <?php endforeach; ?>
    </div>
</div>

<!-- Statistik Per Puskesmas -->
<div class="stats-grid">
    <?php foreach ($puskesmas_list as $puskesmas): 
        $stat = $stats_per_puskesmas[$puskesmas];
    ?>
    <div class="puskesmas-card">
        <h4><i class="fas fa-clinic-medical me-2"></i><?php echo htmlspecialchars($puskesmas); ?></h4>
        <div class="stat-row">
            <span class="stat-label">Total Anak:</span>
            <span class="stat-value"><?php echo $stat['total_anak']; ?></span>
        </div>
        <div class="stat-row">
            <span class="stat-label">Total Ibu:</span>
            <span class="stat-value"><?php echo $stat['total_ibu']; ?></span>
        </div>
        <div class="stat-row">
            <span class="stat-label">Gizi Normal:</span>
            <span class="stat-value" style="color: #28a745;"><?php echo $stat['gizi_normal']; ?></span>
        </div>
    </div>
    <?php endforeach; ?>
</div>

<!-- Data Detail -->
<?php if ($selected_puskesmas == 'all'): ?>
    <!-- Tampilkan Semua Puskesmas -->
    <?php foreach ($data_grouped as $puskesmas => $items): ?>
    <div class="data-section">
        <h3><i class="fas fa-clinic-medical me-2"></i><?php echo htmlspecialchars($puskesmas); ?> 
            <span class="badge bg-primary ms-2"><?php echo count($items); ?> Data</span>
        </h3>
        <div class="table-responsive">
            <table class="table table-hover">
                <thead class="table-light">
                    <tr>
                        <th>No</th>
                        <th>Nama Anak</th>
                        <th>Nama Ibu</th>
                        <th>Usia</th>
                        <th>Jenis Kelamin</th>
                        <th>Email Ibu</th>
                        <th>No. HP</th>
                    </tr>
                </thead>
                <tbody>
                    <?php $no = 1; foreach ($items as $row): 
                        $birthDate = new DateTime($row['tanggal_lahir_anak']);
                        $today = new DateTime("today");
                        $age = $today->diff($birthDate);
                        $umur_bulan = ($age->y * 12) + $age->m;
                    ?>
                    <tr>
                        <td><?php echo $no++; ?></td>
                        <td><strong><?php echo htmlspecialchars($row['nama_anak']); ?></strong></td>
                        <td><?php echo htmlspecialchars($row['nama_ibu']); ?></td>
                        <td><?php echo $umur_bulan; ?> Bulan</td>
                        <td><?php echo $row['jenis_kelamin'] == 'L' ? 'Laki-laki' : 'Perempuan'; ?></td>
                        <td><?php echo htmlspecialchars($row['email']); ?></td>
                        <td><?php echo htmlspecialchars($row['no_hp']); ?></td>
                    </tr>
                    <?php endforeach; ?>
                </tbody>
            </table>
        </div>
    </div>
    <?php endforeach; ?>
<?php else: ?>
    <!-- Tampilkan Hanya Puskesmas Terpilih -->
    <div class="data-section">
        <h3><i class="fas fa-clinic-medical me-2"></i>Data <?php echo htmlspecialchars($selected_puskesmas); ?>
            <span class="badge bg-primary ms-2"><?php echo count($data); ?> Data</span>
        </h3>
        <?php if (count($data) > 0): ?>
        <div class="table-responsive">
            <table class="table table-hover">
                <thead class="table-light">
                    <tr>
                        <th>No</th>
                        <th>Nama Anak</th>
                        <th>Nama Ibu</th>
                        <th>Usia</th>
                        <th>Jenis Kelamin</th>
                        <th>Email</th>
                        <th>No. HP</th>
                        <th>Aksi</th>
                    </tr>
                </thead>
                <tbody>
                    <?php $no = 1; foreach ($data as $row): 
                        $birthDate = new DateTime($row['tanggal_lahir_anak']);
                        $today = new DateTime("today");
                        $age = $today->diff($birthDate);
                        $umur_bulan = ($age->y * 12) + $age->m;
                    ?>
                    <tr>
                        <td><?php echo $no++; ?></td>
                        <td><strong><?php echo htmlspecialchars($row['nama_anak']); ?></strong></td>
                        <td><?php echo htmlspecialchars($row['nama_ibu']); ?></td>
                        <td><?php echo $umur_bulan; ?> Bulan</td>
                        <td><?php echo $row['jenis_kelamin'] == 'L' ? 'Laki-laki' : 'Perempuan'; ?></td>
                        <td><?php echo htmlspecialchars($row['email']); ?></td>
                        <td><?php echo htmlspecialchars($row['no_hp']); ?></td>
                        <td>
                            <div class="btn-group btn-group-sm">
                                <a href="edit_ibu_anak.php?id=<?php echo $row['id']; ?>" class="btn btn-warning" title="Edit">
                                    <i class="fas fa-edit"></i>
                                </a>
                                <a href="tambah_imunisasi.php?id=<?php echo $row['id']; ?>" class="btn btn-success" title="Imunisasi">
                                    <i class="fas fa-syringe"></i>
                                </a>
                            </div>
                        </td>
                    </tr>
                    <?php endforeach; ?>
                </tbody>
            </table>
        </div>
        <?php else: ?>
            <div class="alert alert-info">
                <i class="fas fa-info-circle me-2"></i>Belum ada data untuk <?php echo htmlspecialchars($selected_puskesmas); ?>
            </div>
        <?php endif; ?>
    </div>
<?php endif; ?>

<?php require_once '../includes/footer.php'; ?>