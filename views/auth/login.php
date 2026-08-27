<div class="auth-card">
    <h2 class="auth-title"><?= __('auth_login_title') ?></h2>

    <?php if ($error): ?>
        <div class="alert alert-danger"><?= htmlspecialchars($error) ?></div>
    <?php endif; ?>

    <form action="index.php?route=login" method="POST" class="auth-form">
        <div class="form-group">
            <label for="identifier"><?= __('auth_username') ?> / <?= __('auth_email') ?></label>
            <input type="text" id="identifier" name="identifier" class="form-control" required autofocus>
        </div>

        <div class="form-group">
            <label for="password"><?= __('auth_password') ?></label>
            <input type="password" id="password" name="password" class="form-control" required>
        </div>

        <button type="submit" class="btn btn-auth-submit"><?= __('auth_btn_login') ?></button>
    </form>

    <div class="auth-footer-text">
        <?= __('auth_no_account') ?> <a href="index.php?route=register"><?= __('auth_link_register') ?></a>
    </div>
</div>