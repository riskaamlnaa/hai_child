<?php
$page_title = 'Manajemen Data Ibu';
require_once '../includes/header_bidan.php';
require_once '../config/database.php';

$db = (new Database())->getConnection();

// ========== 1. DATA MENUNGGU VERIFIKASI ==========
$sql_pending = "SELECT u.id, u.nama_lengkap, u.email, u.no_hp, u.created_at 
                FROM users u 
                LEFT JOIN ibu_anak ia ON u.id = ia.user_id 
                WHERE u.role = 'ibu_anak' AND ia.user_id IS NULL 
                ORDER BY u.created_at DESC";
$pending_users = $db->query($sql_pending)->fetchAll(PDO::FETCH_ASSOC);

// ========== 2. DATA SUDAH DIVERIFIKASI ==========
$sql_verified = "SELECT u.id as user_id, u.nama_lengkap, u.email, u.no_hp, u.created_at,
                        ia.id as profile_id, ia.nama_anak, ia.puskesmas, ia.created_at as profil_created 
                 FROM users u 
                 INNER JOIN ibu_anak ia ON u.id = ia.user_id 
                 WHERE u.role = 'ibu_anak' 
                 ORDER BY ia.created_at DESC";
$verified_users = $db->query($sql_verified)->fetchAll(PDO::FETCH_ASSOC);
?>

<style>
    .status-card { border-radius: 15px; padding: 20px; color: white; margin-bottom: 25px; }
    .status-card.pending { background: linear-gradient(135deg, #f39c12, #f1c40f); }
    .status-card.verified { background: linear-gradient(135deg, #27ae60, #2ecc71); }
    .status-number { font-size: 36px; font-weight: 800; }
    .status-label { font-size: 14px; opacity: 0.9; }
    
    .table-card { background: white; border-radius: 15px; box-shadow: 0 5px 20px rgba(0,0,0,0.05); margin-bottom: 30px; overflow: hidden; }
    .table-header { padding: 20px 25px; border-bottom: 2px solid #f0f0f0; display: flex; justify-content: space-between; align-items: center; }
    .table-title { font-size: 18px; font-weight: 700; color: #333; margin: 0; display: flex; align-items: center; gap: 10px; }
    .table-title i { color: #667eea; }
    
    .badge-status { padding: 5px 12px; border-radius: 20px; font-size: 11px; font-weight: 600; }
    .badge-pending { background: #fff3cd; color: #856404; }
    .badge-verified { background: #d4edda; color: #155724; }
    
    .btn-action { width: 32px; height: 32px; padding: 0; display: inline-flex; align-items: center; justify-content: center; border-radius: 8px; margin: 0 3px; font-size: 13px; }
    .btn-input { background: #667eea; color: white; border: none; }
    .btn-input:hover { background: #5a6fd6; color: white; }
    .btn-view { background: #e9ecef; color: #333; border: none; }
    .btn-view:hover { background: #dee2e6; }
</style>

<!-- Statistik Ringkas -->
<div class="row g-3 mb-4">
    <div class="col-md-6">
        <div class="status-card pending">
            <div class="d-flex align-items-center justify-content-between">
                <div>
                    <div class="status-number"><?php echo count($pending_users); ?></div>
                    <div class="status-label">Menunggu Verifikasi</div>
                </div>
                <i class="fas fa-clock fa-3x opacity-25"></i>
            </div>
        </div>
    </div>
    <div class="col-md-6">
        <div class="status-card verified">
            <div class="d-flex align-items-center justify-content-between">
                <div>
                    <div class="status-number"><?php echo count($verified_users); ?></div>
                    <div class="status-label">Sudah Diverifikasi</div>
                </div>
                <i class="fas fa-check-circle fa-3x opacity-25"></i>
            </div>
        </div>
    </div>
</div>

<!-- SECTION 1: MENUNGGU VERIFIKASI -->
<div class="table-card">
    <div class="table-header">
        <h4 class="table-title"><i class="fas fa-user-clock"></i> Menunggu Verifikasi</h4>
        <span class="badge bg-warning text-dark">Belum diinput profil</span>
    </div>
    <div class="p-0">
        <?php if(count($pending_users) > 0): ?>
        <div class="table-responsive">
            <table class="table table-hover align-middle mb-0">
                <thead class="table-light">
                    <tr>
                        <th style="width:50px;">No</th>
                        <th>Nama Ibu</th>
                        <th>Email / Username</th>
                        <th>No. HP</th>
                        <th>Tanggal Daftar</th>
                        <th class="text-center" style="width:120px;">Aksi</th>
                    </tr>
                </thead>
                <tbody>
                    <?php $no = 1; foreach($pending_users as $u): ?>
                    <tr>
                        <td class="fw-bold text-muted"><?php echo $no++; ?></td>
                        <td class="fw-bold"><?php echo htmlspecialchars($u['nama_lengkap']); ?></td>
                        <td>
                            <div class="small"><i class="fas fa-envelope me-1 text-muted"></i><?php echo htmlspecialchars($u['email']); ?></div>
                        </td>
                        <td><?php echo htmlspecialchars($u['no_hp'] ?? '-'); ?></td>
                        <td><?php echo date('d M Y', strtotime($u['created_at'])); ?></td>
                        <td class="text-center">
                            <a href="tambah_ibu_anak.php?email=<?php echo urlencode($u['email']); ?>&nama_ibu=<?php echo urlencode($u['nama_lengkap']); ?>&hp=<?php echo urlencode($u['no_hp']); ?>" 
                               class="btn btn-action btn-input" title="Input Profil">
                                <i class="fas fa-plus"></i>
                            </a>
                        </td>
                    </tr>
                    <?php endforeach; ?>
                </tbody>
            </table>
        </div>
        <?php else: ?>
        <div class="text-center py-4 text-muted">
            <i class="fas fa-check-circle fa-2x mb-2 text-success opacity-50"></i>
            <p class="mb-0">Semua pendaftar sudah diverifikasi!</p>
        </div>
        <?php endif; ?>
    </div>
</div>

<!-- SECTION 2: SUDAH DIVERIFIKASI -->
<div class="table-card">
    <div class="table-header">
        <h4 class="table-title"><i class="fas fa-user-check"></i> Sudah Diverifikasi</h4>
        <span class="badge bg-success">Profil lengkap</span>
    </div>
    <div class="p-0">
        <?php if(count($verified_users) > 0): ?>
        <div class="table-responsive">
            <table class="table table-hover align-middle mb-0">
                <thead class="table-light">
                    <tr>
                        <th style="width:50px;">No</th>
                        <th>Nama Ibu</th>
                        <th>Nama Anak</th>
                        <th>Puskesmas</th>
                        <th>Email</th>
                        <th>Tgl Verifikasi</th>
                        <th class="text-center" style="width:100px;">Aksi</th>
                    </tr>
                </thead>
                <tbody>
                    <?php $no = 1; foreach($verified_users as $v): ?>
                    <tr>
                        <td class="fw-bold text-muted"><?php echo $no++; ?></td>
                        <td class="fw-bold"><?php echo htmlspecialchars($v['nama_lengkap']); ?></td>
                        <td><span class="badge bg-light text-dark"><?php echo htmlspecialchars($v['nama_anak']); ?></span></td>
                        <td><?php echo htmlspecialchars($v['puskesmas']); ?></td>
                        <td class="small text-muted"><?php echo htmlspecialchars($v['email']); ?></td>
                        <td><?php echo date('d M Y', strtotime($v['profil_created'])); ?></td>
                        <td class="text-center">
                            <a href="data_ibu_anak.php" class="btn btn-action btn-view" title="Lihat Detail">
                                <i class="fas fa-eye"></i>
                            </a>
                        </td>
                    </tr>
                    <?php endforeach; ?>
                </tbody>
            </table>
        </div>
        <?php else: ?>
        <div class="text-center py-4 text-muted">
            <i class="fas fa-inbox fa-2x mb-2 opacity-50"></i>
            <p class="mb-0">Belum ada data ibu yang diverifikasi.</p>
        </div>
        <?php endif; ?>
    </div>
</div>

<?php require_once '../includes/footer.php'; ?>