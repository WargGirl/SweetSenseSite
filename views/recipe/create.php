<?php
$isEdit = !empty($recipeData['id']);
$formAction = $isEdit ? 'index.php?route=recipe_edit&id=' . (int)$recipeData['id'] : 'index.php?route=recipe_create';
$pageHeading = $isEdit ? ($isUk ? 'Редагування рецепта' : 'Edit Recipe') : __('recipe_create_title');

$existingImg    = $existingImg ?? $recipeData['image_url'] ?? $recipeData['image'] ?? '';
$hasExistingImg = !empty($existingImg);

$ingredients = $recipeData['ingredients'] ?? [
    ['name' => '', 'amount' => '', 'unit_id' => 1]
];
$steps = $recipeData['steps'] ?? [
    ['uk' => '', 'en' => '', 'tip_uk' => '', 'tip_en' => '']
];
?>

<main class="recipe-form-container">
    <header class="recipe-form-header">
        <a href="<?= $isEdit ? 'index.php?route=recipe&id=' . (int)$recipeData['id'] : 'index.php?route=admin' ?>" class="back-link">
            <?= __('recipe_create_back') ?>
        </a>
        <h1><?= htmlspecialchars($pageHeading) ?></h1>
    </header>

    <?php if (!empty($errorMessage)): ?>
        <div style="background: #fee2e2; color: #b91c1c; padding: 14px 18px; border-radius: 8px; margin-bottom: 20px; font-weight: 500; border: 1px solid #f87171;">
            ⚠️ <?= htmlspecialchars($errorMessage) ?>
        </div>
    <?php endif; ?>

    <form id="recipe-main-form" 
          action="<?= $formAction ?>" 
          method="POST" 
          enctype="multipart/form-data" 
          class="recipe-admin-form" 
          data-has-image="<?= $hasExistingImg ? '1' : '0' ?>"
          autocomplete="off" 
          novalidate>
        
        <?php if ($isEdit): ?>
            <input type="hidden" name="recipe_id" value="<?= (int)$recipeData['id'] ?>">
        <?php endif; ?>

        <!-- General Info -->
        <section class="form-card">
            <h2 class="card-title"><?= __('recipe_sec_general') ?></h2>
            
            <div class="form-row-two">
                <div class="form-group">
                    <label><?= __('recipe_field_title_uk') ?> <span class="req">*</span></label>
                    <input type="text" id="title_uk" name="title_uk" class="form-control" 
                           value="<?= htmlspecialchars($recipeData['title']['uk'] ?? '') ?>" 
                           placeholder="Наприклад: Французькі круасани" autocomplete="off">
                </div>
                <div class="form-group">
                    <label><?= __('recipe_field_title_en') ?> <span class="req">*</span></label>
                    <input type="text" id="title_en" name="title_en" class="form-control" 
                           value="<?= htmlspecialchars($recipeData['title']['en'] ?? '') ?>" 
                           placeholder="e.g. French Croissants" autocomplete="off">
                </div>
            </div>

            <div class="form-row-two">
                <div class="form-group">
                    <label><?= __('recipe_field_desc_uk') ?></label>
                    <div class="preview-field">
                        <textarea id="desc_uk" name="desc_uk" class="form-control textarea-short preview-source" data-preview-target="preview_desc_uk" placeholder="Короткий опис випічки..." autocomplete="off"><?= htmlspecialchars($recipeData['summary']['uk'] ?? '') ?></textarea>
                        <div id="preview_desc_uk" class="text-preview" data-label="<?= __('recipe_preview_label') ?>" aria-live="polite"></div>
                    </div>
                </div>
                <div class="form-group">
                    <label><?= __('recipe_field_desc_en') ?></label>
                    <div class="preview-field">
                        <textarea id="desc_en" name="desc_en" class="form-control textarea-short preview-source" data-preview-target="preview_desc_en" placeholder="Short pastry description..." autocomplete="off"><?= htmlspecialchars($recipeData['summary']['en'] ?? '') ?></textarea>
                        <div id="preview_desc_en" class="text-preview" data-label="<?= __('recipe_preview_label') ?>" aria-live="polite"></div>
                    </div>
                </div>
            </div>
        </section>

        <!-- Details -->
        <section class="form-card">
            <h2 class="card-title"><?= __('recipe_sec_details') ?></h2>
            
            <div class="form-row-four-params">
                <div class="form-group">
                    <label><?= __('recipe_field_category') ?> <span class="req">*</span></label>
                    <select id="category_select" name="category_id" class="form-control">
                        <option value="" disabled <?= empty($recipeData['category_id']) ? 'selected' : '' ?>><?= $isUk ? 'Оберіть категорію...' : 'Select category...' ?></option>
                        <?php foreach ($categories as $cat): ?>
                            <option value="<?= (int)$cat['id'] ?>" <?= ((int)($recipeData['category_id'] ?? 0) === (int)$cat['id']) ? 'selected' : '' ?>>
                                <?= htmlspecialchars($isUk ? $cat['name_uk'] : $cat['name_en']) ?>
                            </option>
                        <?php endforeach; ?>
                    </select>
                </div>
                <div class="form-group">
                    <label><?= __('recipe_field_cooking_time') ?> <span class="req">*</span></label>
                    <input type="number" id="cooking_time" name="cooking_time" class="form-control" 
                           value="<?= htmlspecialchars((string)($recipeData['meta']['cooking_time'] ?? '')) ?>" 
                           placeholder="60" min="1" autocomplete="off">
                </div>
                <div class="form-group">
                    <label><?= __('recipe_field_difficulty') ?> <span class="req">*</span></label>
                    <select name="difficulty" class="form-control">
                        <?php $diff = $recipeData['meta']['difficulty'] ?? 'easy'; ?>
                        <option value="easy" <?= $diff === 'easy' ? 'selected' : '' ?>><?= __('recipe_diff_easy') ?></option>
                        <option value="medium" <?= $diff === 'medium' ? 'selected' : '' ?>><?= __('recipe_diff_medium') ?></option>
                        <option value="hard" <?= $diff === 'hard' ? 'selected' : '' ?>><?= __('recipe_diff_hard') ?></option>
                    </select>
                </div>
                <div class="form-group">
                    <label><?= __('recipe_field_image') ?> <?= $isEdit ? '' : '<span class="req">*</span>' ?></label>
                    <div class="custom-file-wrapper">
                        <label id="recipe_image_label" class="custom-file-btn" for="recipe_image_input"><?= __('recipe_btn_browse_file') ?></label>
        
                        <span id="file-chosen-text" class="file-name-text">
                            <?= !empty($existingImg) ? ('✅ ' . htmlspecialchars(basename($existingImg))) : __('recipe_file_not_chosen') ?>
                        </span>
                        <input type="hidden" name="existing_image" value="<?= htmlspecialchars($existingImg ?? '') ?>">
        
                        <input type="file" id="recipe_image_input" name="image" class="hidden-file-input" accept=".jpg,.jpeg,.png,.webp" onchange="updateFileName(this)">
                    </div>
                </div>
            </div>
        </section>

        <!-- Ingredients -->
        <section class="form-card">
            <div class="card-header-actions">
                <h2 class="card-title"><?= __('recipe_sec_ingredients') ?></h2>
                <button type="button" class="btn btn-secondary btn-sm" onclick="addIngredientRow()"><?= __('recipe_btn_add_ing_row') ?></button>
            </div>

            <div id="ingredients-container" class="dynamic-list-container">
                <?php foreach ($ingredients as $idx => $ing): ?>
                    <div class="dynamic-row ingredient-row">
                        <div class="autocomplete-wrapper">
                            <input type="text" name="ingredients[<?= $idx ?>][name]" class="form-control row-input-search" 
                                   value="<?= htmlspecialchars($ing['name_' . $currentLang] ?? $ing['name_uk'] ?? $ing['name'] ?? '') ?>"
                                   placeholder="<?= __('recipe_choose_ingredient') ?>" autocomplete="off" onfocus="showDropdown(this)" oninput="filterDropdown(this)">
                            <div class="autocomplete-list"></div>
                        </div>
                        <input type="number" step="any" min="0.01" name="ingredients[<?= $idx ?>][amount]" class="form-control row-input-amount" 
                               value="<?= htmlspecialchars((string)($ing['amount'] ?? '')) ?>"
                               placeholder="<?= __('recipe_amount_placeholder') ?>" autocomplete="off">
                        <select name="ingredients[<?= $idx ?>][unit_id]" class="form-control row-select-unit">
                            <?php foreach ($units as $u): ?>
                                <option value="<?= (int)$u['id'] ?>" <?= ((int)($ing['unit_id'] ?? 0) === (int)$u['id']) ? 'selected' : '' ?>>
                                    <?= htmlspecialchars($isUk ? $u['short_uk'] : $u['short_en']) ?>
                                </option>
                            <?php endforeach; ?>
                        </select>
                        <button type="button" class="btn-icon btn-danger" onclick="removeDynamicRow(this)" title="<?= __('admin_action_delete') ?>">🗑️</button>
                    </div>
                <?php endforeach; ?>
            </div>
        </section>

        <!-- Steps -->
        <section class="form-card">
            <div class="card-header-actions">
                <h2 class="card-title"><?= __('recipe_sec_steps') ?></h2>
                <button type="button" class="btn btn-secondary btn-sm" onclick="addStepRow()"><?= __('recipe_btn_add_step_row') ?></button>
            </div>

            <div id="steps-container" class="dynamic-steps-container">
                <?php foreach ($steps as $sIdx => $st): ?>
                    <div class="step-card">
                        <div class="step-card-header">
                            <div class="step-header-left">
                                <span class="step-badge"><?= __('recipe_step_title') ?> <?= $sIdx + 1 ?></span>
                                <div class="custom-file-wrapper step-file-wrapper">
                                    <label class="custom-file-btn btn-sm" for="step_image_<?= $sIdx ?>"><?= __('recipe_step_photo') ?></label>
                                    <span id="step-file-text-<?= $sIdx ?>" class="file-name-text">
                                        <?= !empty($st['image_url']) ? basename($st['image_url']) : __('recipe_file_not_chosen') ?>
                                    </span>
                                    <input type="hidden" name="steps[<?= $sIdx ?>][existing_image]" value="<?= htmlspecialchars($st['image_url'] ?? '') ?>">
                                    <input type="file" id="step_image_<?= $sIdx ?>" name="step_images[<?= $sIdx ?>]" class="hidden-file-input" accept=".jpg,.jpeg,.png,.webp" onchange="updateStepFileName(this, <?= $sIdx ?>)">
                                </div>
                            </div>
                            <button type="button" class="btn-icon btn-danger" onclick="removeStepRow(this)" title="<?= __('admin_action_delete') ?>">🗑️</button>
                        </div>
                        
                        <div class="form-row-two">
                            <div class="preview-field">
                                <textarea name="steps[<?= $sIdx ?>][uk]" class="form-control textarea-step preview-source" data-preview-target="preview_step_<?= $sIdx ?>_uk" placeholder="<?= __('recipe_step_uk') ?>" autocomplete="off"><?= htmlspecialchars($st['desc_uk'] ?? $st['uk'] ?? '') ?></textarea>
                                <div id="preview_step_<?= $sIdx ?>_uk" class="text-preview text-preview-step" data-label="<?= __('recipe_preview_label') ?>" aria-live="polite"></div>
                            </div>
                            <div class="preview-field">
                                <textarea name="steps[<?= $sIdx ?>][en]" class="form-control textarea-step preview-source" data-preview-target="preview_step_<?= $sIdx ?>_en" placeholder="<?= __('recipe_step_en') ?>" autocomplete="off"><?= htmlspecialchars($st['desc_en'] ?? $st['en'] ?? '') ?></textarea>
                                <div id="preview_step_<?= $sIdx ?>_en" class="text-preview text-preview-step" data-label="<?= __('recipe_preview_label') ?>" aria-live="polite"></div>
                            </div>
                        </div>

                        <div class="form-row-two mt-10">
                            <div class="preview-field">
                                <textarea name="steps[<?= $sIdx ?>][tip_uk]" class="form-control textarea-tip preview-source" data-preview-target="preview_tip_<?= $sIdx ?>_uk" placeholder="<?= __('recipe_step_tip_uk') ?>" autocomplete="off"><?= htmlspecialchars($st['tip_uk'] ?? '') ?></textarea>
                                <div id="preview_tip_<?= $sIdx ?>_uk" class="text-preview text-preview-step" data-label="<?= __('recipe_preview_label') ?>" aria-live="polite"></div>
                            </div>
                            <div class="preview-field">
                                <textarea name="steps[<?= $sIdx ?>][tip_en]" class="form-control textarea-tip preview-source" data-preview-target="preview_tip_<?= $sIdx ?>_en" placeholder="<?= __('recipe_step_tip_en') ?>" autocomplete="off"><?= htmlspecialchars($st['tip_en'] ?? '') ?></textarea>
                                <div id="preview_tip_<?= $sIdx ?>_en" class="text-preview text-preview-step" data-label="<?= __('recipe_preview_label') ?>" aria-live="polite"></div>
                            </div>
                        </div>
                    </div>
                <?php endforeach; ?>
            </div>
        </section>

        <!-- Extra / Video / Outro -->
        <section class="form-card">
            <h2 class="card-title"><?= __('recipe_sec_extra') ?></h2>
            
            <div class="form-group mb-16">
                <label><?= __('recipe_field_video_url') ?></label>
                <input type="url" name="video_url" class="form-control" 
                       value="<?= htmlspecialchars($recipeData['video_url'] ?? '') ?>"
                       placeholder="https://www.youtube.com/watch?v=..." autocomplete="off">
            </div>

            <div class="form-row-two">
                <div class="form-group">
                    <label><?= __('recipe_field_outro_uk') ?></label>
                    <div class="preview-field">
                        <textarea id="outro_uk" name="outro_uk" class="form-control textarea-short preview-source" data-preview-target="preview_outro_uk" placeholder="Фінальні поради, подача до столу..." autocomplete="off"><?= htmlspecialchars($recipeData['afterword']['uk'] ?? '') ?></textarea>
                        <div id="preview_outro_uk" class="text-preview" data-label="<?= __('recipe_preview_label') ?>" aria-live="polite"></div>
                    </div>
                </div>
                <div class="form-group">
                    <label><?= __('recipe_field_outro_en') ?></label>
                    <div class="preview-field">
                        <textarea id="outro_en" name="outro_en" class="form-control textarea-short preview-source" data-preview-target="preview_outro_en" placeholder="Final serving suggestions, chef closing note..." autocomplete="off"><?= htmlspecialchars($recipeData['afterword']['en'] ?? '') ?></textarea>
                        <div id="preview_outro_en" class="text-preview" data-label="<?= __('recipe_preview_label') ?>" aria-live="polite"></div>
                    </div>
                </div>
            </div>
        </section>

        <!-- Submit & Actions Bar -->
        <div class="form-submit-bar" style="display: flex; justify-content: space-between; align-items: center; gap: 15px;">
            <?php if ($isEdit): ?>
                <button type="submit" name="action_delete" value="1" 
                        class="btn btn-danger" 
                        style="background: #ef4444; color: #fff; padding: 12px 24px; border: none; border-radius: 8px; font-weight: 600; cursor: pointer;"
                        onclick="if(confirm('<?= $isUk ? 'Ви впевнені, що хочете видалити цей рецепт?' : 'Are you sure you want to delete this recipe?' ?>')) { document.getElementById('recipe-main-form').submittedByDelete = true; return true; } return false;">
                    🗑️ <?= $isUk ? 'Видалити рецепт' : 'Delete Recipe' ?>
                </button>
                <button type="submit" name="action_save" value="1" class="btn btn-primary btn-submit-large">
                    💾 <?= $isUk ? 'Зберегти зміни' : 'Save Changes' ?>
                </button>
            <?php else: ?>
                <div></div>
                <button type="submit" class="btn btn-primary btn-submit-large">
                    <?= __('recipe_btn_publish') ?>
                </button>
            <?php endif; ?>
        </div>
    </form>
</main>

<!-- JS Config and Script Link -->
<script>
const availableIngredients = <?= json_encode(array_values(array_unique($ingredientsList ?? [])), JSON_UNESCAPED_UNICODE) ?>;
const availableUnits       = <?= json_encode($units ?? [], JSON_UNESCAPED_UNICODE) ?>;
const isUkrainian          = <?= json_encode($isUk) ?>;

const MSG_REQUIRED         = <?= json_encode(__('val_required_field')) ?>;
const MSG_UNIQUE_TITLES    = <?= json_encode(__('val_unique_titles')) ?>;
const MSG_CHOOSE_PHOTO     = <?= json_encode(__('val_choose_photo')) ?>;
const MSG_ING_NOT_FOUND    = <?= json_encode(__('val_ing_not_found')) ?>;
const MSG_FILE_NOT_CHOSEN  = <?= json_encode(__('recipe_file_not_chosen')) ?>;
const MSG_CHOOSE_ING       = <?= json_encode(__('recipe_choose_ingredient')) ?>;
const MSG_AMOUNT_PH        = <?= json_encode(__('recipe_amount_placeholder')) ?>;
const MSG_STEP_TITLE       = <?= json_encode(__('recipe_step_title')) ?>;
const MSG_STEP_PHOTO       = <?= json_encode(__('recipe_step_photo')) ?>;
const MSG_STEP_UK          = <?= json_encode(__('recipe_step_uk')) ?>;
const MSG_STEP_EN          = <?= json_encode(__('recipe_step_en')) ?>;
const MSG_STEP_TIP_UK      = <?= json_encode(__('recipe_step_tip_uk')) ?>;
const MSG_STEP_TIP_EN      = <?= json_encode(__('recipe_step_tip_en')) ?>;
const MSG_PREVIEW_LABEL    = <?= json_encode(__('recipe_preview_label')) ?>;

let ingredientIndex = <?= count($ingredients) ?>;
let stepIndex       = <?= count($steps) ?>;
</script>
<script src="<?= BASE_URL ?>/assets/js/recipe_form.js"></script>