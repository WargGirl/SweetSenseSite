<?php
$currentLang = $_SESSION['lang'] ?? DEFAULT_LANG;
$uaParams = $_GET;
$uaParams['lang'] = 'uk';
$uaUrl = '?' . http_build_query($uaParams);

$enParams = $_GET;
$enParams['lang'] = 'en';
$enUrl = '?' . http_build_query($enParams);
?>
<!DOCTYPE html>
<html lang="<?= htmlspecialchars($currentLang) ?>">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?= htmlspecialchars($pageTitle ?? SITE_NAME) ?></title>
    <link rel="stylesheet" href="<?= BASE_URL ?>/assets/css/main.css">
    <link rel="icon" href="data:image/svg+xml,<svg xmlns=%22http://www.w3.org/2000/svg%22 viewBox=%220 0 100 100%22><text y=%22.9em%22 font-size=%2290%22>🧁</text></svg>">
</head>
<script>
    window.currentUserId = <?= (int)($_SESSION['user_id'] ?? 0) ?>;
    window.currentUserName = '<?= htmlspecialchars($_SESSION['username'] ?? 'Guest', ENT_QUOTES) ?>';
    window.currentUserRole = '<?= htmlspecialchars($_SESSION['user_role'] ?? 'user', ENT_QUOTES) ?>';
</script>
<script src="assets/js/support_chat.js" defer></script>
<body>
    <header class="site-header">
        <div class="container header-content">
            <a href="<?= BASE_URL ?>/index.php" class="logo"><?= htmlspecialchars(SITE_NAME) ?></a>

            <nav class="main-nav">
                <a href="<?= BASE_URL ?>/index.php"><?= __('nav_home') ?></a>
                <a href="<?= BASE_URL ?>/index.php?route=catalog"><?= __('nav_catalog') ?></a>
                <a href="<?= BASE_URL ?>/index.php?route=cart"><?= __('nav_cart') ?></a>
                <a href="<?= BASE_URL ?>/index.php?route=support"><?= __('nav_support') ?></a>
            </nav>

            <div class="lang-switch">
                <a href="<?= htmlspecialchars($uaUrl) ?>" class="<?= $currentLang === 'uk' ? 'active' : '' ?>">UA</a> | 
                <a href="<?= htmlspecialchars($enUrl) ?>" class="<?= $currentLang === 'en' ? 'active' : '' ?>">EN</a>
            </div>

            <div class="auth-links">
                <?php if (!empty($_SESSION['user_id'])): ?>
                    <div class="user-dropdown">
                        <button type="button" class="user-dropdown-btn">
                            <span class="user-avatar-icon">👤</span>
                            <span class="user-name"><?= htmlspecialchars($_SESSION['user_name'] ?? __('nav_profile')) ?></span>
                            <span class="dropdown-arrow">▾</span>
                        </button>
                        
                        <div class="user-dropdown-menu">
                            <a href="<?= BASE_URL ?>/index.php?route=profile" class="dropdown-item">
                                <?= __('nav_profile') ?>
                            </a>
                            
                            <?php if (($_SESSION['user_role'] ?? '') === 'admin'): ?>
                                <a href="<?= BASE_URL ?>/index.php?route=admin" class="dropdown-item admin-item">
                                    <?= __('nav_admin') ?>
                                </a>
                            <?php endif; ?>
                            
                            <div class="dropdown-divider"></div>
                            
                            <a href="<?= BASE_URL ?>/index.php?route=logout" class="dropdown-item logout-item">
                                <?= __('nav_logout') ?>
                            </a>
                        </div>
                    </div>
                <?php else: ?>
                    <a href="<?= BASE_URL ?>/index.php?route=login" class="btn-auth btn-login"><?= __('nav_login') ?></a>
                    <a href="<?= BASE_URL ?>//index.php?route=register" class="btn-auth btn-register"><?= __('nav_register') ?></a>
                <?php endif; ?>
            </div>
        </div>
    </header>

    <main class="main-content container">
    