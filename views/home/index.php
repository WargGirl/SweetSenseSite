<div class="home-container">

    <!-- Hero Section -->
    <section class="hero-block">
        <span class="hero-badge">✨ SweetSense Bakery & Recipes</span>
        <h1 class="hero-title"><?= __('home_hero_title') ?></h1>
        <p class="hero-subtitle"><?= __('home_hero_desc') ?></p>
    </section>

    <section class="hub-section">
        <h2 class="section-heading">🧭 <?= __('home_sec_navigation') ?></h2>
        
        <div class="hub-grid">
            
            <div class="hub-card">
                <div class="hub-icon">📖</div>
                <div class="hub-info">
                    <h3><?= __('nav_catalog') ?></h3>
                    <p><?= __('home_catalog_desc') ?></p>
                </div>
                <a href="index.php?route=catalog" class="hub-link">
                    <?= __('home_btn_go') ?> →
                </a>
            </div>

            <div class="hub-card">
                <div class="hub-icon">🛒</div>
                <div class="hub-info">
                    <h3><?= __('nav_cart') ?></h3>
                    <p><?= __('home_cart_desc') ?></p>
                </div>
                <a href="index.php?route=cart" class="hub-link">
                    <?= __('home_btn_go') ?> →
                </a>
            </div>

            <div class="hub-card">
                <div class="hub-icon">💬</div>
                <div class="hub-info">
                    <h3><?= __('nav_support') ?></h3>
                    <p><?= __('home_support_desc') ?></p>
                </div>
                <a href="index.php?route=support" class="hub-link">
                    <?= __('home_btn_go') ?> →
                </a>
            </div>

        </div>
    </section>

    <section class="features-section">
        <div class="feature-item">
            <span class="feature-icon">🍰</span>
            <h4><?= __('home_feat_recipes_title') ?></h4>
            <p><?= __('home_feat_recipes_desc') ?></p>
        </div>
        <div class="feature-item">
            <span class="feature-icon">📋</span>
            <h4><?= __('home_feat_list_title') ?></h4>
            <p><?= __('home_feat_list_desc') ?></p>
        </div>
        <div class="feature-item">
            <span class="feature-icon">🌐</span>
            <h4><?= __('home_feat_lang_title') ?></h4>
            <p><?= __('home_feat_lang_desc') ?></p>
        </div>
    </section>

</div>