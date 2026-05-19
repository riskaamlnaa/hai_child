<?php
// File: bidan/export_imunisasi.php
require_once '../includes/functions.php';
require_once '../config/database.php';

// Cek autentikasi
if (!isLoggedIn() || $_SESSION['role'] !== 'bidan') {
    die("Akses ditolak");
}

$database = new Database();
$db = $database->getConnection();

// Ambil parameter filter (sama seperti halaman utama)
$where_clauses = [];
$params = [];

if (!empty($_GET['search_anak'])) {
    $where_clauses[] = "ia.nama_anak LIKE ?";
    $params[] = "%" . sanitize($_GET['search_anak']) . "%";
}
if (!empty($_GET['search_ibu'])) {
    $where_clauses[] = "ia.nama_ibu LIKE ?";
    $params[] = "%" . sanitize($_GET['search_ibu']) . "%";
}
if (!empty($_GET['jenis'])) {
    $where_clauses[] = "i.jenis_imunisasi = ?";
    $params[] = sanitize($_GET['jenis']);
}
if (!empty($_GET['status'])) {
    $where_clauses[] = "i.status = ?";
    $params[] = sanitize($_GET['status']);
}
if (!empty($_GET['tgl_mulai'])) {
    $where_clauses[] = "i.tanggal_imunisasi >= ?";
    $params[] = $_GET['tgl_mulai'];
}
if (!empty($_GET['tgl_selesai'])) {
    $where_clauses[] = "i.tanggal_imunisasi <= ?";
    $params[] = $_GET['tgl_selesai'];
}

$where_sql = !empty($where_clauses) ? "WHERE " . implode(" AND ", $where_clauses) : "";

// Query data
$query = "SELECT i.*, ia.nama_anak, ia.nama_ibu, ia.puskesmas 
          FROM imunisasi i 
          JOIN ibu_anak ia ON i.ibu_anak_id = ia.id 
          $where_sql
          ORDER BY i.tanggal_imunisasi DESC";

$stmt = $db->prepare($query);
$stmt->execute($params);
$data = $stmt->fetchAll(PDO::FETCH_ASSOC);

// Header CSV
header('Content-Type: text/csv; charset=utf-8');
header('Content-Disposition: attachment; filename=imunisasi_' . date('Y-m-d') . '.csv');

$output = fopen('php://output', 'w');

// BOM untuk Excel support UTF-8
fprintf($output, chr(0xEF).chr(0xBB).chr(0xBF));

// Header kolom
fputcsv($output, [
    'No', 'Nama Anak', 'Nama Ibu', 'Puskesmas', 
    'Jenis Imunisasi', 'Tanggal', 'Status', 'Keterangan'
]);

// Data
$no = 1;
foreach ($data as $row) {
    fputcsv($output, [
        $no++,
        $row['nama_anak'],
        $row['nama_ibu'],
        $row['puskesmas'],
        $row['jenis_imunisasi'],
        date('d/m/Y', strtotime($row['tanggal_imunisasi'])),
        $row['status'],
        $row['keterangan'] ?? ''
    ]);
}

fclose($output);
exit();
?>