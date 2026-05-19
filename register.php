<?php
session_start();
require_once 'config/database.php';

if (isset($_SESSION['user_id'])) {
    if ($_SESSION['role'] == 'bidan') header("Location: bidan/dashboard.php");
    else header("Location: ibu/dashboard.php");
    exit();
}

$error = '';
$success = '';

if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    $nama = sanitize($_POST['nama_lengkap']);
    $contact_method = $_POST['contact_method']; // 'email' atau 'phone'
    $email = ($contact_method == 'email') ? sanitize($_POST['email']) : '';
    $no_hp = ($contact_method == 'phone') ? sanitize($_POST['no_hp']) : '';
    $password = $_POST['password'];
    
    // Validasi
    if (empty($nama)) {
        $error = "Nama lengkap harus diisi!";
    } elseif ($contact_method == 'email' && empty($email)) {
        $error = "Email harus diisi!";
    } elseif ($contact_method == 'phone' && empty($no_hp)) {
        $error = "Nomor telepon harus diisi!";
    } elseif (empty($password) || strlen($password) < 6) {
        $error = "Password minimal 6 karakter!";
    } else {
        $database = new Database();
        $db = $database->getConnection();
        
        // Cek duplikasi
        $check_field = ($contact_method == 'email') ? 'email' : 'no_hp';
        $check_value = ($contact_method == 'email') ? $email : $no_hp;
        
        $stmt = $db->prepare("SELECT id FROM users WHERE $check_field = ?");
        $stmt->execute([$check_value]);
        
        if ($stmt->rowCount() > 0) {
            $error = ($contact_method == 'email') ? "Email sudah terdaftar!" : "Nomor telepon sudah terdaftar!";
        } else {
            // Generate username unik
            $username = ($contact_method == 'email') ? 
                substr($email, 0, strpos($email, '@')) . rand(100,999) : 
                'user' . substr($no_hp, -6);
            
            $hashed_password = password_hash($password, PASSWORD_DEFAULT);
            
            $query = "INSERT INTO users (username, password, role, nama_lengkap, email, no_hp, status) 
                      VALUES (?, ?, 'ibu_anak', ?, ?, ?, 'active')";
            $stmt = $db->prepare($query);
            
            if ($stmt->execute([$username, $hashed_password, $nama, $email, $no_hp])) {
                $success = "✅ Pendaftaran berhasil! Silakan login dengan " . 
                          ($contact_method == 'email' ? "email" : "nomor telepon") . " Anda.";
            } else {
                $error = "Terjadi kesalahan saat mendaftar. Silakan coba lagi.";
            }
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
    <meta name="viewport" content="width=device-width, initial-scale=1.0, maximum-scale=1.0, user-scalable=no">
    <title>Daftar - Hai Child</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <link href="https://fonts.googleapis.com/css2?family=Quicksand:wght@400;500;600;700&display=swap" rel="stylesheet">
    <style>
        * { font-family: 'Quicksand', sans-serif; -webkit-tap-highlight-color: transparent; }
        body { background: linear-gradient(135deg, #667eea 0%, #764ba2 100%); min-height: 100vh; display: flex; align-items: center; justify-content: center; padding: 20px; }
        .register-card { background: white; border-radius: 24px; padding: 35px 30px; max-width: 450px; width: 100%; box-shadow: 0 20px 60px rgba(0,0,0,0.3); }
        .register-header { text-align: center; margin-bottom: 25px; }
        .register-header i { font-size: 50px; color: #667eea; margin-bottom: 10px; }
        .register-header h2 { font-weight: 800; color: #333; margin: 0; }
        .register-header p { color: #888; margin: 5px 0 0; font-size: 14px; }
        
        .form-label { font-weight: 700; color: #555; font-size: 14px; margin-bottom: 8px; display: flex; align-items: center; }
        .form-label i { color: #667eea; margin-right: 8px; font-size: 16px; }
        .form-control { border-radius: 14px; padding: 13px 16px; border: 2px solid #e0e0e0; font-size: 15px; transition: all 0.3s; background: #f8f9fa; }
        .form-control:focus { border-color: #667eea; box-shadow: 0 0 0 4px rgba(102,126,234,0.15); background: white; }
        
        .contact-toggle { display: flex; gap: 10px; margin-bottom: 15px; }
        .contact-option { flex: 1; }
        .contact-option input { display: none; }
        .contact-option label { display: block; padding: 12px; text-align: center; border: 2px solid #e0e0e0; border-radius: 12px; cursor: pointer; transition: all 0.3s; font-weight: 600; color: #666; font-size: 14px; }
        .contact-option input:checked + label { border-color: #667eea; background: rgba(102,126,234,0.1); color: #667eea; }
        .contact-option label i { margin-right: 5px; }
        
        .btn-register { background: linear-gradient(135deg, #667eea 0%, #764ba2 100%); border: none; padding: 14px; font-weight: 700; border-radius: 14px; color: white; width: 100%; margin-top: 10px; transition: all 0.3s; }
        .btn-register:hover { transform: translateY(-2px); box-shadow: 0 10px 25px rgba(102,126,234,0.4); }
        .alert-custom { border-radius: 12px; padding: 12px 15px; margin-bottom: 20px; border: none; font-size: 14px; }
        .back-link { display: block; text-align: center; margin-top: 20px; color: #667eea; text-decoration: none; font-weight: 600; font-size: 14px; }
        .back-link:hover { text-decoration: underline; }
        .form-section { display: none; }
        .form-section.active { display: block; }
    </style>
</head>
<body>
    <div class="register-card">
        <div class="register-header">
            <i class="fas fa-user-plus"></i>
            <h2>Daftar Akun</h2>
            <p>Bergabung untuk pantau kesehatan si kecil</p>
        </div>

        <?php if($error): ?>
        <div class="alert alert-danger alert-custom"><i class="fas fa-exclamation-circle me-2"></i><?php echo $error; ?></div>
        <?php endif; ?>
        <?php if($success): ?>
        <div class="alert alert-success alert-custom"><i class="fas fa-check-circle me-2"></i><?php echo $success; ?><br><a href="login.php" style="color:#155724; font-weight:700;">Klik disini untuk login</a></div>
        <?php endif; ?>

        <?php if(!$success): ?>
        <form method="POST">
            <div class="mb-3">
                <label class="form-label"><i class="fas fa-user"></i>Nama Lengkap</label>
                <input type="text" name="nama_lengkap" class="form-control" required placeholder="Nama lengkap Anda">
            </div>

            <div class="mb-3">
                <label class="form-label"><i class="fas fa-circle-question"></i>Pilih Metode Kontak</label>
                <div class="contact-toggle">
                    <div class="contact-option">
                        <input type="radio" name="contact_method" id="method_email" value="email" checked>
                        <label for="method_email"><i class="fas fa-envelope"></i>Email</label>
                    </div>
                    <div class="contact-option">
                        <input type="radio" name="contact_method" id="method_phone" value="phone">
                        <label for="method_phone"><i class="fas fa-phone"></i>Telepon</label>
                    </div>
                </div>
            </div>

            <div class="mb-3 form-section active" id="section_email">
                <label class="form-label"><i class="fas fa-envelope"></i>Email</label>
                <input type="email" name="email" id="input_email" class="form-control" placeholder="nama@email.com">
                <small class="text-muted">Email akan digunakan untuk login</small>
            </div>

            <div class="mb-3 form-section" id="section_phone">
                <label class="form-label"><i class="fas fa-phone"></i>Nomor Telepon</label>
                <input type="tel" name="no_hp" id="input_phone" class="form-control" placeholder="081234567890">
                <small class="text-muted">Nomor WhatsApp aktif</small>
            </div>

            <div class="mb-4">
                <label class="form-label"><i class="fas fa-lock"></i>Password</label>
                <input type="password" name="password" class="form-control" required placeholder="Minimal 6 karakter">
            </div>

            <button type="submit" class="btn btn-register"><i class="fas fa-user-plus me-2"></i>Daftar Sekarang</button>
        </form>
        <a href="login.php" class="back-link"><i class="fas fa-arrow-left me-1"></i>Sudah punya akun? Login disini</a>
        <?php endif; ?>
    </div>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
    <script>
    // Toggle Email/Phone Form
    document.querySelectorAll('input[name="contact_method"]').forEach(radio => {
        radio.addEventListener('change', function() {
            const emailSec = document.getElementById('section_email');
            const phoneSec = document.getElementById('section_phone');
            const emailInp = document.getElementById('input_email');
            const phoneInp = document.getElementById('input_phone');
            
            if (this.value === 'email') {
                emailSec.classList.add('active');
                phoneSec.classList.remove('active');
                emailInp.required = true;
                phoneInp.required = false;
                phoneInp.value = '';
            } else {
                emailSec.classList.remove('active');
                phoneSec.classList.add('active');
                emailInp.required = false;
                phoneInp.required = true;
                emailInp.value = '';
            }
        });
    });
    // Only numbers for phone
    document.getElementById('input_phone')?.addEventListener('input', function(e) {
        this.value = this.value.replace(/[^0-9]/g, '');
    });
    </script>
</body>
</html>