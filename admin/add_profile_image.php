<?php
require_once '../config.php';

try {
    // Check if column exists
    $check = $pdo->query("SHOW COLUMNS FROM users LIKE 'profile_image'");
    if ($check->rowCount() == 0) {
        $pdo->exec("ALTER TABLE users ADD COLUMN profile_image VARCHAR(255) DEFAULT NULL");
        echo "Column 'profile_image' added successfully.";
    } else {
        echo "Column 'profile_image' already exists.";
    }
} catch (PDOException $e) {
    echo "Error: " . $e->getMessage();
}
?>
