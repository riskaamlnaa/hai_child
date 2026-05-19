<?php
// Mulai Session
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

require_once 'includes/functions.php';
require_once 'config/database.php';

// Redirect jika sudah login
if (isLoggedIn()) {
    redirect($_SESSION['role'] == 'bidan' ? 'bidan/dashboard.php' : 'ibu/dashboard.php');
}

$error = '';

if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    $input = sanitize($_POST['username']);
    $password = $_POST['password'];
    
    try {
        $database = new Database();
        $db = $database->getConnection();
        
        $isEmail = filter_var($input, FILTER_VALIDATE_EMAIL);
        $column = $isEmail ? 'email' : 'username';
        
        $sql = "SELECT * FROM users WHERE $column = ? AND status = 'active'";
        $stmt = $db->prepare($sql);
        $stmt->execute([$input]);
        $user = $stmt->fetch();

        if ($user && password_verify($password, $user['password'])) {
            $_SESSION['user_id'] = $user['id'];
            $_SESSION['nama'] = $user['nama_lengkap'];
            $_SESSION['role'] = $user['role'];
            redirect($user['role'] == 'bidan' ? 'bidan/dashboard.php' : 'ibu/dashboard.php');
        } else {
            $error = "Username/Email atau password salah!";
        }
    } catch (PDOException $e) {
        $error = "Terjadi kesalahan sistem.";
    }
}
?>
<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Login - Hai Child</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <link href="https://fonts.googleapis.com/css2?family=Nunito:wght@400;600;700;800&display=swap" rel="stylesheet">
    
    <style>
        * { font-family: 'Nunito', sans-serif; box-sizing: border-box; }
        
        body {
            background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
            min-height: 100vh;
            display: flex;
            align-items: center;
            justify-content: center;
            padding: 20px;
            position: relative;
            overflow: hidden;
        }

        /* Background Bubbles */
        .bg-circle {
            position: fixed;
            border-radius: 50%;
            opacity: 0.12;
            animation: float 7s ease-in-out infinite;
        }
        .c1 { width: 150px; height: 150px; background: #ffd93d; top: 10%; left: 8%; animation-delay: 0s; }
        .c2 { width: 100px; height: 100px; background: #ff6b6b; top: 65%; right: 8%; animation-delay: 1s; }
        .c3 { width: 180px; height: 180px; background: #6bcf7f; bottom: 5%; left: 5%; animation-delay: 2s; }
        .c4 { width: 80px; height: 80px; background: #4ecdc4; top: 25%; right: 12%; animation-delay: 3s; }
        
        @keyframes float {
            0%, 100% { transform: translateY(0px); }
            50% { transform: translateY(-25px); }
        }

        .login-wrapper {
            width: 100%;
            max-width: 420px;
            position: relative;
            z-index: 10;
            animation: slideUp 0.7s ease;
        }
        
        @keyframes slideUp {
            from { opacity: 0; transform: translateY(50px); }
            to { opacity: 1; transform: translateY(0); }
        }

        /* =========================================
           LOGO SECTION DENGAN ANIMASI BERPUTAR
           ========================================= */
        .logo-container {
            position: relative;
            width: 220px;
            height: 220px;
            margin: 0 auto 25px;
            display: flex;
            align-items: center;
            justify-content: center;
        }

        /* Cincin 1 (Paling Luar) - Merah & Kuning */
        .ring {
            position: absolute;
            border-radius: 50%;
            border: 5px solid transparent;
        }
        
        .ring-1 {
            width: 100%;
            height: 100%;
            border-top-color: #ff6b6b;
            border-right-color: #ffd93d;
            animation: spinRight 4s linear infinite;
        }

        /* Cincin 2 (Tengah) - Hijau & Biru */
        .ring-2 {
            width: 85%;
            height: 85%;
            border-bottom-color: #6bcf7f;
            border-left-color: #4ecdc4;
            animation: spinLeft 3s linear infinite;
        }

        /* Cincin 3 (Dalam) - Pink & Oranye */
        .ring-3 {
            width: 70%;
            height: 70%;
            border-top-color: #fd79a8;
            border-right-color: #fdcb6e;
            animation: spinRight 5s linear infinite;
        }

        /* Animasi Putar Kanan */
        @keyframes spinRight {
            from { transform: rotate(0deg); }
            to { transform: rotate(360deg); }
        }

        /* Animasi Putar Kiri */
        @keyframes spinLeft {
            from { transform: rotate(0deg); }
            to { transform: rotate(-360deg); }
        }

        /* Gambar Logo (Di Tengah Cincin) */
        .logo-img {
            width: 160px;
            height: 160px;
            border-radius: 50%;
            overflow: hidden;
            box-shadow: 0 10px 30px rgba(0,0,0,0.3);
            border: 5px solid white;
            z-index: 5; /* Pastikan gambar di atas cincin */
            background: white;
        }

        .logo-img img {
            width: 100%;
            height: 100%;
            object-fit: cover;
        }

        /* Icon Melayang (Floating Icons) */
        .float-icon {
            position: absolute;
            font-size: 24px;
            animation: floatIcon 3.5s ease-in-out infinite;
            filter: drop-shadow(0 3px 5px rgba(0,0,0,0.2));
        }
        .fi-1 { top: -5px; right: 10px; color: #ff6b6b; animation-delay: 0s; }
        .fi-2 { bottom: 15px; left: 5px; color: #6bcf7f; animation-delay: 0.6s; }
        .fi-3 { top: 45%; right: -15px; color: #ffd93d; animation-delay: 1.2s; }
        .fi-4 { bottom: -5px; right: 25px; color: #4ecdc4; animation-delay: 1.8s; }
        
        @keyframes floatIcon {
            0%, 100% { transform: translateY(0) scale(1); }
            50% { transform: translateY(-12px) scale(1.15); }
        }

        /* Text Styles */
        .app-title { text-align: center; margin-bottom: 8px; }
        .app-title h1 {
            color: white;
            font-size: 36px;
            font-weight: 800;
            margin: 0;
            text-shadow: 0 3px 15px rgba(0,0,0,0.3);
        }
        .app-subtitle {
            color: rgba(255,255,255,0.95);
            font-size: 14px;
            text-align: center;
            margin: 0 0 25px 0;
            font-weight: 600;
        }

        /* Login Card */
        .login-card {
            background: white;
            border-radius: 28px;
            padding: 32px 28px;
            box-shadow: 0 25px 70px rgba(0,0,0,0.25);
            position: relative;
            overflow: hidden;
        }
        
        /* Gradient Top Line */
        .login-card::before {
            content: '';
            position: absolute;
            top: 0; left: 0; right: 0;
            height: 5px;
            background: linear-gradient(90deg, #ff6b6b, #ffd93d, #6bcf7f, #4ecdc4, #667eea);
            background-size: 300% 300%;
            animation: gradient 5s ease infinite;
        }
        @keyframes gradient {
            0%, 100% { background-position: 0% 50%; }
            50% { background-position: 100% 50%; }
        }

        /* Form Styles */
        .form-label { font-weight: 700; color: #555; font-size: 14px; margin-bottom: 8px; display: flex; align-items: center; }
        .form-label i { color: #667eea; margin-right: 10px; font-size: 18px; }
        
        .form-control {
            border-radius: 14px;
            padding: 13px 16px;
            border: 2px solid #e9ecef;
            font-size: 14px;
            transition: all 0.3s;
            background: #f8f9fa;
        }
        .form-control:focus {
            border-color: #667eea;
            box-shadow: 0 0 0 4px rgba(102,126,234,0.12);
            background: white;
            outline: none;
        }
        
        .form-check-input { width: 18px; height: 18px; cursor: pointer; }
        .form-check-label { font-size: 13px; color: #666; cursor: pointer; }
        
        .forgot-link { color: #667eea; text-decoration: none; font-size: 13px; font-weight: 700; }
        .forgot-link:hover { color: #764ba2; }
        
        .btn-login {
            background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
            border: none;
            padding: 14px;
            font-weight: 800;
            font-size: 16px;
            border-radius: 14px;
            transition: all 0.3s;
            box-shadow: 0 10px 25px rgba(102,126,234,0.4);
            margin-top: 15px;
            width: 100%;
            color: white;
            cursor: pointer;
        }
        .btn-login:hover {
            transform: translateY(-3px);
            box-shadow: 0 15px 35px rgba(102,126,234,0.5);
            color: white;
        }
        
        .alert-custom { border-radius: 12px; padding: 12px 16px; font-size: 14px; margin-bottom: 18px; border: none; font-weight: 600; }
        
        .back-link { display: block; text-align: center; margin-top: 18px; color: rgba(255,255,255,0.9); text-decoration: none; font-size: 14px; font-weight: 700; }
        .back-link:hover { color: white; text-decoration: underline; }
    </style>
</head>
<body>
    <!-- Background Bubbles -->
    <div class="bg-circle c1"></div>
    <div class="bg-circle c2"></div>
    <div class="bg-circle c3"></div>
    <div class="bg-circle c4"></div>

    <div class="login-wrapper">
        
        <!-- LOGO SECTION DENGAN ANIMASI CINCIN -->
        <div class="logo-container">
            <!-- Cincin-cincin berputar -->
            <div class="ring ring-1"></div>
            <div class="ring ring-2"></div>
            <div class="ring ring-3"></div>
            
            <!-- Gambar Logo (Sesuai nama file baru) -->
            <div class="logo-img">
                <img src="assets/img/hai-child-illustration.png" alt="Hai Child">
            </div>
            
            <!-- Icon Melayang -->
            <i class="fas fa-syringe float-icon fi-1"></i>
            <i class="fas fa-apple-alt float-icon fi-2"></i>
            <i class="fas fa-star float-icon fi-3"></i>
            <i class="fas fa-weight float-icon fi-4"></i>
        </div>

        <div class="app-title">
            <h1>Hai Child</h1>
        </div>
        <p class="app-subtitle">🌟 Pantau kesehatan si kecil dengan mudah 🌟</p>

        <div class="login-card">
            <?php if($error): ?>
            <div class="alert alert-danger alert-custom">
                <i class="fas fa-exclamation-triangle me-2"></i><?= htmlspecialchars($error) ?>
            </div>
            <?php endif; ?>
            
            <form method="POST" autocomplete="off">
                <div class="mb-3">
                    <label class="form-label"><i class="fas fa-user-circle"></i>Username atau Email</label>
                    <input type="text" class="form-control" name="username" required autocomplete="off" placeholder="Masukkan username/email">
                </div>
                <div class="mb-3">
                    <label class="form-label"><i class="fas fa-lock"></i>Password</label>
                    <input type="password" class="form-control" name="password" id="password_field" required autocomplete="new-password" placeholder="••••••••">
                </div>
                <div class="d-flex justify-content-between align-items-center mb-4">
                    <div class="form-check">
                        <input type="checkbox" class="form-check-input" id="showPass">
                        <label class="form-check-label" for="showPass">Tampilkan</label>
                    </div>
                    <a href="lupa_password.php" class="forgot-link">Lupa Password?</a>
                </div>
                <button type="submit" class="btn btn-login">
                    <i class="fas fa-sign-in-alt me-2"></i>Masuk Sekarang
                </button>
            </form>
            <a href="index.php" class="back-link"><i class="fas fa-home me-1"></i>Kembali ke Beranda</a>
        </div>
    </div>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
    <script>
        document.getElementById('showPass')?.addEventListener('change', function() {
            const pwd = document.getElementById('password_field');
            if(pwd) pwd.type = this.checked ? 'text' : 'password';
        });
    </script>
</body>
</html>