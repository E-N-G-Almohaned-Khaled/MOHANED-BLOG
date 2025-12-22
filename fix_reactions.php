<?php
require 'config.php';

try {
    // 1. Create post_reactions table
    $pdo->exec("CREATE TABLE IF NOT EXISTS post_reactions (
        id INT AUTO_INCREMENT PRIMARY KEY,
        user_id INT NOT NULL,
        post_id INT NOT NULL,
        reaction_type ENUM('like', 'dislike') NOT NULL,
        UNIQUE KEY unique_user_post (user_id, post_id)
    )");
    echo "Table 'post_reactions' ensured.\n";

    // 2. Add likes column to posts
    try {
        $pdo->exec("ALTER TABLE posts ADD COLUMN likes INT DEFAULT 0");
        echo "Column 'likes' added to posts.\n";
    } catch (PDOException $e) {
        if (strpos($e->getMessage(), "Duplicate column name") !== false) {
            echo "Column 'likes' already exists.\n";
        } else {
            echo "Error adding 'likes': " . $e->getMessage() . "\n";
        }
    }

    // 3. Add dislikes column to posts
    try {
        $pdo->exec("ALTER TABLE posts ADD COLUMN dislikes INT DEFAULT 0");
        echo "Column 'dislikes' added to posts.\n";
    } catch (PDOException $e) {
        if (strpos($e->getMessage(), "Duplicate column name") !== false) {
            echo "Column 'dislikes' already exists.\n";
        } else {
            echo "Error adding 'dislikes': " . $e->getMessage() . "\n";
        }
    }

} catch (PDOException $e) {
    echo "Fatal Error: " . $e->getMessage() . "\n";
}
?>
