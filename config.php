<?php
// Veritabanı yapılandırması
// Database configuration

$host = '127.0.0.1';
$port = '3307';
$dbname = 'blog_db';
$username = 'root'; // Varsayılan XAMPP/WAMP kullanıcı adı
$password = '';     // Varsayılan XAMPP/WAMP şifresi (genelde boştur)

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
