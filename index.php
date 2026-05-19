<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Hai Child - Monitoring Imunisasi & Gizi Balita</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <link href="https://fonts.googleapis.com/css2?family=Nunito:wght@400;600;700;800;900&display=swap" rel="stylesheet">
    <style>
        * {
            font-family: 'Nunito', sans-serif;
            margin: 0;
            padding: 0;
            box-sizing: border-box;
        }

        body {
            background: linear-gradient(135deg, #ff6b9d 0%, #c44dff 50%, #4facfe 100%);
            min-height: 100vh;
            overflow-x: hidden;
            position: relative;
        }

        /* BUBBLE ANIMATIONS */
        .bubble {
            position: fixed;
            border-radius: 50%;
            background: rgba(255, 255, 255, 0.1);
            animation: rise 15s infinite ease-in;
            z-index: 0;
        }

        .bubble-1 { width: 80px; height: 80px; left: 10%; bottom: -100px; animation-delay: 0s; }
        .bubble-2 { width: 120px; height: 120px; left: 30%; bottom: -150px; animation-delay: 3s; }
        .bubble-3 { width: 60px; height: 60px; left: 50%; bottom: -80px; animation-delay: 6s; }
        .bubble-4 { width: 100px; height: 100px; left: 70%; bottom: -120px; animation-delay: 9s; }
        .bubble-5 { width: 70px; height: 70px; left: 90%; bottom: -90px; animation-delay: 12s; }

        @keyframes rise {
            0% { bottom: -150px; transform: translateX(0) scale(1); opacity: 0; }
            50% { opacity: 0.8; }
            100% { bottom: 120vh; transform: translateX(100px) scale(1.5); opacity: 0; }
        }

        /* MAIN CONTAINER */
        .container-main {
            position: relative;
            z-index: 1;
            min-height: 100vh;
            display: flex;
            align-items: center;
            justify-content: center;
            padding: 30px 20px;
        }

        /* CARD DESIGN */
        .main-card {
            background: white;
            border-radius: 40px;
            box-shadow: 0 30px 80px rgba(0, 0, 0, 0.25);
            max-width: 1000px;
            width: 100%;
            overflow: hidden;
            animation: slideIn 1s ease;
        }

        @keyframes slideIn {
            from { opacity: 0; transform: translateY(50px) scale(0.9); }
            to { opacity: 1; transform: translateY(0) scale(1); }
        }

        /* TOP SECTION */
        .top-section {
            background: linear-gradient(135deg, #ff6b9d 0%, #c44dff 100%);
            padding: 50px 40px;
            text-align: center;
            color: white;
            position: relative;
            overflow: hidden;
        }

        .top-section::before {
            content: '🌟 💫 🌟 💫';
            position: absolute;
            top: 20px;
            left: 0;
            right: 0;
            font-size: 24px;
            opacity: 0.3;
            letter-spacing: 30px;
            animation: twinkle 3s ease-in-out infinite;
        }

        @keyframes twinkle {
            0%, 100% { opacity: 0.3; }
            50% { opacity: 0.6; }
        }

        .logo-box {
            width: 140px;
            height: 140px;
            background: white;
            border-radius: 30px;
            margin: 0 auto 25px;
            display: flex;
            align-items: center;
            justify-content: center;
            box-shadow: 0 10px 30px rgba(0, 0, 0, 0.2);
            animation: pulse 2s ease-in-out infinite;
            transform: rotate(-5deg);
        }

        @keyframes pulse {
            0%, 100% { transform: rotate(-5deg) scale(1); }
            50% { transform: rotate(5deg) scale(1.05); }
        }

        .logo-box i {
            font-size: 70px;
            background: linear-gradient(135deg, #ff6b9d, #c44dff);
            -webkit-background-clip: text;
            -webkit-text-fill-color: transparent;
        }

        .top-section h1 {
            font-size: 48px;
            font-weight: 900;
            margin: 0 0 10px;
            text-shadow: 0 4px 15px rgba(0, 0, 0, 0.2);
            letter-spacing: 2px;
        }

        .top-section .subtitle {
            font-size: 18px;
            opacity: 0.95;
            margin: 0;
            font-weight: 600;
        }

        /* FEATURES */
        .features-wrapper {
            padding: 40px;
            background: #f8f9fa;
        }

        .features-container {
            display: grid;
            grid-template-columns: repeat(3, 1fr);
            gap: 25px;
            margin-bottom: 35px;
        }

        .feature-item {
            background: white;
            border-radius: 25px;
            padding: 30px 20px;
            text-align: center;
            box-shadow: 0 8px 25px rgba(0, 0, 0, 0.08);
            transition: all 0.3s;
            position: relative;
            overflow: hidden;
        }

        .feature-item::after {
            content: '';
            position: absolute;
            bottom: 0;
            left: 0;
            right: 0;
            height: 5px;
            background: linear-gradient(90deg, #ff6b9d, #c44dff, #4facfe);
            transform: scaleX(0);
            transition: transform 0.3s;
        }

        .feature-item:hover {
            transform: translateY(-12px) rotate(2deg);
            box-shadow: 0 15px 40px rgba(0, 0, 0, 0.15);
        }

        .feature-item:hover::after {
            transform: scaleX(1);
        }

        .feature-item:nth-child(1):hover { background: linear-gradient(135deg, #fff5f9, #ffffff); }
        .feature-item:nth-child(2):hover { background: linear-gradient(135deg, #f0f8ff, #ffffff); }
        .feature-item:nth-child(3):hover { background: linear-gradient(135deg, #f0fff8, #ffffff); }

        .icon-box {
            width: 75px;
            height: 75px;
            margin: 0 auto 18px;
            border-radius: 22px;
            display: flex;
            align-items: center;
            justify-content: center;
            font-size: 35px;
            color: white;
            box-shadow: 0 6px 20px rgba(0, 0, 0, 0.15);
        }

        .feature-item:nth-child(1) .icon-box { background: linear-gradient(135deg, #ff6b9d, #ff9a9e); }
        .feature-item:nth-child(2) .icon-box { background: linear-gradient(135deg, #4facfe, #00f2fe); }
        .feature-item:nth-child(3) .icon-box { background: linear-gradient(135deg, #43e97b, #38f9d7); }

        .feature-item h3 {
            font-size: 20px;
            font-weight: 800;
            color: #333;
            margin: 0 0 10px;
        }

        .feature-item p {
            font-size: 14px;
            color: #888;
            line-height: 1.6;
            margin: 0;
        }

        /* ACTION SECTION */
        .action-wrapper {
            padding: 0 40px 40px;
            background: white;
            text-align: center;
        }

        .button-group {
            display: flex;
            gap: 20px;
            justify-content: center;
            margin-bottom: 25px;
            flex-wrap: wrap;
        }

        .btn-action {
            padding: 18px 45px;
            border-radius: 50px;
            font-weight: 800;
            font-size: 16px;
            text-decoration: none;
            transition: all 0.3s;
            display: inline-flex;
            align-items: center;
            gap: 12px;
            border: none;
            cursor: pointer;
            box-shadow: 0 8px 25px rgba(0, 0, 0, 0.2);
        }

        .btn-login {
            background: linear-gradient(135deg, #ff6b9d 0%, #c44dff 100%);
            color: white;
        }

        .btn-login:hover {
            transform: translateY(-5px) scale(1.05);
            box-shadow: 0 15px 40px rgba(255, 107, 157, 0.4);
            color: white;
        }

        .btn-register {
            background: white;
            color: #c44dff;
            border: 3px solid #c44dff;
        }

        .btn-register:hover {
            background: #c44dff;
            color: white;
            transform: translateY(-5px) scale(1.05);
            box-shadow: 0 15px 40px rgba(196, 77, 255, 0.3);
        }

        .info-tag {
            background: linear-gradient(135deg, #fff5f9, #f8f0ff);
            padding: 18px 30px;
            border-radius: 20px;
            display: inline-block;
            font-size: 14px;
            color: #666;
            border: 3px dashed #ff6b9d;
        }

        .info-tag i {
            color: #ff6b9d;
            margin-right: 8px;
            font-size: 18px;
        }

        .info-tag strong {
            color: #c44dff;
            font-weight: 800;
        }

        /* DECORATIVE ELEMENTS */
        .floating-emoji {
            position: absolute;
            font-size: 40px;
            animation: floatEmoji 4s ease-in-out infinite;
            opacity: 0.6;
        }

        @keyframes floatEmoji {
            0%, 100% { transform: translateY(0) rotate(0deg); }
            50% { transform: translateY(-15px) rotate(10deg); }
        }

        /* RESPONSIVE */
        @media (max-width: 768px) {
            .features-container {
                grid-template-columns: 1fr;
            }

            .top-section h1 {
                font-size: 36px;
            }

            .button-group {
                flex-direction: column;
                align-items: center;
            }

            .btn-action {
                width: 100%;
                max-width: 280px;
                justify-content: center;
            }
        }

        /* CONFETTI EFFECT */
        .confetti {
            position: fixed;
            width: 10px;
            height: 10px;
            background: #ff6b9d;
            position: absolute;
            animation: confetti-fall 3s linear infinite;
        }

        @keyframes confetti-fall {
            0% { transform: translateY(-100px) rotate(0deg); opacity: 1; }
            100% { transform: translateY(100vh) rotate(720deg); opacity: 0; }
        }
    </style>
</head>
<body>
    <!-- Bubbles Animation -->
    <div class="bubble bubble-1"></div>
    <div class="bubble bubble-2"></div>
    <div class="bubble bubble-3"></div>
    <div class="bubble bubble-4"></div>
    <div class="bubble bubble-5"></div>

    <div class="container-main">
        <div class="main-card">
            <!-- Top Section -->
            <div class="top-section">
                <div class="floating-emoji" style="top: 30%; left: 10%;">🎈</div>
                <div class="floating-emoji" style="top: 60%; right: 12%;">🌈</div>
                <div class="floating-emoji" style="bottom: 25%; left: 15%;">⭐</div>
                
                <div class="logo-box">
                    <i class="fas fa-baby-carriage"></i>
                </div>
                <h1>Hai Child</h1>
                <p class="subtitle">Solusi Cerdas Monitoring Kesehatan Balita</p>
            </div>

            <!-- Features -->
            <div class="features-wrapper">
                <div class="features-container">
                    <div class="feature-item">
                        <div class="icon-box">
                            <i class="fas fa-syringe"></i>
                        </div>
                        <h3>Monitoring Imunisasi</h3>
                        <p>Pantau jadwal dan riwayat imunisasi si kecil dengan sistem pengingat</p>
                    </div>

                    <div class="feature-item">
                        <div class="icon-box">
                            <i class="fas fa-weight"></i>
                        </div>
                        <h3>Status Gizi</h3>
                        <p>Pantau pertumbuhan dan status gizi balita secara berkala</p>
                    </div>

                    <div class="feature-item">
                        <div class="icon-box">
                            <i class="fas fa-book-open"></i>
                        </div>
                        <h3>Edukasi</h3>
                        <p>Informasi kesehatan anak dan tips pola asuh yang tepat</p>
                    </div>
                </div>
            </div>

            <!-- Action Buttons -->
            <div class="action-wrapper">
                <div class="button-group">
                    <a href="login.php" class="btn-action btn-login">
                        <i class="fas fa-sign-in-alt"></i>
                        Login Sekarang
                    </a>
                    <a href="register.php" class="btn-action btn-register">
                        <i class="fas fa-user-plus"></i>
                        Daftar Akun
                    </a>
                </div>

                <div class="info-tag">
                    <i class="fas fa-info-circle"></i>
                    <strong>Default Login:</strong> bidan1/123456 atau ibu001/123456
                </div>
            </div>
        </div>
    </div>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
    <script>
        // Create confetti effect
        function createConfetti() {
            const colors = ['#ff6b9d', '#c44dff', '#4facfe', '#43e97b', '#ffd93d'];
            for (let i = 0; i < 20; i++) {
                setTimeout(() => {
                    const confetti = document.createElement('div');
                    confetti.className = 'confetti';
                    confetti.style.left = Math.random() * 100 + 'vw';
                    confetti.style.background = colors[Math.floor(Math.random() * colors.length)];
                    confetti.style.animationDuration = (Math.random() * 2 + 2) + 's';
                    document.body.appendChild(confetti);
                    
                    setTimeout(() => confetti.remove(), 4000);
                }, i * 100);
            }
        }

        // Trigger confetti on load
        window.addEventListener('load', createConfetti);
    </script>
</body>
</html>