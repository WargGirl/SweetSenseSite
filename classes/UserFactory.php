<?php

require_once __DIR__ . '/User.php';

class UserFactory {

    public function createRegisterData(array $formData): array {
        return [
            'display_name'     => trim($formData['display_name'] ?? ''),
            'username'         => trim($formData['username'] ?? ''),
            'email'            => trim($formData['email'] ?? ''),
            'password'         => $formData['password'] ?? '',
            'password_confirm' => $formData['password_confirm'] ?? ''
        ];
    }

    public function createUserEntity(array $dbRow): User {
        return new User($dbRow);
    }
}