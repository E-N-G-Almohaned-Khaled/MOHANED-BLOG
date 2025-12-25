<?php
require 'config.php';

$email = 'admin@example.com';
$new_password = '123456';
$hashed = password_hash($new_password, PASSWORD_DEFAULT);

$sql = "UPDATE users SET password = ? WHERE email = ?";
$stmt = $pdo->prepare($sql);

if ($stmt->execute([$hashed, $email])) {
    echo "<h1>Password Updated</h1>";
    echo "Email: $email<br>";
    echo "New Password: $new_password<br>";
    echo "<a href='index.php?sayfa=giris'>Login Now</a>";
} else {
    echo "Update failed.";
}
?>
