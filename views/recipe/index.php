<?php
use App\Services\HtmlConverter;

$title   = $recipe['title'][$currentLang] ?? $recipe['title']['uk'] ?? '';
$summary = $recipe['summary'][$currentLang] ?? $recipe['summary']['uk'] ?? '';
$image   = $recipe['image'] ?? BASE_URL . '/assets/img/placeholder.jpg';

$cookingTime = $recipe['meta']['cooking_time'] ?? 0;
$difficulty  = $recipe['meta']['difficulty'] ?? 'easy';
$author      = $recipe['meta']['author'] ?? '';

$score        = $recipe['rating']['score'] ?? 5.0;
$reviewsCount = $recipe['rating']['reviews_count'] ?? count($reviews);

$steps     = $recipe['steps'] ?? [];
$afterword = $recipe['afterword'][$currentLang] ?? $recipe['afterword']['uk'] ?? null;

$reviewsList = $reviews ?? [];
$reviewsCount = count($reviewsList);

if ($reviewsCount > 0) {
    $sumRating = array_reduce($reviewsList, fn($carry, $r) => $carry + (float)($r['rating'] ?? 0), 0);
    $avgRating = $sumRating / $reviewsCount;
} else {
    $avgRating = (float)($recipe['avg_rating'] ?? 0);
}
?>

<div class="recipe-detail-container">

    <?php if (!empty($_SESSION['flash_added'])): ?>
        <div class="alert alert-success">
            ✅ <?= __('flash_ingredients_added') ?>
        </div>
        <?php unset($_SESSION['flash_added']); ?>
    <?php endif; ?>

    <!-- Recipe Header -->
    <header class="recipe-header">
        <h1 class="recipe-title"><?= htmlspecialchars($title) ?></h1>
        
        <div class="recipe-meta-bar">
            <div class="rating-stars">
                <span class="star-icon">★</span>
                <strong><?= number_format($avgRating, 1) ?></strong>
                <span class="reviews-count">(<?= $reviewsCount ?>)</span>
        </div>

            <?php if ($cookingTime): ?>
                <div class="meta-item">⏱️ <?= __('cooking_time') ?>: <strong><?= $cookingTime ?> <?= __('min') ?></strong></div>
            <?php endif; ?>

            <?php if ($difficulty): ?>
                <div class="meta-item">📊 <?= __('difficulty') ?>: <strong><?= __('difficulty_' . $difficulty) ?></strong></div>
            <?php endif; ?>

            <?php if ($author): ?>
                <div class="meta-item">👤 <?= htmlspecialchars($author) ?></div>
            <?php endif; ?>
        </div>
    </header>

    <!-- Main Image -->
    <div class="recipe-media">
        <img src="<?= htmlspecialchars($image) ?>" alt="<?= htmlspecialchars($title) ?>" class="recipe-main-img">
    </div>

    <!-- Summary -->
    <?php if ($summary): ?>
        <div class="recipe-summary">
            <p class="recipe-lead"><?= HtmlConverter::textToHtml($summary) ?></p>
        </div>
    <?php endif; ?>

    <!-- Ingredients -->
    <div class="ingredients-section" id="ingredients-section">
        <div class="ingredients-header">
            <h3>🛒 <?= __('ingredients_title') ?></h3>
            
            <button type="button" id="toggle-ingredients-btn" class="btn-toggle-select">
                <?= __('btn_select_missing') ?>
            </button>
        </div>

        <p id="selection-subtext" class="sub-text is-hidden">
            <?= __('selection_subtext') ?>
        </p>

        <form action="index.php?route=recipe&id=<?= (int)$recipe['id'] ?>" method="POST">
            <input type="hidden" name="recipe_title" value="<?= htmlspecialchars($title) ?>">
            
            <ul class="ingredients-list">
                <?php foreach ($recipe['ingredients'] as $index => $item): ?>
                    <?php 
                        $ingName = $item['name_' . $currentLang] ?? $item['name_uk'] ?? $item['name'] ?? '';
                    ?>
                    <li class="ingredient-row">
                        <div class="ing-left">
                            <label class="checkbox-container">
                                <input type="checkbox" 
                                       name="ingredients[<?= $index ?>][selected]" 
                                       value="1" 
                                       class="ing-checkbox select-control is-hidden" 
                                       disabled
                                       onchange="toggleQtyInput(this)">
                                <span class="ing-name"><?= htmlspecialchars($ingName) ?></span>
                            </label>
                        </div>

                        <div class="ing-right">
                            <span class="ing-base-amount" title="<?= __('by_recipe') ?>">
                                <?= htmlspecialchars((string)$item['amount']) ?> <?= htmlspecialchars($item['unit'] ?? '') ?>
                            </span>
                            
                            <div class="qty-control is-hidden">
                                <input type="number" 
                                       name="ingredients[<?= $index ?>][missing_qty]" 
                                       value="<?= htmlspecialchars((string)$item['amount']) ?>" 
                                       min="0.1" 
                                       step="any" 
                                       class="qty-input">
                                <span class="qty-unit"><?= htmlspecialchars($item['unit'] ?? '') ?></span>
                            </div>
                        </div>
                    </li>
                <?php endforeach; ?>
            </ul>

            <button type="submit" id="submit-cart-btn" class="btn-add-to-cart select-control is-hidden" disabled>
                <?= __('btn_add_to_cart') ?>
            </button>
        </form>
    </div>

    <!-- Step-by-step Guide -->
    <?php if (!empty($steps)): ?>
        <section class="steps-section">
            <h3>👩‍🍳 <?= __('steps_title') ?></h3>
            <div class="steps-list">
                <?php foreach ($steps as $step): ?>
                    <?php 
                        $stepTitle = $step['title_' . $currentLang] ?? $step['title_uk'] ?? ('Крок ' . ($step['number'] ?? ''));
                        $stepDesc  = $step['desc_' . $currentLang] ?? $step['desc_uk'] ?? '';
                        $stepTip   = $step['tip_' . $currentLang] ?? $step['tip_uk'] ?? '';
                    ?>
                    <div class="step-card">
                        <div class="step-header">
                            <span class="step-badge"><?= $step['number'] ?? '' ?></span>
                            <h4 class="step-heading"><?= htmlspecialchars($stepTitle) ?></h4>
                        </div>

                        <div class="step-body">
                            <p class="step-text"><?= HtmlConverter::textToHtml($stepDesc) ?></p>

                            <?php if (!empty($step['image_url'])): ?>
                                <div class="step-media">
                                    <img src="<?= htmlspecialchars($step['image_url']) ?>" alt="<?= htmlspecialchars($stepTitle) ?>">
                                </div>
                            <?php endif; ?>

                            <?php if (!empty($stepTip)): ?>
                                <div class="step-tip-box">
                                    <span class="tip-icon">💡</span>
                                    <div class="tip-content">
                                        <strong><?= __('step_tip') ?>:</strong> <?= HtmlConverter::textToHtml($stepTip) ?>
                                    </div>
                                </div>
                            <?php endif; ?>
                        </div>
                    </div>
                <?php endforeach; ?>
            </div>
        </section>
    <?php endif; ?>
   
    <!-- Video Guide -->
    <?php if (!empty($recipe['video_url'])): ?>
        <section class="video-section">
            <h3>🎥 <?= __('video_guide_title') ?></h3>
            <div class="video-wrapper">
                <iframe 
                    src="<?= htmlspecialchars($recipe['video_url']) ?>" 
                    title="Recipe Video" 
                    frameborder="0" 
                    allow="accelerometer; autoplay; clipboard-write; encrypted-media; gyroscope; picture-in-picture" 
                    allowfullscreen>
                </iframe>
            </div>
         </section>
    <?php endif; ?>

    <!-- Afterword -->
    <?php if (!empty($afterword)): ?>
        <div class="recipe-afterword">
            <p class="recipe-lead"><?= HtmlConverter::textToHtml($afterword) ?></p>
        </div>
    <?php endif; ?>

<!-- Admin Actions (Only for Admin) -->
    <?php if (($_SESSION['user_role'] ?? '') === 'admin'): ?>
        <div style="display: flex; gap: 10px; margin-bottom: 20px;">
            <a href="index.php?route=recipe_edit&id=<?= (int)$recipe['id'] ?>" 
               class="btn btn-secondary" 
               style="display: inline-flex; align-items: center; gap: 8px; padding: 10px 18px; border-radius: 8px; font-weight: 600; text-decoration: none;">
                ✏️ <?= (($currentLang ?? 'uk') === 'uk') ? 'Редагувати рецепт' : 'Edit Recipe' ?>
            </a>
        </div>
    <?php endif; ?>

    <!-- Share -->
    <?php 
        $fullShareText = '«' . $title . '» — ' . __('share_text_template');
        $encodedShareText = urlencode($fullShareText);
    ?>
    <div class="share-recipe-bar">
        <span class="share-label">🔗 <?= __('share_title') ?></span>
        <div class="share-buttons">
            <a href="https://twitter.com/intent/tweet?text=<?= $encodedShareText ?>&url=<?= urlencode((isset($_SERVER['HTTPS']) && $_SERVER['HTTPS'] === 'on' ? 'https' : 'http') . '://' . $_SERVER['HTTP_HOST'] . $_SERVER['REQUEST_URI']) ?>" 
               target="_blank" 
               rel="noopener noreferrer" 
               class="share-btn share-x" 
               title="Share on X">
                𝕏
            </a>

            <a href="https://www.facebook.com/sharer/sharer.php?u=<?= urlencode((isset($_SERVER['HTTPS']) && $_SERVER['HTTPS'] === 'on' ? 'https' : 'http') . '://' . $_SERVER['HTTP_HOST'] . $_SERVER['REQUEST_URI']) ?>" 
               target="_blank" 
               rel="noopener noreferrer" 
               class="share-btn share-fb" 
               title="Share on Facebook">
                f
            </a>

            <button type="button" 
                    class="share-btn share-copy" 
                    id="copy-share-btn" 
                    data-share-text="<?= htmlspecialchars($fullShareText) ?>" 
                    onclick="copyRecipeLink(this)" 
                    title="<?= __('share_copy_link') ?>">
                📋 <span><?= __('share_copy_link') ?></span>
            </button>
            <a href="index.php?route=recipe_export&id=<?= (int)$recipe['id'] ?>" class="share-btn share-copy">
                <?= __('recipe_export_txt') ?>
            </a>
        </div>
    </div>

    <!-- Reviews -->
    <?php 
        $currentUserId = (int)($_SESSION['user_id'] ?? 0);
        $myReview = null;
        if ($currentUserId > 0) {
            foreach ($reviews as $r) {
                if ((int)$r['user_id'] === $currentUserId) {
                    $myReview = $r;
                    break;
                }
            }
        }
        $hasReviewed = ($myReview !== null);
        $userRating = $hasReviewed ? (int)$myReview['rating'] : 5;
    ?>

    <section id="reviews-section" class="reviews-container">
        <h3 class="reviews-title">💬 <?= __('reviews_title') ?> (<?= count($reviews) ?>)</h3>
    
        <?php if (!empty($_SESSION['user_id'])): ?>
            <form id="review-main-form" 
                  action="index.php?route=recipe&id=<?= (int)$recipe['id'] ?>" 
                  method="POST" 
                  class="review-form-card <?= $hasReviewed ? 'is-hidden' : '' ?>">
                <input type="hidden" name="action" value="add_review">
                
                <div class="rating-picker">
                    <label><?= __('reviews_rating_label') ?></label>
                    <div class="star-rating" id="star-rating-box">
                        <?php for ($i = 5; $i >= 1; $i--): ?>
                            <input type="radio" id="star-<?= $i ?>" name="rating" value="<?= $i ?>" 
                                   <?= ($i === $userRating) ? 'checked' : '' ?>>
                            <label for="star-<?= $i ?>" title="<?= $i ?>/5">★</label>
                        <?php endfor; ?>
                    </div>
                </div>
    
                <textarea id="review-comment-field"
                          name="comment" 
                          class="review-textarea" 
                          rows="3" 
                          placeholder="<?= __('reviews_comment_ph') ?>"><?= htmlspecialchars($myReview['comment'] ?? '') ?></textarea>
    
                <div class="review-form-actions">
                    <button type="submit" id="review-submit-btn" class="btn btn-submit-review">
                        <?= $hasReviewed ? __('reviews_btn_update') : __('reviews_btn_submit') ?>
                    </button>
                    <?php if ($hasReviewed): ?>
                        <button type="button" class="btn btn-cancel-review" onclick="cancelReviewEditing()">
                            <?= __('reviews_btn_cancel') ?>
                        </button>
                    <?php endif; ?>
                </div>
            </form>
        <?php else: ?>
            <div class="review-login-notice">
                <?= __('reviews_login_req') ?> 
                <a href="<?= BASE_URL ?>/login.php"><?= __('nav_login') ?></a>.
            </div>
        <?php endif; ?>
    
        <div class="reviews-list">
            <?php if (empty($reviews)): ?>
                <p class="no-reviews"><?= __('reviews_empty') ?></p>
            <?php else: ?>
                <?php foreach ($reviews as $rev): ?>
                    <?php $isMyCard = ($currentUserId > 0 && (int)$rev['user_id'] === $currentUserId); ?>
                    <div class="review-card <?= $isMyCard ? 'my-review-card' : '' ?>" <?= $isMyCard ? 'id="my-review-card"' : '' ?>>
                        <div class="review-header">
                            <span class="review-author">👤 <?= htmlspecialchars($rev['display_name'] ?? $rev['username']) ?></span>
                            
                            <?php if ($isMyCard): ?>
                                <span class="my-review-badge"><?= __('reviews_your_review') ?></span>
                            <?php endif; ?>

                            <span class="review-stars"><?= str_repeat('★', (int)$rev['rating']) . str_repeat('☆', 5 - (int)$rev['rating']) ?></span>

                            <div class="review-header-right">
                                <?php if ($isMyCard): ?>
                                    <button type="button" class="btn-edit-review" onclick="enableReviewEditing()" title="<?= __('reviews_btn_edit') ?>">
                                        ✏️
                                    </button>
                                <?php endif; ?>
                                <span class="review-date"><?= date('d.m.Y H:i', strtotime($rev['created_at'])) ?></span>
                            </div>
                        </div>
    
                        <?php if (!empty($rev['comment'])): ?>
                            <div class="review-body" data-original-text="<?= htmlspecialchars($rev['comment']) ?>">
                                <div class="review-text-content"><?= nl2br(htmlspecialchars($rev['comment'])) ?></div>
                                <button type="button" class="btn-translate-comment" onclick="toggleCommentTranslation(this)">
                                    <?= __('reviews_translate_btn') ?>
                                </button>
                            </div>
                        <?php endif; ?>
                    </div>
                <?php endforeach; ?>
            <?php endif; ?>
        </div>
    </section>
</div>

<script>
document.getElementById('toggle-ingredients-btn').addEventListener('click', function() {
    const controls = document.querySelectorAll('.select-control');
    const subtext = document.getElementById('selection-subtext');
    const checkboxes = document.querySelectorAll('.ing-checkbox');
    
    const isSelecting = !controls[0].classList.contains('is-hidden');

    if (isSelecting) {
        controls.forEach(el => el.classList.add('is-hidden'));
        if (subtext) subtext.classList.add('is-hidden');

        checkboxes.forEach(cb => {
            cb.checked = false;
            cb.disabled = true;
            toggleQtyInput(cb);
        });

        this.textContent = '<?= __('btn_select_missing') ?>';
    } else {
        controls.forEach(el => el.classList.remove('is-hidden'));
        if (subtext) subtext.classList.remove('is-hidden');

        checkboxes.forEach(cb => {
            cb.disabled = false;
        });
        
        this.textContent = '<?= __('btn_cancel_select') ?>';
    }
    updateSubmitBtnState();
});

function copyRecipeLink(btn) {
    const textToCopy = btn.getAttribute('data-share-text') + ' ' + window.location.href;
    
    navigator.clipboard.writeText(textToCopy).then(() => {
        const textSpan = btn.querySelector('span');
        const originalText = textSpan.textContent;
        textSpan.textContent = '<?= __('share_copied') ?>';
        btn.classList.add('copied');

        setTimeout(() => {
            textSpan.textContent = originalText;
            btn.classList.remove('copied');
        }, 2000);
    });
}

function enableReviewEditing() {
    const form = document.getElementById('review-main-form');
    if (!form) return;

    form.classList.remove('is-hidden');

    const commentField = document.getElementById('review-comment-field');
    if (commentField) {
        commentField.focus();
    }

    form.scrollIntoView({ behavior: 'smooth', block: 'center' });
}

function cancelReviewEditing() {
    const form = document.getElementById('review-main-form');
    if (form) {
        form.classList.add('is-hidden');
    }
}

async function toggleCommentTranslation(btn) {
    const body = btn.closest('.review-body');
    const textContainer = body.querySelector('.review-text-content');
    const originalText = body.getAttribute('data-original-text');
    const targetLang = '<?= $currentLang ?>';

    if (btn.getAttribute('data-is-translated') === 'true') {
        textContainer.innerHTML = originalText.replace(/\n/g, '<br>');
        btn.textContent = '<?= __('reviews_translate_btn') ?>';
        btn.removeAttribute('data-is-translated');
        return;
    }

    btn.disabled = true;
    const prevBtnText = btn.textContent;
    btn.textContent = '<?= __('reviews_translating') ?>';

    try {
        const url = `https://api.mymemory.translated.net/get?q=${encodeURIComponent(originalText)}&langpair=Autodetect|${targetLang}`;
        const response = await fetch(url);
        const data = await response.json();

        let translated = data?.responseData?.translatedText;

        if (!translated || translated.includes('PLEASE SELECT TWO DISTINCT LANGUAGES')) {
            translated = originalText;
        }

        textContainer.innerHTML = translated.replace(/\n/g, '<br>');
        btn.textContent = '<?= __('reviews_show_original') ?>';
        btn.setAttribute('data-is-translated', 'true');
    } catch (e) {
        btn.textContent = '<?= __('reviews_translate_err') ?>';
        setTimeout(() => { btn.textContent = prevBtnText; }, 2000);
    } finally {
        btn.disabled = false;
    }
}

function toggleQtyInput(checkbox) {
    const row = checkbox.closest('.ingredient-row');
    const qtyControl = row.querySelector('.qty-control');
    if (qtyControl) {
        if (checkbox.checked) {
            qtyControl.classList.remove('is-hidden');
        } else {
            qtyControl.classList.add('is-hidden');
        }
    }
    updateSubmitBtnState();
}

function updateSubmitBtnState() {
    const submitBtn = document.getElementById('submit-cart-btn');
    if (!submitBtn) return;
    const checkedBoxes = document.querySelectorAll('.ing-checkbox:checked');
    submitBtn.disabled = (checkedBoxes.length === 0);
}
</script>