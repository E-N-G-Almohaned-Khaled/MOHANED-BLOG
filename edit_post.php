<?php
// Oturumu başlat
$sessionPath = __DIR__ . '/sessions';
if (!file_exists($sessionPath)) {
    mkdir($sessionPath, 0777, true);
}
session_save_path($sessionPath);
session_start();

require_once 'config.php';

// Giriş yapmış mı kontrol et
if (!isset($_SESSION['user_id'])) {
    header("Location: index.php?sayfa=giris");
    exit;
}

$id = isset($_GET['id']) ? (int)$_GET['id'] : 0;
$hata = '';
$basari = '';

// Yazıyı çek
$stmt = $pdo->prepare("SELECT * FROM posts WHERE id = ?");
$stmt->execute([$id]);
$post = $stmt->fetch();

if (!$post) {
    die("Yazı bulunamadı.");
}

// Yetki Kontrolü: Sadece yazar veya admin düzenleyebilir
if ($post['author_id'] != $_SESSION['user_id'] && $_SESSION['role'] !== 'admin') {
    die("Bu yazıyı düzenleme yetkiniz yok.");
}

// Kaydetme İşlemi
if ($_SERVER['REQUEST_METHOD'] == 'POST' && isset($_POST['guncelle'])) {
    $baslik = trim($_POST['title']);
    $icerik = trim($_POST['content']); // HTML izin verilecek mi?create_post'ta htmlspecialchars yapılmış.
    // create_post.php'de htmlspecialchars ile temizlenmişti. Burada da aynısını yapalım tutarlılık için.
    // Ancak editörde htmlspecialchars'lı veri gelince tekrar encode olmaması lazım.
    // Kullanıcıya gösterirken decode yapacağız veya textarea içinde ham hali göstereceğiz.
    
    // Basitlik için create_post mantığını koruyalım:
    $cleanContent = strip_tags($_POST['content']); // Şimdilik düz metin gibi davranalım ya da minimal koruma
    // *Düzeltme*: create_post htmlspecialchars kullanıyor. Biz de öyle yapalım.
    $icerik = htmlspecialchars($_POST['content']);

    $resimYolu = $post['image_path'];

    // Yeni Resim Yüklendi mi?
    if (isset($_FILES['image']) && $_FILES['image']['error'] == 0) {
        $uploadDir = 'uploads/';
        if (!file_exists($uploadDir)) {
            mkdir($uploadDir, 0777, true);
        }
        $fileExt = strtolower(pathinfo($_FILES['image']['name'], PATHINFO_EXTENSION));
        $allowed = ['jpg', 'jpeg', 'png', 'gif', 'webp'];
        
        if (in_array($fileExt, $allowed)) {
            $newName = uniqid('post_') . '.' . $fileExt;
            if (move_uploaded_file($_FILES['image']['tmp_name'], $uploadDir . $newName)) {
                $resimYolu = 'uploads/' . $newName;
            } else {
                $hata = "Resim yüklenirken hata oluştu.";
            }
        } else {
            $hata = "Geçersiz resim formatı.";
        }
    }

    if (empty($hata)) {
        $sql = "UPDATE posts SET title = ?, content = ?, image_path = ? WHERE id = ?";
        $updateStmt = $pdo->prepare($sql);
        if ($updateStmt->execute([$baslik, $icerik, $resimYolu, $id])) {
            $basari = "Yazı başarıyla güncellendi.";
            // Güncel veriyi yansıt
            $post['title'] = $baslik;
            $post['content'] = $icerik; // Dikkat: Bu encoded halde
            $post['image_path'] = $resimYolu;
        } else {
            $hata = "Veritabanı güncellenemedi.";
        }
    }
}
?>
<!DOCTYPE html>
<html lang="tr">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Yazıyı Düzenle - <?php echo htmlspecialchars($post['title']); ?></title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link href="style.css" rel="stylesheet">
</head>
<body>
    <?php 
    // Navbar will be manually added below
    ?>
    
    <!-- Navbar Manuel (Kısa Versiyon) -->
    <nav class="navbar navbar-expand-lg navbar-dark bg-dark mb-4">
        <div class="container">
            <a class="navbar-brand" href="index.php">BlogProjesi</a>
            <a href="index.php?sayfa=detay&id=<?php echo $id; ?>" class="btn btn-outline-light btn-sm">Geri Dön</a>
        </div>
    </nav>

    <div class="container my-5">
        <div class="row justify-content-center">
            <div class="col-md-8">
                <div class="card shadow glass-card">
                    <div class="card-header bg-primary text-white">
                        <h3 class="mb-0">Yazıyı Düzenle</h3>
                    </div>
                    <div class="card-body">
                        <?php if ($hata): ?>
                            <div class="alert alert-danger"><?php echo $hata; ?></div>
                        <?php endif; ?>
                        <?php if ($basari): ?>
                            <div class="alert alert-success">
                                <?php echo $basari; ?> <a href="index.php?sayfa=detay&id=<?php echo $id; ?>" class="alert-link">Yazıyı Görüntüle</a>
                            </div>
                        <?php endif; ?>

                        <form method="POST" enctype="multipart/form-data">
                            <div class="mb-3">
                                <label class="form-label">Başlık</label>
                                <input type="text" name="title" class="form-control" value="<?php echo htmlspecialchars($post['title']); ?>" required>
                            </div>

                            <div class="mb-3">
                                <label class="form-label">Mevcut Görsel</label><br>
                                <?php if($post['image_path']): ?>
                                    <img src="<?php echo htmlspecialchars($post['image_path']); ?>" style="max-height: 200px;" class="rounded mb-2">
                                <?php else: ?>
                                    <p class="text-muted">Görsel yok.</p>
                                <?php endif; ?>
                                <input type="file" name="image" class="form-control">
                                <div class="form-text">Değiştirmek istiyorsanız yeni bir dosya seçin.</div>
                            </div>

                            <div class="mb-3">
                                <label class="form-label">İçerik</label>
                                <!-- create_post.php htmlspecialchars ile kaydettiği için, textarea içine koyarken decode etmeliyiz ki &lt; olarak görünmesin -->
                                <textarea name="content" class="form-control" rows="10" required><?php echo htmlspecialchars_decode($post['content']); ?></textarea>
                            </div>

                            <div class="d-grid gap-2">
                                <button type="submit" name="guncelle" class="btn btn-primary btn-lg">Değişiklikleri Kaydet</button>
                                <a href="index.php?sayfa=detay&id=<?php echo $id; ?>" class="btn btn-secondary">İptal</a>
                            </div>
                        </form>
                    </div>
                </div>
            </div>
        </div>
    </div>
</body>
</html>
