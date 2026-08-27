<?php

require_once __DIR__ . '/DatabaseException.php';

class Database {
    private ?PDO $connection = null;
    private string $host;
    private string $user;
    private string $password;
    private string $database;
    private int $port;
    private string $charset = 'utf8mb4';

    public function __construct() {
        $this->host     = getenv('DB_HOST') ?: '127.0.0.1';
        $this->user     = getenv('DB_USER') ?: 'root';
        $this->password = getenv('DB_PASS') ?: '';
        $this->database = getenv('DB_NAME') ?: 'railway';
        $this->port     = (int)(getenv('DB_PORT') ?: 3306);

        $this->connect();
    }

    public function connect(): void {
        if ($this->connection !== null) {
            return;
        }

        $dsn = "mysql:host={$this->host};port={$this->port};dbname={$this->database};charset={$this->charset}";
        
        $options = [
            PDO::ATTR_ERRMODE            => PDO::ERRMODE_EXCEPTION,
            PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC,
            PDO::ATTR_EMULATE_PREPARES   => false,
        ];

        try {
            $this->connection = new PDO($dsn, $this->user, $this->password, $options);
        } catch (PDOException $e) {
            throw new DatabaseException("Помилка підключення через PDO: " . $e->getMessage(), (int)$e->getCode(), $e);
        }
    }

    public function close(): void {
        $this->connection = null;
    }

    public function getPdo(): ?PDO {
        return $this->connection;
    }

    private function executeQuery(string $sql, array $params = []): PDOStatement {
        if ($this->connection === null) {
            $this->connect();
        }

        try {
            $stmt = $this->connection->prepare($sql);
            $stmt->execute($params);
            return $stmt;
        } catch (PDOException $e) {
            throw new DatabaseException("Помилка виконання SQL через PDO: " . $e->getMessage(), (int)$e->getCode(), $e);
        }
    }

    public function select(string $sql, array $params = []): array {
        $stmt = $this->executeQuery($sql, $params);
        return $stmt->fetchAll();
    }

    public function insert(string $table, array $data): int {
        if (empty($data)) {
            throw new DatabaseException("Неможливо вставити порожні дані в таблицю {$table}.");
        }

        $columns = implode(', ', array_keys($data));
        $placeholders = implode(', ', array_fill(0, count($data), '?'));
        $sql = "INSERT INTO `{$table}` ({$columns}) VALUES ({$placeholders})";

        $this->executeQuery($sql, array_values($data));
        return (int)$this->connection->lastInsertId();
    }

    public function delete(string $table, string $condition, array $params = []): bool {
        if (empty($condition)) {
            throw new DatabaseException("Умова для видалення не може бути порожньою.");
        }

        $sql = "DELETE FROM `{$table}` WHERE {$condition}";
        $stmt = $this->executeQuery($sql, $params);
        return $stmt->rowCount() > 0;
    }

    public function beginTransaction(): bool {
        if ($this->connection === null) {
            $this->connect();
        }
        return $this->connection->beginTransaction();
    }

    public function commit(): bool {
        return $this->connection->commit();
    }

    public function rollBack(): bool {
        return $this->connection->rollBack();
    }

    public function formatErrorInfo(?PDOStatement $stmt = null): array {
        if ($stmt !== null) {
            $code = $stmt->errorCode();
            $info = $stmt->errorInfo();
        } elseif ($this->connection !== null) {
            $code = $this->connection->errorCode();
            $info = $this->connection->errorInfo();
        } else {
            $code = '00000';
            $info = [null, null, 'З’єднання з базою даних відсутнє'];
        }

        return [
            'sql_state'      => $code,
            'driver_code'    => $info[1] ?? null,
            'driver_message' => $info[2] ?? 'Невідома помилка'
        ];
    }

    public function logError(Exception $e, ?PDOStatement $stmt = null): void {
        $errorDetails = $this->formatErrorInfo($stmt);
        $logMessage = sprintf(
            "[%s] PDO Error [%s | Driver Code: %s]: %s | Exception: %s\n",
            date('Y-m-d H:i:s'),
            $errorDetails['sql_state'],
            $errorDetails['driver_code'],
            $errorDetails['driver_message'],
            $e->getMessage()
        );

        $logsDir = __DIR__ . '/../logs';
        if (!is_dir($logsDir)) {
            @mkdir($logsDir, 0777, true);
        }
        @error_log($logMessage, 3, $logsDir . '/db_errors.log');
    }

    public function __destruct() {
        $this->close();
    }
}
