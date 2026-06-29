<?php
session_start();
require_once "database.php";
require_once "partials/menu.php";

$videoId = isset($_GET["id"]) ? (int) $_GET["id"] : 0;

if ($videoId <= 0) {
    http_response_code(404);
    $video = null;
} else {
    $stmt = $pdo->prepare(
        "SELECT v.*, u.name AS author_name FROM videos v JOIN users u ON v.user_id = u.id WHERE v.id = ? LIMIT 1",
    );
    $stmt->execute([$videoId]);
    $video = $stmt->fetch(PDO::FETCH_ASSOC);
}

if ($video) {
    $stmt = $pdo->prepare("UPDATE videos SET views = views + 1 WHERE id = ?");
    $stmt->execute([$videoId]);
    $video["views"] = (int) $video["views"] + 1;

    $stmt = $pdo->prepare(
        "SELECT COUNT(*) FROM video_likes WHERE video_id = ?",
    );
    $stmt->execute([$videoId]);
    $likesCount = (int) $stmt->fetchColumn();

    $likedByUser = false;
    if (isset($_SESSION["user_id"])) {
        $stmt = $pdo->prepare(
            "SELECT 1 FROM video_likes WHERE video_id = ? AND user_id = ? LIMIT 1",
        );
        $stmt->execute([$videoId, $_SESSION["user_id"]]);
        $likedByUser = (bool) $stmt->fetchColumn();
    }

    $stmt = $pdo->prepare(
        "SELECT c.*, u.name AS commenter_name FROM comments c JOIN users u ON c.user_id = u.id WHERE c.video_id = ? ORDER BY c.created_at DESC",
    );
    $stmt->execute([$videoId]);
    $comments = $stmt->fetchAll(PDO::FETCH_ASSOC);

    $extension = strtolower(pathinfo($video["file_path"], PATHINFO_EXTENSION));
    $mimeTypes = [
        "mp4" => "video/mp4",
        "webm" => "video/webm",
        "ogg" => "video/ogg",
    ];
    $mimeType = isset($mimeTypes[$extension])
        ? $mimeTypes[$extension]
        : "video/mp4";

    $canDelete =
        isset($_SESSION["user_id"]) &&
        ((int) $video["user_id"] === (int) $_SESSION["user_id"] ||
            (isset($_SESSION["user_role"]) &&
                $_SESSION["user_role"] === "admin"));
} else {
    $likesCount = 0;
    $likedByUser = false;
    $comments = [];
    $mimeType = "video/mp4";
    $canDelete = false;
}
?>
<!DOCTYPE html>
<html lang="nl">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?php echo $video
        ? htmlspecialchars($video["title"])
        : "Video niet gevonden"; ?> - StreamHive</title>
    <link rel="stylesheet" href="css/style.css">
</head>
<body>
    <?php renderMenu("videos"); ?>

    <main class="content-viewport">
        <?php if (!$video): ?>
            <div class="empty-state">
                <h1>Video niet gevonden</h1>
                <p>Deze video bestaat niet of is verwijderd.</p>
                <a class="btn-action-submit" href="videos.php" style="display: inline-block; text-decoration: none; margin-top: 12px;">Terug naar video’s</a>
            </div>
        <?php else: ?>
            <section class="video-detail-layout">
                <div class="video-player-card">
                    <video class="video-detail-player" controls autoplay>
                        <source src="<?php echo htmlspecialchars(
                            $video["file_path"],
                        ); ?>" type="<?php echo htmlspecialchars(
    $mimeType,
); ?>">
                        Je browser ondersteunt deze video-indeling niet.
                    </video>

                    <div class="video-detail-meta">
                        <h1><?php echo htmlspecialchars(
                            $video["title"],
                        ); ?></h1>
                        <div class="detail-stats">
                            👁️ <?php echo (int) $video["views"]; ?> views
                            · ❤️ <?php echo $likesCount; ?> likes
                            · 💬 <?php echo count($comments); ?> reacties
                        </div>
                        <p class="stream-creator">Geüpload door <?php echo htmlspecialchars(
                            $video["author_name"],
                        ); ?> op <?php echo htmlspecialchars(
     $video["upload_date"],
 ); ?></p>

                        <?php if (!empty($video["description"])): ?>
                            <p class="video-description"><?php echo nl2br(
                                htmlspecialchars($video["description"]),
                            ); ?></p>
                        <?php endif; ?>

                        <div class="detail-actions">
                            <?php if (isset($_SESSION["user_id"])): ?>
                                <form action="like_video.php" method="POST">
                                    <input type="hidden" name="video_id" value="<?php echo $videoId; ?>">
                                    <button class="btn-action-submit" type="submit"><?php echo $likedByUser
                                        ? "💔 Unlike"
                                        : "❤️ Like"; ?></button>
                                </form>
                            <?php else: ?>
                                <a class="btn-action-submit" href="login.php" style="text-decoration: none;">Log in om te liken</a>
                            <?php endif; ?>

                            <?php if ($canDelete): ?>
                                <form action="delete_video.php" method="POST" onsubmit="return confirm('Weet je zeker dat je deze video wilt verwijderen?');">
                                    <input type="hidden" name="video_id" value="<?php echo $videoId; ?>">
                                    <input type="hidden" name="redirect" value="1">
                                    <button class="btn-danger" type="submit">🗑️ Verwijderen</button>
                                </form>
                            <?php endif; ?>
                        </div>
                    </div>
                </div>

                <aside class="comments-card">
                    <h2>Reacties</h2>

                    <?php if (isset($_SESSION["user_id"])): ?>
                        <form action="add_comment.php" method="POST" class="comment-form">
                            <input type="hidden" name="video_id" value="<?php echo $videoId; ?>">
                            <div class="input-field-group">
                                <label for="comment_text">Plaats een reactie</label>
                                <textarea id="comment_text" name="comment_text" rows="4" required placeholder="Schrijf je reactie..."></textarea>
                            </div>
                            <button class="btn-action-submit" type="submit">Versturen</button>
                        </form>
                    <?php else: ?>
                        <p class="login-notice"><a href="login.php">Log in</a> om een reactie te plaatsen.</p>
                    <?php endif; ?>

                    <div class="comments-list-detail">
                        <?php if (empty($comments)): ?>
                            <p class="stream-stats">Nog geen reacties. Laat als eerste een reactie achter!</p>
                        <?php else: ?>
                            <?php foreach ($comments as $comment): ?>
                                <article class="comment-item">
                                    <strong><?php echo htmlspecialchars(
                                        $comment["commenter_name"],
                                    ); ?></strong>
                                    <span><?php echo htmlspecialchars(
                                        $comment["created_at"],
                                    ); ?></span>
                                    <p><?php echo nl2br(
                                        htmlspecialchars(
                                            $comment["comment_text"],
                                        ),
                                    ); ?></p>
                                </article>
                            <?php endforeach; ?>
                        <?php endif; ?>
                    </div>
                </aside>
            </section>
        <?php endif; ?>
    </main>
</div>

<footer class="app-footer">
    <p>&copy; 2026 StreamHive - gemaakt door daniel wang</p>
</footer>
</body>
</html>
