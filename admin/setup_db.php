<?php
// Define credentials manually to avoid including config.php which tries to connect to the DB immediately
$host = 'localhost';
$username = 'root';
$password = '';

try {
    // Read the schema file
    $sql = file_get_contents('schema.sql');
    
    // Connect WITHOUT selecting a database
    $pdo = new PDO("mysql:host=$host;charset=utf8mb4", $username, $password);
    $pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
    
    echo "Connected to MySQL server.\n";
    
    // Create database if not exists
    $pdo->exec($sql);
    
    echo "Database and tables created successfully!\n";
    
} catch (PDOException $e) {
    die("DB Setup Error: " . $e->getMessage() . "\n");
}
?>
