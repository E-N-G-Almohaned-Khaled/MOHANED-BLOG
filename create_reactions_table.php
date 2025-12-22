<?php
require 'config.php';
try {
    $pdo->exec("CREATE TABLE IF NOT EXISTS post_reactions (
        id INT AUTO_INCREMENT PRIMARY KEY,
        user_id INT NOT NULL,
        post_id INT NOT NULL,
        reaction_type ENUM('like', 'dislike') NOT NULL,
        UNIQUE KEY unique_user_post (user_id, post_id)
    )");
    echo "Table 'post_reactions' created.";
} catch (PDOException $e) {
    echo "Error: " . $e->getMessage();
}
?>
