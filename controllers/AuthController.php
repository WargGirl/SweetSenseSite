<?php

use App\Services\Validator;

require_once CLASSES_PATH . '/Page.php';
require_once CLASSES_PATH . '/Auth.php';
require_once CLASSES_PATH . '/User.php';
require_once CLASSES_PATH . '/UserFactory.php';
require_once CLASSES_PATH . '/DatabaseAdapter.php';

class AuthController {

    public function login(): void {
        $auth = new Auth();
        if ($auth->check()) {
            header('Location: index.php?route=catalog');
            exit;
        }

        $error = null;

        if ($_SERVER['REQUEST_METHOD'] === 'POST') {
            $identifier = trim($_POST['identifier'] ?? '');
            $password   = $_POST['password'] ?? '';

            if (empty($identifier) || empty($password)) {
                $error = __('auth_err_empty_fields');
            } elseif (str_contains($identifier, '@') && !Validator::validateEmail($identifier)) {
                $error = __('auth_err_invalid_email');
            } else {
                if ($auth->login($identifier, $password)) {
                    header('Location: index.php?route=catalog');
                    exit;
                } else {
                    $error = __('auth_err_invalid_auth');
                }
            }
        }

        $page = new Page(SITE_NAME . ' - ' . __('auth_login_title'));
        $page->renderHeader();

        require dirname(__DIR__) . '/views/auth/login.php';

        $page->renderFooter();
    }

    public function register(): void {
        $auth = new Auth();
        if ($auth->check()) {
            header('Location: index.php?route=catalog');
            exit;
        }

        $error = null;

        if ($_SERVER['REQUEST_METHOD'] === 'POST') {
            $userFactory = new UserFactory();
            $data = $userFactory->createRegisterData($_POST);

            if (empty($data['display_name']) || empty($data['username']) || empty($data['email']) || empty($data['password'])) {
                $error = __('auth_err_empty_fields');
            } elseif ($data['password'] !== $data['password_confirm']) {
                $error = __('auth_err_pass_match');
            } elseif (!Validator::validateEmail($data['email'])) {
                $error = __('auth_err_invalid_email');
            } elseif (!Validator::validateStrongPassword($data['password'])) {
                $error = __('auth_err_pass_len');
            } else {
                try {
                    $auth->register($data['username'], $data['email'], $data['password'], $data['display_name']);
                    $auth->login($data['username'], $data['password']);
                    header('Location: index.php?route=catalog');
                    exit;
                } catch (Exception $e) {
                    $error = __('auth_err_user_exists');
                }
            }
        }

        $page = new Page(SITE_NAME . ' - ' . __('auth_register_title'));
        $page->renderHeader();

        require dirname(__DIR__) . '/views/auth/register.php';

        $page->renderFooter();
    }

    public function profile(): void {
        $auth = new Auth();
        if (!$auth->check()) {
            header('Location: index.php?route=login');
            exit;
        }

        $currentUserId = (int)$_SESSION['user_id'];
        $isAdminViewer = ($_SESSION['user_role'] ?? '') === 'admin';

        $targetUserId = $currentUserId;
        if ($isAdminViewer && isset($_GET['id']) && (int)$_GET['id'] > 0) {
            $targetUserId = (int)$_GET['id'];
        }

        $isViewingOther = ($isAdminViewer && $targetUserId !== $currentUserId);
        $db = new MySqlDatabaseAdapter();
        $pdo = $GLOBALS['pdo'] ?? null;
        $message = null;
        $messageIsError = false;

        if ($_SERVER['REQUEST_METHOD'] === 'POST') {
            $action = $_POST['action'] ?? 'edit_profile';

            if ($isViewingOther && in_array($action, ['warn', 'ban', 'unban'], true)) {
                switch ($action) {
                    case 'warn':
                        $stmt = $pdo->prepare("UPDATE users SET warnings = warnings + 1 WHERE id = ?");
                        $message = __('profile_msg_warned');
                        break;
                    case 'ban':
                        $stmt = $pdo->prepare("UPDATE users SET status = 'banned' WHERE id = ?");
                        $message = __('profile_msg_banned');
                        break;
                    case 'unban':
                        $stmt = $pdo->prepare("UPDATE users SET status = 'active' WHERE id = ?");
                        $message = __('profile_msg_unbanned');
                        break;
                } 
                $stmt->execute([$targetUserId]);
            } elseif ($action === 'edit_profile') {
                $displayName = trim($_POST['display_name'] ?? '');
                $username    = trim($_POST['username'] ?? '');
                $email       = trim($_POST['email'] ?? '');
                $newPassword = $_POST['new_password'] ?? '';

                if (empty($displayName)) {
                    $message = __('profile_err_empty');
                    $messageIsError = true;
                } else {
                    if ($isViewingOther && (!empty($username) || !empty($email))) {
                        $check = $db->select("SELECT id FROM users WHERE (username = ? OR email = ?) AND id != ? LIMIT 1", [$username, $email, $targetUserId]);
                        if (!empty($check)) {
                            $message = __('auth_err_user_exists');
                            $messageIsError = true;
                        } else {
                            $stmt = $pdo->prepare("UPDATE users SET display_name = ?, username = ?, email = ? WHERE id = ?");
                            $stmt->execute([$displayName, $username, $email, $targetUserId]);
                        }
                    } else {
                        $stmt = $pdo->prepare("UPDATE users SET display_name = ? WHERE id = ?");
                        $stmt->execute([$displayName, $targetUserId]);
                    }

                    if (!$messageIsError && !empty($newPassword)) {
                        if (!Validator::validateStrongPassword($newPassword)) {
                            $message = __('auth_err_pass_len');
                            $messageIsError = true;
                        } else {
                            $hash = password_hash($newPassword, PASSWORD_BCRYPT);
                            $stmt = $pdo->prepare("UPDATE users SET password = ? WHERE id = ?");
                            $stmt->execute([$hash, $targetUserId]);
                        }
                    }

                    if (!$messageIsError) {
                        $message = __('profile_success');
                        if ($targetUserId === $currentUserId) {
                            $_SESSION['user_name'] = $displayName;
                        }
                    }
                }
            }
        }

        $userData = $db->select("SELECT * FROM users WHERE id = ? LIMIT 1", [$targetUserId]);
        if (empty($userData)) {
            header('Location: index.php?route=admin&tab=users');
            exit;
        }

        $userFactory = new UserFactory();
        $user = $userFactory->createUserEntity($userData[0]);
        $rawUser = $userData[0];

        $page = new Page(SITE_NAME . ' - ' . __('auth_profile_title'));
        $page->renderHeader();

        require dirname(__DIR__) . '/views/auth/profile.php';

        $page->renderFooter();
    }

    public function logout(): void {
        $auth = new Auth();
        $auth->logout();
        header('Location: index.php?route=login');
        exit;
    }
}