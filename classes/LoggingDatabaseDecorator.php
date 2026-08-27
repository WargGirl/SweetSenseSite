<?php

require_once __DIR__ . '/DatabaseAdapter.php';

class LoggingDatabaseDecorator implements DatabaseAdapterInterface {
    private DatabaseAdapterInterface $adapter;
    private string $logFile;

    public function __construct(DatabaseAdapterInterface $adapter, string $logFile = '') {
        $this->adapter = $adapter;
        $this->logFile = !empty($logFile) ? $logFile : dirname(__DIR__) . '/storage/logs/db_operations.log';

        $logDir = dirname($this->logFile);
        if (!is_dir($logDir)) {
            mkdir($logDir, 0775, true);
        }
    }

    public function select(string $query, array $params = []): array {
        return $this->adapter->select($query, $params);
    }

    public function insert(string $table, array $data): int {
        $this->logAction("INSERT INTO [{$table}]", $data);
        return $this->adapter->insert($table, $data);
    }

    public function delete(string $table, string $condition, array $params = []): bool {
        $this->logAction("DELETE FROM [{$table}] WHERE {$condition}", $params);
        return $this->adapter->delete($table, $condition, $params);
    }

    public function getPdo(): ?PDO {
        return $this->adapter->getPdo();
    }

    private function logAction(string $operation, array $payload): void {
        $timestamp = date('Y-m-d H:i:s');
        $jsonPayload = json_encode($payload, JSON_UNESCAPED_UNICODE);
        $logEntry = "[{$timestamp}] [DECORATOR_DB_LOG] Operation: {$operation} | Payload: {$jsonPayload}" . PHP_EOL;

        file_put_contents($this->logFile, $logEntry, FILE_APPEND | LOCK_EX);
    }
}