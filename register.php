<?php
// Kayıt işlemi (Form gönderildiyse)
$hata = '';
$basari = '';

if ($_SERVER['REQUEST_METHOD'] == 'POST' && isset($_POST['kayit_ol'])) {
    $kullanici_adi = trim($_POST['username']);
    $email = trim($_POST['email']);
    $sifre = $_POST['password'];
    $sifre_tekrar = $_POST['confirm_password'];
    
    // Basit doğrulama
    if (empty($kullanici_adi) || empty($email) || empty($sifre)) {
        $hata = 'Lütfen tüm alanları doldurun.';
    } elseif ($sifre != $sifre_tekrar) {
        $hata = 'Şifreler uyuşmuyor.';
    } else {
        // Kullanıcı adı veya email var mı kontrol et
        $stmt = $pdo->prepare("SELECT id FROM users WHERE username = ? OR email = ?");
        $stmt->execute([$kullanici_adi, $email]);
        
        if ($stmt->rowCount() > 0) {
            $hata = 'Bu kullanıcı adı veya e-posta zaten kullanılıyor.';
        } else {
            // Şifreyi hashle
            $sifre_hash = password_hash($sifre, PASSWORD_DEFAULT);
            
            // Varsayılan rol 'user' (kullanıcı)
            $sql = "INSERT INTO users (username, email, password, role) VALUES (?, ?, ?, 'user')";
            $stmt = $pdo->prepare($sql);
            
            if ($stmt->execute([$kullanici_adi, $email, $sifre_hash])) {
                $basari = 'Kayıt başarılı! Şimdi giriş yapabilirsiniz.';
            } else {
                $hata = 'Kayıt sırasında bir hata oluştu.';
            }
        }
    }
}
?>

<div class="row justify-content-center">
    <div class="col-md-6 col-lg-5">
        <div class="card shadow-sm">
            <div class="card-body p-4">
                <h3 class="text-center mb-4">Kayıt Ol</h3>
                
                <?php if ($hata): ?>
                    <div class="alert alert-danger"><?php echo $hata; ?></div>
                <?php endif; ?>
                
                <?php if ($basari): ?>
                    <div class="alert alert-success">
                        <?php echo $basari; ?> 
                        <a href="index.php?sayfa=giris" class="alert-link">Giriş Yap</a>
                    </div>
                <?php endif; ?>

                <form method="POST" action="">
                    <div class="mb-3">
                        <label for="username" class="form-label">Kullanıcı Adı</label>
                        <input type="text" class="form-control" id="username" name="username" required>
                    </div>
                    
                    <div class="mb-3">
                        <label for="email" class="form-label">E-posta Adresi</label>
                        <input type="email" class="form-control" id="email" name="email" required>
                    </div>
                    
                    <div class="mb-3">
                        <label for="password" class="form-label">Şifre</label>
                        <input type="password" class="form-control" id="password" name="password" required>
                    </div>
                    
                    <div class="mb-3">
                        <label for="confirm_password" class="form-label">Şifre Tekrarı</label>
                        <input type="password" class="form-control" id="confirm_password" name="confirm_password" required>
                    </div>
                    
                    <div class="d-grid gap-2">
                        <button type="submit" name="kayit_ol" class="btn btn-primary">Kayıt Ol</button>
                    </div>
                </form>
                
                <div class="text-center mt-3">
                    <p>Zaten hesabınız var mı? <a href="index.php?sayfa=giris">Giriş Yap</a></p>
                </div>
            </div>
        </div>
    </div>
</div>
