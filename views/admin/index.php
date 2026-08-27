<main class="admin-container">
    <header class="admin-header">
        <h1><?= __('admin_title') ?></h1>
        <div class="admin-actions-top">
            <a href="index.php?route=recipe_create" class="btn btn-primary"><?= __('admin_btn_create_recipe') ?></a>
        </div>
    </header>

    <?php if ($message !== null): ?>
        <div class="admin-feedback <?= $isError ? 'admin-feedback-error' : 'admin-feedback-success' ?>">
            <?= htmlspecialchars($message) ?>
        </div>
    <?php endif; ?>

    <nav class="admin-tabs">
        <a href="index.php?route=admin&tab=ingredients" class="tab-btn <?= $activeTab === 'ingredients' ? 'active' : '' ?>"><?= __('admin_tab_ingredients') ?></a>
        <a href="index.php?route=admin&tab=categories" class="tab-btn <?= $activeTab === 'categories' ? 'active' : '' ?>"><?= __('admin_tab_categories') ?></a>
        <a href="index.php?route=admin&tab=users" class="tab-btn <?= $activeTab === 'users' ? 'active' : '' ?>"><?= __('admin_tab_users') ?></a>
        <a href="index.php?route=admin&tab=xml_backup" class="tab-btn <?= $activeTab === 'xml_backup' ? 'active' : '' ?>"><?= __('admin_tab_xml_backup') ?></a>
    </nav>

    <?php
    $tabFile = __DIR__ . "/tab_{$activeTab}.php";
    if (file_exists($tabFile)) {
        require $tabFile;
    }
    ?>
</main>