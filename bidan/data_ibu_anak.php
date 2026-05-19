<?php
$page_title = 'Data Ibu & Anak';
require_once '../includes/header_bidan.php';
require_once '../config/database.php';

$database = new Database();
$db = $database->getConnection();

// Logika Pencarian
$keyword = '';
$query_param = "";

if (isset($_GET['search']) && !empty($_GET['search'])) {
    $keyword = sanitize($_GET['search']);
    $query_param = "WHERE ia.nama_anak LIKE ? OR ia.nama_ibu LIKE ? OR u.nama_lengkap LIKE ?";
    $keyword_like = "%" . $keyword . "%";
}

// Query Utama (Gabung Tabel Ibu_Anak & Users)
$query = "SELECT ia.*, u.email, u.no_hp, u.nama_lengkap as nama_user 
          FROM ibu_anak ia 
          LEFT JOIN users u ON ia.user_id = u.id 
          " . $query_param . "
          ORDER BY ia.created_at DESC";

if (isset($keyword_like)) {
    $stmt = $db->prepare($query);
    $stmt->execute([$keyword_like, $keyword_like, $keyword_like]);
    $data = $stmt->fetchAll(PDO::FETCH_ASSOC);
} else {
    $data = $db->query($query)->fetchAll(PDO::FETCH_ASSOC);
}
?>

<style>
    .search-box {
        max-width: 300px;
        position: relative;
    }
    .search-box i {
        position: absolute;
        left: 15px;
        top: 50%;
        transform: translateY(-50%);
        color: #888;
    }
    .search-input {
        padding-left: 40px;
        border-radius: 20px;
        background-color: #f8f9fa;
        border: 1px solid #e9ecef;
    }
    .search-input:focus {
        background-color: white;
        border-color: #667eea;
        box-shadow: 0 0 0 3px rgba(102, 126, 234, 0.15);
    }
    .btn-action {
        width: 32px;
        height: 32px;
        padding: 0;
        display: inline-flex;
        align-items: center;
        justify-content: center;
        border-radius: 8px;
        margin-right: 4px;
    }
</style>

<div class="card content-card border-0 shadow-sm">
    <div class="card-header bg-white py-3 d-flex justify-content-between align-items-center">
        <h5 class="mb-0 fw-bold text-primary"><i class="fas fa-users me-2"></i>Data Ibu & Anak</h5>
        
        <!-- Form Search -->
        <form method="GET" class="search-box">
            <i class="fas fa-search"></i>
            <input type="text" name="search" class="form-control search-input" 
                   placeholder="Cari nama anak/ibu..." 
                   value="<?php echo htmlspecialchars($keyword); ?>">
        </form>
        
        <a href="tambah_ibu_anak.php" class="btn btn-primary btn-sm rounded-pill px-3">
            <i class="fas fa-plus me-1"></i> Tambah Data
        </a>
    </div>
    
    <div class="card-body p-0">
        <div class="table-responsive">
            <table class="table table-hover align-middle mb-0">
                <thead class="table-light">
                    <tr>
                        <th class="ps-4" style="width: 50px;">No</th>
                        <th>Nama Anak</th>
                        <th>Nama Ibu</th>
                        <th>Kontak (Email/HP)</th>
                        <th>Tgl Lahir</th>
                        <th>Usia</th>
                        <th>Puskesmas</th>
                        <th class="text-center pe-4">Aksi</th>
                    </tr>
                </thead>
                <tbody>
                    <?php 
                    $no = 1; 
                    if(count($data) > 0):
                        foreach($data as $row): 
                            // Hitung Umur
                            $birthDate = new DateTime($row['tanggal_lahir_anak']);
                            $today = new DateTime("today");
                            $diff = $today->diff($birthDate);
                            $umur_bulan = ($diff->y * 12) + $diff->m;
                    ?>
                    <tr>
                        <td class="ps-4 fw-bold text-muted"><?php echo $no++; ?></td>
                        <td>
                            <div class="fw-bold text-dark"><?php echo htmlspecialchars($row['nama_anak']); ?></div>
                            <small class="text-muted"><?php echo $row['jenis_kelamin'] == 'L' ? '♂ Laki-laki' : '♀ Perempuan'; ?></small>
                        </td>
                        <td><?php echo htmlspecialchars($row['nama_ibu']); ?></td>
                        <td>
                            <div class="small"><i class="fas fa-envelope me-1 text-muted"></i><?php echo htmlspecialchars($row['email'] ?? '-'); ?></div>
                            <div class="small"><i class="fas fa-phone me-1 text-muted"></i><?php echo htmlspecialchars($row['no_hp'] ?? '-'); ?></div>
                        </td>
                        <td><?php echo date('d M Y', strtotime($row['tanggal_lahir_anak'])); ?></td>
                        <td><span class="badge bg-info bg-opacity-10 text-info px-2 py-1 rounded-pill"><?php echo $umur_bulan; ?> Bulan</span></td>
                        <td><small class="text-muted"><?php echo htmlspecialchars($row['puskesmas']); ?></small></td>
                        <td class="text-center pe-4">
                            <!-- Tombol Aksi -->
                            <a href="edit_ibu_anak.php?id=<?php echo $row['id']; ?>" class="btn btn-action btn-outline-warning" title="Edit Data">
                                <i class="fas fa-edit"></i>
                            </a>
                            <a href="tambah_imunisasi.php?id=<?php echo $row['id']; ?>" class="btn btn-action btn-outline-success" title="Input Imunisasi">
                                <i class="fas fa-syringe"></i>
                            </a>
                            <a href="tambah_gizi.php?id=<?php echo $row['id']; ?>" class="btn btn-action btn-outline-info" title="Input Gizi">
                                <i class="fas fa-weight"></i>
                            </a>
                            <a href="hapus_ibu_anak.php?id=<?php echo $row['id']; ?>" class="btn btn-action btn-outline-danger" onclick="return confirm('⚠️ PERINGATAN: Menghapus data ini akan menghapus semua data imunisasi dan gizi anak yang terkait. Lanjutkan?')" title="Hapus">
                                <i class="fas fa-trash"></i>
                            </a>
                        </td>
                    </tr>
                    <?php 
                        endforeach;
                    else:
                    ?>
                    <tr>
                        <td colspan="8" class="text-center py-5">
                            <div class="text-muted">
                                <i class="fas fa-folder-open fa-3x mb-3"></i>
                                <h5>Belum ada data</h5>
                                <p>Silakan tambah data ibu dan anak baru.</p>
                            </div>
                        </td>
                    </tr>
                    <?php endif; ?>
                </tbody>
            </table>
        </div>
    </div>
</div>

<?php require_once '../includes/footer.php'; ?>