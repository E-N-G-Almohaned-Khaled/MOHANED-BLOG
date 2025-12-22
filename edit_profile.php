<?php
// Giriş yapmış mı kontrol et
if (!isset($_SESSION['user_id'])) {
    header("Location: index.php?sayfa=giris");
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
        $uploadDir = 'uploads/';
        if (!file_exists($uploadDir)) {
            mkdir($uploadDir, 0777, true);
        }
        $fileExt = strtolower(pathinfo($_FILES['profile_image']['name'], PATHINFO_EXTENSION));
        $allowed = ['jpg', 'jpeg', 'png', 'gif', 'webp', 'bmp', 'tiff', 'svg', 'ico'];
        
        if (in_array($fileExt, $allowed)) {
            $newName = uniqid('profile_' . $_SESSION['user_id'] . '_') . '.' . $fileExt;
            if (move_uploaded_file($_FILES['profile_image']['tmp_name'], $uploadDir . $newName)) {
                $resimYolu = 'uploads/' . $newName;
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
        // Email veya Kullanıcı Adı başka kullanıcıda var mı? (Kendisi hariç)
        $kontrolStmt = $pdo->prepare("SELECT id FROM users WHERE (email = ? OR username = ?) AND id != ?");
        $kontrolStmt->execute([$email, $username, $_SESSION['user_id']]);
        if ($kontrolStmt->rowCount() > 0) {
            $hata = "Bu email adresi veya kullanıcı adı zaten kullanılıyor.";
        } else {
            // Şifre güncelleme var mı?
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
                    $_SESSION['username'] = $username; // Navbar'daki ismi güncelle
                    if (isset($resimYolu)) $user['profile_image'] = $resimYolu;
                } else {
                    $hata = "Güncelleme sırasında bir hata oluştu.";
                }
            }
        }
    }
}
?>

<div class="row justify-content-center">
    <div class="col-md-6">
        <div class="card shadow">
            <div class="card-header bg-primary text-white">
                <h5 class="mb-0">Profili Düzenle</h5>
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
                        $img = $user['profile_image'] ? $user['profile_image'] : 'https://via.placeholder.com/150?text=Profil';
                        ?>
                        <img src="<?php echo htmlspecialchars($img); ?>" alt="Profil Resmi" class="rounded-circle mb-3 shadow-sm" style="width: 100px; height: 100px; object-fit: cover;">
                        <div>
                            <label class="btn btn-sm btn-outline-primary">
                                Fotoğraf Değiştir <input type="file" name="profile_image" style="display: none;" onchange="this.form.submit()" accept="image/*">
                            </label>
                            <!-- Not: Otomatik submit istemiyorsanız onchange'i kaldırın -->
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
                        <input type="password" name="new_password" class="form-control" placeholder="Değiştirmek istemiyorsanız boş bırakın" autocomplete="new-password">
                    </div>

                    <div class="mb-3">
                        <label class="form-label">Yeni Şifre (Tekrar)</label>
                        <input type="password" name="confirm_password" class="form-control" placeholder="Yeni şifreyi tekrar girin" autocomplete="new-password">
                    </div>

                    <button type="submit" name="profil_guncelle" class="btn btn-primary w-100">Değişiklikleri Kaydet</button>
                </form>
            </div>
        </div>
    </div>
</div>
