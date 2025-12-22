<?php
$host = 'localhost';
$username = 'root';
$passwords = ['', 'root', '123456', '12345678', 'admin', 'password', 'toor'];

foreach ($passwords as $password) {
    try {
        $pdo = new PDO("mysql:host=$host", $username, $password);
        echo "SUCCESS: Password '$password' worked!\n";
        exit(0);
    } catch (PDOException $e) {
        // echo "Failed with '$password'\n";
    }
}

echo "FAILURE: No password worked.\n";
?>
