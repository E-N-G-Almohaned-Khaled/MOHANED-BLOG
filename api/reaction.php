<?php
session_start();
require_once '../config.php';

header('Content-Type: application/json');

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    http_response_code(405);
    echo json_encode(['error' => 'Method not allowed']);
    exit;
}

// JSON verisini al
$input = json_decode(file_get_contents('php://input'), true);

if (!isset($_SESSION['user_id'])) {
    http_response_code(401);
    echo json_encode(['error' => 'Unauthorized']);
    exit;
}

$action = $input['action'] ?? '';
$postId = isset($input['postId']) ? (int)$input['postId'] : 0;
$uid = $_SESSION['user_id'];

if (($action !== 'like' && $action !== 'dislike') || $postId <= 0) {
    http_response_code(400);
    echo json_encode(['error' => 'Invalid input']);
    exit;
}

try {
    // Mevcut reaksiyonu kontrol et
    $stmt = $pdo->prepare("SELECT reaction_type FROM post_reactions WHERE user_id = ? AND post_id = ?");
    $stmt->execute([$uid, $postId]);
    $existing = $stmt->fetchColumn();

    if ($existing) {
        if ($existing == $action) {
            // Aynı butona basıldı, kaldır
            $pdo->prepare("DELETE FROM post_reactions WHERE user_id = ? AND post_id = ?")->execute([$uid, $postId]);
            $userAction = 'removed';
        } else {
            // Değiştir
            $pdo->prepare("UPDATE post_reactions SET reaction_type = ? WHERE user_id = ? AND post_id = ?")->execute([$action, $uid, $postId]);
            $userAction = 'updated';
        }
    } else {
        // Yeni ekle
        $pdo->prepare("INSERT INTO post_reactions (user_id, post_id, reaction_type) VALUES (?, ?, ?)")->execute([$uid, $postId, $action]);
        $userAction = 'added';
    }

    // Güncel sayıları hesapla
    $likeCount = $pdo->query("SELECT COUNT(*) FROM post_reactions WHERE post_id = $postId AND reaction_type = 'like'")->fetchColumn();
    $dislikeCount = $pdo->query("SELECT COUNT(*) FROM post_reactions WHERE post_id = $postId AND reaction_type = 'dislike'")->fetchColumn();

    // Cache güncelle
    $pdo->prepare("UPDATE posts SET likes = ?, dislikes = ? WHERE id = ?")->execute([$likeCount, $dislikeCount, $postId]);

    echo json_encode([
        'success' => true,
        'likes' => $likeCount,
        'dislikes' => $dislikeCount,
        'userAction' => $userAction
    ]);

} catch (Exception $e) {
    http_response_code(500);
    echo json_encode(['error' => 'Database error']);
}
