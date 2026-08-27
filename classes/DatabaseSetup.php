<?php

class DatabaseSetup
{
    private string $host;
    private string $user;
    private string $password;
    private string $database;
    private int $port;
    private string $charset;
    private ?PDO $pdo = null;

    public function __construct(
        ?string $host = null,
        ?string $user = null,
        ?string $password = null,
        ?string $database = null,
        ?int $port = null,
        string $charset = 'utf8mb4'
    ) {
        $this->host     = $host     ?? getenv('DB_HOST') ?: '127.0.0.1';
        $this->user     = $user     ?? getenv('DB_USER') ?: 'root';
        $this->password = $password ?? getenv('DB_PASS') ?: '';
        $this->database = $database ?? getenv('DB_NAME') ?: (getenv('RAILWAY_ENVIRONMENT') ? 'railway' : 'sweetsense_db');
        $this->port     = $port     ?? (int)(getenv('DB_PORT') ?: 3306);
        $this->charset  = $charset;

        try {
            if (!getenv('RAILWAY_ENVIRONMENT')) {
                try {
                    $serverPdo = new PDO(
                        "mysql:host={$this->host};port={$this->port};charset={$this->charset}",
                        $this->user,
                        $this->password,
                        [PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION]
                    );
                    $serverPdo->exec(
                        "CREATE DATABASE IF NOT EXISTS `{$this->database}` " .
                        "CHARACTER SET {$this->charset} COLLATE utf8mb4_unicode_ci"
                    );
                } catch (PDOException $e) {
                }
            }

            $this->pdo = $this->createConnection();
            $this->createTables();
            $this->pdo->beginTransaction();
            $this->seedData();
            $this->pdo->commit();
        } catch (PDOException $e) {
            if ($this->pdo !== null && $this->pdo->inTransaction()) {
                $this->pdo->rollBack();
            }

            $errorMessage = htmlspecialchars($e->getMessage(), ENT_QUOTES, 'UTF-8');
            $errorCode = htmlspecialchars((string)$e->getCode(), ENT_QUOTES, 'UTF-8');

            echo "<div style='background-color: #fee2e2; border: 1px solid #ef4444; color: #b91c1c; padding: 20px; border-radius: 8px; font-family: sans-serif; max-width: 650px; margin: 40px auto; box-shadow: 0 4px 6px -1px rgba(0,0,0,0.1);'>
                    <h3 style='margin-top: 0;'>Помилка ініціалізації бази даних</h3>
                    <p>Неможливо створити або ініціалізувати базу даних <strong>{$this->database}</strong> (хост: {$this->host}:{$this->port}).</p>
                    <p style='font-size: 13px; color: #7f1d1d;'><strong>Код помилки:</strong> {$errorCode}<br><strong>Деталі:</strong> {$errorMessage}</p>
                  </div>";
            exit;
        }
    }

    private function createConnection(): PDO
    {
        $dsn = "mysql:host={$this->host};port={$this->port};dbname={$this->database};charset={$this->charset}";

        return new PDO($dsn, $this->user, $this->password, [
            PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION,
            PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC,
            PDO::ATTR_EMULATE_PREPARES => false,
        ]);
    }

    private function createTables(): void
    {
        $tables = [
            "CREATE TABLE IF NOT EXISTS users (
                id INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
                username VARCHAR(100) NOT NULL UNIQUE,
                email VARCHAR(255) NOT NULL UNIQUE,
                password_hash VARCHAR(255) NOT NULL,
                display_name VARCHAR(150) NOT NULL,
                role ENUM('user', 'admin') NOT NULL DEFAULT 'user',
                status ENUM('active', 'banned') NOT NULL DEFAULT 'active',
                warnings INT UNSIGNED NOT NULL DEFAULT 0,
                created_at TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP
            ) ENGINE=InnoDB",
            "CREATE TABLE IF NOT EXISTS categories (
                id INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
                name_uk VARCHAR(100) NOT NULL UNIQUE,
                name_en VARCHAR(100) NOT NULL UNIQUE
            ) ENGINE=InnoDB",
            "CREATE TABLE IF NOT EXISTS units (
                id INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
                name_uk VARCHAR(100) NOT NULL,
                name_en VARCHAR(100) NOT NULL,
                short_uk VARCHAR(20) NOT NULL,
                short_en VARCHAR(20) NOT NULL
            ) ENGINE=InnoDB",
            "CREATE TABLE IF NOT EXISTS ingredients (
                id INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
                name_uk VARCHAR(150) NOT NULL UNIQUE,
                name_en VARCHAR(150) NOT NULL UNIQUE
            ) ENGINE=InnoDB",
            "CREATE TABLE IF NOT EXISTS recipes (
                id INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
                category_id INT UNSIGNED NULL,
                author_id INT UNSIGNED NULL,
                title_uk VARCHAR(255) NOT NULL,
                title_en VARCHAR(255) NOT NULL,
                summary_uk TEXT NULL,
                summary_en TEXT NULL,
                video_url VARCHAR(500) NULL,
                afterword_uk TEXT NULL,
                afterword_en TEXT NULL,
                image_url VARCHAR(500) NULL,
                cooking_time INT UNSIGNED NOT NULL,
                difficulty ENUM('easy', 'medium', 'hard') NOT NULL DEFAULT 'easy',
                avg_rating DECIMAL(3,2) NOT NULL DEFAULT 0,
                reviews_count INT UNSIGNED NOT NULL DEFAULT 0,
                created_at TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP,
                FOREIGN KEY (category_id) REFERENCES categories(id) ON DELETE SET NULL,
                FOREIGN KEY (author_id) REFERENCES users(id) ON DELETE SET NULL
            ) ENGINE=InnoDB",
            "CREATE TABLE IF NOT EXISTS recipe_steps (
                id INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
                recipe_id INT UNSIGNED NOT NULL,
                step_number INT UNSIGNED NOT NULL,
                title_uk VARCHAR(255) NOT NULL,
                title_en VARCHAR(255) NOT NULL,
                instruction_uk TEXT NOT NULL,
                instruction_en TEXT NOT NULL,
                image_url VARCHAR(500) NULL,
                tip_uk TEXT NULL,
                tip_en TEXT NULL,
                UNIQUE KEY recipe_step_number (recipe_id, step_number),
                FOREIGN KEY (recipe_id) REFERENCES recipes(id) ON DELETE CASCADE
            ) ENGINE=InnoDB",
            "CREATE TABLE IF NOT EXISTS recipe_ingredients (
                id INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
                recipe_id INT UNSIGNED NOT NULL,
                ingredient_id INT UNSIGNED NOT NULL,
                unit_id INT UNSIGNED NOT NULL,
                amount DECIMAL(10,2) NOT NULL,
                FOREIGN KEY (recipe_id) REFERENCES recipes(id) ON DELETE CASCADE,
                FOREIGN KEY (ingredient_id) REFERENCES ingredients(id),
                FOREIGN KEY (unit_id) REFERENCES units(id)
            ) ENGINE=InnoDB",
            "CREATE TABLE IF NOT EXISTS recipe_categories (
                recipe_id INT UNSIGNED NOT NULL,
                category_id INT UNSIGNED NOT NULL,
                PRIMARY KEY (recipe_id, category_id),
                FOREIGN KEY (recipe_id) REFERENCES recipes(id) ON DELETE CASCADE,
                FOREIGN KEY (category_id) REFERENCES categories(id) ON DELETE CASCADE
            ) ENGINE=InnoDB",
            "CREATE TABLE IF NOT EXISTS reviews (
                id INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
                recipe_id INT UNSIGNED NOT NULL,
                user_id INT UNSIGNED NOT NULL,
                rating TINYINT UNSIGNED NOT NULL,
                comment TEXT NULL,
                created_at TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP,
                UNIQUE KEY one_review_per_user (recipe_id, user_id),
                FOREIGN KEY (recipe_id) REFERENCES recipes(id) ON DELETE CASCADE,
                FOREIGN KEY (user_id) REFERENCES users(id) ON DELETE CASCADE
            ) ENGINE=InnoDB",
            "CREATE TABLE IF NOT EXISTS user_cart (
                id INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
                user_id INT UNSIGNED NOT NULL,
                ingredient_id INT UNSIGNED NOT NULL,
                recipe_id INT UNSIGNED NOT NULL,
                unit_id INT UNSIGNED NOT NULL,
                amount DECIMAL(10,2) NOT NULL,
                created_at TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP,
                FOREIGN KEY (user_id) REFERENCES users(id) ON DELETE CASCADE,
                FOREIGN KEY (ingredient_id) REFERENCES ingredients(id),
                FOREIGN KEY (recipe_id) REFERENCES recipes(id) ON DELETE CASCADE,
                FOREIGN KEY (unit_id) REFERENCES units(id)
            ) ENGINE=InnoDB",
        ];

        foreach ($tables as $sql) {
            $this->pdo->exec($sql);
        }
    }

    private function seedData(): void
    {
        $categories = [
            ['Випічка', 'Baking'],
            ['Торти', 'Cakes'],
            ['Печиво', 'Cookies'],
            ['Десерти', 'Desserts'],
        ];
        $categoryStatement = $this->pdo->prepare(
            'INSERT IGNORE INTO categories (name_uk, name_en) VALUES (?, ?)'
        );
        foreach ($categories as $category) {
            $categoryStatement->execute($category);
        }

        $units = [
            ['Грам', 'Gram', 'г', 'g'],
            ['Мілілітр', 'Milliliter', 'мл', 'ml'],
            ['Штука', 'Piece', 'шт', 'pcs'],
            ['Чайна ложка', 'Teaspoon', 'ч. л.', 'tsp'],
            ['Столова ложка', 'Tablespoon', 'ст. л.', 'tbsp'],
        ];
        $unitStatement = $this->pdo->prepare(
            'INSERT INTO units (name_uk, name_en, short_uk, short_en)
             SELECT ?, ?, ?, ? FROM DUAL
             WHERE NOT EXISTS (SELECT 1 FROM units WHERE short_uk = ? OR short_en = ?)'
        );
        foreach ($units as $unit) {
            $unitStatement->execute([$unit[0], $unit[1], $unit[2], $unit[3], $unit[2], $unit[3]]);
        }

        $ingredients = [
            ['Борошно пшеничне', 'Wheat flour'],
            ['Цукор', 'Sugar'],
            ['Вершкове масло', 'Butter'],
            ['Молоко', 'Milk'],
            ['Яйця', 'Eggs'],
            ['Розпушувач', 'Baking powder'],
            ['Мигдальні пластівці', 'Almond flakes'],
            ['Ванільний екстракт', 'Vanilla extract'],
            ['Какао-порошок', 'Cocoa powder'],
        ];
        $ingredientStatement = $this->pdo->prepare(
            'INSERT IGNORE INTO ingredients (name_uk, name_en) VALUES (?, ?)'
        );
        foreach ($ingredients as $ingredient) {
            $ingredientStatement->execute($ingredient);
        }
    }
}
