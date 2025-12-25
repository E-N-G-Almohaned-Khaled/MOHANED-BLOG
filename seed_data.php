<?php
require_once 'config.php';

try {
    // 1. Create User
    $stmt = $pdo->prepare("SELECT id FROM users WHERE username = ?");
    $stmt->execute(['admin']);
    $user = $stmt->fetch();

    if (!$user) {
        $password = password_hash('admin123', PASSWORD_DEFAULT);
        $stmt = $pdo->prepare("INSERT INTO users (username, email, password, role, profile_image) VALUES (?, ?, ?, ?, ?)");
        $stmt->execute(['admin', 'admin@example.com', $password, 'admin', 'https://via.placeholder.com/150']);
        $userId = $pdo->lastInsertId();
        echo "Created admin user (ID: $userId)\n";
    } else {
        $userId = $user['id'];
        echo "Admin user exists (ID: $userId)\n";
    }

    // 2. Create Post
    $title = "Merhaba Dünya! - Blog Açıldı";
    $content = "Bu otomatik oluşturulmuş bir test yazısıdır. Blog sistemi başarıyla çalışıyor.";
    
    $stmt = $pdo->prepare("INSERT INTO posts (title, content, author_id, image_path) VALUES (?, ?, ?, ?)");
    $stmt->execute([$title, $content, $userId, 'https://via.placeholder.com/800x400']);
    
    echo "Created sample post linked to user $userId\n";

} catch (PDOException $e) {
    echo "Error: " . $e->getMessage();
}
?>
