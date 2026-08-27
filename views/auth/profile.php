<?php
$roleTitle = $user->isAdmin() ? __('auth_role_admin') : __('auth_role_user');
$createdAtFormatted = !empty($rawUser['created_at']) ? date('d.m.Y', strtotime($rawUser['created_at'])) : '—';
?>
<div class="profile-container">
    <div class="profile-card">
        <?php if ($isViewingOther): ?>
            <div style="margin-bottom: 15px;">
                <a href="index.php?route=admin&tab=users" class="link-profile-btn"><?= __('profile_back_users') ?></a>
            </div>
        <?php endif; ?>

        <?php if ($message): ?>
            <div class="alert-<?= $messageIsError ? 'danger' : 'success' ?>">
                <?= htmlspecialchars($message) ?>
            </div>
        <?php endif; ?>

        <div class="profile-header">
            <div class="profile-avatar">👤</div>
            <div class="profile-info">
                <h2><?= htmlspecialchars($user->getDisplayName()) ?></h2>
                <span class="role-badge <?= $user->isAdmin() ? 'badge-admin' : '' ?>">
                    <?= htmlspecialchars($roleTitle) ?>
                </span>
            </div>
        </div>

        <div class="profile-details">
            <div class="detail-row">
                <span class="detail-label"><?= __('auth_username') ?>:</span>
                <span class="detail-val">@<?= htmlspecialchars($user->getUsername()) ?></span>
            </div>
            <div class="detail-row">
                <span class="detail-label"><?= __('auth_email') ?>:</span>
                <span class="detail-val"><?= htmlspecialchars($user->getEmail()) ?></span>
            </div>
            <div class="detail-row">
                <span class="detail-label"><?= __('auth_member_since') ?>:</span>
                <span class="detail-val"><?= htmlspecialchars($createdAtFormatted) ?></span>
            </div>
            <div class="detail-row">
                <span class="detail-label"><?= __('admin_th_status') ?>:</span>
                <span class="detail-val <?= $user->isBanned() ? 'profile-status-banned' : 'profile-status-active' ?>">
                    <?= $user->isBanned() ? __('admin_status_banned') : __('admin_status_active') ?>
                </span>
            </div>
            <div class="detail-row">
                <span class="detail-label"><?= __('admin_th_warnings') ?>:</span>
                <span class="detail-val"><?= $user->getWarnings() ?></span>
            </div>
        </div>

        <?php if ($isViewingOther): ?>
            <div class="profile-moderation-top">
                <form action="index.php?route=profile&id=<?= (int)$user->getId() ?>" method="POST" class="inline-form">
                    <input type="hidden" name="action" value="warn">
                    <button type="submit" class="btn-warning-action">⚠️ <?= __('admin_btn_warn') ?></button>
                </form>

                <?php if ($user->isBanned()): ?>
                    <form action="index.php?route=profile&id=<?= (int)$user->getId() ?>" method="POST" class="inline-form">
                        <input type="hidden" name="action" value="unban">
                        <button type="submit" class="btn-unban">✅ <?= __('admin_btn_unban') ?></button>
                    </form>
                <?php else: ?>
                    <form action="index.php?route=profile&id=<?= (int)$user->getId() ?>" method="POST" class="inline-form">
                        <input type="hidden" name="action" value="ban">
                        <button type="submit" class="btn-ban">🚫 <?= __('admin_btn_ban') ?></button>
                    </form>
                <?php endif; ?>
            </div>
        <?php endif; ?>

        <div class="profile-interactive-section">
            <details class="profile-edit-accordion" <?= ($messageIsError ? 'open' : '') ?>>
                <summary class="btn-toggle-edit">
                    ✏️ <?= $isViewingOther ? __('profile_admin_edit_title') : __('profile_edit_title') ?>
                </summary>

                <form action="index.php?route=profile<?= $isViewingOther ? '&id=' . (int)$user->getId() : '' ?>" method="POST" class="profile-edit-form">
                    <input type="hidden" name="action" value="edit_profile">

                    <div class="form-group-profile">
                        <label><?= __('profile_display_name') ?></label>
                        <input type="text" name="display_name" value="<?= htmlspecialchars($user->getDisplayName()) ?>" required>
                    </div>

                    <?php if ($isViewingOther): ?>
                        <div class="form-group-profile">
                            <label><?= __('auth_username') ?></label>
                            <input type="text" name="username" value="<?= htmlspecialchars($user->getUsername()) ?>" required>
                        </div>
                        <div class="form-group-profile">
                            <label><?= __('auth_email') ?></label>
                            <input type="email" name="email" value="<?= htmlspecialchars($user->getEmail()) ?>" required>
                        </div>
                    <?php endif; ?>

                    <div class="form-group-profile">
                        <label><?= __('profile_new_pass') ?></label>
                        <input type="password" name="new_password" placeholder="••••••••">
                    </div>

                    <button type="submit" class="btn-save"><?= __('profile_save_btn') ?></button>
                </form>
            </details>

            <div class="profile-actions">
                <?php if (!$isViewingOther): ?>
                    <?php if ($user->isAdmin()): ?>
                        <a href="index.php?route=admin" class="btn btn-admin">🛠️ <?= __('nav_admin') ?></a>
                    <?php endif; ?>
                    <a href="index.php?route=logout" class="btn btn-danger"><?= __('nav_logout') ?></a>
                <?php endif; ?>
            </div>
        </div>
    </div>
</div>