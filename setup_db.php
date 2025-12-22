<?php
$host = '127.0.0.1';
$port = '3307';
$username = 'root';
$password = '';
$dbname = 'blog_db';
$schemaFile = 'schema.sql';

echo "Starting database setup...\n";

try {
    // Connect to MySQL server (no DB selected yet)
    $pdo = new PDO("mysql:host=$host;port=$port", $username, $password);
    $pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
    
    echo "Connected to MySQL server.\n";

    // Create Database
    $pdo->exec("CREATE DATABASE IF NOT EXISTS $dbname CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci");
    echo "Database '$dbname' ensured.\n";

    // Select Database
    $pdo->exec("USE $dbname");

    // Read Schema file
    if (!file_exists($schemaFile)) {
        die("Error: schema.sql not found.\n");
    }
    $sql = file_get_contents($schemaFile);

    // Import Schema
    // Splitting by ; might be needed if PDO doesn't handle multiple queries well in all versions, 
    // but typically exec() can handle it or we proceed statement by statement. 
    // For simplicity, we'll try running the whole block. if fails, we split.
    try {
        $pdo->exec($sql);
        echo "Schema imported successfully.\n";
    } catch (PDOException $e) {
        echo "Warning during bulk import (might be due to empty lines or comments): " . $e->getMessage() . "\n";
        echo "Attempting statement by statement import...\n";
        
        $statements = explode(';', $sql);
        foreach ($statements as $stmt) {
            $stmt = trim($stmt);
            if (!empty($stmt)) {
                try {
                    $pdo->exec($stmt);
                } catch (PDOException $e2) {
                    echo "Error executing statement: \n$stmt\nError: " . $e2->getMessage() . "\n";
                }
            }
        }
    }

    echo "Setup compeleted successfully!\n";

} catch (PDOException $e) {
    die("DB Error: " . $e->getMessage() . "\nMake sure XAMPP/MySQL is running.\n");
}
?>
