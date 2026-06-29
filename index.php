<?php
session_start();
require_once "database.php";
require_once "partials/menu.php";
?>
<!DOCTYPE html>
<html lang="nl">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>StreamHive - Home</title>
    <link rel="stylesheet" href="css/style.css">
</head>
<body>
    <?php renderMenu("home"); ?>

    <main class="content-viewport">
        <section class="hero-card">
            <h1>Welkom bij StreamHive</h1>
            <p>Kijk video’s, upload je eigen content en praat mee via likes en comments.</p>
            <div class="hero-actions">
                <a class="btn-action-submit" href="videos.php">Bekijk video’s</a>
                <?php if (isset($_SESSION["user_id"])): ?>
                    <a class="btn-nav-outline" href="upload_page.php">Upload video</a>
                <?php else: ?>
                    <a class="btn-nav-outline" href="login.php">Inloggen</a>
                <?php endif; ?>
            </div>
        </section>
    </main>
</div>

<footer class="app-footer">
    <p>&copy; 2026 StreamHive - gemaakt door daniel wang</p>
</footer>
</body>
</html>
