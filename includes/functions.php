<?php
// Fungsi umum yang dipakai di seluruh aplikasi

function startSession() {
    if (session_status() === PHP_SESSION_NONE) {
        session_start();
    }
}

// Cek apakah user sudah login
function isLoggedIn() {
    startSession();
    return isset($_SESSION['user_id']);
}

// Cek Role (Bidan atau Ibu)
function checkAuth($requiredRole = null) {
    startSession();
    if (!isset($_SESSION['user_id'])) {
        header("Location: ../login.php");
        exit();
    }
    
    if ($requiredRole && $_SESSION['role'] !== $requiredRole) {
        // Jika role tidak sesuai (misal Ibu coba akses Bidan)
        header("Location: ../login.php");
        exit();
    }
}

// Redirect helper
function redirect($url) {
    header("Location: " . $url);
    exit();
}

// Sanitasi input HTML
function sanitize($data) {
    return htmlspecialchars(strip_tags(trim($data)), ENT_QUOTES, 'UTF-8');
}

// Hitung Umur dalam Bulan
function hitungUmurBulan($tanggal_lahir) {
    $birth = new DateTime($tanggal_lahir);
    $now = new DateTime();
    $diff = $now->diff($birth);
    return ($diff->y * 12) + $diff->m;
}
?>