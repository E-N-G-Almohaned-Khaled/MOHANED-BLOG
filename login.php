<?php
// Giriş işlemi
$hata = '';

if ($_SERVER['REQUEST_METHOD'] == 'POST' && isset($_POST['giris_yap'])) {
    $email = trim($_POST['email']);
    $sifre = $_POST['password'];
    
    if (empty($email) || empty($sifre)) {
        $hata = 'Lütfen tüm alanları doldurun.';
    } else {
        // Kullanıcıyı bul
        $stmt = $pdo->prepare("SELECT * FROM users WHERE email = ?");
        $stmt->execute([$email]);
        $user = $stmt->fetch();
        
        if ($user && password_verify($sifre, $user['password'])) {
            // Giriş başarılı
            $_SESSION['user_id'] = $user['id'];
            $_SESSION['username'] = $user['username'];
            $_SESSION['role'] = $user['role'];
            
            // Ana sayfaya yönlendir
            header("Location: index.php");
            exit;
        } else {
            $hata = 'E-posta veya şifre hatalı.';
        }
    }
}
?>

<div class="row justify-content-center">
    <div class="col-md-6 col-lg-5">
        <div class="card shadow-sm">
            <div class="card-body p-4">
                <h3 class="text-center mb-4">Giriş Yap</h3>
                
                <?php if ($hata): ?>
                    <div class="alert alert-danger"><?php echo $hata; ?></div>
                <?php endif; ?>

                <form method="POST" action="">
                    <div class="mb-3">
                        <label for="email" class="form-label">E-posta Adresi</label>
                        <input type="email" class="form-control" id="email" name="email" required>
                    </div>
                    
                    <div class="mb-3">
                        <label for="password" class="form-label">Şifre</label>
                        <input type="password" class="form-control" id="password" name="password" required>
                    </div>
                    
                    <div class="d-grid gap-2">
                        <button type="submit" name="giris_yap" class="btn btn-primary">Giriş Yap</button>
                    </div>
                </form>
                
                <div class="text-center mt-3">
                    <p>Hesabınız yok mu? <a href="index.php?sayfa=kayit">Kayıt Ol</a></p>
                </div>
            </div>
        </div>
    </div>
</div>
