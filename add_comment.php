<?php
session_start();
require_once "database.php";

if (!isset($_SESSION["user_id"])) {
    header("Location: login.php");
    exit();
}

if ($_SERVER["REQUEST_METHOD"] !== "POST") {
    header("Location: videos.php");
    exit();
}

$videoId = isset($_POST["video_id"]) ? (int) $_POST["video_id"] : 0;
$commentText = isset($_POST["comment_text"])
    ? trim($_POST["comment_text"])
    : "";
$userId = (int) $_SESSION["user_id"];

if ($videoId <= 0 || $commentText === "") {
    header("Location: videos.php");
    exit();
}

$stmt = $pdo->prepare("SELECT id FROM videos WHERE id = ? LIMIT 1");
$stmt->execute([$videoId]);

if (!$stmt->fetchColumn()) {
    header("Location: videos.php");
    exit();
}

$stmt = $pdo->prepare(
    "INSERT INTO comments (video_id, user_id, comment_text) VALUES (?, ?, ?)",
);
$stmt->execute([$videoId, $userId, $commentText]);

header("Location: video.php?id=" . $videoId);
exit();
?>
