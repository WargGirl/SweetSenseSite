<section id="tab-ingredients" class="admin-tab-content active">
    <form method="GET" class="table-controls">
        <input type="hidden" name="route" value="admin">
        <input type="hidden" name="tab" value="ingredients">
        <input type="text" name="ingredient_q" value="<?= htmlspecialchars($search) ?>" placeholder="<?= __('admin_search_ingredient') ?>" class="search-input">
        
        <div class="filter-group">
            <select name="ingredient_sort" class="select-input sort-select" onchange="this.form.submit()">
                <option value="name_asc" <?= $sort === 'name_asc' ? 'selected' : '' ?>><?= __('admin_sort_alpha_asc') ?></option>
                <option value="name_desc" <?= $sort === 'name_desc' ? 'selected' : '' ?>><?= __('admin_sort_alpha_desc') ?></option>
                <option value="date_new" <?= $sort === 'date_new' ? 'selected' : '' ?>><?= __('admin_sort_newest') ?></option>
                <option value="date_old" <?= $sort === 'date_old' ? 'selected' : '' ?>><?= __('admin_sort_oldest') ?></option>
            </select>
        </div>
        <button type="submit" class="btn btn-secondary admin-btn-add-sm"><?= __('admin_search_button') ?></button>
        <a href="index.php?route=admin&tab=ingredients&ingredient_action=new#ingredient-editor" class="btn btn-secondary admin-btn-add"><?= __('admin_btn_add_ingredient') ?></a>
    </form>

    <?php if (isset($_GET['ingredient_action']) && $_GET['ingredient_action'] === 'new'): ?>
        <form id="ingredient-editor" method="POST" class="ingredient-editor">
            <input type="hidden" name="ingredient_action" value="save">
            <input type="text" name="name_uk" placeholder="<?= __('admin_th_name_uk') ?>" required>
            <input type="text" name="name_en" placeholder="<?= __('admin_th_name_en') ?>" required>
            <button type="submit" class="btn btn-primary"><?= __('admin_btn_add') ?></button>
            <a href="index.php?route=admin&tab=ingredients" class="btn btn-secondary"><?= __('reviews_btn_cancel') ?></a>
        </form>
    <?php elseif ($editIngredient !== null): ?>
        <form id="ingredient-editor" method="POST" class="ingredient-editor">
            <input type="hidden" name="ingredient_action" value="save">
            <input type="hidden" name="ingredient_id" value="<?= (int)$editIngredient['id'] ?>">
            <input type="text" name="name_uk" value="<?= htmlspecialchars($editIngredient['name_uk']) ?>" required>
            <input type="text" name="name_en" value="<?= htmlspecialchars($editIngredient['name_en']) ?>" required>
            <button type="submit" class="btn btn-primary"><?= __('admin_action_edit') ?></button>
            <a href="index.php?route=admin&tab=ingredients" class="btn btn-secondary"><?= __('reviews_btn_cancel') ?></a>
        </form>
    <?php endif; ?>

    <table class="admin-table table-ingredients">
        <thead>
            <tr>
                <th class="col-ing-id"><?= __('admin_th_id') ?></th>
                <th class="col-ing-uk"><?= __('admin_th_name_uk') ?></th>
                <th class="col-ing-en"><?= __('admin_th_name_en') ?></th>
                <th class="col-ing-linked"><?= __('admin_th_linked_recipes') ?></th>
                <th class="col-ing-act"><?= __('admin_th_actions') ?></th>
            </tr>
        </thead>
        <tbody>
            <?php foreach ($ingredients as $ingredient): ?>
                <?php $linkedRecipes = (int)$ingredient['linked_recipes']; ?>
                <tr>
                    <td class="col-ing-id"><?= (int)$ingredient['id'] ?></td>
                    <td class="col-ing-uk"><?= htmlspecialchars($ingredient['name_uk']) ?></td>
                    <td class="col-ing-en"><?= htmlspecialchars($ingredient['name_en']) ?></td>
                    <td class="col-ing-linked"><span class="badge <?= $linkedRecipes > 0 ? 'badge-info' : 'badge-muted' ?>"><?= $linkedRecipes ?> <?= __('admin_recipes_suffix') ?></span></td>
                    <td class="col-ing-act">
                        <a class="btn-icon" href="index.php?route=admin&tab=ingredients&ingredient_q=<?= urlencode($search) ?>&ingredient_sort=<?= urlencode($sort) ?>&edit_ingredient=<?= (int)$ingredient['id'] ?>#ingredient-editor" title="<?= __('admin_action_edit') ?>">✏️</a>
                        <?php if ($linkedRecipes > 0): ?>
                            <button class="btn-icon btn-disabled" title="<?= __('admin_cannot_delete_used') ?>" disabled>🔒</button>
                        <?php else: ?>
                            <form method="POST" class="inline-form" onsubmit="return confirm('<?= htmlspecialchars(__('admin_confirm_delete'), ENT_QUOTES) ?>');">
                                <input type="hidden" name="ingredient_action" value="delete">
                                <input type="hidden" name="ingredient_id" value="<?= (int)$ingredient['id'] ?>">
                                <button type="submit" class="btn-icon btn-danger" title="<?= __('admin_action_delete') ?>">🗑️</button>
                            </form>
                        <?php endif; ?>
                    </td>
                </tr>
            <?php endforeach; ?>
        </tbody>
    </table>
</section>