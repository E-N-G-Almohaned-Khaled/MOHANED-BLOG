<?php
require_once 'config.php';

echo "<h1>Image Path Debugger V2</h1>";

try {
    // Check Posts
    $stmt = $pdo->query("SELECT id, title, image_path FROM posts");
    $posts = $stmt->fetchAll(PDO::FETCH_ASSOC);

    echo "<h2>Posts (" . count($posts) . ")</h2>";
    echo "<table border='1'><tr><th>ID</th><th>Title</th><th>Image Path</th><th>Expected File</th><th>Exists?</th></tr>";
    
    foreach ($posts as $post) {
        $path = $post['image_path'];
        // Assume path might be relative to root
        $fullPath = __DIR__ . '/' . $path;
        $exists = file_exists($fullPath) ? 'YES' : 'NO';
        
        echo "<tr>";
        echo "<td>" . $post['id'] . "</td>";
        echo "<td>" . htmlspecialchars($post['title']) . "</td>";
        echo "<td>" . htmlspecialchars($path) . "</td>";
        echo "<td>" . htmlspecialchars($fullPath) . "</td>";
        echo "<td>" . $exists . "</td>";
        echo "</tr>";
    }
    echo "</table>";

    // Check Users
    $stmt = $pdo->query("SELECT id, username, profile_image FROM users");
    $users = $stmt->fetchAll(PDO::FETCH_ASSOC);

    echo "<h2>Users (" . count($users) . ")</h2>";
    echo "<table border='1'><tr><th>ID</th><th>Username</th><th>Profile Image</th><th>Exists?</th></tr>";
    foreach ($users as $user) {
        $path = $user['profile_image'];
        $fullPath = __DIR__ . '/' . $path;
        $exists = file_exists($fullPath) ? 'YES' : 'NO';
        
        echo "<tr>";
        echo "<td>" . $user['id'] . "</td>";
        echo "<td>" . htmlspecialchars($user['username']) . "</td>";
        echo "<td>" . htmlspecialchars($path) . "</td>";
        echo "<td>" . $exists . "</td>";
        echo "</tr>";
    }
    echo "</table>";

} catch (PDOException $e) {
    echo "Error: " . $e->getMessage();
}
?>
