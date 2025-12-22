<?php
// Giriş yapmış mı kontrol et
if (!isset($_SESSION['user_id'])) {
    // Giriş yapmamışsa login sayfasına at
    header("Location: index.php?sayfa=giris");
    exit;
}

$hata = '';
$basari = '';

// Yazı Ekleme
// Yazı Ekleme
if ($_SERVER['REQUEST_METHOD'] == 'POST' && isset($_POST['yazi_paylas'])) {
    $baslik = trim($_POST['title']);
    // Kullanıcıdan gelen içeriği XSS'e karşı temizle (Sadece metin olarak kaydet)
    $icerik = htmlspecialchars(trim($_POST['content']));
    
    // Resim Yükleme (Opsiyonel)
    $resimYolu = '';
    if (isset($_FILES['image']) && $_FILES['image']['error'] == 0) {
        $uploadDir = 'uploads/';
        if (!file_exists($uploadDir)) {
            mkdir($uploadDir, 0777, true);
        }
        $fileExt = strtolower(pathinfo($_FILES['image']['name'], PATHINFO_EXTENSION));
        $allowed = ['jpg', 'jpeg', 'png', 'gif', 'webp', 'bmp', 'tiff', 'svg', 'ico'];
        
        if (in_array($fileExt, $allowed)) {
            $newName = uniqid('post_') . '.' . $fileExt;
            if (move_uploaded_file($_FILES['image']['tmp_name'], $uploadDir . $newName)) {
                $resimYolu = 'uploads/' . $newName;
            } else {
                $hata = "Dosya yüklenemedi.";
            }
        } else {
            $hata = "Geçersiz dosya formatı.";
        }
    }

    if (empty($hata)) {
        if (empty($baslik) || empty($icerik)) {
            $hata = 'Başlık ve içerik alanları zorunludur.';
        } else {
            $sql = "INSERT INTO posts (title, content, image_path, author_id) VALUES (?, ?, ?, ?)";
            $stmt = $pdo->prepare($sql);
            
            if ($stmt->execute([$baslik, $icerik, $resimYolu, $_SESSION['user_id']])) {
                $basari = 'Yazınız başarıyla paylaşıldı!';
            } else {
                $hata = 'Yazı paylaşılırken bir hata oluştu.';
            }
        }
    }
}
?>

<div class="row justify-content-center">
    <div class="col-md-8">
        <div class="card shadow">
            <div class="card-header bg-success text-white">
                <h3 class="mb-0"><i class="bi bi-pencil-square"></i> Düşünceni Paylaş</h3>
            </div>
            <div class="card-body p-4">
                <?php if ($hata): ?>
                    <div class="alert alert-danger"><?php echo $hata; ?></div>
                <?php endif; ?>
                <?php if ($basari): ?>
                    <div class="alert alert-success">
                        <?php echo $basari; ?>
                        <br>
                        <a href="index.php" class="alert-link">Ana Sayfaya Dön</a> veya yeni bir tane daha yaz.
                    </div>
                <?php endif; ?>

                    <form method="POST" action="" enctype="multipart/form-data">
                        <div class="mb-3">
                            <label for="title" class="form-label">Konu Başlığı</label>
                            <input type="text" class="form-control form-control-lg" id="title" name="title" placeholder="Hakkında konuşmak istediğin konu ne?" required>
                        </div>
                        
                        <div class="mb-3">
                            <label for="image" class="form-label">Görsel (İsteğe bağlı)</label>
                            <input type="file" class="form-control" id="image" name="image" accept="image/*">
                            <div class="form-text">Görsel eklemek yazılarını daha ilgi çekici yapar.</div>
                        </div>
                    
                    <div class="mb-3">
                        <label for="content" class="form-label">Düşüncelerin</label>
                        <textarea class="form-control" id="content" name="content" rows="8" placeholder="Buraya içini dökebilirsin..." required></textarea>
                    </div>
                    
                    <div class="d-grid">
                        <button type="submit" name="yazi_paylas" class="btn btn-success btn-lg">Paylaş</button>
                    </div>
                </form>
            </div>
        </div>
    </div>
</div>
