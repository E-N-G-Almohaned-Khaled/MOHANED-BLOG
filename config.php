<?php
// Veritabanı yapılandırması
// Database configuration

// Detect environment
// Detect environment
$httpHost = isset($_SERVER['HTTP_HOST']) ? $_SERVER['HTTP_HOST'] : 'localhost';
if (strpos($httpHost, 'localhost') !== false || strpos($httpHost, '127.0.0.1') !== false) {
    // Local Configuration
    $host = '127.0.0.1';
    $port = '3307';
    $dbname = 'blog_db';
    $username = 'root';
    $password = '';
} else {
    // Live Database Configuration (InfinityFree)
    $host = 'sql100.infinityfree.com';
    $port = '3306';
    $dbname = 'if0_40748074_blog';
    $username = 'if0_40748074';
    $password = 'Mohaned2025';
}

try {
    // PDO bağlantısı oluşturma
    $pdo = new PDO("mysql:host=$host;port=$port;dbname=$dbname;charset=utf8mb4", $username, $password);
    
    // Hata modunu exception olarak ayarla
    $pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
    
    // Bağlantı başarılı mesajı (Test için, canlıda kapatılabilir)
    // echo "Veritabanı bağlantısı başarılı!";
    
} catch(PDOException $e) {
    // Bağlantı hatası durumunda
    die("HATA: Veritabanına bağlanılamadı. " . $e->getMessage());
}
?>
