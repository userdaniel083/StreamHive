<?php
if (!function_exists('renderMenu')) {
    function renderMenu($activePage = 'videos') {
        $isLoggedIn = isset($_SESSION['user_id']);
        $userName = $isLoggedIn ? $_SESSION['user_name'] : '';
        $firstLetter = $userName !== '' ? strtoupper(substr($userName, 0, 1)) : '?';
        $search = isset($_GET['q']) ? trim($_GET['q']) : '';

        $active = function ($page) use ($activePage) {
            return $activePage === $page ? ' active' : '';
        };
        ?>
        <header class="main-header">
            <div class="header-left">
                <a class="logo" href="videos.php" style="text-decoration: none;">🎮 <span>StreamHive</span></a>
            </div>

            <div class="header-center">
                <form class="search-form" action="videos.php" method="GET">
                    <input type="text" name="q" value="<?php echo htmlspecialchars($search); ?>" placeholder="Zoek video’s...">
                    <button class="search-btn" type="submit">Zoeken</button>
                </form>
            </div>

            <div class="header-right">
                <?php if ($isLoggedIn): ?>
                    <a class="btn-nav-outline" href="upload_page.php">Uploaden</a>
                    <div class="user-profile-menu">
                        <div class="user-avatar-circle"><?php echo htmlspecialchars($firstLetter); ?></div>
                        <span><?php echo htmlspecialchars($userName); ?></span>
                        <a class="btn-nav-outline" href="logout.php">Uitloggen</a>
                    </div>
                <?php else: ?>
                    <a class="btn-nav-outline" href="register.php">Registreren</a>
                    <a class="btn-nav-primary" href="login.php" style="text-decoration: none;">Inloggen</a>
                <?php endif; ?>
            </div>
        </header>

        <div class="app-container">
            <aside class="sidebar-menu">
                <div class="sidebar-section">
                    <div class="sidebar-heading">Menu</div>
                    <a class="sidebar-item<?php echo $active('home'); ?>" href="index.php">🏠 Home</a>
                    <a class="sidebar-item<?php echo $active('videos'); ?>" href="videos.php">🎬 Video’s</a>
                    <?php if ($isLoggedIn): ?>
                        <a class="sidebar-item<?php echo $active('upload'); ?>" href="upload_page.php">📤 Upload video</a>
                    <?php endif; ?>
                </div>
                <div class="sidebar-divider"></div>
                <div class="sidebar-section">
                    <div class="sidebar-heading">Account</div>
                    <?php if ($isLoggedIn): ?>
                        <a class="sidebar-item" href="logout.php">🚪 Uitloggen</a>
                    <?php else: ?>
                        <a class="sidebar-item<?php echo $active('login'); ?>" href="login.php">🔐 Inloggen</a>
                        <a class="sidebar-item<?php echo $active('register'); ?>" href="register.php">📝 Registreren</a>
                    <?php endif; ?>
                </div>
            </aside>
        <?php
    }
}
