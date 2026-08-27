<?php

require_once __DIR__ . '/DatabaseAdapter.php';
require_once __DIR__ . '/Cache.php';

class CategoryModel {
    private DatabaseAdapterInterface $db;

    public function __construct(?DatabaseAdapterInterface $db = null) {
        $this->db = $db ?? new MySqlDatabaseAdapter();
    }

    public function getAll(string $search = '', string $sort = 'name_asc'): array {
        $cacheKey = 'categories_' . $sort;
        if (empty($search)) {
            $cached = Cache::get($cacheKey, 3600);
            if ($cached !== null) {
                return $cached;
            }
        }

        $sortColumns = [
            'name_asc'  => 'c.name_uk ASC',
            'name_desc' => 'c.name_uk DESC',
            'date_new'  => 'c.id DESC',
            'date_old'  => 'c.id ASC',
        ];
        $orderBy = $sortColumns[$sort] ?? $sortColumns['name_asc'];

        $sql = "SELECT c.id, c.name_uk, c.name_en, COUNT(rc.recipe_id) AS linked_recipes
                FROM categories c 
                LEFT JOIN recipe_categories rc ON rc.category_id = c.id
                WHERE c.name_uk LIKE ? OR c.name_en LIKE ?
                GROUP BY c.id, c.name_uk, c.name_en 
                ORDER BY {$orderBy}";

        $pattern = '%' . $search . '%';
        $result = $this->db->select($sql, [$pattern, $pattern]);

        if (empty($search)) {
            Cache::set($cacheKey, $result);
        }

        return $result;
    }

    public function save(string $nameUk, string $nameEn, int $id = 0): void {
        if ($id > 0) {
            $pdo = $this->db->getPdo();
            if ($pdo) {
                $stmt = $pdo->prepare('UPDATE categories SET name_uk = ?, name_en = ? WHERE id = ?');
                $stmt->execute([$nameUk, $nameEn, $id]);
            }
        } else {
            $this->db->insert('categories', ['name_uk' => $nameUk, 'name_en' => $nameEn]);
        }

        Cache::clear();
    }
}