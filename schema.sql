-- Veritabanını oluştur (Eğer yoksa)
CREATE DATABASE IF NOT EXISTS blog_db DEFAULT CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci;
USE blog_db;

-- Kullanıcılar tablosu (Users table)
-- id: Benzersiz kimlik
-- username: Kullanıcı adı
-- email: E-posta adresi
-- password: Şifre (Hashlenmiş)
-- role: Rol (admin, editor, user)
CREATE TABLE IF NOT EXISTS users (
    id INT AUTO_INCREMENT PRIMARY KEY,
    username VARCHAR(50) NOT NULL UNIQUE,
    email VARCHAR(100) NOT NULL UNIQUE,
    password VARCHAR(255) NOT NULL,
    role ENUM('admin', 'editor', 'user') DEFAULT 'user',
    profile_image VARCHAR(255) DEFAULT NULL,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
);

-- Yazılar tablosu (Posts table)
-- id: Benzersiz kimlik
-- title: Başlık
-- content: İçerik
-- image_path: Resim yolu
-- author_id: Yazar ID (users tablosuna foreign key)
CREATE TABLE IF NOT EXISTS posts (
    id INT AUTO_INCREMENT PRIMARY KEY,
    title VARCHAR(255) NOT NULL,
    content TEXT NOT NULL,
    image_path VARCHAR(255),
    author_id INT NOT NULL,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (author_id) REFERENCES users(id) ON DELETE CASCADE
);

-- Örnek Yönetici Hesabı (Şifre: admin123)
-- Not: Gerçek uygulamada şifreler password_hash() ile php tarafında hashlenmelidir. 
-- Bu örnek veri olduğu için manuel ekleme SQL'i veriyorum, ancak register.php üzerinden eklemek daha doğrudur.
-- INSERT INTO users (username, email, password, role) VALUES ('admin', 'admin@blog.com', '$2y$10$YourHashedPasswordHere', 'admin');

-- Yorumlar tablosu (Comments table)
-- id: Benzersiz kimlik
-- post_id: Hangi yazıya ait olduğu
-- user_id: Yazan kullanıcı
-- content: Yorum içeriği
CREATE TABLE IF NOT EXISTS comments (
    id INT AUTO_INCREMENT PRIMARY KEY,
    post_id INT NOT NULL,
    user_id INT NOT NULL,
    content TEXT NOT NULL,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (post_id) REFERENCES posts(id) ON DELETE CASCADE,
    FOREIGN KEY (user_id) REFERENCES users(id) ON DELETE CASCADE
);
