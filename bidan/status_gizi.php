<?php
$page_title = 'Data Status Gizi';
require_once '../includes/header_bidan.php';
require_once '../config/database.php';
require_once '../includes/functions.php'; // ← TAMBAHKAN INI!

$database = new Database();
$db = $database->getConnection();

// ========== FILTER ==========
$where = [];
$params = [];

if (!empty($_GET['search'])) {
    $kw = "%" . sanitize($_GET['search']) . "%";
    $where[] = "(ia.nama_anak LIKE ? OR ia.nama_ibu LIKE ?)";
    $params = [$kw, $kw];
}
if (!empty($_GET['status'])) {
    $where[] = "s.status_gizi = ?";
    $params[] = sanitize($_GET['status']);
}
if (!empty($_GET['kategori'])) {
    $where[] = "s.kategori = ?";
    $params[] = sanitize($_GET['kategori']);
}
if (!empty($_GET['tgl_mulai'])) {
    $where[] = "s.tanggal_ukur >= ?";
    $params[] = $_GET['tgl_mulai'];
}
if (!empty($_GET['tgl_selesai'])) {
    $where[] = "s.tanggal_ukur <= ?";
    $params[] = $_GET['tgl_selesai'];
}

$where_sql = !empty($where) ? "WHERE " . implode(" AND ", $where) : "";

// Query - TAMBAHKAN jenis_kelamin
$query = "SELECT s.*, ia.nama_anak, ia.nama_ibu, ia.puskesmas, ia.jenis_kelamin 
          FROM status_gizi s 
          JOIN ibu_anak ia ON s.ibu_anak_id = ia.id 
          $where_sql
          ORDER BY s.tanggal_ukur DESC, s.created_at DESC";

$stmt = $db->prepare($query);
$stmt->execute($params);
$data = $stmt->fetchAll(PDO::FETCH_ASSOC);

// Statistik Ringkas
$stats = [
    'total' => count($data),
    'normal' => count(array_filter($data, fn($r) => $r['status_gizi'] == 'normal')),
    'kurang' => count(array_filter($data, fn($r) => $r['status_gizi'] == 'kurang')),
    'lebih' => count(array_filter($data, fn($r) => $r['status_gizi'] == 'lebih')),
];
?>

<style>
    .filter-card { background: white; border-radius: 15px; padding: 15px; margin-bottom: 20px; box-shadow: 0 2px 10px rgba(0,0,0,0.05); }
    .stat-badge { font-size: 11px; padding: 4px 10px; border-radius: 20px; font-weight: 600; }
    .btn-action { width: 30px; height: 30px; padding: 0; display: inline-flex; align-items: center; justify-content: center; border-radius: 8px; margin: 0 2px; font-size: 12px; }
    .table th { font-weight: 600; font-size: 12px; text-transform: uppercase; color: #666; background: #f8f9fa; }
</style>

<!-- Filter Card -->
<div class="filter-card">
    <form method="GET" class="row g-2 align-items-end">
        <div class="col-md-3">
            <label class="form-label small fw-bold">Cari Nama</label>
            <input type="text" name="search" class="form-control form-control-sm" 
                   placeholder="Anak atau Ibu..." value="<?php echo htmlspecialchars($_GET['search'] ?? ''); ?>">
        </div>
        <div class="col-md-2">
            <label class="form-label small fw-bold">Status</label>
            <select name="status" class="form-select form-select-sm">
                <option value="">Semua</option>
                <option value="normal" <?php echo (($_GET['status']??'')=='normal')?'selected':''; ?>>Normal</option>
                <option value="kurang" <?php echo (($_GET['status']??'')=='kurang')?'selected':''; ?>>Kurang</option>
                <option value="lebih" <?php echo (($_GET['status']??'')=='lebih')?'selected':''; ?>>Lebih</option>
            </select>
        </div>
        <div class="col-md-2">
            <label class="form-label small fw-bold">Kategori</label>
            <select name="kategori" class="form-select form-select-sm">
                <option value="">Semua</option>
                <option value="gizi_baik" <?php echo (($_GET['kategori']??'')=='gizi_baik')?'selected':''; ?>>Gizi Baik</option>
                <option value="kurus" <?php echo (($_GET['kategori']??'')=='kurus')?'selected':''; ?>>Kurus</option>
                <option value="gemuk" <?php echo (($_GET['kategori']??'')=='gemuk')?'selected':''; ?>>Gemuk</option>
            </select>
        </div>
        <div class="col-md-3">
            <label class="form-label small fw-bold">Tanggal</label>
            <div class="input-group input-group-sm">
                <input type="date" name="tgl_mulai" class="form-control" value="<?php echo htmlspecialchars($_GET['tgl_mulai'] ?? ''); ?>">
                <span class="input-group-text">s/d</span>
                <input type="date" name="tgl_selesai" class="form-control" value="<?php echo htmlspecialchars($_GET['tgl_selesai'] ?? ''); ?>">
            </div>
        </div>
        <div class="col-md-2 d-flex gap-2">
            <button type="submit" class="btn btn-primary btn-sm flex-fill"><i class="fas fa-filter"></i></button>
            <a href="status_gizi.php" class="btn btn-outline-secondary btn-sm"><i class="fas fa-undo"></i></a>
        </div>
    </form>
</div>

<!-- Statistik Ringkas -->
<div class="row g-2 mb-3">
    <div class="col-3">
        <div class="p-2 bg-light rounded text-center">
            <div class="fw-bold"><?php echo $stats['total']; ?></div>
            <small class="text-muted">Total</small>
        </div>
    </div>
    <div class="col-3">
        <div class="p-2 bg-success bg-opacity-10 text-success rounded text-center">
            <div class="fw-bold"><?php echo $stats['normal']; ?></div>
            <small>Normal</small>
        </div>
    </div>
    <div class="col-3">
        <div class="p-2 bg-warning bg-opacity-10 text-warning rounded text-center">
            <div class="fw-bold"><?php echo $stats['kurang']; ?></div>
            <small>Kurang</small>
        </div>
    </div>
    <div class="col-3">
        <div class="p-2 bg-danger bg-opacity-10 text-danger rounded text-center">
            <div class="fw-bold"><?php echo $stats['lebih']; ?></div>
            <small>Lebih</small>
        </div>
    </div>
</div>

<!-- Table Card -->
<div class="card content-card border-0 shadow-sm">
    <div class="card-header bg-white py-3 d-flex justify-content-between align-items-center">
        <h5 class="mb-0 fw-bold text-primary"><i class="fas fa-weight me-2"></i>Data Status Gizi</h5>
        <div class="d-flex gap-2">
            <a href="export_gizi.php?<?php echo http_build_query($_GET); ?>" class="btn btn-success btn-sm" target="_blank">
                <i class="fas fa-file-excel me-1"></i> Export
            </a>
            <a href="tambah_gizi.php" class="btn btn-primary btn-sm">
                <i class="fas fa-plus me-1"></i> Input Gizi
            </a>
        </div>
    </div>
    
    <div class="card-body p-0">
        <div class="table-responsive">
            <table class="table table-hover align-middle mb-0">
                <thead>
                    <tr>
                        <th class="ps-4" style="width:40px;">No</th>
                        <th>Nama Anak</th>
                        <th>Nama Ibu</th>
                        <th>Puskesmas</th>
                        <th>Tanggal</th>
                        <th>Umur</th>
                        <th>BB/TB</th>
                        <th>Status</th>
                        <th>Kategori</th>
                        <th class="text-center pe-4" style="width:100px;">Aksi</th>
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
                            <small class="text-muted">
                                <?php 
                                if (isset($row['jenis_kelamin'])) {
                                    echo $row['jenis_kelamin'] == 'L' ? '♂' : '♀';
                                } else {
                                    echo '♀';
                                }
                                ?>
                            </small>
                        </td>
                        <td><?php echo htmlspecialchars($row['nama_ibu']); ?></td>
                        <td><small class="text-muted"><?php echo htmlspecialchars($row['puskesmas']); ?></small></td>
                        <td><?php echo date('d/m/Y', strtotime($row['tanggal_ukur'])); ?></td>
                        <td><span class="badge bg-light text-dark"><?php echo $row['umur_bulan']; ?> bln</span></td>
                        <td>
                            <small class="d-block"><?php echo $row['berat_badan']; ?> kg</small>
                            <small class="text-muted"><?php echo $row['tinggi_badan']; ?> cm</small>
                        </td>
                        <td>
                            <?php $badge = match($row['status_gizi']) {
                                'normal' => 'success', 'kurang' => 'warning', 'lebih' => 'danger', default => 'secondary'
                            }; ?>
                            <span class="badge bg-<?php echo $badge; ?> stat-badge"><?php echo ucfirst($row['status_gizi']); ?></span>
                        </td>
                        <td><?php echo ucfirst(str_replace('_', ' ', $row['kategori'])); ?></td>
                        <td class="text-center pe-4">
                            <a href="edit_gizi.php?id=<?php echo $row['id']; ?>" class="btn btn-action btn-outline-primary" title="Edit"><i class="fas fa-edit"></i></a>
                            <a href="hapus_gizi.php?id=<?php echo $row['id']; ?>" class="btn btn-action btn-outline-danger" onclick="return confirm('Hapus data ini?')" title="Hapus"><i class="fas fa-trash"></i></a>
                        </td>
                    </tr>
                    <?php 
                        endforeach;
                    else:
                    ?>
                    <tr>
                        <td colspan="10" class="text-center py-5 text-muted">
                            <i class="fas fa-weight fa-3x mb-3 opacity-25"></i><br>
                            Belum ada data status gizi
                        </td>
                    </tr>
                    <?php endif; ?>
                </tbody>
            </table>
        </div>
    </div>
</div>

<?php require_once '../includes/footer.php'; ?>