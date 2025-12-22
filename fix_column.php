<?php
require 'config.php';

try {
    echo "Attempting to add 'profile_image' column to 'users' table...\n";
    $pdo->exec("ALTER TABLE users ADD COLUMN profile_image VARCHAR(255) DEFAULT NULL AFTER role");
    echo "SUCCESS: Column added.\n";
} catch (PDOException $e) {
    if (strpos($e->getMessage(), "Duplicate column name") !== false) {
        echo "INFO: Column already exists.\n";
    } else {
        echo "ERROR: " . $e->getMessage() . "\n";
    }
}
?>
