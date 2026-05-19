<?php
$page_title = 'Input Status Gizi';
require_once '../includes/header_bidan.php';
require_once '../config/database.php';
require_once '../includes/functions.php';

$db = (new Database())->getConnection();

$error = '';
$success = '';
$anak = null;
$anak_id = $_GET['id'] ?? null;
$hasil_kalkulasi = null;

// Proses Simpan
if ($_SERVER['REQUEST_METHOD'] == 'POST' && isset($_POST['simpan_gizi'])) {
    $anak_id = (int)$_POST['anak_id'];
    $tanggal = $_POST['tanggal'];
    $berat = floatval($_POST['berat_badan']);
    $tinggi = floatval($_POST['tinggi_badan']);

    if (empty($anak_id) || empty($tanggal) || $berat <= 0 || $tinggi <= 0) {
        $error = "⚠️ Lengkapi semua field dengan nilai yang valid!";
    } else {
        // Ambil tanggal lahir untuk hitung umur
        $stmt_anak = $db->prepare("SELECT tanggal_lahir_anak FROM ibu_anak WHERE id = ?");
        $stmt_anak->execute([$anak_id]);
        $tgl_lahir = $stmt_anak->fetchColumn();
        $umur_bulan = hitungUmurBulan($tgl_lahir);

        // Hitung berat ideal (WHO simplified)
        if ($umur_bulan <= 12) {
            $ideal_bb = 3 + ($umur_bulan * 0.5);
        } elseif ($umur_bulan <= 24) {
            $ideal_bb = 9 + (($umur_bulan - 12) * 0.25);
        } else {
            $ideal_bb = 12 + (($umur_bulan - 24) * 0.2);
        }

        // Hitung deviasi & status
        $deviasi = (($berat - $ideal_bb) / $ideal_bb) * 100;
        
        if ($deviasi < -20) {
            $status = 'kurang'; 
            $kategori = 'Kurang Gizi';
            $warna = 'warning';
        } elseif ($deviasi > 20) {
            $status = 'lebih'; 
            $kategori = 'Gizi Lebih';
            $warna = 'danger';
        } else {
            $status = 'normal'; 
            $kategori = 'Gizi Baik';
            $warna = 'success';
        }

        $stmt = $db->prepare("INSERT INTO status_gizi (ibu_anak_id, tanggal_ukur, umur_bulan, berat_badan, tinggi_badan, status_gizi, kategori) VALUES (?, ?, ?, ?, ?, ?, ?)");
        if ($stmt->execute([$anak_id, $tanggal, $umur_bulan, $berat, $tinggi, $status, $kategori])) {
            $hasil_kalkulasi = compact('status', 'kategori', 'deviasi', 'ideal_bb', 'warna');
            $success = "✅ Data gizi berhasil disimpan!";
        } else {
            $error = "❌ Gagal menyimpan data.";
        }
    }
}

// Ambil data anak
if ($anak_id) {
    $stmt = $db->prepare("SELECT * FROM ibu_anak WHERE id = ?");
    $stmt->execute([$anak_id]);
    $anak = $stmt->fetch();
}

// Ambil semua anak
$semua_anak = $db->query("SELECT id, nama_anak, nama_ibu FROM ibu_anak ORDER BY nama_anak ASC")->fetchAll();

// Hitung statistik
$gizi_count = 0;
if ($anak) {
    $stmt = $db->prepare("SELECT COUNT(*) FROM status_gizi WHERE ibu_anak_id = ?");
    $stmt->execute([$anak['id']]);
    $gizi_count = $stmt->fetchColumn();
}
?>

<style>
    .input-container { max-width: 900px; margin: 0 auto; }
    
    .form-card {
        background: white;
        border-radius: 20px;
        box-shadow: 0 10px 40px rgba(0,0,0,0.08);
        overflow: hidden;
        border: none;
    }
    
    .form-header {
        background: linear-gradient(135deg, #17a2b8 0%, #20c997 100%);
        padding: 30px;
        color: white;
    }
    
    .form-header h4 { margin: 0; font-weight: 700; font-size: 24px; }
    .form-header p { margin: 5px 0 0; opacity: 0.9; font-size: 14px; }
    
    .child-selector {
        background: #f8f9fa;
        padding: 25px 30px;
        border-bottom: 2px solid #e9ecef;
    }
    
    .child-info-card {
        background: linear-gradient(135deg, #17a2b8 0%, #20c997 100%);
        border-radius: 15px;
        padding: 25px;
        color: white;
        margin-top: 20px;
        display: none;
    }
    
    .child-info-card.active { display: block; animation: slideDown 0.4s ease; }
    
    @keyframes slideDown {
        from { opacity: 0; transform: translateY(-20px); }
        to { opacity: 1; transform: translateY(0); }
    }
    
    .child-avatar {
        width: 70px; height: 70px;
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
    
    .form-body { padding: 30px; }
    
    .form-label {
        font-weight: 600;
        color: #333;
        margin-bottom: 8px;
        font-size: 14px;
    }
    
    .form-control {
        border-radius: 12px;
        border: 2px solid #e9ecef;
        padding: 12px 15px;
        font-size: 14px;
        transition: all 0.3s;
    }
    
    .form-control:focus {
        border-color: #17a2b8;
        box-shadow: 0 0 0 4px rgba(23,162,184,0.1);
    }
    
    .input-group-custom {
        position: relative;
    }
    
    .input-group-custom .input-unit {
        position: absolute;
        right: 15px;
        top: 50%;
        transform: translateY(-50%);
        color: #888;
        font-weight: 600;
        font-size: 13px;
    }
    
    .hasil-kalkulasi {
        background: #f8f9fa;
        border-radius: 15px;
        padding: 25px;
        margin-top: 25px;
        text-align: center;
        border: 3px solid;
    }
    
    .hasil-kalkulasi.success { border-color: #28a745; background: #d4edda; }
    .hasil-kalkulasi.warning { border-color: #ffc107; background: #fff3cd; }
    .hasil-kalkulasi.danger { border-color: #dc3545; background: #f8d7da; }
    
    .hasil-icon {
        font-size: 50px;
        margin-bottom: 10px;
    }
    
    .hasil-kategori {
        font-size: 24px;
        font-weight: 800;
        margin: 10px 0;
    }
    
    .hasil-detail {
        font-size: 14px;
        color: #666;
    }
    
    .btn-submit {
        background: linear-gradient(135deg, #17a2b8 0%, #20c997 100%);
        border: none;
        border-radius: 12px;
        padding: 14px 30px;
        font-weight: 700;
        font-size: 15px;
        box-shadow: 0 5px 20px rgba(23,162,184,0.4);
        transition: all 0.3s;
    }
    
    .btn-submit:hover {
        transform: translateY(-2px);
        box-shadow: 0 8px 25px rgba(23,162,184,0.5);
    }
    
    .empty-state {
        text-align: center;
        padding: 60px 20px;
        color: #999;
    }
    
    .empty-state i { font-size: 80px; opacity: 0.2; margin-bottom: 20px; }
    
    .alert-custom {
        border-radius: 12px;
        border: none;
        padding: 15px 20px;
        font-weight: 600;
    }
    
    .riwayat-gizi {
        background: #f8f9fa;
        border-radius: 12px;
        padding: 20px;
        margin-top: 25px;
    }
    
    .riwayat-gizi h6 {
        font-size: 13px;
        font-weight: 700;
        color: #666;
        margin-bottom: 15px;
        text-transform: uppercase;
    }
    
    .riwayat-item {
        background: white;
        padding: 12px 15px;
        border-radius: 8px;
        margin-bottom: 8px;
        font-size: 13px;
        border-left: 4px solid #17a2b8;
        display: flex;
        justify-content: space-between;
        align-items: center;
    }
</style>

<div class="input-container">
    <div class="form-card">
        <div class="form-header">
            <h4><i class="fas fa-weight me-2"></i>Input Status Gizi</h4>
            <p>Catat pengukuran berat dan tinggi badan balita</p>
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
                        <span class="stat-badge">📊 <?php echo $gizi_count; ?> Pengukuran</span>
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

            <?php if($anak): 
                $umur = hitungUmurBulan($anak['tanggal_lahir_anak']);
                $ideal_bb = ($umur <= 12) ? 3 + ($umur * 0.5) : 9 + (($umur - 12) * 0.25);
            ?>
            <form method="POST">
                <input type="hidden" name="anak_id" value="<?php echo $anak['id']; ?>">
                
                <div class="row">
                    <div class="col-md-6 mb-3">
                        <label class="form-label">Tanggal Ukur <span class="text-danger">*</span></label>
                        <input type="date" name="tanggal" class="form-control" value="<?php echo $_POST['tanggal'] ?? date('Y-m-d'); ?>" required>
                    </div>
                    <div class="col-md-6 mb-3">
                        <label class="form-label">Umur Anak</label>
                        <input type="text" class="form-control" value="<?php echo $umur; ?> Bulan" readonly style="background: #e9ecef;">
                    </div>
                </div>

                <div class="row">
                    <div class="col-md-6 mb-3">
                        <label class="form-label">Berat Badan (kg) <span class="text-danger">*</span></label>
                        <div class="input-group-custom">
                            <input type="number" step="0.01" name="berat_badan" class="form-control" 
                                   placeholder="Contoh: 8.50" 
                                   value="<?php echo $_POST['berat_badan'] ?? ''; ?>" 
                                   required oninput="hitungEstimasi()">
                            <span class="input-unit">kg</span>
                        </div>
                        <small class="text-muted">Berat ideal estimasi: <strong><?php echo number_format($ideal_bb, 1); ?> kg</strong></small>
                    </div>
                    <div class="col-md-6 mb-3">
                        <label class="form-label">Tinggi Badan (cm) <span class="text-danger">*</span></label>
                        <div class="input-group-custom">
                            <input type="number" step="0.1" name="tinggi_badan" class="form-control" 
                                   placeholder="Contoh: 72.5" 
                                   value="<?php echo $_POST['tinggi_badan'] ?? ''; ?>" 
                                   required>
                            <span class="input-unit">cm</span>
                        </div>
                    </div>
                </div>

                <div class="d-flex gap-2 mt-4">
                    <button type="submit" name="simpan_gizi" class="btn btn-primary btn-submit">
                        <i class="fas fa-save me-2"></i>Simpan Data Gizi
                    </button>
                    <a href="data_ibu_anak.php" class="btn btn-light border" style="border-radius: 12px; padding: 14px 25px;">
                        <i class="fas fa-times me-2"></i>Batal
                    </a>
                </div>
            </form>

            <!-- Hasil Kalkulasi -->
            <?php if($hasil_kalkulasi): 
                $icons = ['success' => '✅', 'warning' => '⚠️', 'danger' => '🔶'];
            ?>
            <div class="hasil-kalkulasi <?php echo $hasil_kalkulasi['warna']; ?>">
                <div class="hasil-icon"><?php echo $icons[$hasil_kalkulasi['warna']] ?? '📊'; ?></div>
                <div class="hasil-kategori"><?php echo $hasil_kalkulasi['kategori']; ?></div>
                <div class="hasil-detail">
                    Deviasi: <?php echo number_format($hasil_kalkulasi['deviasi'], 1); ?>% dari berat ideal<br>
                    Berat ideal: <?php echo number_format($hasil_kalkulasi['ideal_bb'], 1); ?> kg
                </div>
            </div>
            <?php endif; ?>

            <!-- Riwayat Pengukuran -->
            <?php
            $stmt = $db->prepare("SELECT tanggal_ukur, berat_badan, tinggi_badan, kategori FROM status_gizi WHERE ibu_anak_id = ? ORDER BY tanggal_ukur DESC LIMIT 5");
            $stmt->execute([$anak['id']]);
            $riwayat = $stmt->fetchAll();
            if(count($riwayat) > 0):
            ?>
            <div class="riwayat-gizi">
                <h6><i class="fas fa-history me-2"></i>5 Pengukuran Terakhir</h6>
                <?php foreach($riwayat as $r): ?>
                <div class="riwayat-item">
                    <div>
                        <i class="fas fa-calendar me-2 text-muted"></i>
                        <?php echo date('d M Y', strtotime($r['tanggal_ukur'])); ?>
                    </div>
                    <div>
                        <strong><?php echo $r['berat_badan']; ?> kg</strong> / <?php echo $r['tinggi_badan']; ?> cm
                    </div>
                    <span class="badge bg-info"><?php echo htmlspecialchars($r['kategori']); ?></span>
                </div>
                <?php endforeach; ?>
            </div>
            <?php endif; ?>

            <?php else: ?>
            <div class="empty-state">
                <i class="fas fa-hand-pointer"></i>
                <h5>Pilih Anak Terlebih Dahulu</h5>
                <p class="mb-0">Silakan pilih nama anak dari dropdown di atas untuk melanjutkan input status gizi.</p>
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

function hitungEstimasi() {
    // Bisa ditambahkan live kalkulasi jika diperlukan
}
</script>

<?php require_once '../includes/footer.php'; ?>