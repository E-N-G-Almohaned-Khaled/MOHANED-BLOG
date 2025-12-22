<?php
// Define credentials manually
$host = 'localhost';
$username = 'root';
$password = '';
$dbname = 'blog_db';

try {
    // Connect to the specific database
    $pdo = new PDO("mysql:host=$host;dbname=$dbname;charset=utf8mb4", $username, $password);
    $pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
    
    echo "Connected to database.\n";
    
    // SQL to create comments table
    $sql = "CREATE TABLE IF NOT EXISTS comments (
        id INT AUTO_INCREMENT PRIMARY KEY,
        post_id INT NOT NULL,
        user_id INT NOT NULL,
        content TEXT NOT NULL,
        created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
        FOREIGN KEY (post_id) REFERENCES posts(id) ON DELETE CASCADE,
        FOREIGN KEY (user_id) REFERENCES users(id) ON DELETE CASCADE
    );";
    
    $pdo->exec($sql);
    
    echo "Comments table created successfully!\n";
    
} catch (PDOException $e) {
    die("Update Error: " . $e->getMessage() . "\n");
}
?>
