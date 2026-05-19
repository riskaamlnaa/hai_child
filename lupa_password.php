<?php
session_start(); // PENTING: Untuk menyimpan kode verifikasi di session
require_once 'config/database.php';

// Inisialisasi variabel
$success = '';
$error = '';
$email = '';
$step = isset($_GET['step']) ? (int)$_GET['step'] : 1; // Default step 1

// Generate kode verifikasi jika belum ada
if (!isset($_SESSION['verify_code'])) {
    $_SESSION['verify_code'] = rand(100000, 999999);
}

if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    
    // === STEP 1: Verifikasi Email ===
    if ($step == 1) {
        $email = sanitize($_POST['email']);
        $database = new Database();
        $db = $database->getConnection();
        
        // Cek apakah email terdaftar
        $stmt = $db->prepare("SELECT id FROM users WHERE email = ? AND status = 'active'");
        $stmt->execute([$email]);
        
        if ($stmt->rowCount() == 0) {
            $error = "Email tidak terdaftar atau akun tidak aktif!";
        } else {
            // Email valid, simpan ke session dan generate kode baru
            $_SESSION['reset_email'] = $email;
            $_SESSION['verify_code'] = rand(100000, 999999); // Kode baru setiap request
            
            // SIMULASI PENGIRIMAN EMAIL (Karena localhost tidak ada mail server)
            // Di produksi, gunakan PHPMailer atau fungsi mail() disini
            $success = "Kode verifikasi telah dikirim ke email Anda.<br>
                       <strong>Kode Simulasi:</strong> <span style='background:#eee; padding:5px 10px; border-radius:5px; font-weight:bold; letter-spacing:2px;'>" . $_SESSION['verify_code'] . "</span><br>
                       <small class='text-muted'>(Hanya untuk demo, di produksi kode dikirim via email)</small>";
            $step = 2; // Lanjut ke step 2
        }
    } 
    // === STEP 2: Verifikasi Kode ===
    elseif ($step == 2) {
        $input_code = $_POST['verify_code'];
        
        if ($input_code == $_SESSION['verify_code']) {
            // Kode cocok, lanjut ke form password
            $success = "Verifikasi berhasil! Silakan masukkan password baru.";
            $step = 3;
        } else {
            $error = "Kode verifikasi salah! Silakan coba lagi atau minta kode baru.";
        }
    } 
    // === STEP 3: Ganti Password ===
    elseif ($step == 3) {
        $password_baru = $_POST['password_baru'];
        $konfirmasi = $_POST['konfirmasi_password'];
        
        if ($password_baru !== $konfirmasi) {
            $error = "Password dan konfirmasi tidak cocok!";
        } elseif (strlen($password_baru) < 5) {
            $error = "Password minimal 5 karakter!";
        } else {
            // Update password ke database
            $database = new Database();
            $db = $database->getConnection();
            
            $hash_baru = password_hash($password_baru, PASSWORD_DEFAULT);
            $update = $db->prepare("UPDATE users SET password = ? WHERE email = ?");
            $update->execute([$hash_baru, $_SESSION['reset_email']]);
            
            // Bersihkan session setelah sukses
            unset($_SESSION['reset_email']);
            unset($_SESSION['verify_code']);
            
            $success = "✅ Password berhasil direset! Silakan login dengan password baru.";
            $step = 4; // Step selesai
        }
    }
}

function sanitize($data) {
    return htmlspecialchars(strip_tags(trim($data)), ENT_QUOTES, 'UTF-8');
}
?>
<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Lupa Password - Hai Child</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <style>
        body {
            background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
            min-height: 100vh;
            display: flex;
            align-items: center;
            justify-content: center;
            padding: 20px;
        }
        .reset-container {
            background: white;
            border-radius: 24px;
            box-shadow: 0 20px 60px rgba(0,0,0,0.3);
            max-width: 480px;
            width: 100%;
            overflow: hidden;
        }
        .reset-header {
            background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
            color: white;
            padding: 35px 30px;
            text-align: center;
        }
        .reset-header i { font-size: 50px; margin-bottom: 15px; }
        .reset-header h2 { font-size: 24px; margin: 0; }
        .reset-header p { margin: 5px 0 0; opacity: 0.9; font-size: 14px; }
        .reset-body { padding: 30px; }
        
        /* Step Indicator */
        .step-indicator {
            display: flex;
            justify-content: space-between;
            margin-bottom: 25px;
            position: relative;
        }
        .step-indicator::before {
            content: '';
            position: absolute;
            top: 12px;
            left: 10%;
            right: 10%;
            height: 3px;
            background: #e0e0e0;
            z-index: 0;
        }
        .step-dot {
            width: 28px;
            height: 28px;
            border-radius: 50%;
            background: #e0e0e0;
            display: flex;
            align-items: center;
            justify-content: center;
            font-size: 12px;
            font-weight: bold;
            color: white;
            position: relative;
            z-index: 1;
            transition: all 0.3s;
        }
        .step-dot.active {
            background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
            transform: scale(1.1);
            box-shadow: 0 4px 10px rgba(102,126,234,0.4);
        }
        .step-dot.completed {
            background: #43e97b;
        }
        
        .form-control {
            border-radius: 12px;
            padding: 12px 15px;
            border: 2px solid #e0e0e0;
            font-size: 15px;
            transition: all 0.3s;
        }
        .form-control:focus {
            border-color: #667eea;
            box-shadow: 0 0 0 4px rgba(102,126,234,0.1);
            outline: none;
        }
        .btn-reset {
            background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
            border: none;
            padding: 13px;
            font-weight: 700;
            border-radius: 12px;
            color: white;
            width: 100%;
            transition: transform 0.2s;
        }
        .btn-reset:hover {
            transform: translateY(-2px);
            box-shadow: 0 8px 20px rgba(102,126,234,0.3);
        }
        .alert-custom {
            border-radius: 12px;
            padding: 12px 15px;
            margin-bottom: 20px;
            border: none;
        }
        .code-input {
            font-size: 24px !important;
            letter-spacing: 8px;
            text-align: center;
            font-weight: bold;
            padding: 15px !important;
        }
        .back-link {
            display: block;
            text-align: center;
            margin-top: 20px;
            color: #667eea;
            text-decoration: none;
            font-weight: 600;
            font-size: 14px;
        }
        .back-link:hover { text-decoration: underline; }
    </style>
</head>
<body>
    <div class="reset-container">
        <div class="reset-header">
            <i class="fas fa-shield-alt"></i>
            <h2>Lupa Password</h2>
            <p>Verifikasi identitas Anda untuk reset password</p>
        </div>
        
        <div class="reset-body">
            <!-- Step Indicator -->
            <div class="step-indicator">
                <div class="step-dot <?php echo $step >= 1 ? 'active' : ''; echo $step > 1 ? ' completed' : ''; ?>">1</div>
                <div class="step-dot <?php echo $step >= 2 ? 'active' : ''; echo $step > 2 ? ' completed' : ''; ?>">2</div>
                <div class="step-dot <?php echo $step >= 3 ? 'active' : ''; echo $step > 3 ? ' completed' : ''; ?>">3</div>
            </div>

            <!-- Pesan Error -->
            <?php if($error): ?>
            <div class="alert alert-danger alert-custom">
                <i class="fas fa-exclamation-circle me-2"></i><?php echo $error; ?>
            </div>
            <?php endif; ?>

            <!-- Pesan Sukses -->
            <?php if($success && $step < 4): ?>
            <div class="alert alert-success alert-custom">
                <i class="fas fa-check-circle me-2"></i><?php echo $success; ?>
            </div>
            <?php endif; ?>

            <!-- STEP 4: SELESAI -->
            <?php if($step == 4): ?>
            <div class="text-center py-3">
                <div style="width: 80px; height: 80px; background: #d4edda; border-radius: 50%; display: flex; align-items: center; justify-content: center; margin: 0 auto 20px; color: #155724; font-size: 35px;">
                    <i class="fas fa-check"></i>
                </div>
                <h4 class="text-success mb-3">Password Berhasil Diubah!</h4>
                <p class="text-muted mb-4">Silakan login dengan password baru Anda.</p>
                <a href="login.php" class="btn btn-reset">
                    <i class="fas fa-sign-in-alt me-2"></i>Kembali ke Login
                </a>
            </div>

            <!-- STEP 3: INPUT PASSWORD BARU -->
            <?php elseif($step == 3): ?>
            <form method="POST">
                <div class="mb-3">
                    <label class="form-label fw-bold">
                        <i class="fas fa-lock me-2 text-primary"></i>Password Baru
                    </label>
                    <input type="password" name="password_baru" class="form-control" required 
                           placeholder="Minimal 5 karakter">
                </div>
                <div class="mb-4">
                    <label class="form-label fw-bold">
                        <i class="fas fa-lock me-2 text-primary"></i>Konfirmasi Password Baru
                    </label>
                    <input type="password" name="konfirmasi_password" class="form-control" required 
                           placeholder="Ulangi password baru">
                </div>
                <button type="submit" class="btn btn-reset">
                    <i class="fas fa-save me-2"></i>Simpan Password Baru
                </button>
            </form>
            <a href="?step=2" class="back-link"><i class="fas fa-arrow-left me-1"></i>Kembali</a>

            <!-- STEP 2: INPUT KODE VERIFIKASI -->
            <?php elseif($step == 2): ?>
            <form method="POST">
                <div class="mb-4">
                    <label class="form-label fw-bold text-center d-block">
                        <i class="fas fa-key me-2 text-primary"></i>Masukkan Kode Verifikasi
                    </label>
                    <input type="text" name="verify_code" class="form-control code-input" required 
                           placeholder="000000" maxlength="6" pattern="\d{6}" 
                           style="letter-spacing: 12px; font-size: 28px;">
                    <small class="text-muted d-block text-center mt-2">
                        Masukkan 6 angka yang dikirim ke email Anda
                    </small>
                </div>
                <button type="submit" class="btn btn-reset">
                    <i class="fas fa-check me-2"></i>Verifikasi & Lanjutkan
                </button>
            </form>
            <a href="?step=1" class="back-link"><i class="fas fa-arrow-left me-1"></i>Ganti Email</a>

            <!-- STEP 1: INPUT EMAIL -->
            <?php else: ?>
            <form method="POST">
                <div class="mb-4">
                    <label class="form-label fw-bold">
                        <i class="fas fa-envelope me-2 text-primary"></i>Email Terdaftar
                    </label>
                    <input type="email" name="email" class="form-control" required 
                           placeholder="nama@email.com" value="<?php echo htmlspecialchars($email); ?>">
                    <small class="text-muted">Email yang digunakan saat mendaftar akun</small>
                </div>
                <button type="submit" class="btn btn-reset">
                    <i class="fas fa-paper-plane me-2"></i>Kirim Kode Verifikasi
                </button>
            </form>
            <a href="login.php" class="back-link"><i class="fas fa-arrow-left me-1"></i>Kembali ke Login</a>
            <?php endif; ?>
        </div>
    </div>
    
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
    <script>
        // Auto focus ke input kode jika di step 2
        <?php if($step == 2): ?>
        document.querySelector('input[name="verify_code"]').focus();
        <?php endif; ?>
        
        // Hanya izinkan angka di input kode
        document.querySelector('input[name="verify_code"]')?.addEventListener('input', function(e) {
            this.value = this.value.replace(/[^0-9]/g, '').slice(0, 6);
        });
    </script>
</body>
</html>