<?php
// Tampilkan semua error untuk debugging
error_reporting(E_ALL);
ini_set('display_errors', 1);

// Mulai session
if(session_status() === PHP_SESSION_NONE) {
    session_start();
}

// Cek login
if(!isset($_SESSION['user_id'])) {
    die("❌ <b>ERROR:</b> Anda belum login. <a href='../login.php'>Klik untuk login</a>");
}

// Koneksi database
try {
    $pdo = new PDO("mysql:host=localhost;dbname=hai_child_db", "root", "");
    $pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
} catch(PDOException $e) {
    die("❌ <b>ERROR DATABASE:</b> " . $e->getMessage());
}

// Ambil data user
try {
    $stmt = $pdo->prepare("SELECT * FROM users WHERE id = ?");
    $stmt->execute([$_SESSION['user_id']]);
    $user = $stmt->fetch();
    
    if(!$user) {
        die("❌ <b>ERROR:</b> User dengan ID " . $_SESSION['user_id'] . " tidak ditemukan di database.");
    }
} catch(PDOException $e) {
    die("❌ <b>ERROR QUERY:</b> " . $e->getMessage());
}

// Proses ubah password
if(isset($_POST['ubah_password'])) {
    $pass_lama = $_POST['password_lama'];
    $pass_baru = $_POST['password_baru'];
    $konfirmasi = $_POST['konfirmasi_password'];
    
    if(!password_verify($pass_lama, $user['password'])) {
        $msg = "<div style='background:#f8d7da; color:#721c24; padding:10px; margin:10px 0; border-radius:5px;'>❌ Password lama salah!</div>";
    } elseif($pass_baru !== $konfirmasi) {
        $msg = "<div style='background:#f8d7da; color:#721c24; padding:10px; margin:10px 0; border-radius:5px;'>❌ Password baru tidak cocok!</div>";
    } elseif(strlen($pass_baru) < 5) {
        $msg = "<div style='background:#f8d7da; color:#721c24; padding:10px; margin:10px 0; border-radius:5px;'>❌ Password minimal 5 karakter!</div>";
    } else {
        $hash_baru = password_hash($pass_baru, PASSWORD_DEFAULT);
        $update = $pdo->prepare("UPDATE users SET password = ? WHERE id = ?");
        $update->execute([$hash_baru, $user['id']]);
        $msg = "<div style='background:#d4edda; color:#155724; padding:10px; margin:10px 0; border-radius:5px;'>✅ Password berhasil diubah!</div>";
        
        // Refresh data
        $stmt = $pdo->prepare("SELECT * FROM users WHERE id = ?");
        $stmt->execute([$_SESSION['user_id']]);
        $user = $stmt->fetch();
    }
}
?>
<!DOCTYPE html>
<html>
<head>
    <title>Profil Bidan</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <style>
        body { 
            background: linear-gradient(135deg, #667eea 0%, #764ba2 100%); 
            padding: 40px 20px; 
            min-height: 100vh;
        }
        .card { 
            max-width: 600px; 
            margin: 0 auto; 
            border-radius: 15px; 
            box-shadow: 0 10px 30px rgba(0,0,0,0.3);
        }
        .card-header { 
            background: linear-gradient(135deg, #667eea 0%, #764ba2 100%); 
            color: white; 
            text-align: center;
            padding: 30px;
        }
        .avatar { 
            width: 80px; 
            height: 80px; 
            background: white; 
            border-radius: 50%; 
            display: inline-flex; 
            align-items: center; 
            justify-content: center; 
            font-size: 40px; 
            color: #667eea;
            margin-bottom: 15px;
        }
        .info-row { 
            display: flex; 
            padding: 10px; 
            border-bottom: 1px solid #f0f0f0; 
        }
        .info-label { 
            width: 100px; 
            font-weight: bold; 
            color: #666; 
        }
        .info-value { 
            flex: 1; 
            color: #333; 
        }
    </style>
</head>
<body>
    <div class="container">
        <?php if(isset($msg)) echo $msg; ?>
        
        <div class="card">
            <div class="card-header">
                <div class="avatar">
                    <i class="fas fa-user-nurse"></i>
                </div>
                <h3><?php echo htmlspecialchars($user['nama_lengkap']); ?></h3>
                <p class="mb-0"><?php echo htmlspecialchars($user['email']); ?></p>
                <span class="badge bg-white text-primary mt-2">Bidan</span>
            </div>
            
            <div class="card-body">
                <h5 class="mb-3"><i class="fas fa-id-card me-2"></i>Informasi Akun</h5>
                
                <div class="info-row">
                    <div class="info-label">Username</div>
                    <div class="info-value"><?php echo htmlspecialchars($user['username']); ?></div>
                </div>
                <div class="info-row">
                    <div class="info-label">Email</div>
                    <div class="info-value"><?php echo htmlspecialchars($user['email']); ?></div>
                </div>
                <div class="info-row">
                    <div class="info-label">No. HP</div>
                    <div class="info-value"><?php echo htmlspecialchars($user['no_hp'] ?? '-'); ?></div>
                </div>
                
                <hr class="my-4">
                
                <h5 class="mb-3"><i class="fas fa-lock me-2"></i>Ubah Password</h5>
                <form method="POST">
                    <div class="mb-3">
                        <label class="form-label">Password Lama</label>
                        <input type="password" name="password_lama" class="form-control" required>
                    </div>
                    <div class="mb-3">
                        <label class="form-label">Password Baru</label>
                        <input type="password" name="password_baru" class="form-control" required>
                        <small class="text-muted">Minimal 5 karakter</small>
                    </div>
                    <div class="mb-3">
                        <label class="form-label">Konfirmasi Password Baru</label>
                        <input type="password" name="konfirmasi_password" class="form-control" required>
                    </div>
                    <button type="submit" name="ubah_password" class="btn btn-primary w-100">
                        <i class="fas fa-save me-2"></i>Simpan Password
                    </button>
                </form>
                
                <hr class="my-4">
                
                <a href="dashboard.php" class="btn btn-outline-secondary w-100 mb-2">
                    <i class="fas fa-arrow-left me-2"></i>Kembali ke Dashboard
                </a>
                <a href="../logout.php" class="btn btn-danger w-100">
                    <i class="fas fa-sign-out-alt me-2"></i>Logout
                </a>
            </div>
        </div>
    </div>
    
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>