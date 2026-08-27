<?php

use App\Services\Validator;

require_once __DIR__ . '/Database.php';
require_once __DIR__ . '/User.php';

class Auth {
    private Database $db;

    public function __construct() {
        $this->db = new Database();
    }

    public function register(string $username, string $email, string $password, string $displayName = ''): int {
        $username = trim($username);
        $email = trim($email);

        if (!Validator::validateEmail($email)) {
            throw new InvalidArgumentException('Некоректний формат електронної пошти.');
        }

        if (!Validator::validateStrongPassword($password)) {
            throw new InvalidArgumentException('Пароль не відповідає вимогам надійності.');
        }

        if (empty($displayName)) {
            $displayName = $username;
        }

        $exists = $this->db->select(
            "SELECT id FROM users WHERE username = ? OR email = ? LIMIT 1",
            [$username, $email]
        );

        if (!empty($exists)) {
            throw new Exception("Користувач із таким логіном або поштою вже існує.");
        }

        $passwordHash = password_hash($password, PASSWORD_DEFAULT);

        return $this->db->insert('users', [
            'username'      => $username,
            'email'         => $email,
            'password_hash' => $passwordHash,
            'display_name'  => $displayName,
            'role'          => 'user'
        ]);
    }

    public function login(string $identifier, string $password): bool {
        $identifier = trim($identifier);

        $users = $this->db->select(
            "SELECT * FROM users WHERE username = ? OR email = ? LIMIT 1",
            [$identifier, $identifier]
        );

        if (empty($users)) {
            return false;
        }

        $user = $users[0];

        if (password_verify($password, $user['password_hash']) && ($user['status'] ?? 'active') === 'active') {
            $_SESSION['user_id']   = (int)$user['id'];
            $_SESSION['user_name'] = $user['display_name'];
            $_SESSION['user_role'] = $user['role'];
            return true;
        }

        return false;
    }

    public function getCurrentUser(): ?User {
        if (empty($_SESSION['user_id'])) {
            return null;
        }

        $userData = $this->db->select(
                "SELECT id, username, email, display_name, role, status, warnings FROM users WHERE id = ? LIMIT 1",
            [(int)$_SESSION['user_id']]
        );

        if (empty($userData)) {
            $this->logout();
            return null;
        }

        return new User($userData[0]);
    }

    public function getUserById(int $userId): ?User {
        $userData = $this->db->select(
            "SELECT id, username, email, display_name, role, status, warnings
             FROM users WHERE id = ? LIMIT 1",
            [$userId]
        );

        return empty($userData) ? null : new User($userData[0]);
    }

    public function check(): bool {
        return !empty($_SESSION['user_id']);
    }

    public function logout(): void {
        unset($_SESSION['user_id']);
    }
}