<?php
// Oturumu başlat
$sessionPath = __DIR__ . '/sessions';
if (!file_exists($sessionPath)) {
    mkdir($sessionPath, 0777, true);
}
session_save_path($sessionPath);
session_start();
ob_start();

// Dil Ayarları (Language Settings)
require_once 'languages.php';
if (isset($_GET['lang']) && array_key_exists($_GET['lang'], $lang)) {
    $_SESSION['lang'] = $_GET['lang'];
}
// Varsayılan dil: tr
$current_lang = isset($_SESSION['lang']) ? $_SESSION['lang'] : 'tr';
$t = $lang[$current_lang];

// RTL Kontrolü (Arabic Layout)
$dir = ($current_lang == 'ar') ? 'rtl' : 'ltr';
$lang_attr = ($current_lang == 'ar') ? 'ar' : 'tr'; // veya 'en' dinamik

// Veritabanı bağlantısını dahil et
require_once 'config.php';

// Hangi sayfanın yükleneceğini belirle (Basit Router)
$sayfa = isset($_GET['sayfa']) ? $_GET['sayfa'] : 'anasayfa';

// Varsayılan SEO Bilgileri
$pageTitle = 'Blog Projesi - Ana Sayfa';
$pageDesc = 'Teknoloji, yazılım ve hayat üzerine güncel blog yazıları.';
$pageImage = ''; // Varsayılan paylaşım resmi URL'si eklenebilir

// Detay sayfası ise ve ID varsa, veriyi önden çek
$fetchedPost = null;
if ($sayfa == 'detay' && isset($_GET['id'])) {
    $id = (int)$_GET['id'];
    $stmt = $pdo->prepare("SELECT posts.*, users.username FROM posts JOIN users ON posts.author_id = users.id WHERE posts.id = ?");
    $stmt->execute([$id]);
    $fetchedPost = $stmt->fetch();
    
    if ($fetchedPost) {
        $pageTitle = htmlspecialchars($fetchedPost['title']);
        // İçerikten kısa bir açıklama oluştur (ilk 160 karakter)
        $cleanContent = strip_tags($fetchedPost['content']);
        $pageDesc = mb_substr($cleanContent, 0, 160) . '...';
        $pageImage = $fetchedPost['image_path'];
    }
}
?>
<!DOCTYPE html>
<html lang="<?php echo $lang_attr; ?>" dir="<?php echo $dir; ?>">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    
    <!-- Dinamik SEO Etiketleri -->
    <title><?php echo $pageTitle; ?></title>
    <meta name="description" content="<?php echo htmlspecialchars($pageDesc); ?>">
    
    <!-- Open Graph (Sosyal Medya) -->
    <meta property="og:title" content="<?php echo $pageTitle; ?>">
    <meta property="og:description" content="<?php echo htmlspecialchars($pageDesc); ?>">
    <meta property="og:type" content="article">
    <?php if($pageImage): ?>
        <meta property="og:image" content="<?php echo htmlspecialchars($pageImage); ?>">
    <?php endif; ?>

    <!-- Bootstrap 5 CSS CDN -->
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <!-- Font: Inter & Outfit -->
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@400;500;600&family=Outfit:wght@500;700&display=swap" rel="stylesheet">
    
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.0/font/bootstrap-icons.css">
    <link href="style.css" rel="stylesheet">
</head>
<body>
    <!-- Background Animations -->
    <div class="bg-orb orb-1"></div>
    <div class="bg-orb orb-2"></div>

    <!-- Navigasyon Çubuğu (Navbar) -->
    <nav class="navbar navbar-expand-lg navbar-light bg-white sticky-top">
        <div class="container">
            <a class="navbar-brand" href="index.php">BlogProjesi</a>
            <button class="navbar-toggler" type="button" data-bs-toggle="collapse" data-bs-target="#navbarNav">
                <span class="navbar-toggler-icon"></span>
            </button>
            <div class="collapse navbar-collapse" id="navbarNav">
                
                <!-- Language Selector (Glass Box) - Moved to left for LTR, right for RTL -->
                <div class="d-flex align-items-center <?php echo ($dir == 'rtl') ? 'ms-lg-3' : 'me-lg-3'; ?> mb-2 mb-lg-0 p-1 rounded-pill bg-white bg-opacity-10 border border-white border-opacity-25 shadow-sm">
                    <a href="?lang=en" class="btn btn-sm btn-link text-decoration-none fw-bold <?php echo ($current_lang == 'en') ? 'text-warning' : 'text-white opacity-50'; ?>" style="font-size: 0.8rem;">EN</a>
                    <span class="text-white opacity-25">|</span>
                    <a href="?lang=tr" class="btn btn-sm btn-link text-decoration-none fw-bold <?php echo ($current_lang == 'tr') ? 'text-warning' : 'text-white opacity-50'; ?>" style="font-size: 0.8rem;">TR</a>
                    <span class="text-white opacity-25">|</span>
                    <a href="?lang=ar" class="btn btn-sm btn-link text-decoration-none fw-bold <?php echo ($current_lang == 'ar') ? 'text-warning' : 'text-white opacity-50'; ?>" style="font-size: 0.8rem;">AR</a>
                </div>

                <form class="d-flex <?php echo ($dir == 'rtl') ? 'ms-auto' : 'me-auto'; ?>" action="index.php" method="GET">
                    <input type="hidden" name="sayfa" value="anasayfa">
                    <input class="form-control <?php echo ($dir == 'rtl') ? 'ms-2' : 'me-2'; ?>" type="search" name="q" placeholder="<?php echo $t['search_placeholder']; ?>" aria-label="Search" value="<?php echo isset($_GET['q']) ? htmlspecialchars($_GET['q']) : ''; ?>">
                    <button class="btn btn-outline-primary" type="submit"><?php echo $t['search_btn']; ?></button>
                </form>

                <!-- Orta Kısım: Menü Linkleri -->
                <ul class="navbar-nav mb-2 mb-lg-0">
                    <li class="nav-item">
                        <a class="nav-link <?php echo $sayfa == 'anasayfa' ? 'active' : ''; ?>" href="index.php?sayfa=anasayfa"><?php echo $t['home']; ?></a>
                    </li>
                    <?php if (isset($_SESSION['user_id'])): ?>
                         <?php if ($_SESSION['role'] === 'admin'): ?>
                            <li class="nav-item">
                                <a class="nav-link" href="admin/index.php">Yönetim Paneli</a>
                            </li>
                         <?php endif; ?>
                        <li class="nav-item">
                            <a class="nav-link <?php echo $sayfa == 'yazi-paylas' ? 'active' : ''; ?>" href="index.php?sayfa=yazi-paylas"><?php echo $t['share_article']; ?></a>
                        </li>
                    <?php endif; ?>
                </ul>

                <!-- Sağ Kısım: Profil veya Giriş -->
                <ul class="navbar-nav mb-2 mb-lg-0">
                    <?php if (isset($_SESSION['user_id'])): ?>
                        <?php 
                        $currentUserImg = 'https://via.placeholder.com/40?text=User';
                         if (isset($_SESSION['user_id'])) {
                             $uStmt = $pdo->prepare("SELECT profile_image FROM users WHERE id = ?");
                             $uStmt->execute([$_SESSION['user_id']]);
                             $uRow = $uStmt->fetch();
                             if ($uRow && !empty($uRow['profile_image'])) {
                                 $currentUserImg = $uRow['profile_image'];
                             }
                        }
                        ?>
                        <li class="nav-item dropdown">
                            <a class="nav-link dropdown-toggle d-flex align-items-center" href="#" id="userDropdown" role="button" data-bs-toggle="dropdown" aria-expanded="false">
                                <img src="<?php echo htmlspecialchars($currentUserImg); ?>" alt="Profil" class="rounded-circle <?php echo ($dir == 'rtl') ? 'ms-2' : 'me-2'; ?> border" style="width: 45px; height: 45px; object-fit: cover;">
                                <span><?php echo htmlspecialchars($_SESSION['username']); ?></span>
                            </a>
                            <ul class="dropdown-menu dropdown-menu-end" aria-labelledby="userDropdown">
                                <li><a class="dropdown-item" href="index.php?sayfa=profil"><?php echo $t['profile']; ?></a></li>
                                <li><hr class="dropdown-divider"></li>
                                <li><a class="dropdown-item text-danger" href="logout.php"><?php echo $t['logout']; ?></a></li>
                            </ul>
                        </li>
                    <?php else: ?>
                        <!-- Misafir kullanıcılar için menü -->
                        <li class="nav-item">
                            <a class="nav-link <?php echo $sayfa == 'giris' ? 'active' : ''; ?>" href="index.php?sayfa=giris"><?php echo $t['login']; ?></a>
                        </li>
                        <li class="nav-item">
                            <a class="nav-link <?php echo $sayfa == 'kayit' ? 'active' : ''; ?>" href="index.php?sayfa=kayit"><?php echo $t['register']; ?></a>
                        </li>
                    <?php endif; ?>
                </ul>
            </div>
        </div>
    </nav>

    <!-- Ana İçerik Alanı -->
    <div class="container my-5">
        <?php
        // Sayfa yönlendirme mantığı
        switch ($sayfa) {
            case 'giris':
                include 'login.php';
                break;
            case 'kayit':
                include 'register.php';
                break;
            /* Admin paneli artık /admin klasöründe */
            case 'yazi-paylas':
                include 'create_post.php';
                break;
            case 'profil':
                include 'edit_profile.php';
                break;
            case 'detay':
                include 'view_post.php';
                break;
            default:
                // Varsayılan: Blog listesi
                // Arama sorgusu var mı?
                $q = isset($_GET['q']) ? trim($_GET['q']) : '';
                
                if ($q) {
                    $sql = "SELECT posts.*, users.username FROM posts JOIN users ON posts.author_id = users.id WHERE posts.title LIKE ? OR posts.content LIKE ? ORDER BY created_at DESC";
                    $stmt = $pdo->prepare($sql);
                    $stmt->execute(['%'.$q.'%', '%'.$q.'%']);
                    echo '<h1 class="mb-4 text-center">Arama Sonuçları: "'.htmlspecialchars($q).'"</h1>';
                } else {
                    // Hero Section
                    echo '
                    <div class="p-4 p-lg-4 mb-3 rounded-3 text-center hero-section position-relative overflow-hidden">
                        <div class="position-relative z-1">
                            <h1 class="display-6 fw-bold hero-title animate-title mb-2">' . $t['welcome_title'] . '</h1>
                            <p class="col-lg-8 mx-auto fs-6 text-muted animate-fade-up">
                                ' . $t['welcome_desc'] . '
                            </p>
                        </div>
                    </div>';

                    $stmt = $pdo->query("SELECT posts.*, users.username FROM posts JOIN users ON posts.author_id = users.id ORDER BY created_at DESC");
                    echo '<h1 class="mb-4 text-center">' . $t['latest_posts'] . '</h1>';
                }
                
                $yazilar = $stmt->fetchAll();
                
                echo '<div class="row">';
                
                if (count($yazilar) > 0) {
                    foreach ($yazilar as $yazi) {
                        $ozet = mb_substr(strip_tags($yazi['content']), 0, 100) . '...';
                        $resim = $yazi['image_path'] ? $yazi['image_path'] : 'https://via.placeholder.com/300x200?text=Resim+Yok';
                        
                        echo '
                        <div class="col-md-4 mb-4">
                            <div class="card h-100">
                                <img src="' . htmlspecialchars($resim) . '" class="post-image card-img-top" alt="Yazı Görseli">
                                <div class="card-body">
                                    <h5 class="card-title">' . htmlspecialchars($yazi['title']) . '</h5>
                                    <p class="card-text text-muted small">Yazar: ' . htmlspecialchars($yazi['username']) . '</p>
                                    <p class="card-text">' . htmlspecialchars($ozet) . '</p>
                                    <a href="index.php?sayfa=detay&id=' . $yazi['id'] . '" class="btn btn-outline-primary btn-sm">' . $t['read_more'] . '</a>
                                </div>
                            </div>
                        </div>';
                    }
                } else {
                    echo '<div class="col-12"><div class="alert alert-info glass-card">' . $t['no_posts'] . '</div></div>';
                }
                
                echo '</div>'; // .row sonu
                break;
        }
        ?>
    </div>

    <!-- Footer -->
    <footer class="bg-light text-center py-4 mt-5 border-top">
        <div class="container">
            <p class="text-muted mb-0">&copy; 2025 Blog Projesi - <?php echo $t['footer_text']; ?></p>
        </div>
    </footer>

    <!-- Bootstrap JS Bundle -->
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>
