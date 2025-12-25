<?php
header('Content-Type: text/plain');
echo "STARTING DEBUG\n";

require_once 'config.php';
echo "Config loaded.\n";

try {
    $stmt = $pdo->query("SELECT COUNT(*) FROM posts");
    $count = $stmt->fetchColumn();
    echo "Post count: " . $count . "\n";

    if ($count > 0) {
        $stmt = $pdo->query("SELECT id, image_path FROM posts LIMIT 5");
        while ($row = $stmt->fetch(PDO::FETCH_ASSOC)) {
            echo "Post " . $row['id'] . ": " . $row['image_path'] . "\n";
        }
    } else {
        echo "No posts found in database.\n";
    }

} catch (PDOException $e) {
    echo "DB Error: " . $e->getMessage() . "\n";
}

echo "ENDING DEBUG\n";
?>
