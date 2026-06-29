<?php
session_start();

if (isset($_SESSION["user_id"])) {
    header("Location: videos.php");
    exit();
}
?>
<!DOCTYPE html>
<html lang="nl">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>StreamHive - Inloggen</title>
    <link rel="stylesheet" href="css/style.css">
</head>
<body>
    <main class="auth-page">
        <section class="auth-card">
            <h1>🎮 StreamHive</h1>
            <p class="auth-subtitle">Log in om video’s te uploaden, liken en reacties te plaatsen.</p>

            <div id="loginError" class="message error hidden"></div>

            <form id="loginForm" method="post">
                <input type="hidden" name="action" value="login">

                <div class="input-field-group">
                    <label for="loginEmail">Email</label>
                    <input type="email" id="loginEmail" name="email" placeholder="jouw@email.com" required>
                </div>

                <div class="input-field-group">
                    <label for="loginPassword">Wachtwoord</label>
                    <input type="password" id="loginPassword" name="password" placeholder="Wachtwoord" required>
                </div>

                <button class="btn-action-submit" type="submit">Inloggen</button>
            </form>

            <p class="auth-switch">Nog geen account? <a href="register.php">Registreer hier</a></p>
        </section>
    </main>

    <script>
        document.getElementById('loginForm').addEventListener('submit', async (event) => {
            event.preventDefault();

            const errorBox = document.getElementById('loginError');
            errorBox.classList.add('hidden');

            try {
                const response = await fetch('auth.php', {
                    method: 'POST',
                    body: new FormData(event.target),
                    credentials: 'same-origin'
                });
                const data = await response.json();

                if (data.success) {
                    window.location.href = 'videos.php';
                } else {
                    errorBox.textContent = data.message;
                    errorBox.classList.remove('hidden');
                }
            } catch (error) {
                console.error('Login error:', error);
                errorBox.textContent = 'Fout bij inloggen. Controleer de verbinding.';
                errorBox.classList.remove('hidden');
            }
        });
    </script>
</body>
</html>
