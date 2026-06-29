<?php
session_start();
require_once 'database.php';
require_once 'partials/menu.php';

$search = isset($_GET['q']) ? trim($_GET['q']) : '';
$params = [];
$where = '';

if ($search !== '') {
    $where = 'WHERE v.title LIKE ? OR v.description LIKE ? OR u.name LIKE ?';
    $params = ['%' . $search . '%', '%' . $search . '%', '%' . $search . '%'];
}

$sql = "
    SELECT
        v.*,
        u.name AS author_name,
        (SELECT COUNT(*) FROM video_likes vl WHERE vl.video_id = v.id) AS likes_count,
        (SELECT COUNT(*) FROM comments c WHERE c.video_id = v.id) AS comments_count
    FROM videos v
    JOIN users u ON v.user_id = u.id
    $where
    ORDER BY v.upload_date DESC
";

$stmt = $pdo->prepare($sql);
$stmt->execute($params);
$videos = $stmt->fetchAll(PDO::FETCH_ASSOC);
?>
<!DOCTYPE html>
<html lang="nl">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>StreamHive - Video’s</title>
    <link rel="stylesheet" href="css/style.css">
</head>
<body>
    <?php renderMenu('videos'); ?>

    <main class="content-viewport">
        <h1 class="page-title">Video’s</h1>
        <?php if ($search !== ''): ?>
            <p class="page-subtitle">Zoekresultaten voor: <strong><?php echo htmlspecialchars($search); ?></strong></p>
        <?php else: ?>
            <p class="page-subtitle">Bekijk alle geüploade video’s op StreamHive.</p>
        <?php endif; ?>

        <?php if (empty($videos)): ?>
            <div class="empty-state">
                <p>Er zijn nog geen video’s gevonden.</p>
                <?php if (isset($_SESSION['user_id'])): ?>
                    <a class="btn-action-submit" href="upload_page.php" style="display: inline-block; text-decoration: none; margin-top: 12px;">Upload de eerste video</a>
                <?php endif; ?>
            </div>
        <?php else: ?>
            <section class="videos-layout-grid">
                <?php foreach ($videos as $video): ?>
                    <?php
                    $videoId = (int)$video['id'];
                    $author = $video['author_name'] ?: 'Onbekend';
                    ?>
                    <article class="stream-card">
                        <a class="stream-thumbnail" href="video.php?id=<?php echo $videoId; ?>" aria-label="Bekijk <?php echo htmlspecialchars($video['title']); ?>">
                            <video muted preload="metadata">
                                <source src="<?php echo htmlspecialchars($video['file_path']); ?>" type="video/mp4">
                            </video>
                        </a>
                        <div class="stream-details">
                            <div class="stream-avatar"><?php echo htmlspecialchars(strtoupper(substr($author, 0, 1))); ?></div>
                            <div class="stream-metadata">
                                <a class="stream-title" href="video.php?id=<?php echo $videoId; ?>"><?php echo htmlspecialchars($video['title']); ?></a>
                                <div class="stream-creator">👤 <?php echo htmlspecialchars($author); ?></div>
                                <div class="stream-stats">
                                    👁️ <?php echo (int)$video['views']; ?> views · ❤️ <?php echo (int)$video['likes_count']; ?> likes · 💬 <?php echo (int)$video['comments_count']; ?> reacties
                                </div>
                            </div>
                        </div>
                    </article>
                <?php endforeach; ?>
            </section>
        <?php endif; ?>
    </main>
</div>

<footer class="app-footer">
    <p>&copy; 2026 StreamHive - gemaakt door daniel wang</p>
</footer>
</body>
</html>
