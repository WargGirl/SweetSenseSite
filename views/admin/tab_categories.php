<section id="tab-categories" class="admin-tab-content active">
    <form method="GET" class="table-controls">
        <input type="hidden" name="route" value="admin">
        <input type="hidden" name="tab" value="categories">
        <input type="text" name="category_q" value="<?= htmlspecialchars($categorySearch) ?>" placeholder="<?= __('admin_search_category') ?>" class="search-input">
        <div class="filter-group">
            <select name="category_sort" class="select-input sort-select" onchange="this.form.submit()">
                <option value="name_asc" <?= $categorySort === 'name_asc' ? 'selected' : '' ?>><?= __('admin_sort_alpha_asc') ?></option>
                <option value="name_desc" <?= $categorySort === 'name_desc' ? 'selected' : '' ?>><?= __('admin_sort_alpha_desc') ?></option>
                <option value="date_new" <?= $categorySort === 'date_new' ? 'selected' : '' ?>><?= __('admin_sort_newest') ?></option>
                <option value="date_old" <?= $categorySort === 'date_old' ? 'selected' : '' ?>><?= __('admin_sort_oldest') ?></option>
            </select>
        </div>
        <button type="submit" class="btn btn-secondary admin-btn-add-sm"><?= __('admin_search_button') ?></button>
        <a href="index.php?route=admin&tab=categories&category_action=new#category-editor" class="btn btn-secondary admin-btn-add"><?= __('admin_btn_add_category') ?></a>
    </form>

    <?php if (isset($_GET['category_action']) && $_GET['category_action'] === 'new'): ?>
        <form id="category-editor" method="POST" class="ingredient-editor">
            <input type="hidden" name="category_action" value="save">
            <input type="text" name="name_uk" placeholder="<?= __('admin_th_name_uk') ?>" required>
            <input type="text" name="name_en" placeholder="<?= __('admin_th_name_en') ?>" required>
            <button type="submit" class="btn btn-primary"><?= __('admin_btn_add') ?></button>
            <a href="index.php?route=admin&tab=categories" class="btn btn-secondary"><?= __('reviews_btn_cancel') ?></a>
        </form>
    <?php elseif ($editCategory !== null): ?>
        <form id="category-editor" method="POST" class="ingredient-editor">
            <input type="hidden" name="category_action" value="save">
            <input type="hidden" name="category_id" value="<?= (int)$editCategory['id'] ?>">
            <input type="text" name="name_uk" value="<?= htmlspecialchars($editCategory['name_uk']) ?>" required>
            <input type="text" name="name_en" value="<?= htmlspecialchars($editCategory['name_en']) ?>" required>
            <button type="submit" class="btn btn-primary"><?= __('admin_action_edit') ?></button>
            <a href="index.php?route=admin&tab=categories" class="btn btn-secondary"><?= __('reviews_btn_cancel') ?></a>
        </form>
    <?php endif; ?>

    <table class="admin-table table-categories">
        <thead>
            <tr>
                <th class="col-cat-id"><?= __('admin_th_id') ?></th>
                <th class="col-cat-uk"><?= __('admin_th_name_uk') ?></th>
                <th class="col-cat-en"><?= __('admin_th_name_en') ?></th>
                <th class="col-cat-act"><?= __('admin_th_actions') ?></th>
            </tr>
        </thead>
        <tbody>
            <?php foreach ($categories as $category): ?>
                <tr>
                    <td class="col-cat-id"><?= (int)$category['id'] ?></td>
                    <td class="col-cat-uk"><?= htmlspecialchars($category['name_uk']) ?></td>
                    <td class="col-cat-en"><?= htmlspecialchars($category['name_en']) ?></td>
                    <td class="col-cat-act">
                        <a class="btn-icon" href="index.php?route=admin&tab=categories&category_q=<?= urlencode($categorySearch) ?>&category_sort=<?= urlencode($categorySort) ?>&edit_category=<?= (int)$category['id'] ?>#category-editor" title="<?= __('admin_action_edit') ?>">✏️</a>
                    </td>
                </tr>
            <?php endforeach; ?>
        </tbody>
    </table>
</section>