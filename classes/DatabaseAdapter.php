<?php

require_once __DIR__ . '/Database.php';

interface DatabaseAdapterInterface {
    public function select(string $query, array $params = []): array;
    public function insert(string $table, array $data): int;
    public function delete(string $table, string $condition, array $params = []): bool;
    public function getPdo(): ?PDO;
}

class MySqlDatabaseAdapter implements DatabaseAdapterInterface {
    private Database $db;

    public function __construct(?Database $db = null) {
        $this->db = $db ?? new Database();
    }

    public function select(string $query, array $params = []): array {
        return $this->db->select($query, $params);
    }

    public function insert(string $table, array $data): int {
        return $this->db->insert($table, $data);
    }

    public function delete(string $table, string $condition, array $params = []): bool {
        return $this->db->delete($table, $condition, $params);
    }

    public function getPdo(): ?PDO {
        return $this->db->getPdo();
    }
}

class MongoDatabaseAdapter implements DatabaseAdapterInterface {
    private string $connectionString;

    public function __construct(string $connectionString = "mongodb://localhost:27017") {
        $this->connectionString = $connectionString;
    }

    public function select(string $query, array $params = []): array {
        return [
            ['_id' => '64f8a12b', 'type' => 'mongo_doc', 'data' => 'Simulated MongoDB result']
        ];
    }

    public function insert(string $table, array $data): int {
        return rand(100, 999);
    }

    public function delete(string $table, string $condition, array $params = []): bool {
        return true;
    }

    public function getPdo(): ?PDO {
        return null;
    }
}