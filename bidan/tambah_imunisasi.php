<?php
$page_title = 'Input Imunisasi';
require_once '../includes/header_bidan.php';
require_once '../config/database.php';
require_once '../includes/functions.php';

$db = (new Database())->getConnection();

// Daftar jenis imunisasi dasar
$jenis_imunisasi = [
    'Hepatitis B-0' => '0-7 Hari',
    'BCG' => '0-1 Bulan',
    'Polio-1' => '1 Bulan',
    'DPT-HB-Hib-1' => '2 Bulan',
    'Polio-2' => '2 Bulan',
    'DPT-HB-Hib-2' => '3 Bulan',
    'Polio-3' => '3 Bulan',
    'DPT-HB-Hib-3' => '4 Bulan',
    'Polio-4' => '4 Bulan',
    'IPV' => '4 Bulan',
    'Campak/MR-1' => '9 Bulan',
    'Campak/MR-2' => '18 Bulan'
];

$error = '';
$success = '';
$anak = null;
$anak_id = $_GET['id'] ?? null;

// Proses Simpan
if ($_SERVER['REQUEST_METHOD'] == 'POST' && isset($_POST['simpan_imunisasi'])) {
    $anak_id = (int)$_POST['anak_id'];
    $jenis = sanitize($_POST['jenis']);
    $tanggal = $_POST['tanggal'];
    $status = $_POST['status'];
    $keterangan = sanitize($_POST['keterangan']);

    if (empty($anak_id) || empty($jenis) || empty($tanggal)) {
        $error = "⚠️ Lengkapi semua field wajib!";
    } else {
        // Cek duplikasi
        $stmt = $db->prepare("SELECT id FROM imunisasi WHERE ibu_anak_id = ? AND jenis_imunisasi = ?");
        $stmt->execute([$anak_id, $jenis]);
        if ($stmt->rowCount() > 0) {
            $error = "⚠️ Imunisasi ini sudah pernah dicatat!";
        } else {
            $stmt = $db->prepare("INSERT INTO imunisasi (ibu_anak_id, jenis_imunisasi, tanggal_imunisasi, status, keterangan) VALUES (?, ?, ?, ?, ?)");
            if ($stmt->execute([$anak_id, $jenis, $tanggal, $status, $keterangan])) {
                $success = "✅ Data imunisasi berhasil disimpan!";
                // Refresh untuk reset form
                header("Location: tambah_imunisasi.php?id=" . $anak_id . "&success=1");
                exit();
            } else {
                $error = "❌ Gagal menyimpan data.";
            }
        }
    }
}

// Cek success dari redirect
if (isset($_GET['success'])) {
    $success = "✅ Data imunisasi berhasil disimpan!";
}

// Ambil data anak
if ($anak_id) {
    $stmt = $db->prepare("SELECT * FROM ibu_anak WHERE id = ?");
    $stmt->execute([$anak_id]);
    $anak = $stmt->fetch();
}

// Ambil semua anak untuk dropdown
$semua_anak = $db->query("SELECT id, nama_anak, nama_ibu FROM ibu_anak ORDER BY nama_anak ASC")->fetchAll();

// Hitung statistik imunisasi anak
$imun_count = 0;
if ($anak) {
    $stmt = $db->prepare("SELECT COUNT(*) FROM imunisasi WHERE ibu_anak_id = ?");
    $stmt->execute([$anak['id']]);
    $imun_count = $stmt->fetchColumn();
}
?>

<style>
    .input-container {
        max-width: 900px;
        margin: 0 auto;
    }
    
    .form-card {
        background: white;
        border-radius: 20px;
        box-shadow: 0 10px 40px rgba(0,0,0,0.08);
        overflow: hidden;
        border: none;
    }
    
    .form-header {
        background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
        padding: 30px;
        color: white;
        position: relative;
    }
    
    .form-header h4 {
        margin: 0;
        font-weight: 700;
        font-size: 24px;
    }
    
    .form-header p {
        margin: 5px 0 0;
        opacity: 0.9;
        font-size: 14px;
    }
    
    .child-selector {
        background: #f8f9fa;
        padding: 25px 30px;
        border-bottom: 2px solid #e9ecef;
    }
    
    .child-info-card {
        background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
        border-radius: 15px;
        padding: 25px;
        color: white;
        margin-top: 20px;
        display: none;
    }
    
    .child-info-card.active {
        display: block;
        animation: slideDown 0.4s ease;
    }
    
    @keyframes slideDown {
        from { opacity: 0; transform: translateY(-20px); }
        to { opacity: 1; transform: translateY(0); }
    }
    
    .child-avatar {
        width: 70px;
        height: 70px;
        background: rgba(255,255,255,0.2);
        border-radius: 50%;
        display: flex;
        align-items: center;
        justify-content: center;
        font-size: 35px;
        margin-right: 20px;
    }
    
    .stat-badge {
        background: rgba(255,255,255,0.2);
        padding: 5px 15px;
        border-radius: 20px;
        font-size: 12px;
        margin-right: 10px;
        display: inline-block;
    }
    
    .form-body {
        padding: 30px;
    }
    
    .form-label {
        font-weight: 600;
        color: #333;
        margin-bottom: 8px;
        font-size: 14px;
    }
    
    .form-control, .form-select {
        border-radius: 12px;
        border: 2px solid #e9ecef;
        padding: 12px 15px;
        font-size: 14px;
        transition: all 0.3s;
    }
    
    .form-control:focus, .form-select:focus {
        border-color: #667eea;
        box-shadow: 0 0 0 4px rgba(102,126,234,0.1);
    }
    
    .imunisasi-timeline {
        background: #f8f9fa;
        border-radius: 12px;
        padding: 20px;
        margin-bottom: 25px;
    }
    
    .imunisasi-timeline h6 {
        font-size: 13px;
        font-weight: 700;
        color: #666;
        margin-bottom: 15px;
        text-transform: uppercase;
    }
    
    .imun-item {
        display: flex;
        justify-content: space-between;
        padding: 10px 15px;
        background: white;
        border-radius: 8px;
        margin-bottom: 8px;
        font-size: 13px;
        border-left: 4px solid #667eea;
    }
    
    .imun-item.done {
        border-left-color: #28a745;
        opacity: 0.7;
    }
    
    .btn-submit {
        background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
        border: none;
        border-radius: 12px;
        padding: 14px 30px;
        font-weight: 700;
        font-size: 15px;
        box-shadow: 0 5px 20px rgba(102,126,234,0.4);
        transition: all 0.3s;
    }
    
    .btn-submit:hover {
        transform: translateY(-2px);
        box-shadow: 0 8px 25px rgba(102,126,234,0.5);
    }
    
    .empty-state {
        text-align: center;
        padding: 60px 20px;
        color: #999;
    }
    
    .empty-state i {
        font-size: 80px;
        opacity: 0.2;
        margin-bottom: 20px;
    }
    
    .alert-custom {
        border-radius: 12px;
        border: none;
        padding: 15px 20px;
        font-weight: 600;
    }
</style>

<div class="input-container">
    <div class="form-card">
        <div class="form-header">
            <h4><i class="fas fa-syringe me-2"></i>Input Imunisasi</h4>
            <p>Catat riwayat imunisasi balita dengan lengkap</p>
        </div>

        <div class="child-selector">
            <label class="form-label">Pilih Anak <span class="text-danger">*</span></label>
            <select class="form-select form-select-lg" id="selectAnak" onchange="pilihAnak(this.value)">
                <option value="">-- Pilih Nama Anak --</option>
                <?php foreach($semua_anak as $a): ?>
                <option value="<?php echo $a['id']; ?>" <?php echo ($anak_id == $a['id']) ? 'selected' : ''; ?>>
                    <?php echo htmlspecialchars($a['nama_anak']); ?> (Ibu: <?php echo htmlspecialchars($a['nama_ibu']); ?>)
                </option>
                <?php endforeach; ?>
            </select>
        </div>

        <?php if($anak): ?>
        <div class="child-info-card active">
            <div class="d-flex align-items-center">
                <div class="child-avatar">
                    <?php echo $anak['jenis_kelamin'] == 'L' ? '👦' : '👧'; ?>
                </div>
                <div class="flex-grow-1">
                    <h5 class="mb-1"><?php echo htmlspecialchars($anak['nama_anak']); ?></h5>
                    <div>
                        <span class="stat-badge">👤 <?php echo htmlspecialchars($anak['nama_ibu']); ?></span>
                        <span class="stat-badge">🎂 <?php echo hitungUmurBulan($anak['tanggal_lahir_anak']); ?> Bulan</span>
                        <span class="stat-badge">💉 <?php echo $imun_count; ?> Imunisasi</span>
                    </div>
                </div>
            </div>
        </div>
        <?php endif; ?>

        <div class="form-body">
            <?php if($error): ?>
            <div class="alert alert-danger alert-custom mb-4"><?php echo $error; ?></div>
            <?php endif; ?>
            
            <?php if($success): ?>
            <div class="alert alert-success alert-custom mb-4"><?php echo $success; ?></div>
            <?php endif; ?>

            <?php if($anak): ?>
            <form method="POST">
                <input type="hidden" name="anak_id" value="<?php echo $anak['id']; ?>">
                
                <div class="row">
                    <div class="col-md-8 mb-3">
                        <label class="form-label">Jenis Imunisasi <span class="text-danger">*</span></label>
                        <select name="jenis" class="form-select" required>
                            <option value="">Pilih Jenis Imunisasi</option>
                            <?php foreach($jenis_imunisasi as $nama => $usia): ?>
                            <option value="<?php echo $nama; ?>" <?php echo (($_POST['jenis'] ?? '') == $nama) ? 'selected' : ''; ?>>
                                <?php echo $nama; ?> (Ideal: <?php echo $usia; ?>)
                            </option>
                            <?php endforeach; ?>
                        </select>
                    </div>
                    <div class="col-md-4 mb-3">
                        <label class="form-label">Tanggal Imunisasi <span class="text-danger">*</span></label>
                        <input type="date" name="tanggal" class="form-control" value="<?php echo $_POST['tanggal'] ?? date('Y-m-d'); ?>" required>
                    </div>
                </div>

                <div class="row">
                    <div class="col-md-6 mb-3">
                        <label class="form-label">Status</label>
                        <select name="status" class="form-select">
                            <option value="lengkap">✅ Lengkap</option>
                            <option value="tidak_lengkap">❌ Tidak Lengkap</option>
                            <option value="menunggu">⏳ Menunggu</option>
                        </select>
                    </div>
                    <div class="col-md-6 mb-3">
                        <label class="form-label">Keterangan</label>
                        <textarea name="keterangan" class="form-control" rows="3" placeholder="Contoh: Tidak ada reaksi alergi, demam ringan..."><?php echo $_POST['keterangan'] ?? ''; ?></textarea>
                    </div>
                </div>

                <!-- Timeline Imunisasi yang Sudah Diinput -->
                <?php
                $stmt = $db->prepare("SELECT jenis_imunisasi, tanggal_imunisasi FROM imunisasi WHERE ibu_anak_id = ? ORDER BY tanggal_imunisasi DESC");
                $stmt->execute([$anak['id']]);
                $riwayat = $stmt->fetchAll();
                if(count($riwayat) > 0):
                ?>
                <div class="imunisasi-timeline">
                    <h6><i class="fas fa-history me-2"></i>Riwayat Imunisasi</h6>
                    <?php foreach($riwayat as $r): ?>
                    <div class="imun-item done">
                        <span><i class="fas fa-check-circle text-success me-2"></i><?php echo htmlspecialchars($r['jenis_imunisasi']); ?></span>
                        <span class="text-muted"><?php echo date('d M Y', strtotime($r['tanggal_imunisasi'])); ?></span>
                    </div>
                    <?php endforeach; ?>
                </div>
                <?php endif; ?>

                <div class="d-flex gap-2 mt-4">
                    <button type="submit" name="simpan_imunisasi" class="btn btn-primary btn-submit">
                        <i class="fas fa-save me-2"></i>Simpan Imunisasi
                    </button>
                    <a href="data_ibu_anak.php" class="btn btn-light border" style="border-radius: 12px; padding: 14px 25px;">
                        <i class="fas fa-times me-2"></i>Batal
                    </a>
                </div>
            </form>
            <?php else: ?>
            <div class="empty-state">
                <i class="fas fa-hand-pointer"></i>
                <h5>Pilih Anak Terlebih Dahulu</h5>
                <p class="mb-0">Silakan pilih nama anak dari dropdown di atas untuk melanjutkan input imunisasi.</p>
            </div>
            <?php endif; ?>
        </div>
    </div>
</div>

<script>
function pilihAnak(anakId) {
    if(anakId) {
        window.location.href = '?id=' + anakId;
    }
}
</script>

<?php require_once '../includes/footer.php'; ?>