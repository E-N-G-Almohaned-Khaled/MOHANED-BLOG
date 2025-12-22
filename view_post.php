<?php
$id = isset($_GET['id']) ? (int)$_GET['id'] : 0;

// Yorum Gönderme İşlemi
if ($_SERVER['REQUEST_METHOD'] == 'POST' && isset($_POST['yorum_yap'])) {
    if (isset($_SESSION['user_id'])) {
        $yorum = trim($_POST['comment']);
        if (!empty($yorum)) {
            $stmt = $pdo->prepare("INSERT INTO comments (post_id, user_id, content) VALUES (?, ?, ?)");
            $stmt->execute([$id, $_SESSION['user_id'], $yorum]);
            // Sayfayı yenile ki tekrar gönderim olmasın
            header("Location: index.php?sayfa=detay&id=$id");
            exit;
        }
    }
}

// Beğeni / Dislike İşlemi API üzerinden yapılıyor (AJAX)

if ($id > 0) {
    // 1. Veri index.php tarafından önden çekildiyse onu kullan.
    // 2. Yoksa kendimiz çekelim (direct call durumu için).
    if (isset($fetchedPost) && $fetchedPost) {
        $yazi = $fetchedPost;
    } else {
        $stmt = $pdo->prepare("SELECT posts.*, users.username FROM posts JOIN users ON posts.author_id = users.id WHERE posts.id = ?");
        $stmt->execute([$id]);
        $yazi = $stmt->fetch();
    }
    
    if ($yazi) {
        $resim = $yazi['image_path'] ? $yazi['image_path'] : 'https://via.placeholder.com/800x400?text=Resim+Yok';

        // Yorumları Çek
        $stmt_comments = $pdo->prepare("SELECT comments.*, users.username, users.profile_image FROM comments JOIN users ON comments.user_id = users.id WHERE post_id = ? ORDER BY created_at DESC");
        $stmt_comments->execute([$id]);
        $yorumlar = $stmt_comments->fetchAll();
?>
        <div class="row justify-content-center">
            <div class="col-lg-8">
                <!-- Başlık ve Meta Bilgiler -->
                <h1 class="mb-3"><?php echo htmlspecialchars($yazi['title']); ?></h1>
                <div class="text-muted mb-4">
                    <small>
                        <span class="me-3"><i class="bi bi-person"></i> Yazar: <?php echo htmlspecialchars($yazi['username']); ?></span>
                        <span><i class="bi bi-calendar"></i> Tarih: <?php echo date('d F Y', strtotime($yazi['created_at'])); ?></span>
                    </small>
                </div>

                <!-- Görsel -->
                <div class="mb-4">
                    <img src="<?php echo htmlspecialchars($resim); ?>" class="img-fluid rounded shadow-sm w-100" alt="Yazı Görseli">
                </div>

                <!-- Etkileşim Butonları (Görsel Altı - Büyük) -->
                <div class="d-flex align-items-center justify-content-between mb-4 p-3 bg-light rounded shadow-sm">
                    <div class="d-flex gap-3">
                        <button onclick="react('like')" id="btn-like" class="btn btn-outline-success btn-lg rounded-pill px-4">
                            <i class="bi bi-hand-thumbs-up fs-4"></i> <span class="fw-bold" id="like-count"><?php echo $yazi['likes']; ?></span>
                        </button>
                        <button onclick="react('dislike')" id="btn-dislike" class="btn btn-outline-danger btn-lg rounded-pill px-4">
                            <i class="bi bi-hand-thumbs-down fs-4"></i> <span class="fw-bold" id="dislike-count"><?php echo $yazi['dislikes']; ?></span>
                        </button>
                    </div>
                    <div class="d-flex gap-2">
                        <a href="#comments-section" class="btn btn-outline-secondary btn-lg rounded-pill px-4">
                            <i class="bi bi-chat-dots fs-4"></i>
                        </a>
                        <button onclick="sharePost()" class="btn btn-outline-primary btn-lg rounded-pill px-4">
                            <i class="bi bi-share fs-4"></i>
                        </button>
                    </div>
                </div>

                <!-- İçerik -->
                <div class="blog-content fs-5 lh-lg">
                    <?php echo $yazi['content']; ?>
                </div>

                <!-- Etkileşim Butonları -->

                
                <hr class="my-5">

                <!-- Yorum Alanı -->
                <div class="comments-section" id="comments-section">
                    <h3 class="mb-4">Yorumlar (<?php echo count($yorumlar); ?>)</h3>
                    
                    <!-- Yorum Formu -->
                    <?php if (isset($_SESSION['user_id'])): ?>
                        <div class="card mb-4">
                            <div class="card-body">
                                <form method="POST" action="">
                                    <div class="mb-3">
                                        <label for="comment" class="form-label">Düşünceni Paylaş</label>
                                        <textarea class="form-control" id="comment" name="comment" rows="3" required></textarea>
                                    </div>
                                    <button type="submit" name="yorum_yap" class="btn btn-primary btn-sm">Yorum Yap</button>
                                </form>
                            </div>
                        </div>
                    <?php else: ?>
                        <div class="alert alert-info">
                            Yorum yapmak için <a href="index.php?sayfa=giris">giriş yapmalısınız</a>.
                        </div>
                    <?php endif; ?>

                    <!-- Yorum Listesi -->
                    <?php foreach ($yorumlar as $yorum): 
                        $cImg = $yorum['profile_image'] ? $yorum['profile_image'] : 'https://via.placeholder.com/50?text=User';
                    ?>
                        <div class="d-flex mb-3">
                            <div class="flex-shrink-0">
                                <img src="<?php echo htmlspecialchars($cImg); ?>" class="rounded-circle shadow-sm" alt="User" style="width: 50px; height: 50px; object-fit: cover;">
                            </div>
                            <div class="flex-grow-1 ms-3">
                                <div class="card border-0 shadow-sm bg-light">
                                    <div class="card-body py-2 px-3">
                                        <div class="d-flex justify-content-between align-items-center mb-1">
                                            <h6 class="card-title mb-0 fw-bold"><?php echo htmlspecialchars($yorum['username']); ?></h6>
                                            <small class="text-muted" style="font-size: 0.8rem;"><?php echo date('d.m.Y H:i', strtotime($yorum['created_at'])); ?></small>
                                        </div>
                                        <p class="card-text mb-0"><?php echo nl2br(htmlspecialchars($yorum['content'])); ?></p>
                                    </div>
                                </div>
                            </div>
                        </div>
                    <?php endforeach; ?>
                </div>
                
                <div class="d-flex justify-content-between mt-5">
                    <a href="index.php" class="btn btn-outline-secondary">&larr; Ana Sayfaya Dön</a>
                </div>
            </div>
        </div>
<?php
    } else {
        echo '<div class="alert alert-warning">Aradığınız yazı bulunamadı.</div>';
    }
} else {
    echo '<div class="alert alert-danger">Geçersiz yazı ID.</div>';
}
?>
<script>
function react(action) {
    <?php if (!isset($_SESSION['user_id'])): ?>
        window.location.href = 'index.php?sayfa=giris';
        return;
    <?php endif; ?>

    const postId = <?php echo $id; ?>;
    
    // UI Feedback (Optimistic - opsiyonel ama butonu disable et)
    document.getElementById('btn-like').disabled = true;
    document.getElementById('btn-dislike').disabled = true;

    fetch('api/reaction.php', {
        method: 'POST',
        headers: {
            'Content-Type': 'application/json',
        },
        body: JSON.stringify({
            action: action,
            postId: postId
        })
    })
    .then(response => response.json())
    .then(data => {
        if (data.success) {
            // Update Counts
            document.getElementById('like-count').innerText = data.likes;
            document.getElementById('dislike-count').innerText = data.dislikes;
            
            // Re-enable buttons
            document.getElementById('btn-like').disabled = false;
            document.getElementById('btn-dislike').disabled = false;
        } else {
            alert('Bir hata oluştu: ' + data.error);
        }
    })
    .catch(error => {
        console.error('Error:', error);
        document.getElementById('btn-like').disabled = false;
        document.getElementById('btn-dislike').disabled = false;
    });
}

function sharePost() {
    if (navigator.share) {
        navigator.share({
            title: '<?php echo addslashes($yazi['title']); ?>',
            text: 'Bu harika yazıya göz at!',
            url: window.location.href,
        })
        .then(() => console.log('Paylaşıldı successful'))
        .catch((error) => console.log('Paylaşım hatası', error));
    } else {
        // Fallback: Linki kopyala
        var dummy = document.createElement('input'),
        text = window.location.href;
        document.body.appendChild(dummy);
        dummy.value = text;
        dummy.select();
        document.execCommand('copy');
        document.body.removeChild(dummy);
        alert('Bağlantı panoya kopyalandı!');
    }
}
</script>
