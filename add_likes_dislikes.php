<?php
require 'config.php';
try {
    $pdo->exec("ALTER TABLE posts ADD COLUMN likes INT DEFAULT 0, ADD COLUMN dislikes INT DEFAULT 0");
    echo "Columns added.";
} catch (PDOException $e) {
    echo "Error (maybe columns exist): " . $e->getMessage();
}
?>
