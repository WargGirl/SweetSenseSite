<?php

require_once __DIR__ . '/DatabaseAdapter.php';

class SupportModel {
    private DatabaseAdapterInterface $db;

    public function __construct(?DatabaseAdapterInterface $db = null) {
        $this->db = $db ?? new MySqlDatabaseAdapter();
    }

    public function getAdminUser(): ?array {
        $rows = $this->db->select("SELECT id, username, display_name FROM users WHERE role = 'admin' LIMIT 1");
        return $rows[0] ?? null;
    }

    public function getUserDialogs(int $currentUserId): array {
        $sql = "
            SELECT u.id, u.username, u.display_name,
                   MAX(m.created_at) AS last_message_time
            FROM users u
            LEFT JOIN chat_messages m 
                   ON (m.from_user_id = u.id AND m.to_user_id = ?)
                   OR (m.from_user_id = ? AND m.to_user_id = u.id)
            WHERE u.id != ?
            GROUP BY u.id, u.username, u.display_name
            ORDER BY last_message_time DESC, u.id DESC
        ";
        return $this->db->select($sql, [$currentUserId, $currentUserId, $currentUserId]);
    }

    public function getChatHistory(int $userId1, int $userId2): array {
        $sql = "
            SELECT m.id, m.from_user_id, m.to_user_id, m.message, m.created_at,
                   u.username AS from_username, u.display_name AS from_display_name
            FROM chat_messages m
            JOIN users u ON u.id = m.from_user_id
            WHERE (m.from_user_id = ? AND m.to_user_id = ?)
               OR (m.from_user_id = ? AND m.to_user_id = ?)
            ORDER BY m.created_at ASC
        ";
        return $this->db->select($sql, [$userId1, $userId2, $userId2, $userId1]);
    }
}