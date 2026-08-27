<script>
    window.currentUserId = <?= (int)$currentUserId ?>;
    window.currentUserName = '<?= htmlspecialchars((string)$currentUserName, ENT_QUOTES) ?>';
    window.currentUserRole = '<?= htmlspecialchars((string)$currentUserRole, ENT_QUOTES) ?>';
    window.adminId = <?= (int)$adminId ?>;
    window.activeTargetId = <?= (int)$activeTargetId ?>;
</script>
<script src="<?= BASE_URL ?>/assets/js/support_chat.js" defer></script>

<main class="admin-container">
    <header class="admin-header">
        <h1><?= $currentUserRole === 'admin' ? __('support_title_admin') : __('support_title_user') ?></h1>
    </header>

    <div class="support-wrapper">
        
        <?php if ($currentUserRole === 'admin'): ?>
            <div class="support-top-bar">
                <label for="chat-target-user" class="support-bar-label"><?= __('support_select_dialog') ?>:</label>
                <select id="chat-target-user" class="support-select-field" onchange="window.location.href='index.php?route=support&with_user=' + this.value;">
                    <?php foreach ($userList as $u): ?>
                        <option value="<?= (int)$u['id'] ?>" <?= $activeTargetId === (int)$u['id'] ? 'selected' : '' ?>>
                            @<?= htmlspecialchars($u['username']) ?> (<?= htmlspecialchars($u['display_name']) ?>)
                        </option>
                    <?php endforeach; ?>
                </select>
            </div>
        <?php endif; ?>

        <div class="support-chat-card">
            <div class="support-chat-header">
                <div class="support-chat-title">
                    <strong><?= __('support_dialog_label') ?></strong> 
                    <span><?= $currentUserRole === 'admin' ? __('support_target_client') : __('support_target_admin') . ' (@' . htmlspecialchars($admin['username'] ?? 'admin') . ')' ?></span>
                </div>
                <div class="support-status">
                    <span class="support-dot"></span>
                    <span><?= __('support_status_online') ?></span>
                </div>
            </div>

            <div id="chat-messages" class="support-messages-area">
                <?php if (empty($history)): ?>
                    <div class="chat-bubble chat-bubble-partner">
                        <div class="chat-meta">
                            <strong><?= $currentUserRole === 'admin' ? __('support_system_sender') : __('support_admin_sender') ?></strong> 
                            <small><?= date('H:i') ?></small>
                        </div>
                        <div class="chat-text">
                            <?= $currentUserRole === 'admin' ? __('support_admin_welcome_text') : __('support_user_welcome_text') ?>
                        </div>
                    </div>
                <?php else: ?>
                    <?php foreach ($history as $msg): ?>
                        <?php $isMine = (int)$msg['from_user_id'] === $currentUserId; ?>
                        <div class="chat-bubble <?= $isMine ? 'chat-bubble-mine' : 'chat-bubble-partner' ?>">
                            <div class="chat-meta">
                                <strong><?= $isMine ? __('support_chat_you') : htmlspecialchars($msg['from_username']) ?></strong> 
                                <small><?= date('d.m.Y H:i', strtotime($msg['created_at'])) ?></small>
                            </div>
                            <div class="chat-text"><?= htmlspecialchars($msg['message']) ?></div>
                        </div>
                    <?php endforeach; ?>
                <?php endif; ?>
            </div>

            <form class="support-chat-form" onsubmit="event.preventDefault(); sendSupportMessage();">
                <textarea id="chat-input" class="support-input-field" rows="4" placeholder="<?= __('support_input_placeholder') ?>" required></textarea>
                <button type="submit" class="admin-btn-add"><?= __('support_btn_send') ?></button>
            </form>
        </div>

    </div>
</main>