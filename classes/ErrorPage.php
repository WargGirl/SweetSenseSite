<?php

require_once __DIR__ . '/Page.php';

class ErrorPage extends Page {

    public function renderNotFound(string $titleKey = 'error_recipe_title', string $textKey = 'error_recipe_text'): void {
        ?>
        <div class="error-container">
            <div class="error-card">
                <div class="error-icon">🥐💔</div>
                <h1 class="error-code">404</h1>
                <h2 class="error-title"><?= __($titleKey) ?></h2>
                <p class="error-description"><?= __($textKey) ?></p>
                <div class="error-actions">
                    <a href="<?= BASE_URL ?>/catalog.php" class="btn btn-go-catalog">
                        <?= __('error_btn_catalog') ?>
                    </a>
                </div>
            </div>
        </div>
        <?php
    }
}

