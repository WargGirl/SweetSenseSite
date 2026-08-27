<?php

class User {
    private int $id;
    private string $username;
    private string $email;
    private string $displayName;
    private string $role;
    private string $status;
    private int $warnings;

    public function __construct(array $data) {
        $this->id          = (int)($data['id'] ?? 0);
        $this->username    = $data['username'] ?? '';
        $this->email       = $data['email'] ?? '';
        $this->displayName = $data['display_name'] ?? $this->username;
        $this->role        = $data['role'] ?? 'user';
        $this->status      = $data['status'] ?? 'active';
        $this->warnings    = (int)($data['warnings'] ?? 0);
    }

    public function getId(): int { return $this->id; }
    public function getUsername(): string { return $this->username; }
    public function getEmail(): string { return $this->email; }
    public function getDisplayName(): string { return $this->displayName; }
    public function getRole(): string { return $this->role; }
    public function isAdmin(): bool { return $this->role === 'admin'; }
    public function getStatus(): string { return $this->status; }
    public function isBanned(): bool { return $this->status === 'banned'; }
    public function getWarnings(): int { return $this->warnings; }

    public static function fetchAll(PDO $pdo): array {
        $stmt = $pdo->query("SELECT id, username, email, display_name, role, status, warnings FROM users");
        $rows = $stmt->fetchAll(PDO::FETCH_ASSOC);
        
        $users = [];
        foreach ($rows as $row) {
            $users[] = new self($row);
        }
        return $users;
    }
}