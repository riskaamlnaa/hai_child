<?php
// File: bidan/hapus_ibu_anak.php
// Fungsi: Menghapus data ibu & anak beserta data terkait (imunisasi, gizi)

require_once '../includes/functions.php';
require_once '../config/database.php';

// 1. CEK AUTHENTIKASI & ROLE
// Pastikan hanya bidan yang bisa mengakses
if (!isLoggedIn() || $_SESSION['role'] !== 'bidan') {
    $_SESSION['flash_message'] = "❌ Akses ditolak! Anda tidak memiliki izin.";
    $_SESSION['flash_type'] = "danger";
    header("Location: ../login.php");
    exit();
}

// 2. VALIDASI METHOD & PARAMETER
// Hanya terima request POST dengan ID yang valid
if ($_SERVER['REQUEST_METHOD'] !== 'POST' || !isset($_POST['id']) || !is_numeric($_POST['id'])) {
    $_SESSION['flash_message'] = "⚠️ Parameter tidak valid!";
    $_SESSION['flash_type'] = "warning";
    header("Location: data_ibu_anak.php");
    exit();
}

$id = (int)$_POST['id'];
$deleted_by = $_SESSION['user_id'] ?? 0; // Untuk audit log

// 3. VALIDASI CSRF TOKEN (Keamanan Tambahan)
// Pastikan request berasal dari form yang sah
if (!isset($_POST['csrf_token']) || $_POST['csrf_token'] !== $_SESSION['csrf_token'] ?? '') {
    $_SESSION['flash_message'] = "🔒 Token keamanan tidak valid!";
    $_SESSION['flash_type'] = "danger";
    header("Location: data_ibu_anak.php");
    exit();
}

$database = new Database();
$db = $database->getConnection();

try {
    // Mulai Transaction (Agar semua delete berhasil atau gagal bersama)
    $db->beginTransaction();

    // A. Ambil Nama Anak untuk Log/Audit (Opsional)
    $stmt_info = $db->prepare("SELECT nama_anak, nama_ibu FROM ibu_anak WHERE id = ?");
    $stmt_info->execute([$id]);
    $data_info = $stmt_info->fetch();

    if (!$data_info) {
        throw new Exception("Data anak dengan ID $id tidak ditemukan!");
    }

    // B. Hapus Data Terkait Terlebih Dahulu (Jika Foreign Key tidak CASCADE)
    // Hapus data imunisasi anak ini
    $stmt_del_imun = $db->prepare("DELETE FROM imunisasi WHERE ibu_anak_id = ?");
    $stmt_del_imun->execute([$id]);

    // Hapus data status gizi anak ini
    $stmt_del_gizi = $db->prepare("DELETE FROM status_gizi WHERE ibu_anak_id = ?");
    $stmt_del_gizi->execute([$id]);

    // C. Hapus User Account Ibu (Opsional - Hati-hati!)
    // Jika ingin menghapus akun login ibu juga, uncomment baris berikut:
    // $stmt_user = $db->prepare("SELECT user_id FROM ibu_anak WHERE id = ?");
    // $stmt_user->execute([$id]);
    // $user_id = $stmt_user->fetchColumn();
    // if ($user_id) {
    //     $db->prepare("DELETE FROM users WHERE id = ?")->execute([$user_id]);
    // }

    // D. Hapus Data Utama (Ibu & Anak)
    $stmt_del_main = $db->prepare("DELETE FROM ibu_anak WHERE id = ?");
    $stmt_del_main->execute([$id]);

    // Commit Transaction
    $db->commit();

    // E. Audit Log Sederhana (Opsional - Simpan ke file log)
    // file_put_contents('../logs/delete_log.txt', 
    //     date('Y-m-d H:i:s') . " | Bidan ID:$deleted_by | Hapus Anak:{$data_info['nama_anak']} (ID:$id)\n", 
    //     FILE_APPEND);

    // F. Set Flash Message Sukses
    $_SESSION['flash_message'] = "✅ Data <strong>" . htmlspecialchars($data_info['nama_anak']) . "</strong> berhasil dihapus!";
    $_SESSION['flash_type'] = "success";

} catch (PDOException $e) {
    // Rollback jika ada error
    if ($db->inTransaction()) {
        $db->rollBack();
    }
    
    // Log error ke file (untuk developer)
    error_log("Delete Error: " . $e->getMessage());
    
    $_SESSION['flash_message'] = "❌ Gagal menghapus data: " . $e->getMessage();
    $_SESSION['flash_type'] = "danger";

} catch (Exception $e) {
    if ($db->inTransaction()) {
        $db->rollBack();
    }
    $_SESSION['flash_message'] = "❌ " . $e->getMessage();
    $_SESSION['flash_type'] = "danger";
}

// Redirect kembali ke halaman data
header("Location: data_ibu_anak.php");
exit();
?>