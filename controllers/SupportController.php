<?php

require_once CLASSES_PATH . '/Page.php';
require_once CLASSES_PATH . '/SupportModel.php';
require_once CLASSES_PATH . '/ChatMessageFactory.php';

class SupportController {
    public function index(): void {
        $currentUserId = (int)($_SESSION['user_id'] ?? 0);
        $currentUserName = $_SESSION['username'] ?? 'User';
        $currentUserRole = $_SESSION['user_role'] ?? 'user';

        $pageTitle = $currentUserRole === 'admin' ? __('support_title_admin') : __('support_title_user');
        $page = new Page($pageTitle);
        $page->renderHeader();

        if ($currentUserId === 0) {
            echo '<main class="admin-container"><div class="admin-feedback admin-feedback-error">' 
                 . (__('auth_required_message') ?? 'Please sign in to access support.') 
                 . '</div></main>';
            $page->renderFooter();
            return;
        }

        $supportModel = new SupportModel();
        $admin = $supportModel->getAdminUser();
        $adminId = $admin ? (int)$admin['id'] : 1;

        $userList = [];
        $activeTargetId = $adminId;

        if ($currentUserRole === 'admin') {
            $userList = $supportModel->getUserDialogs($currentUserId);
            $activeTargetId = (int)($_GET['with_user'] ?? ($userList[0]['id'] ?? 0));
        }

        $history = $supportModel->getChatHistory($currentUserId, $activeTargetId);

        require dirname(__DIR__) . '/views/support/index.php';

        $page->renderFooter();
    }
}