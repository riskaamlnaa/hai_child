<?php
// 1. Load functions library (MUST be first)
require_once __DIR__ . '/functions.php';

// 2. Check Authentication
checkAuth('bidan');

// Set default page title if not set
$page_title = $page_title ?? 'Dashboard';
?>
<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0, maximum-scale=1.0, user-scalable=no">
    <title><?php echo $page_title; ?> - Hai Child</title>
    <!-- Bootstrap CSS -->
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <!-- Font Awesome -->
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <!-- Google Fonts -->
    <link href="https://fonts.googleapis.com/css2?family=Poppins:wght@300;400;500;600;700&display=swap" rel="stylesheet">
    <style>
        * {
            font-family: 'Poppins', sans-serif;
            -webkit-tap-highlight-color: transparent;
        }
        
        body {
            background-color: #f4f6f9;
            margin: 0;
            overflow-x: hidden;
        }
        
        /* Sidebar Style */
        .sidebar {
            min-height: 100vh;
            background: linear-gradient(180deg, #667eea 0%, #764ba2 100%);
            color: white;
            width: 260px;
            position: fixed;
            top: 0;
            left: 0;
            z-index: 1000;
            transition: all 0.3s ease;
            box-shadow: 4px 0 10px rgba(0,0,0,0.1);
            overflow-y: auto;
        }
        
        .sidebar .sidebar-header {
            padding: 25px 20px;
            text-align: center;
            border-bottom: 1px solid rgba(255,255,255,0.1);
        }
        
        .sidebar .sidebar-header h4 {
            margin: 0;
            font-weight: 700;
            letter-spacing: 1px;
        }
        
        .sidebar .sidebar-header small {
            opacity: 0.8;
            font-size: 12px;
        }
        
        .sidebar .nav-link {
            color: rgba(255,255,255,0.8);
            padding: 12px 20px;
            margin: 4px 10px;
            border-radius: 10px;
            font-weight: 500;
            transition: all 0.3s;
            display: flex;
            align-items: center;
            text-decoration: none;
        }
        
        .sidebar .nav-link i {
            width: 25px;
            margin-right: 10px;
            text-align: center;
        }
        
        .sidebar .nav-link:hover, .sidebar .nav-link.active {
            background: rgba(255,255,255,0.2);
            color: white;
            transform: translateX(5px);
        }
        
        /* Main Content Area */
        .main-content {
            margin-left: 260px;
            padding: 20px;
            transition: all 0.3s;
            min-height: 100vh;
            padding-bottom: 100px;
        }
        
        /* Top Navbar */
        .top-navbar {
            background: white;
            padding: 15px 25px;
            border-radius: 15px;
            margin-bottom: 25px;
            box-shadow: 0 4px 15px rgba(0,0,0,0.05);
            display: flex;
            justify-content: space-between;
            align-items: center;
        }
        
        /* Mobile Menu Toggle */
        .mobile-toggle {
            display: none;
            background: none;
            border: none;
            font-size: 24px;
            color: #667eea;
            cursor: pointer;
            padding: 5px 10px;
        }
        
        /* Bottom Navigation for Mobile */
        .bottom-nav {
            display: none;
            position: fixed;
            bottom: 0;
            left: 0;
            right: 0;
            background: white;
            box-shadow: 0 -4px 20px rgba(0,0,0,0.1);
            padding: 8px 0;
            z-index: 999;
            justify-content: space-around;
        }
        
        .bottom-nav .nav-item {
            flex: 1;
            text-align: center;
            padding: 8px 5px;
            color: #999;
            text-decoration: none;
            font-size: 11px;
            transition: all 0.3s;
            display: flex;
            flex-direction: column;
            align-items: center;
            justify-content: center;
        }
        
        .bottom-nav .nav-item i {
            font-size: 20px;
            margin-bottom: 3px;
        }
        
        .bottom-nav .nav-item.active {
            color: #667eea;
        }
        
        .bottom-nav .nav-item.logout {
            color: #ff4757;
        }
        
        /* Overlay for mobile */
        .sidebar-overlay {
            display: none;
            position: fixed;
            top: 0;
            left: 0;
            right: 0;
            bottom: 0;
            background: rgba(0,0,0,0.5);
            z-index: 998;
        }
        
        /* Responsive Styles */
        @media (max-width: 768px) {
            .sidebar {
                margin-left: -260px;
                z-index: 1000;
            }
            
            .sidebar.active {
                margin-left: 0;
            }
            
            .main-content {
                margin-left: 0;
                padding: 15px;
                padding-bottom: 100px;
            }
            
            .mobile-toggle {
                display: block;
            }
            
            .sidebar-overlay.active {
                display: block;
            }
            
            .bottom-nav {
                display: flex;
            }
            
            .top-navbar {
                padding: 12px 15px;
            }
            
            .top-navbar h4 {
                font-size: 16px;
            }
            
            .top-navbar .badge {
                display: none;
            }
            
            .top-navbar .d-none.d-md-inline {
                display: none !important;
            }
        }
        
        @media (min-width: 769px) {
            .bottom-nav {
                display: none !important;
            }
            
            .mobile-toggle {
                display: none !important;
            }
        }
    </style>
</head>
<body>

    <!-- Overlay for mobile -->
    <div class="sidebar-overlay" onclick="toggleSidebar()"></div>

    <!-- Sidebar -->
    <nav class="sidebar" id="sidebar">
        <div class="sidebar-header">
            <i class="fas fa-baby-carriage fa-2x mb-2"></i>
            <h4>Hai Child</h4>
            <small>Panel Bidan</small>
        </div>
        <div class="nav flex-column mt-3">
            <a class="nav-link <?php echo basename($_SERVER['PHP_SELF']) == 'dashboard.php' ? 'active' : ''; ?>" href="dashboard.php">
                <i class="fas fa-home"></i> Dashboard
            </a>
            <a class="nav-link <?php echo basename($_SERVER['PHP_SELF']) == 'data_ibu_anak.php' ? 'active' : ''; ?>" href="data_ibu_anak.php">
                <i class="fas fa-users"></i> Data Ibu & Anak
            </a>
            
            <!-- MENU: Manajemen Data Ibu -->
            <a class="nav-link <?php echo basename($_SERVER['PHP_SELF']) == 'manajemen_ibu.php' ? 'active' : ''; ?>" href="manajemen_ibu.php">
                <i class="fas fa-users-cog"></i> Manajemen Data Ibu
            </a>
            
            <a class="nav-link <?php echo basename($_SERVER['PHP_SELF']) == 'imunisasi.php' ? 'active' : ''; ?>" href="imunisasi.php">
                <i class="fas fa-syringe"></i> Imunisasi
            </a>
            <a class="nav-link <?php echo basename($_SERVER['PHP_SELF']) == 'status_gizi.php' ? 'active' : ''; ?>" href="status_gizi.php">
                <i class="fas fa-weight"></i> Status Gizi
            </a>
            <a class="nav-link <?php echo basename($_SERVER['PHP_SELF']) == 'laporan.php' ? 'active' : ''; ?>" href="laporan.php">
                <i class="fas fa-chart-bar"></i> Laporan
            </a>
            <a class="nav-link <?php echo basename($_SERVER['PHP_SELF']) == 'grafik.php' ? 'active' : ''; ?>" href="grafik.php">
                <i class="fas fa-chart-pie"></i> Grafik
            </a>
            <a class="nav-link <?php echo basename($_SERVER['PHP_SELF']) == 'data_per_puskesmas.php' ? 'active' : ''; ?>" href="data_per_puskesmas.php">
                <i class="fas fa-map-marker-alt"></i> Data Per Puskesmas
            </a>
            <hr class="bg-white mx-3 my-2">
            <a class="nav-link <?php echo basename($_SERVER['PHP_SELF']) == 'profil.php' ? 'active' : ''; ?>" href="profil.php">
                <i class="fas fa-user-circle"></i> Profil Saya
            </a>
            <a class="nav-link" href="../logout.php" style="color: #ff6b6b; font-weight: 600;">
                <i class="fas fa-sign-out-alt"></i> Logout
            </a>
        </div>
    </nav>

    <!-- Main Content -->
    <div class="main-content">
        <!-- Top Navbar -->
        <div class="top-navbar">
            <div class="d-flex align-items-center">
                <button class="mobile-toggle me-2" onclick="toggleSidebar()">
                    <i class="fas fa-bars"></i>
                </button>
                <h4 class="mb-0 text-primary">
                    <i class="fas fa-chart-line me-2 d-none d-md-inline"></i><?php echo $page_title; ?>
                </h4>
            </div>
            <div class="d-flex align-items-center">
                <span class="badge bg-light text-dark me-2 me-md-3 p-2 d-none d-md-inline-block">
                    <i class="far fa-calendar me-1"></i> <?php echo date('d M Y'); ?>
                </span>
                <span class="fw-bold me-2" style="font-size: 14px;">Halo, <?php echo htmlspecialchars($_SESSION['nama'] ?? 'User'); ?></span>
            </div>
        </div>
        
        <!-- Page Content Starts Here -->

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
    <script>
        function toggleSidebar() {
            document.getElementById('sidebar').classList.toggle('active');
            document.querySelector('.sidebar-overlay').classList.toggle('active');
        }
        
        // Close sidebar when clicking on a link (mobile)
        document.querySelectorAll('.sidebar .nav-link').forEach(link => {
            link.addEventListener('click', () => {
                if (window.innerWidth <= 768) {
                    toggleSidebar();
                }
            });
        });
    </script>