<?php
session_start();

// Redirect jika sudah login
if (isset($_SESSION['user_id'])) {
    if ($_SESSION['role'] == 'bidan') {
        header("Location: bidan/dashboard.php");
    } else {
        header("Location: ibu/dashboard.php");
    }
    exit();
}
?>
<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Hai Child - Monitoring Kesehatan Balita</title>
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
            font-family: 'Segoe UI', Tahoma, Geneva, Verdana, sans-serif;
        }
        .main-card {
            background: white;
            border-radius: 30px;
            padding: 50px 40px;
            max-width: 900px;
            width: 100%;
            box-shadow: 0 20px 60px rgba(0,0,0,0.3);
        }
        .header {
            text-align: center;
            margin-bottom: 40px;
        }
        .logo {
            width: 100px;
            height: 100px;
            background: linear-gradient(135deg, #667eea, #764ba2);
            border-radius: 25px;
            display: inline-flex;
            align-items: center;
            justify-content: center;
            font-size: 50px;
            color: white;
            margin-bottom: 20px;
        }
        .header h1 {
            color: #667eea;
            margin: 10px 0;
            font-weight: 700;
        }
        .header p {
            color: #666;
            margin: 0;
        }
        .features {
            display: grid;
            grid-template-columns: repeat(auto-fit, minmax(250px, 1fr));
            gap: 25px;
            margin-bottom: 40px;
        }
        .feature-card {
            background: #f8f9fa;
            padding: 30px 25px;
            border-radius: 20px;
            text-align: center;
            transition: transform 0.3s;
        }
        .feature-card:hover {
            transform: translateY(-5px);
        }
        .feature-icon {
            width: 70px;
            height: 70px;
            border-radius: 15px;
            display: inline-flex;
            align-items: center;
            justify-content: center;
            font-size: 32px;
            margin-bottom: 15px;
        }
        .feature-card:nth-child(1) .feature-icon {
            background: linear-gradient(135deg, #ff6b9d, #ff8fa3);
            color: white;
        }
        .feature-card:nth-child(2) .feature-icon {
            background: linear-gradient(135deg, #4facfe, #00f2fe);
            color: white;
        }
        .feature-card:nth-child(3) .feature-icon {
            background: linear-gradient(135deg, #43e97b, #38f9d7);
            color: white;
        }
        .feature-card h3 {
            margin: 10px 0;
            color: #333;
            font-size: 18px;
        }
        .feature-card p {
            color: #666;
            font-size: 14px;
            margin: 0;
        }
        .buttons {
            text-align: center;
            margin-bottom: 30px;
        }
        .btn-custom {
            padding: 15px 40px;
            border-radius: 50px;
            font-weight: 600;
            margin: 10px;
            text-decoration: none;
            display: inline-block;
            transition: all 0.3s;
        }
        .btn-primary {
            background: linear-gradient(135deg, #667eea, #764ba2);
            color: white;
            border: none;
        }
        .btn-primary:hover {
            transform: translateY(-3px);
            box-shadow: 0 10px 25px rgba(102,126,234,0.4);
            color: white;
        }
        .btn-outline {
            background: white;
            color: #667eea;
            border: 2px solid #667eea;
        }
        .btn-outline:hover {
            background: #667eea;
            color: white;
            transform: translateY(-3px);
        }
        .footer {
            text-align: center;
            color: #999;
            font-size: 13px;
            padding-top: 20px;
            border-top: 1px solid #eee;
        }
        @media (max-width: 768px) {
            .main-card { padding: 30px 20px; }
            .features { grid-template-columns: 1fr; }
        }
    </style>
</head>
<body>
    <div class="main-card">
        <div class="header">
            <div class="logo">
                <i class="fas fa-baby-carriage"></i>
            </div>
            <h1>Hai Child</h1>
            <p>Solusi Cerdas Monitoring Kesehatan Balita</p>
        </div>
        
        <div class="features">
            <div class="feature-card">
                <div class="feature-icon">
                    <i class="fas fa-syringe"></i>
                </div>
                <h3>Monitoring Imunisasi</h3>
                <p>Pantau jadwal dan riwayat imunisasi si kecil dengan sistem pengingat</p>
            </div>
            
            <div class="feature-card">
                <div class="feature-icon">
                    <i class="fas fa-weight"></i>
                </div>
                <h3>Status Gizi</h3>
                <p>Pantau pertumbuhan dan status gizi balita secara berkala</p>
            </div>
            
            <div class="feature-card">
                <div class="feature-icon">
                    <i class="fas fa-book"></i>
                </div>
                <h3>Edukasi</h3>
                <p>Informasi kesehatan anak dan tips pola asuh yang tepat</p>
            </div>
        </div>
        
        <div class="buttons">
            <a href="login.php" class="btn-custom btn-primary">
                <i class="fas fa-sign-in-alt me-2"></i>Login Sekarang
            </a>
            <a href="register.php" class="btn-custom btn-outline">
                <i class="fas fa-user-plus me-2"></i>Daftar Akun
            </a>
        </div>
        
        <div class="footer">
            <small>&copy; <?php echo date('Y'); ?> Hai Child - Sistem Monitoring Kesehatan Balita</small>
        </div>
    </div>
    
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>