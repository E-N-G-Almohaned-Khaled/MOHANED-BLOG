<?php
require_once 'config.php';

try {
    // Content kolonunu LONGTEXT yap (4GB'a kadar veri, Base64 resimler için gerekli)
    $sql = "ALTER TABLE posts MODIFY content LONGTEXT";
    $pdo->exec($sql);
    echo "<h1>Başarılı!</h1><p>Tablo güncellendi. Artık büyük resimler ve içerikler sığacak.</p>";
} catch (PDOException $e) {
    echo "<h1>Hata</h1><p>" . $e->getMessage() . "</p>";
}
?>
