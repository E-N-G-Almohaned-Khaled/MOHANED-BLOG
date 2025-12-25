<?php
header('Content-Type: text/plain');
echo "=== DATABASE DIAGNOSTIC START ===\n";

require_once 'config.php';
echo "Config loaded. Host: $host, Port: $port, DB: $dbname\n";

try {
    // 1. Check Connection
    echo "Connection status: " . ($pdo ? "OK" : "FAILED") . "\n";

    // 2. Check Users
    $stmt = $pdo->query("SELECT COUNT(*) FROM users");
    $userCount = $stmt->fetchColumn();
    echo "Total Users: $userCount\n";
    
    if ($userCount > 0) {
        $stmt = $pdo->query("SELECT id, username FROM users LIMIT 5");
        while ($row = $stmt->fetch(PDO::FETCH_ASSOC)) {
            echo " - User: " . $row['id'] . " (" . $row['username'] . ")\n";
        }
    }

    // 3. Check Posts
    $stmt = $pdo->query("SELECT COUNT(*) FROM posts");
    $postCount = $stmt->fetchColumn();
    echo "Total Posts: $postCount\n";

    if ($postCount > 0) {
        $stmt = $pdo->query("SELECT id, title, author_id FROM posts LIMIT 5");
        while ($row = $stmt->fetch(PDO::FETCH_ASSOC)) {
            echo " - Post: " . $row['id'] . " (AuthorID: " . $row['author_id'] . ")\n";
        }
    }

    // 4. Check Join (What index.php uses)
    $stmt = $pdo->query("SELECT COUNT(*) FROM posts JOIN users ON posts.author_id = users.id");
    $joinCount = $stmt->fetchColumn();
    echo "Posts valid for display (JOIN check): $joinCount\n";
    
    if ($postCount > 0 && $joinCount == 0) {
        echo "CRITICAL: Posts exist but don't match any user! (Orphaned posts)\n";
    }

} catch (PDOException $e) {
    echo "DB Error: " . $e->getMessage() . "\n";
}

echo "=== DATABASE DIAGNOSTIC END ===\n";
?>
