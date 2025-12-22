<?php
require 'config.php';
$s=$pdo->query('SHOW COLUMNS FROM posts');
print_r($s->fetchAll(PDO::FETCH_ASSOC));
?>
