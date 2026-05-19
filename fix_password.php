<?php
// File: fix_password.php
// Fungsi: Fix password user yang bermasalah

echo "<h1>FIX PASSWORD LOGIN</h1>";
echo "<hr>";

// Load database
require_once 'config/database.php';

try {
    $db = (new Database())->getConnection();
    echo "✅ Database connected<br><br>";
    
    // Generate password hash BARU untuk '123456'
    $password_baru = '123456';
    $hash_baru = password_hash($password_baru, PASSWORD_DEFAULT);
    
    echo "Password plain: <strong>$password_baru</strong><br>";
    echo "Hash baru: <strong>$hash_baru</strong><br><br>";
    
    // Update semua user demo
    $users = ['bidan1', 'bidan2', 'ibu001', 'ibu002'];
    
    foreach ($users as $username) {
        $stmt = $db->prepare("UPDATE users SET password = ? WHERE username = ?");
        $stmt->execute([$hash_baru, $username]);
        
        $count = $stmt->rowCount();
        if ($count > 0) {
            echo "✅ User <strong>$username</strong> - Password berhasil diupdate!<br>";
        } else {
            echo "⚠️ User <strong>$username</strong> - Tidak ditemukan (mungkin belum dibuat)<br>";
        }
    }
    
    echo "<br><hr>";
    echo "<h3>Verifikasi User di Database:</h3>";
    
    // Tampilkan semua user
    $stmt = $db->query("SELECT username, email, role, status FROM users");
    $users = $stmt->fetchAll();
    
    if (count($users) > 0) {
        echo "<table border='1' cellpadding='8'>";
        echo "<tr><th>Username</th><th>Email</th><th>Role</th><th>Status</th></tr>";
        foreach ($users as $u) {
            echo "<tr>";
            echo "<td>{$u['username']}</td>";
            echo "<td>{$u['email']}</td>";
            echo "<td>{$u['role']}</td>";
            echo "<td>{$u['status']}</td>";
            echo "</tr>";
        }
        echo "</table>";
    } else {
        echo "❌ Tidak ada user di database!<br>";
        echo "Silakan jalankan script create tabel dan insert user terlebih dahulu.";
    }
    
    echo "<br><hr>";
    echo "<h3>Test Login:</h3>";
    echo "Sekarang coba login dengan:<br>";
    echo "<strong>Username:</strong> bidan1<br>";
    echo "<strong>Password:</strong> 123456<br><br>";
    echo "<a href='login.php' style='background:#667eea; color:white; padding:10px 20px; text-decoration:none; border-radius:5px;'>Coba Login Sekarang</a>";
    
} catch (Exception $e) {
    echo "❌ Error: " . $e->getMessage();
}
?>