<?php
session_start();
require_once 'config/database.php';
require_once 'includes/functions.php';

$error = '';
$success = '';
$password_tampil = '';

if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    $nama_lengkap = sanitize($_POST['nama_lengkap']);
    $email = sanitize($_POST['email']);
    $no_hp = sanitize($_POST['no_hp']);
    $username = sanitize($_POST['username']);
    $password = $_POST['password'];
    $konfirmasi_password = $_POST['konfirmasi_password'];
    $role = $_POST['role'] ?? 'ibu_anak';
    
    // Validasi
    if (empty($nama_lengkap) || empty($email) || empty($username) || empty($password)) {
        $error = "Semua field wajib diisi!";
    } elseif (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
        $error = "Format email tidak valid!";
    } elseif (strlen($password) < 8) {
        $error = "Password minimal 8 karakter!";
    } elseif ($password !== $konfirmasi_password) {
        $error = "Konfirmasi password tidak cocok!";
    } else {
        try {
            $db = (new Database())->getConnection();
            
            // Cek apakah email/username sudah ada
            $check = $db->prepare("SELECT id FROM users WHERE email = ? OR username = ?");
            $check->execute([$email, $username]);
            
            if ($check->rowCount() > 0) {
                $error = "Email atau username sudah terdaftar!";
            } else {
                // Hash password
                $password_hash = password_hash($password, PASSWORD_DEFAULT);
                
                // Insert user
                $query = "INSERT INTO users (username, password, role, nama_lengkap, email, no_hp, status, created_at) 
                         VALUES (?, ?, ?, ?, ?, ?, 'active', NOW())";
                $stmt = $db->prepare($query);
                $stmt->execute([$username, $password_hash, $role, $nama_lengkap, $email, $no_hp]);
                
                // SIMPAN PASSWORD UNTUK DITAMPILKAN
                $password_tampil = $password;
                $success = true;
                
                $success_message = "
                <div class='alert alert-success alert-dismissible fade show' role='alert'>
                    <h5 class='mb-3'><i class='fas fa-check-circle me-2'></i>Registrasi Berhasil!</h5>
                    <p class='mb-3'>Akun Anda telah dibuat. Silakan simpan informasi login berikut:</p>
                    
                    <div class='card bg-light border-0 mb-3'>
                        <div class='card-body'>
                            <div class='row g-3'>
                                <div class='col-md-6'>
                                    <strong>📧 Email:</strong><br>
                                    <span class='text-primary'>$email</span>
                                </div>
                                <div class='col-md-6'>
                                    <strong>👤 Username:</strong><br>
                                    <span class='text-primary'>$username</span>
                                </div>
                                <div class='col-12'>
                                    <strong>🔐 Password:</strong><br>
                                    <div class='input-group'>
                                        <input type='text' class='form-control font-monospace' 
                                               value='$password' id='passwordTampil' readonly 
                                               style='background: #fff3cd; font-weight: bold; color: #856404;'>
                                        <button class='btn btn-outline-primary' type='button' onclick='copyPassword()'>
                                            <i class='fas fa-copy me-1'></i> Copy
                                        </button>
                                    </div>
                                    <small class='text-muted'>
                                        <i class='fas fa-exclamation-triangle me-1'></i>
                                        <strong>PENTING:</strong> Salin dan simpan password ini di tempat aman! 
                                        Password tidak akan ditampilkan lagi.
                                    </small>
                                </div>
                            </div>
                        </div>
                    </div>
                    
                    <div class='d-flex gap-2'>
                        <a href='login.php' class='btn btn-primary flex-fill'>
                            <i class='fas fa-sign-in-alt me-2'></i> Login Sekarang
                        </a>
                        <a href='register.php' class='btn btn-outline-secondary flex-fill'>
                            <i class='fas fa-redo me-2'></i> Daftar Lagi
                        </a>
                    </div>
                    
                    <button type='button' class='btn-close' data-bs-dismiss='alert'></button>
                </div>
                ";
            }
        } catch (PDOException $e) {
            $error = "Terjadi kesalahan: " . $e->getMessage();
        }
    }
}
?>
<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Daftar Akun - Hai Child</title>
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
        .register-card {
            background: white;
            border-radius: 20px;
            box-shadow: 0 20px 60px rgba(0,0,0,0.3);
            max-width: 600px;
            width: 100%;
        }
        .password-requirements {
            background: #fff3cd;
            border-left: 4px solid #ffc107;
            padding: 12px 15px;
            border-radius: 8px;
            margin: 15px 0;
        }
        .password-example {
            background: white;
            border: 2px dashed #ffc107;
            padding: 8px 12px;
            border-radius: 6px;
            font-family: monospace;
            font-weight: bold;
            color: #dc3545;
            display: inline-block;
            margin-top: 5px;
        }
    </style>
</head>
<body>
    <div class="register-card p-4 p-md-5">
        <div class="text-center mb-4">
            <h2 class="fw-bold text-primary"><i class="fas fa-user-plus me-2"></i>Daftar Akun</h2>
            <p class="text-muted">Bergabung dengan Hai Child untuk monitoring kesehatan balita</p>
        </div>

        <?php if ($error): ?>
        <div class="alert alert-danger alert-dismissible fade show">
            <i class="fas fa-exclamation-circle me-2"></i><?php echo $error; ?>
            <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
        </div>
        <?php endif; ?>

        <?php if ($success && isset($success_message)): ?>
            <?php echo $success_message; ?>
        <?php else: ?>
        
        <form method="POST">
            <div class="mb-3">
                <label class="form-label fw-bold">Nama Lengkap <span class="text-danger">*</span></label>
                <input type="text" name="nama_lengkap" class="form-control" required 
                       placeholder="Masukkan nama lengkap" value="<?php echo $_POST['nama_lengkap'] ?? ''; ?>">
            </div>

            <div class="mb-3">
                <label class="form-label fw-bold">Email <span class="text-danger">*</span></label>
                <input type="email" name="email" class="form-control" required 
                       placeholder="email@example.com" value="<?php echo $_POST['email'] ?? ''; ?>">
            </div>

            <div class="mb-3">
                <label class="form-label fw-bold">No. HP</label>
                <input type="text" name="no_hp" class="form-control" 
                       placeholder="081234567890" value="<?php echo $_POST['no_hp'] ?? ''; ?>">
            </div>

            <div class="mb-3">
                <label class="form-label fw-bold">Username <span class="text-danger">*</span></label>
                <input type="text" name="username" class="form-control" required 
                       placeholder="Username untuk login" value="<?php echo $_POST['username'] ?? ''; ?>">
            </div>

            <div class="mb-3">
                <label class="form-label fw-bold">Password <span class="text-danger">*</span></label>
                <input type="password" name="password" id="password" class="form-control" required 
                       placeholder="Minimal 8 karakter">
                
                <!-- CATATAN PASSWORD -->
                <div class="password-requirements mt-2">
                    <small class="text-dark">
                        <i class="fas fa-shield-alt me-1"></i>
                        <strong>Password harus:</strong> Minimal 8 karakter, kombinasi huruf besar, kecil, angka, dan simbol.<br>
                        <strong>Contoh:</strong> <span class="password-example">Banjarmasin123.!</span>
                    </small>
                </div>
            </div>

            <div class="mb-3">
                <label class="form-label fw-bold">Konfirmasi Password <span class="text-danger">*</span></label>
                <input type="password" name="konfirmasi_password" class="form-control" required 
                       placeholder="Ulangi password">
            </div>

            <div class="mb-4">
                <label class="form-label fw-bold">Daftar Sebagai</label>
                <select name="role" class="form-select" required>
                    <option value="ibu_anak">Ibu Balita</option>
                    <option value="bidan">Bidan (Perlu verifikasi)</option>
                </select>
            </div>

            <div class="d-grid gap-2">
                <button type="submit" class="btn btn-primary btn-lg">
                    <i class="fas fa-user-plus me-2"></i>Daftar Sekarang
                </button>
                <a href="login.php" class="btn btn-outline-secondary">
                    <i class="fas fa-sign-in-alt me-2"></i>Sudah Punya Akun? Login
                </a>
            </div>
        </form>
        <?php endif; ?>
    </div>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
    <script>
        function copyPassword() {
            const passwordInput = document.getElementById('passwordTampil');
            passwordInput.select();
            document.execCommand('copy');
            
            // Visual feedback
            const btn = event.target.closest('button');
            const originalHTML = btn.innerHTML;
            btn.innerHTML = '<i class="fas fa-check me-1"></i> Tersalin!';
            btn.classList.remove('btn-outline-primary');
            btn.classList.add('btn-success');
            
            setTimeout(() => {
                btn.innerHTML = originalHTML;
                btn.classList.remove('btn-success');
                btn.classList.add('btn-outline-primary');
            }, 2000);
        }
    </script>
</body>
</html>