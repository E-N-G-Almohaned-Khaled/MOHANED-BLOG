<?php
session_start();
require_once '../config.php';

// Yetki Kontrolü
if (!isset($_SESSION['user_id']) || $_SESSION['role'] !== 'admin') {
    header("Location: login.php");
    exit;
}

$id = isset($_GET['id']) ? (int)$_GET['id'] : 0;
$hata = '';
$basari = '';

// Mevcut veriyi çek
$stmt = $pdo->prepare("SELECT * FROM posts WHERE id = ?");
$stmt->execute([$id]);
$post = $stmt->fetch();

if (!$post) {
    die("Yazı bulunamadı!");
}

// Güncelleme İşlemi
// Güncelleme İşlemi
if ($_SERVER['REQUEST_METHOD'] == 'POST' && isset($_POST['guncelle'])) {
    $baslik = trim($_POST['title']);
    $icerik = trim($_POST['content']);
    $resim = $post['image_path']; // Varsayılan olarak eskisi kalsın

    // Yeni resim yüklendi mi?
    if (isset($_FILES['image']) && $_FILES['image']['error'] == 0) {
        $uploadDir = '../uploads/';
        if (!file_exists($uploadDir)) {
            mkdir($uploadDir, 0777, true);
        }
        $fileExt = strtolower(pathinfo($_FILES['image']['name'], PATHINFO_EXTENSION));
        $allowed = ['jpg', 'jpeg', 'png', 'gif', 'webp', 'bmp', 'tiff', 'svg', 'ico'];
        
        if (in_array($fileExt, $allowed)) {
            $newName = uniqid('post_') . '.' . $fileExt;
            if (move_uploaded_file($_FILES['image']['tmp_name'], $uploadDir . $newName)) {
                $resim = 'uploads/' . $newName; // Yeni resim yolu
            }
        }
    }

    if (empty($baslik) || empty($icerik)) {
        $hata = 'Başlık ve içerik zorunludur.';
    } else {
        $sql = "UPDATE posts SET title = ?, content = ?, image_path = ? WHERE id = ?";
        $updateStmt = $pdo->prepare($sql);
        if ($updateStmt->execute([$baslik, $icerik, $resim, $id])) {
            $basari = 'Yazı başarıyla güncellendi. <a href="index.php">Panele Dön</a>';
            // Güncel veriyi yansıtmak için değişkenleri güncelle
            $post['title'] = $baslik;
            $post['content'] = $icerik;
            $post['image_path'] = $resim;
        } else {
            $hata = 'Güncelleme sırasında hata oluştu.';
        }
    }
}
?>

<!DOCTYPE html>
<html lang="tr">
<head>
    <meta charset="UTF-8">
    <title>Yazı Düzenle - Admin</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <!-- Summernote CSS -->
    <link href="https://cdn.jsdelivr.net/npm/summernote@0.8.18/dist/summernote-lite.min.css" rel="stylesheet">
    <link href="style.css" rel="stylesheet">
</head>
<body>
    <nav class="navbar navbar-dark bg-dark mb-4">
        <div class="container">
            <a class="navbar-brand" href="index.php">Yönetim Paneli</a>
            
            <div class="d-flex align-items-center ms-auto">
                <a href="index.php" class="btn btn-outline-light btn-sm me-3">Geri Dön</a>
                
                 <?php 
                 if (isset($_SESSION['user_id'])) {
                     $uStmt = $pdo->prepare("SELECT profile_image, username FROM users WHERE id = ?");
                     $uStmt->execute([$_SESSION['user_id']]);
                     $uRow = $uStmt->fetch();
                     $adminImg = ($uRow && $uRow['profile_image']) ? '../'.$uRow['profile_image'] : 'https://via.placeholder.com/30?text=A';
                     $adminName = $uRow['username'] ?? 'Admin';
                 }
                 ?>
                 
                <div class="dropdown">
                    <a class="text-white text-decoration-none dropdown-toggle d-flex align-items-center" href="#" id="adminDropdown" role="button" data-bs-toggle="dropdown" aria-expanded="false">
                        <img src="<?= htmlspecialchars($adminImg) ?>" class="rounded-circle me-2" style="width:32px; height:32px; object-fit:cover; border:2px solid rgba(255,255,255,0.2);">
                        <span><?= htmlspecialchars($adminName) ?></span>
                    </a>
                    <ul class="dropdown-menu dropdown-menu-end shadow" aria-labelledby="adminDropdown">
                        <li><a class="dropdown-item" href="profile.php"><i class="bi bi-person me-2"></i>Profili Düzenle</a></li>
                        <li><a class="dropdown-item" href="../index.php" target="_blank"><i class="bi bi-eye me-2"></i>Siteyi Gör</a></li>
                        <li><hr class="dropdown-divider"></li>
                        <li><a class="dropdown-item text-danger" href="logout.php"><i class="bi bi-box-arrow-right me-2"></i>Çıkış Yap</a></li>
                    </ul>
                </div>
            </div>
        </div>
    </nav>

    <div class="container">
        <div class="row justify-content-center">
            <div class="col-md-8">
                <div class="card shadow-sm">
                    <div class="card-header bg-warning text-dark">
                        <h5 class="mb-0">Yazıyı Düzenle</h5>
                    </div>
                    <div class="card-body">
                        <?php if ($hata): ?>
                            <div class="alert alert-danger"><?php echo $hata; ?></div>
                        <?php endif; ?>
                        <?php if ($basari): ?>
                            <div class="alert alert-success"><?php echo $basari; ?></div>
                        <?php endif; ?>

                        <form method="POST" enctype="multipart/form-data">
                            <div class="mb-3">
                                <label class="form-label">Başlık</label>
                                <input type="text" name="title" class="form-control" value="<?php echo htmlspecialchars($post['title']); ?>" required>
                            </div>
                            
                            <div class="mb-3">
                                <label class="form-label">Mevcut Resim</label>
                                <?php if($post['image_path']): ?>
                                    <div class="mb-2">
                                        <img src="../<?php echo htmlspecialchars($post['image_path']); ?>" alt="Mevcut Resim" style="max-height: 150px; border-radius: 8px;">
                                    </div>
                                <?php endif; ?>
                                <label class="form-label text-muted small">Resmi Değiştir (İsteğe bağlı)</label>
                                <input type="file" name="image" class="form-control" accept="image/*">
                            </div>
                            
                            <div class="mb-3">
                                <label class="form-label">İçerik</label>
                                <textarea id="summernote" name="content" class="form-control" required><?php echo htmlspecialchars($post['content']); ?></textarea>
                            </div>
                            
                            <button type="submit" name="guncelle" class="btn btn-primary w-100">Değişiklikleri Kaydet</button>
                        </form>
                    </div>
                </div>
            </div>
        </div>
    </div>
    </div>
    
    <!-- JQuery & Summernote JS -->
    <script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
    <script src="https://cdn.jsdelivr.net/npm/summernote@0.8.18/dist/summernote-lite.min.js"></script>
    <script>
      $('#summernote').summernote({
        placeholder: 'İçerik giriniz...',
        tabsize: 2,
        height: 300,
        toolbar: [
            ['style', ['style']],
            ['font', ['bold', 'underline', 'clear']],
            ['color', ['color']],
            ['para', ['ul', 'ol', 'paragraph']],
            ['table', ['table']],
            ['insert', ['link', 'picture', 'video']],
            ['view', ['fullscreen', 'codeview', 'help']]
        ],
        callbacks: {
            onImageUpload: function(files) {
                for(let i=0; i < files.length; i++) {
                    uploadImage(files[i]);
                }
            }
        }
      });

      function uploadImage(file) {
        let data = new FormData();
        data.append("file", file);
        $.ajax({
            url: 'upload_image.php',
            cache: false,
            contentType: false,
            processData: false,
            data: data,
            type: "post",
            success: function(url) {
                var image = $('<img>').attr('src', url);
                $('#summernote').summernote("insertNode", image[0]);
            },
            error: function(data) {
                console.log(data);
                alert("Resim yüklenemedi.");
            }
        });
      }
    </script>
</body>
</html>
