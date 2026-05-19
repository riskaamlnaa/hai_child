<?php
$page_title = 'Grafik & Statistik';
require_once '../includes/header_bidan.php';
require_once '../config/database.php';

$db = (new Database())->getConnection();

// --- 1. AMBIL DATA STATISTIK DARI DATABASE ---

// A. Data Status Gizi (Normal, Kurang, Lebih)
// Mengambil jumlah anak berdasarkan status gizi terakhir
$q_gizi = $db->query("SELECT status_gizi, COUNT(*) as jumlah FROM status_gizi GROUP BY status_gizi");
$gizi_labels = [];
$gizi_data = [];
$gizi_colors = [];

while ($row = $q_gizi->fetch()) {
    $label = ucfirst($row['status_gizi']);
    $gizi_labels[] = $label;
    $gizi_data[] = $row['jumlah'];
    
    // Warna chart
    if ($row['status_gizi'] == 'normal') $gizi_colors[] = '#28a745'; // Hijau
    elseif ($row['status_gizi'] == 'kurang') $gizi_colors[] = '#dc3545'; // Merah
    else $gizi_colors[] = '#ffc107'; // Kuning
}
// Fallback jika data kosong
if (empty($gizi_labels)) { $gizi_labels = ['Belum Ada Data']; $gizi_data = [1]; $gizi_colors = ['#eee']; }

// B. Data Puskesmas (Distribusi Anak)
// Menghitung berapa anak per Puskesmas
$q_puskesmas = $db->query("SELECT puskesmas, COUNT(*) as jumlah FROM ibu_anak GROUP BY puskesmas ORDER BY jumlah DESC");
$puskesmas_labels = [];
$puskesmas_data = [];

while ($row = $q_puskesmas->fetch()) {
    $puskesmas_labels[] = $row['puskesmas'];
    $puskesmas_data[] = $row['jumlah'];
}
// Fallback jika data kosong
if (empty($puskesmas_labels)) { $puskesmas_labels = ['Belum Ada Data']; $puskesmas_data = [0]; }

// C. Data Imunisasi (Status Lengkap vs Belum)
$q_imun = $db->query("SELECT status, COUNT(*) as jumlah FROM imunisasi GROUP BY status");
$imun_labels = [];
$imun_data = [];

while ($row = $q_imun->fetch()) {
    $imun_labels[] = ucfirst($row['status']);
    $imun_data[] = $row['jumlah'];
}
if (empty($imun_labels)) { $imun_labels = ['Belum Ada Data']; $imun_data = [0]; }

?>

<!-- Include Chart.js CDN (Wajib ada untuk grafik) -->
<script src="https://cdn.jsdelivr.net/npm/chart.js"></script>

<div class="row">
    <!-- Statistik Gizi (Pie Chart) -->
    <div class="col-md-6 mb-4">
        <div class="card card-stat p-4 h-100">
            <h5 class="fw-bold text-center mb-4"><i class="fas fa-weight text-info me-2"></i>Distribusi Status Gizi</h5>
            <div style="position: relative; height: 300px; width: 100%; display: flex; justify-content: center;">
                <canvas id="chartGizi"></canvas>
            </div>
        </div>
    </div>

    <!-- Statistik Imunisasi (Doughnut Chart) -->
    <div class="col-md-6 mb-4">
        <div class="card card-stat p-4 h-100">
            <h5 class="fw-bold text-center mb-4"><i class="fas fa-syringe text-primary me-2"></i>Status Imunisasi</h5>
            <div style="position: relative; height: 300px; width: 100%; display: flex; justify-content: center;">
                <canvas id="chartImun"></canvas>
            </div>
        </div>
    </div>

    <!-- Statistik Puskesmas (Bar Chart) -->
    <div class="col-12 mb-4">
        <div class="card card-stat p-4">
            <h5 class="fw-bold text-center mb-4"><i class="fas fa-hospital text-success me-2"></i>Jumlah Anak per Puskesmas</h5>
            <div style="position: relative; height: 400px; width: 100%;">
                <canvas id="chartPuskesmas"></canvas>
            </div>
        </div>
    </div>
</div>

<!-- Script Konfigurasi Chart -->
<script>
    // --- 1. CHART GIZI ---
    const ctxGizi = document.getElementById('chartGizi').getContext('2d');
    new Chart(ctxGizi, {
        type: 'pie',
        data: {
            labels: <?php echo json_encode($gizi_labels); ?>,
            datasets: [{
                data: <?php echo json_encode($gizi_data); ?>,
                backgroundColor: <?php echo json_encode($gizi_colors); ?>,
                borderWidth: 1
            }]
        },
        options: {
            responsive: true,
            maintainAspectRatio: false,
            plugins: {
                legend: { position: 'bottom' }
            }
        }
    });

    // --- 2. CHART IMUNISASI ---
    const ctxImun = document.getElementById('chartImun').getContext('2d');
    new Chart(ctxImun, {
        type: 'doughnut',
        data: {
            labels: <?php echo json_encode($imun_labels); ?>,
            datasets: [{
                data: <?php echo json_encode($imun_data); ?>,
                backgroundColor: ['#667eea', '#ffc107', '#dc3545'], // Warna default
                borderWidth: 1
            }]
        },
        options: {
            responsive: true,
            maintainAspectRatio: false,
            plugins: {
                legend: { position: 'bottom' }
            }
        }
    });

    // --- 3. CHART PUSKESMAS (BATANG) ---
    const ctxPus = document.getElementById('chartPuskesmas').getContext('2d');
    new Chart(ctxPus, {
        type: 'bar',
        data: {
            labels: <?php echo json_encode($puskesmas_labels); ?>,
            datasets: [{
                label: 'Jumlah Anak',
                data: <?php echo json_encode($puskesmas_data); ?>,
                backgroundColor: '#667eea',
                borderRadius: 5
            }]
        },
        options: {
            responsive: true,
            maintainAspectRatio: false,
            scales: {
                y: { beginAtZero: true, ticks: { stepSize: 1 } }
            },
            plugins: {
                legend: { display: false }
            }
        }
    });
</script>

<?php require_once '../includes/footer.php'; ?>