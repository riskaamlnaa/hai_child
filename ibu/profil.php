<?php
// --- PERBAIKAN: Load functions.php AGAR hitungUmurBulan() dikenali ---
require_once '../includes/functions.php'; 
require_once '../includes/header_ibu.php';
require_once '../config/database.php';

$db = (new Database())->getConnection();
$user_id = $_SESSION['user_id'];

// Ambil data User
$stmt = $db->prepare("SELECT * FROM users WHERE id = ?");
$stmt->execute([$user_id]);
$user = $stmt->fetch();

// Ambil data Anak
$stmt_anak = $db->prepare("SELECT * FROM ibu_anak WHERE user_id = ?");
$stmt_anak->execute([$user_id]);
$anak = $stmt_anak->fetch();

$pesan = '';

// Proses Ubah Password
if ($_SERVER['REQUEST_METHOD'] == 'POST' && isset($_POST['ubah_pass'])) {
    $pass_lama = $_POST['pass_lama'];
    $pass_baru = $_POST['pass_baru'];
    $pass_konf = $_POST['pass_konf'];

    if (!password_verify($pass_lama, $user['password'])) {
        $pesan = '<div class="alert alert-danger">Password lama salah!</div>';
    } elseif ($pass_baru !== $pass_konf) {
        $pesan = '<div class="alert alert-danger">Konfirmasi password tidak cocok!</div>';
    } elseif (strlen($pass_baru) < 5) {
        $pesan = '<div class="alert alert-danger">Password minimal 5 karakter!</div>';
    } else {
        $hash = password_hash($pass_baru, PASSWORD_DEFAULT);
        $db->prepare("UPDATE users SET password = ? WHERE id = ?")->execute([$hash, $user_id]);
        $pesan = '<div class="alert alert-success">✅ Password berhasil diubah!</div>';
    }
}
?>

<style>
    .profil-container { padding: 20px 15px 100px; max-width: 600px; margin: 0 auto; }
    
    /* Profile Header Card */
    .profile-header {
        background: linear-gradient(135deg, #667eea, #764ba2);
        border-radius: 20px;
        padding: 30px 20px;
        text-align: center;
        color: white;
        margin-bottom: 25px;
        box-shadow: 0 10px 30px rgba(102,126,234,0.3);
        position: relative;
        overflow: hidden;
    }
    
    .profile-avatar {
        width: 100px;
        height: 100px;
        background: rgba(255,255,255,0.2);
        border-radius: 50%;
        margin: 0 auto 15px;
        display: flex;
        align-items: center;
        justify-content: center;
        font-size: 45px;
        border: 4px solid rgba(255,255,255,0.5);
    }
    
    .profile-name { font-size: 24px; font-weight: 700; margin-bottom: 5px; }
    .profile-role { font-size: 14px; opacity: 0.9; background: rgba(255,255,255,0.2); display: inline-block; padding: 4px 15px; border-radius: 20px; }
    
    /* Info Cards */
    .info-section {
        background: white;
        border-radius: 20px;
        padding: 25px;
        margin-bottom: 20px;
        box-shadow: 0 5px 20px rgba(0,0,0,0.05);
    }
    
    .section-title {
        font-size: 18px;
        font-weight: 700;
        color: #333;
        margin-bottom: 20px;
        padding-bottom: 10px;
        border-bottom: 2px solid #f0f0f0;
        display: flex;
        align-items: center;
        gap: 10px;
    }
    
    .info-row {
        display: flex;
        justify-content: space-between;
        align-items: center;
        padding: 12px 0;
        border-bottom: 1px dashed #eee;
    }
    
    .info-row:last-child { border-bottom: none; }
    .info-label { color: #888; font-size: 14px; }
    .info-value { font-weight: 600; color: #333; font-size: 15px; text-align: right; }
    
    /* Child Info Special */
    .child-info-box {
        background: #f8f9fa;
        border-radius: 15px;
        padding: 20px;
        text-align: center;
        margin-top: 10px;
    }
    
    .child-avatar { font-size: 50px; margin-bottom: 10px; }
    .child-name { font-size: 20px; font-weight: 700; color: #667eea; margin-bottom: 5px; }
    .child-detail { font-size: 14px; color: #666; }
    
    /* Logout Button */
    .btn-logout {
        background: white;
        color: #dc3545;
        border: 2px solid #dc3545;
        width: 100%;
        padding: 15px;
        border-radius: 15px;
        font-weight: 700;
        font-size: 16px;
        display: flex;
        align-items: center;
        justify-content: center;
        gap: 10px;
        text-decoration: none;
        transition: all 0.3s;
    }
    
    .btn-logout:hover {
        background: #dc3545;
        color: white;
    }
    
    .form-control { border-radius: 12px; border: 2px solid #eee; padding: 12px; }
    .form-control:focus { border-color: #667eea; box-shadow: 0 0 0 3px rgba(102,126,234,0.1); }
</style>

<div class="profil-container">
    <!-- Header -->
    <div class="profile-header">
        <div class="profile-avatar">
            <i class="fas fa-user"></i>
        </div>
        <h2 class="profile-name"><?php echo htmlspecialchars($user['nama_lengkap']); ?></h2>
        <span class="profile-role">Ibu</span>
    </div>

    <!-- Data Akun -->
    <div class="info-section">
        <h3 class="section-title"><i class="fas fa-id-card text-primary"></i>Informasi Akun</h3>
        <div class="info-row">
            <span class="info-label">Username</span>
            <span class="info-value"><?php echo htmlspecialchars($user['username']); ?></span>
        </div>
        <div class="info-row">
            <span class="info-label">Email</span>
            <span class="info-value"><?php echo htmlspecialchars($user['email']); ?></span>
        </div>
        <div class="info-row">
            <span class="info-label">No. HP</span>
            <span class="info-value"><?php echo htmlspecialchars($user['no_hp'] ?? '-'); ?></span>
        </div>
    </div>

    <!-- Data Anak -->
    <div class="info-section">
        <h3 class="section-title"><i class="fas fa-baby text-success"></i>Data Anak</h3>
        <?php if ($anak): 
            // Hitung umur menggunakan fungsi yang sudah dimuat dari functions.php
            $umur = hitungUmurBulan($anak['tanggal_lahir_anak']);
        ?>
        <div class="child-info-box">
            <div class="child-avatar"><?php echo $anak['jenis_kelamin'] == 'L' ? '👦' : '👧'; ?></div>
            <div class="child-name"><?php echo htmlspecialchars($anak['nama_anak']); ?></div>
            <div class="child-detail">
                <?php echo $umur; ?> Bulan • <?php echo $anak['jenis_kelamin'] == 'L' ? 'Laki-laki' : 'Perempuan'; ?>
            </div>
            <div style="margin-top: 10px; font-size: 13px; color: #888;">
                <i class="fas fa-hospital me-1"></i> <?php echo htmlspecialchars($anak['puskesmas']); ?>
            </div>
        </div>
        <?php else: ?>
        <p class="text-muted text-center">Data anak belum tersedia. Silakan hubungi Bidan untuk pendaftaran.</p>
        <?php endif; ?>
    </div>

    <!-- Ubah Password -->
    <div class="info-section">
        <h3 class="section-title"><i class="fas fa-lock text-warning"></i>Ubah Password</h3>
        <?php echo $pesan; ?>
        <form method="POST">
            <div class="mb-3">
                <input type="password" name="pass_lama" class="form-control" placeholder="Password Lama" required>
            </div>
            <div class="mb-3">
                <input type="password" name="pass_baru" class="form-control" placeholder="Password Baru" required>
            </div>
            <div class="mb-3">
                <input type="password" name="pass_konf" class="form-control" placeholder="Ulangi Password Baru" required>
            </div>
            <button type="submit" name="ubah_pass" class="btn btn-primary w-100" style="border-radius: 12px; padding: 12px; font-weight: 700;">
                Simpan Perubahan
            </button>
        </form>
    </div>

    <!-- Logout -->
    <a href="../logout.php" class="btn-logout">
        <i class="fas fa-sign-out-alt"></i> Logout
    </a>
</div>

<?php require_once '../includes/footer.php'; ?>