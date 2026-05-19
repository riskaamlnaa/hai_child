<?php
// File: bidan/hapus_gizi.php
// Fungsi: Menghapus data status gizi berdasarkan ID

session_start();

// 1. Cek apakah user sudah login dan merupakan bidan
if (!isset($_SESSION['user_id']) || $_SESSION['role'] !== 'bidan') {
    header("Location: ../login.php");
    exit();
}

// 2. Cek apakah ada ID yang dikirim via URL
if (!isset($_GET['id']) || empty($_GET['id'])) {
    $_SESSION['flash_message'] = "⚠️ ID data gizi tidak valid!";
    $_SESSION['flash_type'] = "warning";
    header("Location: status_gizi.php");
    exit();
}

$id = (int)$_GET['id'];

require_once '../config/database.php';

try {
    $db = (new Database())->getConnection();
    
    // 3. Hapus data status gizi
    $stmt = $db->prepare("DELETE FROM status_gizi WHERE id = ?");
    $stmt->execute([$id]);
    
    if ($stmt->rowCount() > 0) {
        $_SESSION['flash_message'] = "✅ Data status gizi berhasil dihapus!";
        $_SESSION['flash_type'] = "success";
    } else {
        $_SESSION['flash_message'] = "⚠️ Data tidak ditemukan atau sudah dihapus.";
        $_SESSION['flash_type'] = "warning";
    }
    
} catch (PDOException $e) {
    // Jika ada error database (misal foreign key constraint)
    $_SESSION['flash_message'] = "❌ Gagal menghapus data: " . $e->getMessage();
    $_SESSION['flash_type'] = "danger";
}

// 4. Redirect kembali ke halaman status gizi
header("Location: status_gizi.php");
exit();
?>