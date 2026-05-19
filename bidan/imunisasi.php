<?php
$page_title = 'Data Imunisasi';
require_once '../includes/header_bidan.php';
require_once '../config/database.php';

$database = new Database();
$db = $database->getConnection();

// ========== FILTER & PENCARIAN ==========
$where_clauses = [];
$params = [];

// Filter Nama Anak
if (isset($_GET['search_anak']) && !empty($_GET['search_anak'])) {
    $where_clauses[] = "ia.nama_anak LIKE ?";
    $params[] = "%" . sanitize($_GET['search_anak']) . "%";
}

// Filter Nama Ibu
if (isset($_GET['search_ibu']) && !empty($_GET['search_ibu'])) {
    $where_clauses[] = "ia.nama_ibu LIKE ?";
    $params[] = "%" . sanitize($_GET['search_ibu']) . "%";
}

// Filter Jenis Imunisasi
if (isset($_GET['jenis']) && !empty($_GET['jenis'])) {
    $where_clauses[] = "i.jenis_imunisasi = ?";
    $params[] = sanitize($_GET['jenis']);
}

// Filter Status
if (isset($_GET['status']) && !empty($_GET['status'])) {
    $where_clauses[] = "i.status = ?";
    $params[] = sanitize($_GET['status']);
}

// Filter Tanggal
if (isset($_GET['tgl_mulai']) && !empty($_GET['tgl_mulai'])) {
    $where_clauses[] = "i.tanggal_imunisasi >= ?";
    $params[] = $_GET['tgl_mulai'];
}
if (isset($_GET['tgl_selesai']) && !empty($_GET['tgl_selesai'])) {
    $where_clauses[] = "i.tanggal_imunisasi <= ?";
    $params[] = $_GET['tgl_selesai'];
}

$where_sql = !empty($where_clauses) ? "WHERE " . implode(" AND ", $where_clauses) : "";

// ========== QUERY UTAMA ==========
$query = "SELECT i.*, ia.nama_anak, ia.nama_ibu, ia.puskesmas 
          FROM imunisasi i 
          JOIN ibu_anak ia ON i.ibu_anak_id = ia.id 
          $where_sql
          ORDER BY i.tanggal_imunisasi DESC, i.created_at DESC";

$stmt = $db->prepare($query);
$stmt->execute($params);
$data = $stmt->fetchAll(PDO::FETCH_ASSOC);

// ========== STATISTIK RINGKAS ==========
$stats = [
    'total' => count($data),
    'lengkap' => 0,
    'menunggu' => 0,
    'tidak_lengkap' => 0
];
foreach ($data as $d) {
    if ($d['status'] == 'lengkap') $stats['lengkap']++;
    elseif ($d['status'] == 'menunggu') $stats['menunggu']++;
    else $stats['tidak_lengkap']++;
}

// ========== LIST JENIS IMUNISASI UNTUK FILTER ==========
$jenis_list = $db->query("SELECT DISTINCT jenis_imunisasi FROM imunisasi ORDER BY jenis_imunisasi")->fetchAll(PDO::FETCH_COLUMN);
?>

<style>
    .filter-card { background: white; border-radius: 15px; padding: 20px; margin-bottom: 20px; box-shadow: 0 2px 10px rgba(0,0,0,0.05); }
    .stat-card { border-radius: 15px; padding: 20px; text-align: center; color: white; }
    .stat-card.total { background: linear-gradient(135deg, #667eea, #764ba2); }
    .stat-card.lengkap { background: linear-gradient(135deg, #28a745, #34ce57); }
    .stat-card.menunggu { background: linear-gradient(135deg, #ffc107, #ffcd39); color: #333; }
    .stat-card.tidak { background: linear-gradient(135deg, #dc3545, #e4606d); }
    .stat-number { font-size: 28px; font-weight: 800; margin: 5px 0; }
    .stat-label { font-size: 13px; opacity: 0.9; }
    .btn-action { width: 32px; height: 32px; padding: 0; display: inline-flex; align-items: center; justify-content: center; border-radius: 8px; margin: 0 2px; }
    .table th { font-weight: 600; font-size: 13px; text-transform: uppercase; color: #666; }
    .badge-status { font-size: 11px; padding: 5px 10px; border-radius: 20px; font-weight: 600; }
</style>

<!-- Statistik Ringkas -->
<div class="row g-3 mb-4">
    <div class="col-6 col-md-3">
        <div class="stat-card total">
            <i class="fas fa-list fa-2x mb-2"></i>
            <div class="stat-number"><?php echo $stats['total']; ?></div>
            <div class="stat-label">Total Data</div>
        </div>
    </div>
    <div class="col-6 col-md-3">
        <div class="stat-card lengkap">
            <i class="fas fa-check-circle fa-2x mb-2"></i>
            <div class="stat-number"><?php echo $stats['lengkap']; ?></div>
            <div class="stat-label">Lengkap</div>
        </div>
    </div>
    <div class="col-6 col-md-3">
        <div class="stat-card menunggu">
            <i class="fas fa-clock fa-2x mb-2"></i>
            <div class="stat-number"><?php echo $stats['menunggu']; ?></div>
            <div class="stat-label">Menunggu</div>
        </div>
    </div>
    <div class="col-6 col-md-3">
        <div class="stat-card tidak">
            <i class="fas fa-exclamation-circle fa-2x mb-2"></i>
            <div class="stat-number"><?php echo $stats['tidak_lengkap']; ?></div>
            <div class="stat-label">Tidak Lengkap</div>
        </div>
    </div>
</div>

<!-- Filter Card -->
<div class="filter-card">
    <form method="GET" class="row g-3 align-items-end">
        <div class="col-md-3">
            <label class="form-label small fw-bold">Cari Nama Anak</label>
            <input type="text" name="search_anak" class="form-control form-control-sm" 
                   placeholder="Nama anak..." value="<?php echo htmlspecialchars($_GET['search_anak'] ?? ''); ?>">
        </div>
        <div class="col-md-3">
            <label class="form-label small fw-bold">Cari Nama Ibu</label>
            <input type="text" name="search_ibu" class="form-control form-control-sm" 
                   placeholder="Nama ibu..." value="<?php echo htmlspecialchars($_GET['search_ibu'] ?? ''); ?>">
        </div>
        <div class="col-md-2">
            <label class="form-label small fw-bold">Jenis Imunisasi</label>
            <select name="jenis" class="form-select form-select-sm">
                <option value="">Semua</option>
                <?php foreach($jenis_list as $j): ?>
                <option value="<?php echo htmlspecialchars($j); ?>" <?php echo (($_GET['jenis'] ?? '') == $j) ? 'selected' : ''; ?>>
                    <?php echo htmlspecialchars($j); ?>
                </option>
                <?php endforeach; ?>
            </select>
        </div>
        <div class="col-md-2">
            <label class="form-label small fw-bold">Status</label>
            <select name="status" class="form-select form-select-sm">
                <option value="">Semua</option>
                <option value="lengkap" <?php echo (($_GET['status'] ?? '') == 'lengkap') ? 'selected' : ''; ?>>Lengkap</option>
                <option value="menunggu" <?php echo (($_GET['status'] ?? '') == 'menunggu') ? 'selected' : ''; ?>>Menunggu</option>
                <option value="tidak_lengkap" <?php echo (($_GET['status'] ?? '') == 'tidak_lengkap') ? 'selected' : ''; ?>>Tidak Lengkap</option>
            </select>
        </div>
        <div class="col-md-2 d-flex gap-2">
            <button type="submit" class="btn btn-primary btn-sm flex-fill">
                <i class="fas fa-filter me-1"></i> Filter
            </button>
            <a href="imunisasi.php" class="btn btn-outline-secondary btn-sm">
                <i class="fas fa-undo"></i>
            </a>
        </div>
    </form>
    
    <!-- Date Range Filter (Collapsible) -->
    <div class="mt-3 pt-3 border-top">
        <button class="btn btn-link btn-sm text-muted p-0" type="button" data-bs-toggle="collapse" data-bs-target="#dateFilter">
            <i class="fas fa-calendar me-1"></i> Filter Berdasarkan Tanggal ▾
        </button>
        <div class="collapse mt-2" id="dateFilter">
            <div class="row g-2 align-items-end">
                <div class="col-md-3">
                    <label class="form-label small">Tanggal Mulai</label>
                    <input type="date" name="tgl_mulai" class="form-control form-control-sm" 
                           value="<?php echo htmlspecialchars($_GET['tgl_mulai'] ?? ''); ?>">
                </div>
                <div class="col-md-3">
                    <label class="form-label small">Tanggal Selesai</label>
                    <input type="date" name="tgl_selesai" class="form-control form-control-sm" 
                           value="<?php echo htmlspecialchars($_GET['tgl_selesai'] ?? ''); ?>">
                </div>
                <div class="col-md-2">
                    <button type="submit" class="btn btn-primary btn-sm w-100">Terapkan</button>
                </div>
            </div>
        </div>
    </div>
</form>
</div>

<!-- Table Card -->
<div class="card content-card border-0 shadow-sm">
    <div class="card-header bg-white py-3 d-flex justify-content-between align-items-center">
        <h5 class="mb-0 fw-bold text-primary"><i class="fas fa-syringe me-2"></i>Data Imunisasi</h5>
        <div class="d-flex gap-2">
            <!-- Export Button -->
            <a href="export_imunisasi.php?<?php echo http_build_query($_GET); ?>" 
               class="btn btn-success btn-sm" target="_blank">
                <i class="fas fa-file-excel me-1"></i> Export CSV
            </a>
            <a href="tambah_imunisasi.php" class="btn btn-primary btn-sm">
                <i class="fas fa-plus me-1"></i> Tambah Data
            </a>
        </div>
    </div>
    
    <div class="card-body p-0">
        <div class="table-responsive">
            <table class="table table-hover align-middle mb-0">
                <thead class="table-light">
                    <tr>
                        <th class="ps-4" style="width: 50px;">No</th>
                        <th>Nama Anak</th>
                        <th>Nama Ibu</th>
                        <th>Puskesmas</th>
                        <th>Jenis Imunisasi</th>
                        <th>Tanggal</th>
                        <th>Status</th>
                        <th>Keterangan</th>
                        <th class="text-center pe-4" style="width: 120px;">Aksi</th>
                    </tr>
                </thead>
                <tbody>
                    <?php 
                    $no = 1;
                    if(count($data) > 0):
                        foreach($data as $row): 
                    ?>
                    <tr>
                        <td class="ps-4 fw-bold text-muted"><?php echo $no++; ?></td>
                        <td>
                            <div class="fw-bold"><?php echo htmlspecialchars($row['nama_anak']); ?></div>
                        </td>
                        <td><?php echo htmlspecialchars($row['nama_ibu']); ?></td>
                        <td><small class="text-muted"><?php echo htmlspecialchars($row['puskesmas']); ?></small></td>
                        <td>
                            <span class="badge bg-light text-dark border">
                                <?php echo htmlspecialchars($row['jenis_imunisasi']); ?>
                            </span>
                        </td>
                        <td><?php echo date('d M Y', strtotime($row['tanggal_imunisasi'])); ?></td>
                        <td>
                            <?php 
                            $badge_class = match($row['status']) {
                                'lengkap' => 'success',
                                'menunggu' => 'warning',
                                'tidak_lengkap' => 'danger',
                                default => 'secondary'
                            };
                            ?>
                            <span class="badge badge-status bg-<?php echo $badge_class; ?>">
                                <?php echo ucfirst($row['status']); ?>
                            </span>
                        </td>
                        <td>
                            <small class="text-muted" title="<?php echo htmlspecialchars($row['keterangan'] ?? ''); ?>">
                                <?php 
                                $ket = htmlspecialchars($row['keterangan'] ?? '-');
                                echo (strlen($ket) > 30) ? substr($ket, 0, 30) . '...' : $ket; 
                                ?>
                            </small>
                        </td>
                        <td class="text-center pe-4">
                            <a href="edit_imunisasi.php?id=<?php echo $row['id']; ?>" 
                               class="btn btn-action btn-outline-primary" title="Edit">
                                <i class="fas fa-edit"></i>
                            </a>
                            <a href="hapus_imunisasi.php?id=<?php echo $row['id']; ?>" 
                               class="btn btn-action btn-outline-danger" 
                               onclick="return confirm('Hapus data imunisasi ini?')" title="Hapus">
                                <i class="fas fa-trash"></i>
                            </a>
                        </td>
                    </tr>
                    <?php 
                        endforeach;
                    else:
                    ?>
                    <tr>
                        <td colspan="9" class="text-center py-5">
                            <div class="text-muted">
                                <i class="fas fa-syringe fa-3x mb-3 opacity-25"></i>
                                <h6 class="fw-bold">Tidak ada data imunisasi</h6>
                                <p class="small mb-3">Silakan tambah data imunisasi baru atau ubah filter pencarian.</p>
                                <a href="tambah_imunisasi.php" class="btn btn-primary btn-sm">
                                    <i class="fas fa-plus me-1"></i> Tambah Data Imunisasi
                                </a>
                            </div>
                        </td>
                    </tr>
                    <?php endif; ?>
                </tbody>
            </table>
        </div>
        
        <!-- Pagination Info -->
        <?php if(count($data) > 0): ?>
        <div class="card-footer bg-white py-2 small text-muted">
            Menampilkan <?php echo count($data); ?> dari <?php echo $stats['total']; ?> data imunisasi
        </div>
        <?php endif; ?>
    </div>
</div>

<?php require_once '../includes/footer.php'; ?>