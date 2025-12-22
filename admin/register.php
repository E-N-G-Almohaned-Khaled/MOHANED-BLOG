<?php
require_once '../config.php';

$hata = '';
$basari = '';

if ($_SERVER['REQUEST_METHOD'] == 'POST' && isset($_POST['admin_olustur'])) {
    $username = trim($_POST['username']);
    $email = trim($_POST['email']);
    $password = $_POST['password'];
    $secret = $_POST['secret_code'];
    
    // Güvenlik kodu
    if ($secret !== 'super_secret_code_123') {
        $hata = 'Hatalı gizli kod!';
    } else {
        $stmt = $pdo->prepare("SELECT id FROM users WHERE username = ? OR email = ?");
        $stmt->execute([$username, $email]);
        
        if ($stmt->rowCount() > 0) {
            $hata = 'Bu kullanıcı zaten var.';
        } else {
            $hashed_password = password_hash($password, PASSWORD_DEFAULT);
            // Doğrudan 'admin' rolü veriyoruz
            $sql = "INSERT INTO users (username, email, password, role) VALUES (?, ?, ?, 'admin')";
            $stmt = $pdo->prepare($sql);
            
            if ($stmt->execute([$username, $email, $hashed_password])) {
                $basari = 'Admin hesabı oluşturuldu. <a href="login.php">Giriş Yap</a>';
            } else {
                $hata = 'Bir hata oluştu.';
            }
        }
    }
}
?>
<!DOCTYPE html>
<html lang="tr">
<head>
    <meta charset="UTF-8">
    <title>Admin Kayıt</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@400;500;600&family=Outfit:wght@500;700&display=swap" rel="stylesheet">
    <link href="style.css" rel="stylesheet">
</head>
<body class="bg-secondary d-flex align-items-center justify-content-center" style="height: 100vh;">
    <div class="card shadow p-4 auth-card">
        <h4 class="text-center mb-3">Yeni Admin Ekle</h4>
        
        <?php if ($hata): ?>
            <div class="alert alert-danger"><?php echo $hata; ?></div>
        <?php endif; ?>
        <?php if ($basari): ?>
            <div class="alert alert-success"><?php echo $basari; ?></div>
        <?php endif; ?>
        
        <form method="POST">
            <div class="mb-3">
                <label>Kullanıcı Adı</label>
                <input type="text" name="username" class="form-control" required>
            </div>
            <div class="mb-3">
                <label>E-posta</label>
                <input type="email" name="email" class="form-control" required>
            </div>
            <div class="mb-3">
                <label>Şifre</label>
                <input type="password" name="password" class="form-control" required>
            </div>
            <div class="mb-3">
                <label>Gizli Kod</label>
                <input type="text" name="secret_code" class="form-control" placeholder="super_secret_code_123" required>
            </div>
            <button type="submit" name="admin_olustur" class="btn btn-danger w-100">Oluştur</button>
        </form>
        <div class="text-center mt-3">
             <a href="login.php" class="text-decoration-none">Girişe Dön</a>
        </div>
    </div>
</body>
</html>
