<?php
session_start();
require_once 'database.php';

if (!isset($_SESSION['user_id'])) {
    header('Location: login.php');
    exit;
}

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    header('Location: videos.php');
    exit;
}

$videoId = isset($_POST['video_id']) ? (int)$_POST['video_id'] : 0;

if ($videoId <= 0) {
    header('Location: videos.php');
    exit;
}

$stmt = $pdo->prepare('SELECT id FROM videos WHERE id = ? LIMIT 1');
$stmt->execute([$videoId]);

if (!$stmt->fetchColumn()) {
    header('Location: videos.php');
    exit;
}

$stmt = $pdo->prepare('SELECT id FROM video_likes WHERE video_id = ? AND user_id = ? LIMIT 1');
$stmt->execute([$videoId, $_SESSION['user_id']]);
$existingLikeId = $stmt->fetchColumn();

if ($existingLikeId) {
    $stmt = $pdo->prepare('DELETE FROM video_likes WHERE id = ?');
    $stmt->execute([$existingLikeId]);
} else {
    $stmt = $pdo->prepare('INSERT INTO video_likes (video_id, user_id) VALUES (?, ?)');
    $stmt->execute([$videoId, $_SESSION['user_id']]);
}

header('Location: video.php?id=' . $videoId);
exit;
?>
