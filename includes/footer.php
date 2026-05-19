    </div> <!-- Tutup Container -->

    <!-- Bottom Navigation (Hanya muncul untuk Ibu) -->
    <?php if (isset($_SESSION['role']) && $_SESSION['role'] == 'ibu_anak'): ?>
    <div class="bottom-nav">
        <a href="dashboard.php" class="nav-item <?php echo basename($_SERVER['PHP_SELF']) == 'dashboard.php' ? 'active' : ''; ?>">
            <i class="fas fa-home"></i>
            <span>Beranda</span>
        </a>
        <a href="kategori.php" class="nav-item <?php echo basename($_SERVER['PHP_SELF']) == 'kategori.php' ? 'active' : ''; ?>">
            <i class="fas fa-th-large"></i>
            <span>Kategori</span>
        </a>
        <a href="cari.php" class="nav-item <?php echo basename($_SERVER['PHP_SELF']) == 'cari.php' ? 'active' : ''; ?>">
            <i class="fas fa-search"></i>
            <span>Cari</span>
        </a>
        <a href="profil.php" class="nav-item <?php echo basename($_SERVER['PHP_SELF']) == 'profil.php' ? 'active' : ''; ?>">
            <i class="fas fa-user"></i>
            <span>Profil</span>
        </a>
    </div>
    <?php endif; ?>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>