<?php
require 'config.php';

// Kullanıcı Bilgileri
$username = 'admin';
$email = 'admin@blog.com';
$password = '123456'; // Basit şifre (Demo için)

// Şifreyi Hash'le (Güvenlik için)
$hashed_password = password_hash($password, PASSWORD_DEFAULT);

try {
    // Önce kullanıcı var mı kontrol et
    $stmt = $pdo->prepare("SELECT id FROM users WHERE email = ?");
    $stmt->execute([$email]);
    
    if ($stmt->rowCount() > 0) {
        echo "<b>Hata:</b> Bu e-posta adresiyle ($email) zaten bir kullanıcı var.<br>";
        echo "Lütfen giriş yapmayı deneyin veya farklı bir e-posta kullanın.";
    } else {
        // Kullanıcıyı Ekle (Rol = admin)
        $sql = "INSERT INTO users (username, email, password, role) VALUES (?, ?, ?, 'admin')";
        $stmt = $pdo->prepare($sql);
        $stmt->execute([$username, $email, $hashed_password]);
        
        echo "<h1>✅ Hesap Başarıyla Oluşturuldu!</h1>";
        echo "<p><b>Kullanıcı Adı:</b> $username</p>";
        echo "<p><b>E-posta:</b> $email</p>";
        echo "<p><b>Şifre:</b> $password</p>";
        echo "<p><b>Not:</b> Bu hesap 'Yönetici' (Admin) yetkisine sahiptir.</p>";
        echo "<hr>";
        echo "<a href='index.php?sayfa=giris'>👉 Buraya tıklayarak Giriş Yapın</a>";
    }

} catch (PDOException $e) {
    echo "Veritabanı Hatası: " . $e->getMessage();
}
?>
