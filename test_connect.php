<?php
$host = 'localhost';
$username = 'root';
$passwords = ['', 'root', '123456', '12345678', 'admin', 'password', 'toor'];

$ports = ['3306', '3307'];
$passwords = ['', 'root', '123456', '12345678', 'admin', 'password', 'toor'];

foreach ($ports as $port) {
    echo "Testing Port: $port\n";
    foreach ($passwords as $password) {
        try {
            $pdo = new PDO("mysql:host=$host;port=$port", $username, $password);
            echo "SUCCESS: Found valid credentials! Port: $port, Password: '$password'\n";
            exit(0);
        } catch (PDOException $e) {
            // echo "Failed Port $port Password '$password': " . $e->getMessage() . "\n";
        }
    }
}

echo "FAILURE: No password worked.\n";
?>
