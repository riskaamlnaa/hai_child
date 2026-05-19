<?php
$page_title = 'Riwayat Imunisasi';
require_once '../includes/header_ibu.php';
require_once '../config/database.php';

$db = (new Database())->getConnection();
$user_id = $_SESSION['user_id'];

// Ambil ID anak
$stmt = $db->prepare("SELECT id FROM ibu_anak WHERE user_id = ?");
$stmt->execute([$user_id]);
$anak = $stmt->fetch();

$imunisasi = [];
if ($anak) {
    $stmt = $db->prepare("SELECT * FROM imunisasi WHERE ibu_anak_id = ? ORDER BY tanggal_imunisasi DESC");
    $stmt->execute([$anak['id']]);
    $imunisasi = $stmt->fetchAll();
}
?>

<div class="row">
    <div class="col-12">
        <div class="card card-custom p-3">
            <h5 class="fw-bold mb-3"><i class="fas fa-syringe text-primary me-2"></i>Data Imunisasi</h5>
            
            <?php if (count($imunisasi) > 0): ?>
                <div class="list-group list-group-flush">
                    <?php foreach ($imunisasi as $row): ?>
                    <div class="list-group-item d-flex justify-content-between align-items-center px-0">
                        <div>
                            <h6 class="mb-1 fw-bold"><?php echo htmlspecialchars($row['jenis_imunisasi']); ?></h6>
                            <small class="text-muted"><i class="far fa-calendar me-1"></i><?php echo date('d M Y', strtotime($row['tanggal_imunisasi'])); ?></small>
                        </div>
                        <span class="badge bg-<?php echo $row['status'] == 'lengkap' ? 'success' : 'warning'; ?>">
                            <?php echo $row['status']; ?>
                        </span>
                    </div>
                    <?php endforeach; ?>
                </div>
            <?php else: ?>
                <div class="text-center py-4 text-muted">
                    <i class="fas fa-folder-open fa-3x mb-2"></i>
                    <p>Belum ada data imunisasi.</p>
                </div>
            <?php endif; ?>
        </div>
    </div>
</div>

<?php require_once '../includes/footer.php'; ?>