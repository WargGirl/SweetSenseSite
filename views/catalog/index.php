<!-- views/catalog/index.php -->
<?php
    $ingredientsList = $allIngredients ?? [];
    $categoriesList  = $categories ?? [];
    $selectedCategory = (int)($_GET['category'] ?? 0);

    $hasActiveFilters = !empty($includeIngredients) 
                     || !empty($excludeIngredients) 
                     || !empty($selectedCategory)
                     || !empty($_GET['time_from']) 
                     || !empty($_GET['time_to']) 
                     || !empty($_GET['min_rating']) 
                     || !empty($_GET['difficulty']);
?>

<div class="catalog-search-bar">
    <form action="index.php" method="GET">
        <input type="hidden" name="route" value="catalog">

        <!-- Верхній ряд: пошук, сортування, кнопка -->
        <div class="search-top-row">
            <input type="text" 
                   name="q"
                   autocomplete="off"
                   placeholder="<?= htmlspecialchars(__('search_placeholder')) ?>" 
                   value="<?= htmlspecialchars($searchQuery ?? '') ?>">

            <select name="sort" onchange="this.form.submit()">
                <option value="rating" <?= (($selectedSort ?? 'rating') === 'rating') ? 'selected' : '' ?>>
                    <?= __('sort_rating') ?>
                </option>
                <option value="popularity" <?= (($selectedSort ?? '') === 'popularity') ? 'selected' : '' ?>>
                    <?= __('sort_popularity') ?>
                </option>
                <option value="time" <?= (($selectedSort ?? '') === 'time') ? 'selected' : '' ?>>
                    <?= __('sort_time') ?>
                </option>
                <option value="difficulty" <?= (($selectedSort ?? '') === 'difficulty') ? 'selected' : '' ?>>
                    <?= __('sort_difficulty') ?>
                </option>
            </select>

            <button type="submit">🔍 <?= __('search_btn') ?></button>
        </div>

        <!-- Головна панель фільтрів -->
        <details class="ingredients-filter-details" <?= $hasActiveFilters ? 'open' : '' ?>>
            <summary class="ingredients-filter-summary">
                ⚙️ <?= __('filters_btn') ?>
                <?php if ($hasActiveFilters): ?>
                    <span class="active-badge">•</span>
                <?php endif; ?>
            </summary>

            <!-- Верхній ряд фільтрів: Категорія, Час, Рейтинг, Складність -->
            <div class="main-filters-grid">
                <!-- Категорія -->
                <div class="filter-group">
                    <label><?= __('filter_category') ?></label>
                    <select name="category" class="filter-select">
                        <option value="0"><?= __('filter_category_all') ?></option>
                        <?php foreach ($categoriesList as $cat): ?>
                            <?php 
                                $catName = ($currentLang === 'en') 
                                    ? ($cat['name_en'] ?? $cat['name_uk'] ?? '') 
                                    : ($cat['name_uk'] ?? '');
                            ?>
                            <option value="<?= (int)$cat['id'] ?>" <?= ($selectedCategory === (int)$cat['id']) ? 'selected' : '' ?>>
                                <?= htmlspecialchars($catName) ?>
                            </option>
                        <?php endforeach; ?>
                    </select>
                </div>

                <!-- Час приготування -->
                <div class="filter-group">
                    <label><?= __('filter_time') ?></label>
                    <div class="time-inputs">
                        <input type="number" 
                               name="time_from" 
                               min="0" 
                               placeholder="<?= __('filter_time_from') ?>" 
                               value="<?= htmlspecialchars($_GET['time_from'] ?? '') ?>">
                        <span>—</span>
                        <input type="number" 
                               name="time_to" 
                               min="0" 
                               placeholder="<?= __('filter_time_to') ?>" 
                               value="<?= htmlspecialchars($_GET['time_to'] ?? '') ?>">
                    </div>
                </div>

                <!-- Рейтинг -->
                <div class="filter-group">
                    <label><?= __('filter_min_rating') ?></label>
                    <select name="min_rating" class="filter-select">
                        <option value=""><?= __('filter_rating_all') ?></option>
                        <option value="4.5" <?= (($_GET['min_rating'] ?? '') === '4.5') ? 'selected' : '' ?>>★ 4.5+</option>
                        <option value="4.0" <?= (($_GET['min_rating'] ?? '') === '4.0') ? 'selected' : '' ?>>★ 4.0+</option>
                        <option value="3.0" <?= (($_GET['min_rating'] ?? '') === '3.0') ? 'selected' : '' ?>>★ 3.0+</option>
                    </select>
                </div>

                <!-- Складність -->
                <div class="filter-group">
                    <label><?= __('filter_difficulty') ?></label>
                    <select name="difficulty" class="filter-select">
                        <option value=""><?= __('diff_all') ?></option>
                        <option value="easy" <?= (($_GET['difficulty'] ?? '') === 'easy') ? 'selected' : '' ?>><?= __('diff_easy') ?></option>
                        <option value="medium" <?= (($_GET['difficulty'] ?? '') === 'medium') ? 'selected' : '' ?>><?= __('diff_medium') ?></option>
                        <option value="hard" <?= (($_GET['difficulty'] ?? '') === 'hard') ? 'selected' : '' ?>><?= __('diff_hard') ?></option>
                    </select>
                </div>
            </div>

            <!-- Нижній ряд: Інгредієнти -->
            <div class="ingredients-filter-body">
                <div class="filter-col include-col">
                    <h4>✅ <?= __('include_ingredients') ?></h4>
                    <div class="checkbox-scroll-list">
                        <?php if (empty($ingredientsList)): ?>
                            <span class="no-items-text"><?= __('no_ingredients_found') ?></span>
                        <?php else: ?>
                            <?php foreach ($ingredientsList as $ing): ?>
                                <label class="checkbox-item">
                                    <input type="checkbox" 
                                           name="include_ingredients[]" 
                                           value="<?= (int)$ing['id'] ?>"
                                           <?= in_array((int)$ing['id'], $includeIngredients ?? []) ? 'checked' : '' ?>>
                                    <span><?= htmlspecialchars($ing['name']) ?></span>
                                </label>
                            <?php endforeach; ?>
                        <?php endif; ?>
                    </div>
                </div>

                <div class="filter-col exclude-col">
                    <h4>🚫 <?= __('exclude_ingredients') ?></h4>
                    <div class="checkbox-scroll-list">
                        <?php if (empty($ingredientsList)): ?>
                            <span class="no-items-text"><?= __('no_ingredients_found') ?></span>
                        <?php else: ?>
                            <?php foreach ($ingredientsList as $ing): ?>
                                <label class="checkbox-item">
                                    <input type="checkbox" 
                                           name="exclude_ingredients[]" 
                                           value="<?= (int)$ing['id'] ?>"
                                           <?= in_array((int)$ing['id'], $excludeIngredients ?? []) ? 'checked' : '' ?>>
                                    <span><?= htmlspecialchars($ing['name']) ?></span>
                                </label>
                            <?php endforeach; ?>
                        <?php endif; ?>
                    </div>
                </div>
            </div>

            <div class="filter-actions">
                <button type="submit" class="btn-apply-filter"><?= __('apply_filters') ?></button>
                <a href="index.php?route=catalog" class="btn-reset-filter">
                    <?= __('reset_filters') ?>
                </a>
            </div>
        </details>
    </form>
</div>

<?php if (empty($recipes)): ?>
    <p class="no-recipes"><?= __('no_recipes_found') ?></p>
<?php else: ?>
    <div class="recipe-grid">
        <?php foreach ($recipes as $recipe): ?>
            <?php
                $title = is_array($recipe['title'] ?? null)
                    ? ($recipe['title'][$currentLang] ?? $recipe['title']['uk'] ?? '')
                    : ($recipe['title'] ?? '');

                $desc = is_array($recipe['description'] ?? null)
                    ? ($recipe['description'][$currentLang] ?? $recipe['description']['uk'] ?? '')
                    : ($recipe['description'] ?? '');

                $categoryName = ($currentLang === 'en')
                    ? ($recipe['category_name_en'] ?? $recipe['category_name'] ?? '')
                    : ($recipe['category_name_uk'] ?? $recipe['category_name'] ?? '');

                $rawImg = $recipe['image_url'] ?? $recipe['image'] ?? '';
                if (!empty($rawImg)) {
                    if (str_starts_with($rawImg, 'http') || str_starts_with($rawImg, '/')) {
                        $image = $rawImg;
                    } else {
                        $image = BASE_URL . '/' . ltrim($rawImg, '/');
                    }
                } else {
                    $image = BASE_URL . '/assets/img/placeholder.jpg';
                }
                $rating = number_format((float)($recipe['avg_rating'] ?? 0), 1);
                $reviewsCount = (int)($recipe['reviews_count'] ?? 0);
                $diffKey = $recipe['difficulty'] ?? 'easy';
                $diffText = match($diffKey) {
                    'hard'   => __('diff_hard'),
                    'medium' => __('diff_medium'),
                    default  => __('diff_easy')
                };
            ?>
            <div class="recipe-card">
                <div class="card-image-wrap">
                    <div class="card-image">
                        <img src="<?= htmlspecialchars($image) ?>" alt="<?= htmlspecialchars($title) ?>">
                    </div>
                    <span class="badge-difficulty badge-<?= htmlspecialchars($diffKey) ?>">
                        <?= htmlspecialchars($diffText) ?>
                    </span>
                </div>

                <div class="card-body">
                    <div class="card-meta-top">
                        <div class="rating-stars">
                            <span class="star-icon">★</span>
                            <strong><?= $rating ?></strong>
                            <span class="reviews-count">(<?= $reviewsCount ?>)</span>
                        </div>

                        <?php if (!empty($categoryName)): ?>
                            <span class="card-category-tag"><?= htmlspecialchars($categoryName) ?></span>
                        <?php endif; ?>
                    </div>

                    <h3><?= htmlspecialchars($title) ?></h3>
                    <p><?= htmlspecialchars($desc) ?></p>
                </div>

                <div class="card-footer">
                    <span class="time">⏱️ <?= htmlspecialchars($recipe['time']) ?></span>
                    <a href="index.php?route=recipe&id=<?= (int)$recipe['id'] ?>" class="btn-view">
                        <?= __('btn_view_recipe') ?>
                    </a>
                </div>
            </div>
        <?php endforeach; ?>
    </div>
<?php endif; ?>