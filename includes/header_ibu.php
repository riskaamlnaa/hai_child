<?php
session_start();
// Cek apakah user sudah login dan role-nya ibu_anak
if (!isset($_SESSION['user_id']) || $_SESSION['role'] !== 'ibu_anak') {
    header("Location: ../login.php");
    exit();
}
?>
<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0, maximum-scale=1.0, user-scalable=no">
    <title><?php echo $page_title ?? 'Hai Child'; ?></title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <link href="https://fonts.googleapis.com/css2?family=Nunito:wght@400;600;700&display=swap" rel="stylesheet">
    <style>
        body {
            font-family: 'Nunito', sans-serif;
            background-color: #f4f6f9;
            padding-bottom: 80px; /* Ruang untuk menu bawah */
        }
        /* Header Atas */
        .app-header {
            background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
            color: white;
            padding: 20px 20px 60px; /* Padding bawah besar agar konten bisa menumpuk */
            border-radius: 0 0 30px 30px;
            position: relative;
        }
        .header-content {
            display: flex;
            justify-content: space-between;
            align-items: center;
        }
        /* Card Style */
        .card-custom {
            border: none;
            border-radius: 15px;
            box-shadow: 0 4px 15px rgba(0,0,0,0.05);
            margin-bottom: 15px;
        }
        /* Bottom Navigation */
        .bottom-nav {
            position: fixed;
            bottom: 0;
            left: 0;
            right: 0;
            background: white;
            display: flex;
            justify-content: space-around;
            padding: 10px 0 15px;
            box-shadow: 0 -2px 10px rgba(0,0,0,0.1);
            z-index: 1000;
            border-radius: 20px 20px 0 0;
        }
        .nav-item {
            text-align: center;
            color: #999;
            text-decoration: none;
            font-size: 11px;
            font-weight: 600;
        }
        .nav-item i {
            font-size: 20px;
            display: block;
            margin-bottom: 4px;
        }
        .nav-item.active {
            color: #667eea;
        }
    </style>
</head>
<body>

    <!-- Header Tetap di Setiap Halaman Ibu -->
    <div class="app-header">
        <div class="header-content">
            <div>
                <h4 class="mb-0 fw-bold"><i class="fas fa-baby-carriage me-2"></i>Hai Child</h4>
                <small>Panel Ibu</small>
            </div>
            <div class="d-flex align-items-center">
                <span class="me-2 fw-bold small d-none d-sm-block"><?php echo htmlspecialchars($_SESSION['nama']); ?></span>
                <a href="../logout.php" class="text-white"><i class="fas fa-sign-out-alt fa-lg"></i></a>
            </div>
        </div>
    </div>

    <!-- Container Utama (Konten akan masuk sini) -->
    <div class="container" style="margin-top: -40px; position: relative; z-index: 10;">