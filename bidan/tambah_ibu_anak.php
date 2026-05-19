<?php
$page_title = 'Tambah Data';
require_once '../includes/header_bidan.php';
require_once '../config/database.php';

$database = new Database();
$db = $database->getConnection();

$error = '';
$success = '';
$password_baru = '';

// AUTO-FILL DARI HALAMAN MANAJEMEN DATA IBU
$prefill_nama_ibu = $_GET['nama_ibu'] ?? '';
$prefill_email = $_GET['email'] ?? '';
$prefill_no_hp = $_GET['hp'] ?? '';

if($_SERVER['REQUEST_METHOD'] == 'POST') {
    // Data ibu
    $nama_ibu_baru = sanitize($_POST['nama_ibu_baru']);
    $email_baru = sanitize($_POST['email_baru']);
    $no_hp_baru = sanitize($_POST['no_hp_baru']);
    
    // Data anak
    $nama_anak = sanitize($_POST['nama_anak']);
    $tgl_lahir = $_POST['tanggal_lahir'];
    $jk = $_POST['jenis_kelamin'];
    $puskesmas = sanitize($_POST['puskesmas']);
    
    try {
        // CEK 1: Apakah email ini sudah punya profil di tabel ibu_anak?
        $check_profil = $db->prepare("
            SELECT ia.id 
            FROM users u 
            LEFT JOIN ibu_anak ia ON u.id = ia.user_id 
            WHERE u.email = ? AND ia.id IS NOT NULL
        ");
        $check_profil->execute([$email_baru]);
        
        if($check_profil->rowCount() > 0) {
            $error = "⚠️ Email sudah memiliki profil data ibu & anak!";
        } else {
            // CEK 2: Apakah user sudah terdaftar di tabel users?
            $check_user = $db->prepare("SELECT id FROM users WHERE email = ?");
            $check_user->execute([$email_baru]);
            
            if($check_user->rowCount() > 0) {
                // User sudah ada di tabel users, ambil user_id
                $user_data = $check_user->fetch(PDO::FETCH_ASSOC);
                $user_id = $user_data['id'];
            } else {
                // User belum ada, buat user baru dengan password random
                $password_baru = 'HaiChild' . rand(1000, 9999);
                $password_hash = password_hash($password_baru, PASSWORD_DEFAULT);
                $username = 'ibu' . time() . rand(100, 999);
                
                $query_user = "INSERT INTO users (username, password, role, nama_lengkap, email, no_hp, status) 
                              VALUES (?, ?, 'ibu_anak', ?, ?, ?, 'active')";
                $stmt_user = $db->prepare($query_user);
                $stmt_user->execute([$username, $password_hash, $nama_ibu_baru, $email_baru, $no_hp_baru]);
                
                $user_id = $db->lastInsertId();
            }
            
            // INSERT data ke tabel ibu_anak (TANPA kolom email)
            $query_anak = "INSERT INTO ibu_anak (user_id, nama_ibu, nama_anak, tanggal_lahir_anak, jenis_kelamin, puskesmas) 
                          VALUES (?, ?, ?, ?, ?, ?)";
            $stmt_anak = $db->prepare($query_anak);
            $stmt_anak->execute([$user_id, $nama_ibu_baru, $nama_anak, $tgl_lahir, $jk, $puskesmas]);
            
            $success_msg = "<i class='fas fa-check-circle me-2'></i>✅ Data berhasil ditambahkan!";
            if(!empty($password_baru)) {
                $success_msg .= "<br><br><strong>Password untuk ibu:</strong> <span class='text-danger fw-bold'>$password_baru</span>";
            }
            
            $success = "<div class='alert alert-success'>$success_msg</div>";
            
            // Reset form & auto-fill
            $_POST = array();
            $prefill_nama_ibu = '';
            $prefill_email = '';
            $prefill_no_hp = '';
        }
    } catch(PDOException $e) {
        $error = "<i class='fas fa-exclamation-triangle me-2'></i>Error: " . $e->getMessage();
    }
}
?>

<div class="card content-card border-0 shadow-sm">
    <div class="card-header bg-white py-3">
        <h5 class="mb-0 fw-bold text-primary"><i class="fas fa-plus me-2"></i>Tambah Data Ibu & Anak Baru</h5>
    </div>
    <div class="card-body">
        <?php if($error): ?>
        <div class="alert alert-danger"><?php echo $error; ?></div>
        <?php endif; ?>
        <?php echo $success; ?>

        <?php if(empty($success)): ?>
        <form method="POST">
            <h6 class="mb-3 text-primary"><i class="fas fa-user me-2"></i>Data Ibu</h6>
            <div class="row">
                <div class="col-md-6 mb-3">
                    <label class="form-label">Nama Lengkap Ibu <span class="text-danger">*</span></label>
                    <input type="text" class="form-control" name="nama_ibu_baru" required 
                           placeholder="Masukkan nama lengkap ibu"
                           value="<?php echo htmlspecialchars($prefill_nama_ibu); ?>">
                </div>
                <div class="col-md-6 mb-3">
                    <label class="form-label">Email <span class="text-danger">*</span></label>
                    <input type="email" class="form-control" name="email_baru" required 
                           placeholder="email@example.com"
                           value="<?php echo htmlspecialchars($prefill_email); ?>">
                    <small class="text-muted">Email akan digunakan untuk login</small>
                </div>
            </div>
            <div class="mb-3">
                <label class="form-label">No. HP</label>
                <input type="text" class="form-control" name="no_hp_baru" 
                       placeholder="081234567890"
                       value="<?php echo htmlspecialchars($prefill_no_hp); ?>">
            </div>
            
            <hr class="my-4">
            
            <h6 class="mb-3 text-primary"><i class="fas fa-baby me-2"></i>Data Anak</h6>
            <div class="row">
                <div class="col-md-6 mb-3">
                    <label class="form-label">Nama Anak <span class="text-danger">*</span></label>
                    <input type="text" class="form-control" name="nama_anak" required 
                           placeholder="Masukkan nama anak">
                </div>
                <div class="col-md-3 mb-3">
                    <label class="form-label">Tanggal Lahir <span class="text-danger">*</span></label>
                    <input type="date" class="form-control" name="tanggal_lahir" id="tanggal_lahir" required>
                </div>
                <div class="col-md-3 mb-3">
                    <label class="form-label">Usia Anak</label>
                    <input type="text" class="form-control" id="usia_anak" readonly 
                           placeholder="Otomatis" style="background-color: #e9ecef;">
                </div>
            </div>
            <div class="row">
                <div class="col-md-6 mb-3">
                    <label class="form-label">Jenis Kelamin <span class="text-danger">*</span></label>
                    <select class="form-select" name="jenis_kelamin" required>
                        <option value="">-- Pilih --</option>
                        <option value="L">Laki-laki</option>
                        <option value="P">Perempuan</option>
                    </select>
                </div>
                <div class="col-md-6 mb-3">
                    <label class="form-label">Puskesmas</label>
                    <input type="text" class="form-control" name="puskesmas" 
                           value="Puskesmas Banjarmasin Timur">
                </div>
            </div>
            
            <div class="alert alert-warning mt-3">
                <i class="fas fa-lock me-2"></i>
                <strong>Keamanan:</strong> Password akan di-generate otomatis dan ditampilkan setelah data tersimpan.
            </div>
            
            <div class="mt-3">
                <button type="submit" class="btn btn-primary">
                    <i class="fas fa-save me-2"></i> Simpan Data
                </button>
                <a href="manajemen_ibu.php" class="btn btn-secondary">
                    <i class="fas fa-times me-2"></i> Batal
                </a>
            </div>
        </form>
        <?php else: ?>
        <div class="mt-3">
            <a href="tambah_ibu_anak.php" class="btn btn-primary">
                <i class="fas fa-plus me-2"></i> Tambah Data Lagi
            </a>
            <a href="manajemen_ibu.php" class="btn btn-secondary">
                <i class="fas fa-list me-2"></i> Kembali
            </a>
        </div>
        <?php endif; ?>
    </div>
</div>

<script>
// Hitung usia otomatis saat tanggal lahir diisi
document.getElementById('tanggal_lahir')?.addEventListener('change', function() {
    var tanggalLahir = this.value;
    if(tanggalLahir) {
        var lahir = new Date(tanggalLahir);
        var sekarang = new Date();
        var tahun = sekarang.getFullYear() - lahir.getFullYear();
        var bulan = sekarang.getMonth() - lahir.getMonth();
        if(bulan < 0) { tahun--; bulan += 12; }
        document.getElementById('usia_anak').value = tahun + ' tahun ' + bulan + ' bulan';
    }
});
</script>

<?php require_once '../includes/footer.php'; ?>