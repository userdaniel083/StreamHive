<?php
session_start();

if (!isset($_SESSION['user_id'])) {
    header('Location: login.php');
    exit;
}

require_once 'database.php';
require_once 'partials/menu.php';
?>
<!DOCTYPE html>
<html lang="nl">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>StreamHive - Video uploaden</title>
    <link rel="stylesheet" href="css/style.css">
</head>
<body>
    <?php renderMenu('upload'); ?>

    <main class="content-viewport">
        <h1 class="page-title">Video uploaden</h1>
        <p class="page-subtitle">Upload een MP4, WebM of Ogg video naar de map <code>uploads/</code>.</p>

        <section class="video-upload-card upload-page-card">
            <div id="uploadMessage" class="message hidden"></div>

            <form id="uploadForm" action="upload.php" method="POST" enctype="multipart/form-data">
                <div class="input-field-group">
                    <label for="videoTitle">Video titel</label>
                    <input type="text" id="videoTitle" name="title" required>
                </div>

                <div class="input-field-group">
                    <label for="videoDescription">Beschrijving</label>
                    <textarea id="videoDescription" name="description" rows="5" placeholder="Vertel waar je video over gaat..."></textarea>
                </div>

                <div class="input-field-group">
                    <label for="videoFile">Video bestand</label>
                    <input type="file" id="videoFile" name="fileToUpload" accept="video/mp4,video/webm,video/ogg,video/*" required>
                </div>

                <button class="btn-action-submit" type="submit">📤 Uploaden</button>
            </form>
        </section>
    </main>
</div>

<footer class="app-footer">
    <p>&copy; 2026 StreamHive - gemaakt door daniel wang</p>
</footer>

<script>
    document.getElementById('uploadForm').addEventListener('submit', async (event) => {
        event.preventDefault();

        const form = event.target;
        const button = form.querySelector('button[type="submit"]');
        const message = document.getElementById('uploadMessage');
        const formData = new FormData(form);

        message.className = 'message hidden';
        button.disabled = true;
        button.textContent = '⏳ Uploaden...';

        try {
            const response = await fetch('upload.php', {
                method: 'POST',
                body: formData,
                credentials: 'same-origin'
            });
            const data = await response.json();

            if (data.success) {
                message.className = 'message success';
                message.innerHTML = `${data.message} <a href="video.php?id=${data.video_id}">Bekijk video</a>`;
                form.reset();
            } else {
                message.className = 'message error';
                message.textContent = data.message;
            }
        } catch (error) {
            console.error('Upload error:', error);
            message.className = 'message error';
            message.textContent = 'Er is iets misgegaan tijdens het uploaden.';
        } finally {
            button.disabled = false;
            button.textContent = '📤 Uploaden';
        }
    });
</script>
</body>
</html>
