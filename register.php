<?php
session_start();

if (isset($_SESSION['user_id'])) {
    header('Location: videos.php');
    exit;
}
?>
<!DOCTYPE html>
<html lang="nl">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>StreamHive - Registreren</title>
    <link rel="stylesheet" href="css/style.css">
</head>
<body>
    <main class="auth-page">
        <section class="auth-card">
            <h1>🎮 Account maken</h1>
            <p class="auth-subtitle">Maak een account aan om mee te doen op StreamHive.</p>

            <div id="registerError" class="message error hidden"></div>
            <div id="registerSuccess" class="message success hidden"></div>

            <form id="registerForm" method="post">
                <input type="hidden" name="action" value="register">
                <input type="hidden" name="role" value="user">

                <div class="input-field-group">
                    <label for="registerName">Volledige naam</label>
                    <input type="text" id="registerName" name="name" required>
                </div>

                <div class="input-field-group">
                    <label for="registerEmail">Email</label>
                    <input type="email" id="registerEmail" name="email" required>
                </div>

                <div class="input-field-group">
                    <label for="registerPassword">Wachtwoord</label>
                    <input type="password" id="registerPassword" name="password" required minlength="6">
                </div>

                <div class="input-field-group">
                    <label for="registerPasswordConfirm">Wachtwoord bevestigen</label>
                    <input type="password" id="registerPasswordConfirm" name="password_confirm" required minlength="6">
                </div>

                <button class="btn-action-submit" type="submit">Registreren</button>
            </form>

            <p class="auth-switch">Al een account? <a href="login.php">Log hier in</a></p>
        </section>
    </main>

    <script>
        document.getElementById('registerForm').addEventListener('submit', async (event) => {
            event.preventDefault();

            const errorBox = document.getElementById('registerError');
            const successBox = document.getElementById('registerSuccess');
            const password = document.getElementById('registerPassword').value;
            const passwordConfirm = document.getElementById('registerPasswordConfirm').value;

            errorBox.classList.add('hidden');
            successBox.classList.add('hidden');

            if (password !== passwordConfirm) {
                errorBox.textContent = 'Wachtwoorden komen niet overeen!';
                errorBox.classList.remove('hidden');
                return;
            }

            try {
                const response = await fetch('auth.php', {
                    method: 'POST',
                    body: new FormData(event.target),
                    credentials: 'same-origin'
                });
                const data = await response.json();

                if (data.success) {
                    successBox.textContent = data.message;
                    successBox.classList.remove('hidden');
                    event.target.reset();
                    setTimeout(() => {
                        window.location.href = 'login.php';
                    }, 1500);
                } else {
                    errorBox.textContent = data.message;
                    errorBox.classList.remove('hidden');
                }
            } catch (error) {
                console.error('Register error:', error);
                errorBox.textContent = 'Fout bij registreren. Controleer de verbinding.';
                errorBox.classList.remove('hidden');
            }
        });
    </script>
</body>
</html>
