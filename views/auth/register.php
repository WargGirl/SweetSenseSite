<?php
$oldDisplayName = htmlspecialchars($_POST['display_name'] ?? '', ENT_QUOTES);
$oldUsername    = htmlspecialchars($_POST['username'] ?? '', ENT_QUOTES);
$oldEmail       = htmlspecialchars($_POST['email'] ?? '', ENT_QUOTES);
?>
<div class="auth-card">
    <h2 class="auth-title"><?= __('auth_register_title') ?></h2>

    <?php if ($error): ?>
        <div class="alert alert-danger"><?= htmlspecialchars($error) ?></div>
    <?php endif; ?>

    <form action="index.php?route=register" method="POST" class="auth-form">
        <div class="form-group">
            <label for="display_name"><?= __('auth_display_name') ?></label>
            <input type="text" id="display_name" name="display_name" value="<?= $oldDisplayName ?>" class="form-control" required>
        </div>

        <div class="form-group">
            <label for="username"><?= __('auth_username') ?></label>
            <input type="text" id="username" name="username" value="<?= $oldUsername ?>" class="form-control" required>
        </div>

        <div class="form-group">
            <label for="email"><?= __('auth_email') ?></label>
            <input type="email" id="email" name="email" value="<?= $oldEmail ?>" class="form-control" required>
        </div>

        <div class="form-group">
            <label for="password"><?= __('auth_password') ?></label>
            <input type="password" id="password" name="password" class="form-control" required>
        </div>

        <div class="form-group">
            <label for="password_confirm"><?= __('auth_password_confirm') ?></label>
            <input type="password" id="password_confirm" name="password_confirm" class="form-control" required>
        </div>

        <button type="submit" class="btn btn-auth-submit"><?= __('auth_btn_register') ?></button>
    </form>

    <div class="auth-footer-text">
        <?= __('auth_have_account') ?> <a href="index.php?route=login"><?= __('auth_link_login') ?></a>
    </div>
</div>