<?php
session_start();
require_once '../config.php';

// Yetki Kontrolü
if (!isset($_SESSION['user_id']) || $_SESSION['role'] !== 'admin') {
    header("Location: login.php");
    exit;
}

$hata = '';
$basari = '';

// Kullanıcı bilgilerini çek
$stmt = $pdo->prepare("SELECT * FROM users WHERE id = ?");
$stmt->execute([$_SESSION['user_id']]);
$user = $stmt->fetch();

if ($_SERVER['REQUEST_METHOD'] == 'POST' && isset($_POST['profil_guncelle'])) {
    $username = trim($_POST['username']);
    $email = trim($_POST['email']);
    $yeni_sifre = $_POST['new_password'];
    $sifre_tekrar = $_POST['confirm_password'];
    
    // Resim Yükleme
    $resimYolu = $user['profile_image'];
    if (isset($_FILES['profile_image']) && $_FILES['profile_image']['error'] == 0) {
        $uploadDir = '../uploads/'; // Admin klasöründen bir üst dizine
        if (!file_exists($uploadDir)) {
            mkdir($uploadDir, 0777, true);
        }
        $fileExt = strtolower(pathinfo($_FILES['profile_image']['name'], PATHINFO_EXTENSION));
        $allowed = ['jpg', 'jpeg', 'png', 'gif', 'webp', 'bmp', 'tiff', 'svg', 'ico'];
        
        if (in_array($fileExt, $allowed)) {
            $newName = uniqid('profile_' . $_SESSION['user_id'] . '_') . '.' . $fileExt;
            if (move_uploaded_file($_FILES['profile_image']['tmp_name'], $uploadDir . $newName)) {
                $resimYolu = 'uploads/' . $newName; // DB'ye kaydedilecek yol (root'a göre)
            }
        }
    }

    if (empty($username)) {
        $hata = "Kullanıcı adı boş bırakılamaz.";
    } elseif (empty($email)) {
        $hata = "Email adresi boş bırakılamaz.";
    } elseif (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
        $hata = "Geçersiz email formatı.";
    } else {
        // Email veya Kullanıcı Adı kontrolü
        $kontrolStmt = $pdo->prepare("SELECT id FROM users WHERE (email = ? OR username = ?) AND id != ?");
        $kontrolStmt->execute([$email, $username, $_SESSION['user_id']]);
        if ($kontrolStmt->rowCount() > 0) {
            $hata = "Bu email adresi veya kullanıcı adı zaten kullanılıyor.";
        } else {
            // Şifre güncelleme
            $sifreGuncelle = false;
            if (!empty($yeni_sifre)) {
                if (strlen($yeni_sifre) < 6) {
                    $hata = "Yeni şifre en az 6 karakter olmalıdır.";
                } elseif ($yeni_sifre !== $sifre_tekrar) {
                    $hata = "Şifreler uyuşmuyor.";
                } else {
                    $sifreGuncelle = true;
                }
            }

            if (empty($hata)) {
                if ($sifreGuncelle) {
                    $hashedPassword = password_hash($yeni_sifre, PASSWORD_DEFAULT);
                    $updateSql = "UPDATE users SET username = ?, email = ?, password = ?, profile_image = ? WHERE id = ?";
                    $updateStmt = $pdo->prepare($updateSql);
                    $sonuc = $updateStmt->execute([$username, $email, $hashedPassword, $resimYolu, $_SESSION['user_id']]);
                } else {
                    $updateSql = "UPDATE users SET username = ?, email = ?, profile_image = ? WHERE id = ?";
                    $updateStmt = $pdo->prepare($updateSql);
                    $sonuc = $updateStmt->execute([$username, $email, $resimYolu, $_SESSION['user_id']]);
                }

                if ($sonuc) {
                    $basari = "Profil başarıyla güncellendi.";
                    // Güncel veriyi yansıt
                    $user['username'] = $username;
                    $user['email'] = $email;
                    $_SESSION['username'] = $username;
                    if (isset($resimYolu)) $user['profile_image'] = $resimYolu;
                } else {
                    $hata = "Güncelleme hatası.";
                }
            }
        }
    }
}
?>

<!DOCTYPE html>
<html lang="tr">
<head>
    <meta charset="UTF-8">
    <title>Profil Düzenle - Admin</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link href="style.css" rel="stylesheet">
</head>
<body>
    <nav class="navbar navbar-dark bg-dark mb-4">
        <div class="container">
            <a class="navbar-brand" href="index.php">Yönetim Paneli</a>
            
            <div class="d-flex align-items-center ms-auto">
                <a href="../index.php" class="btn btn-outline-light btn-sm me-3" target="_blank">Siteyi Gör</a>
                
                 <?php 
                 // Admin profil fotosunu çek
                 if (isset($_SESSION['user_id'])) {
                     $adminImg = ($user['profile_image']) ? '../'.$user['profile_image'] : 'https://via.placeholder.com/30?text=A';
                     $adminName = $user['username'] ?? 'Admin';
                 }
                 ?>
                 
                <div class="dropdown">
                    <a class="text-white text-decoration-none dropdown-toggle d-flex align-items-center" href="#" id="adminDropdown" role="button" data-bs-toggle="dropdown" aria-expanded="false">
                        <img src="<?= htmlspecialchars($adminImg) ?>" class="rounded-circle me-2" style="width:32px; height:32px; object-fit:cover; border:2px solid rgba(255,255,255,0.2);">
                        <span><?= htmlspecialchars($adminName) ?></span>
                    </a>
                    <ul class="dropdown-menu dropdown-menu-end shadow" aria-labelledby="adminDropdown">
                        <li><a class="dropdown-item" href="profile.php"><i class="bi bi-person me-2"></i>Profili Düzenle</a></li>
                        <li><hr class="dropdown-divider"></li>
                        <li><a class="dropdown-item text-danger" href="logout.php"><i class="bi bi-box-arrow-right me-2"></i>Çıkış Yap</a></li>
                    </ul>
                </div>
            </div>
        </div>
    </nav>

    <div class="container">
        <div class="row justify-content-center">
            <div class="col-md-6">
                <div class="card shadow">
                    <div class="card-header bg-primary text-white">
                        <h5 class="mb-0">Admin Profilini Düzenle</h5>
                    </div>
                    <div class="card-body">
                        <?php if ($hata): ?>
                            <div class="alert alert-danger"><?php echo $hata; ?></div>
                        <?php endif; ?>
                        <?php if ($basari): ?>
                            <div class="alert alert-success"><?php echo $basari; ?></div>
                        <?php endif; ?>

                        <form method="POST" enctype="multipart/form-data">
                            <input type="hidden" name="profil_guncelle" value="1">
                            <div class="mb-3 text-center">
                                <?php 
                                // Resim yolunu admin klasöründen çıkacak şekilde ayarla
                                $img = $user['profile_image'] ? '../' . $user['profile_image'] : 'https://via.placeholder.com/150?text=Profil';
                                ?>
                                <img src="<?php echo htmlspecialchars($img); ?>" alt="Profil Resmi" class="rounded-circle mb-3 shadow-sm" style="width: 100px; height: 100px; object-fit: cover;">
                                <div>
                                    <label class="btn btn-sm btn-outline-primary">
                                        Fotoğraf Değiştir <input type="file" name="profile_image" style="display: none;" onchange="this.form.submit()" accept="image/*">
                                    </label>
                                </div>
                            </div>

                            <div class="mb-3">
                                <label class="form-label">Kullanıcı Adı</label>
                                <input type="text" name="username" class="form-control" value="<?php echo htmlspecialchars($user['username']); ?>" required>
                            </div>
                            
                            <div class="mb-3">
                                <label class="form-label">Email Adresi</label>
                                <input type="email" name="email" class="form-control" value="<?php echo htmlspecialchars($user['email']); ?>" required>
                            </div>

                            <hr class="my-4">
                            <h6 class="mb-3">Şifre Değiştir (İsteğe Bağlı)</h6>

                            <div class="mb-3">
                                <label class="form-label">Yeni Şifre</label>
                                <input type="password" name="new_password" class="form-control" placeholder="Değiştirmek istemiyorsanız boş bırakın">
                            </div>

                            <div class="mb-3">
                                <label class="form-label">Yeni Şifre (Tekrar)</label>
                                <input type="password" name="confirm_password" class="form-control" placeholder="Yeni şifreyi tekrar girin">
                            </div>

                            <div class="d-grid gap-2">
                                <button type="submit" name="profil_guncelle" class="btn btn-primary">Değişiklikleri Kaydet</button>
                                <a href="index.php" class="btn btn-secondary">İptal</a>
                            </div>
                        </form>
                    </div>
                </div>
            </div>
        </div>
    </div>
    
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>
