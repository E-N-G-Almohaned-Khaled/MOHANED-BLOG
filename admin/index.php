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

// Yazı Ekleme
// Yazı Ekleme
if ($_SERVER['REQUEST_METHOD'] == 'POST' && isset($_POST['yazi_ekle'])) {
    $baslik = trim($_POST['title']);
    $icerik = trim($_POST['content']);
    
    // Resim Yükleme (Opsiyonel)
    $resimYolu = '';
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
            $hata = 'Başlık ve içerik doldurulmalı.';
        } else {
            // Eğer resim yüklenmediyse, post edilen eski text inputu veya boşluk (eski inputu kaldırdım ama mantıken formda yoksa null gelir)
            // Formdan artık text olarak url gelmeyecek, sadece file.
            
            $sql = "INSERT INTO posts (title, content, image_path, author_id) VALUES (?, ?, ?, ?)";
            $stmt = $pdo->prepare($sql);
            if ($stmt->execute([$baslik, $icerik, $resimYolu, $_SESSION['user_id']])) {
                $basari = 'Yazı eklendi.';
            } else {
                $hata = 'Veritabanı hatası.';
            }
        }
    }
}

// Yazı Silme
if (isset($_GET['sil_post'])) {
    $stmt = $pdo->prepare("DELETE FROM posts WHERE id = ?");
    $stmt->execute([$_GET['sil_post']]);
    header("Location: index.php"); // Temiz URL için
    exit;
}

// Yorum Silme
if (isset($_GET['sil_comment'])) {
    $stmt = $pdo->prepare("DELETE FROM comments WHERE id = ?");
    $stmt->execute([$_GET['sil_comment']]);
    header("Location: index.php");
    exit;
}

// Verileri Çek
$posts = $pdo->query("SELECT posts.*, users.username FROM posts JOIN users ON posts.author_id = users.id ORDER BY created_at DESC")->fetchAll();
$comments = $pdo->query("SELECT comments.*, users.username, posts.title as post_title FROM comments JOIN users ON comments.user_id = users.id JOIN posts ON comments.post_id = posts.id ORDER BY created_at DESC LIMIT 20")->fetchAll();
?>

<!DOCTYPE html>
<html lang="tr">
<head>
    <meta charset="UTF-8">
    <title>Admin Paneli</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <!-- Google Fonts -->
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@400;500;600&family=Outfit:wght@500;700&display=swap" rel="stylesheet">
    <!-- Summernote CSS -->
    <link href="https://cdn.jsdelivr.net/npm/summernote@0.8.18/dist/summernote-lite.min.css" rel="stylesheet">
    <link href="style.css" rel="stylesheet">
</head>
<body>
    <nav class="navbar navbar-dark bg-dark mb-4">
        <div class="container">
            <a class="navbar-brand" href="#">Yönetim Paneli</a>
            
            <div class="d-flex align-items-center ms-auto">
                <a href="../index.php" class="btn btn-outline-light btn-sm me-3" target="_blank">Siteyi Gör</a>
                
                 <?php 
                 // Admin profil fotosunu çek (Session'da yoksa DB'den)
                 if (isset($_SESSION['user_id'])) {
                     $adminStmt = $pdo->prepare("SELECT profile_image, username FROM users WHERE id = ?");
                     $adminStmt->execute([$_SESSION['user_id']]);
                     $adminUser = $adminStmt->fetch();
                     $adminImg = ($adminUser && $adminUser['profile_image']) ? '../'.$adminUser['profile_image'] : 'https://via.placeholder.com/30?text=A';
                     $adminName = $adminUser['username'] ?? 'Admin';
                 } else {
                     $adminImg = 'https://via.placeholder.com/30?text=A';
                     $adminName = 'Admin';
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
        <div class="row">
            <!-- Yazı Ekle -->
            <div class="col-md-4">
                <div class="card shadow-sm mb-4">
                    <div class="card-header bg-primary text-white">Yazı Ekle</div>
                    <div class="card-body">
                        <?php if($hata) echo "<div class='alert alert-danger'>$hata</div>"; ?>
                        <?php if($basari) echo "<div class='alert alert-success'>$basari</div>"; ?>
                        <form method="POST" enctype="multipart/form-data">
                            <input type="text" name="title" class="form-control mb-2" placeholder="Başlık" required>
                            
                            <label class="form-label small text-muted">Kapak Resmi</label>
                            <input type="file" name="image" class="form-control mb-2" accept="image/*">
                            
                            <textarea id="summernote" name="content" class="form-control mb-2" required></textarea>
                            <button type="submit" name="yazi_ekle" class="btn btn-success w-100">Yayınla</button>
                        </form>
                    </div>
                </div>

                <!-- Son Yorumlar -->
                <div class="card shadow-sm">
                    <div class="card-header bg-warning">Son Yorumlar</div>
                    <ul class="list-group list-group-flush">
                        <?php foreach($comments as $c): ?>
                            <li class="list-group-item">
                                <small><b><?= htmlspecialchars($c['username']) ?></b>: <?= htmlspecialchars(substr($c['content'],0,30)) ?>...</small>
                                <a href="?sil_comment=<?= $c['id'] ?>" onclick="return confirm('Sil?')" class="float-end text-danger">&times;</a>
                            </li>
                        <?php endforeach; ?>
                    </ul>
                </div>
            </div>

            <!-- Yazı Listesi -->
            <div class="col-md-8">
                <div class="card shadow-sm">
                    <div class="card-header bg-secondary text-white">Tüm Yazılar</div>
                    <div class="card-body">
                        <table class="table table-sm">
                            <thead><tr><th>ID</th><th>Başlık</th><th>Yazar</th><th>Tarih</th><th>İşlem</th></tr></thead>
                            <tbody>
                                <?php foreach($posts as $p): ?>
                                    <tr>
                                        <td><?= $p['id'] ?></td>
                                        <td><?= htmlspecialchars($p['title']) ?></td>
                                        <td><span class="badge bg-secondary"><?= htmlspecialchars($p['username']) ?></span></td>
                                        <td><?= date('d.m.Y', strtotime($p['created_at'])) ?></td>
                                        <td>
                                            <a href="../index.php?sayfa=detay&id=<?= $p['id'] ?>" target="_blank" class="btn btn-sm btn-info text-white">Gör</a>
                                            <a href="edit_post.php?id=<?= $p['id'] ?>" class="btn btn-sm btn-warning">Düzenle</a>
                                            <a href="?sil_post=<?= $p['id'] ?>" onclick="return confirm('Sil?')" class="btn btn-sm btn-danger">Sil</a>
                                        </td>
                                    </tr>
                                <?php endforeach; ?>
                            </tbody>
                        </table>
                    </div>
                </div>
            </div>
        </div>
    </div>
    <!-- JQuery & Summernote JS -->
    <script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script> <!-- Bootstrap JS Eklendi -->
    <script src="https://cdn.jsdelivr.net/npm/summernote@0.8.18/dist/summernote-lite.min.js"></script>
    <script>
      $('#summernote').summernote({
        placeholder: 'İçerik giriniz...',
        tabsize: 2,
        height: 200,
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
