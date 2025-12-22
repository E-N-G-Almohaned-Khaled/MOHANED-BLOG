<?php
session_start();
// Ana config dosyasını dahil et (bir üst dizinde)
require_once '../config.php';

$hata = '';

if ($_SERVER['REQUEST_METHOD'] == 'POST' && isset($_POST['giris_yap'])) {
    $email = trim($_POST['email']);
    $sifre = $_POST['password'];
    
    if (empty($email) || empty($sifre)) {
        $hata = 'Lütfen tüm alanları doldurun.';
    } else {
        $stmt = $pdo->prepare("SELECT * FROM users WHERE email = ?");
        $stmt->execute([$email]);
        $user = $stmt->fetch();
        
        if ($user && password_verify($sifre, $user['password'])) {
            // Sadece ADMIN rolüne sahip olanlar girebilir
            if ($user['role'] === 'admin') {
                $_SESSION['user_id'] = $user['id'];
                $_SESSION['username'] = $user['username'];
                $_SESSION['role'] = $user['role'];
                
                header("Location: index.php");
                exit;
            } else {
                $hata = 'Bu alana sadece yöneticiler girebilir.';
            }
        } else {
            $hata = 'E-posta veya şifre hatalı.';
        }
    }
}
?>
<!DOCTYPE html>
<html lang="tr">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Admin Girişi</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@400;500;600&family=Outfit:wght@500;700&display=swap" rel="stylesheet">
    <link href="style.css" rel="stylesheet">
</head>
<body class="bg-dark d-flex align-items-center justify-content-center" style="height: 100vh;">
    <div class="card shadow p-4 auth-card">
        <h3 class="text-center mb-4">Admin Girişi</h3>
        <?php if ($hata): ?>
            <div class="alert alert-danger"><?php echo $hata; ?></div>
        <?php endif; ?>
        
        <form method="POST">
            <div class="mb-3">
                <label class="form-label">E-posta</label>
                <input type="email" name="email" class="form-control" required>
            </div>
            <div class="mb-3">
                <label class="form-label">Şifre</label>
                <input type="password" name="password" class="form-control" required>
            </div>
            <button type="submit" name="giris_yap" class="btn btn-primary w-100">Giriş Yap</button>
        </form>
        <div class="text-center mt-3">
            <a href="../index.php" class="text-decoration-none">&larr; Bloga Dön</a> | 
            <a href="register.php" class="text-decoration-none">Yeni Admin Ekle</a>
        </div>
    </div>
</body>
</html>
