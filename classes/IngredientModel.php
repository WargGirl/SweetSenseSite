<?php

require_once __DIR__ . '/DatabaseAdapter.php';

class IngredientModel {
    private DatabaseAdapterInterface $db;

    public function __construct(?DatabaseAdapterInterface $db = null) {
        $this->db = $db ?? new MySqlDatabaseAdapter();
    }

    public function getAll(string $search = '', string $sort = 'name_asc'): array {
        $sortColumns = [
            'name_asc'  => 'i.name_uk ASC',
            'name_desc' => 'i.name_uk DESC',
            'date_new'  => 'i.id DESC',
            'date_old'  => 'i.id ASC',
        ];
        $orderBy = $sortColumns[$sort] ?? $sortColumns['name_asc'];

        $sql = "SELECT i.id, i.name_uk, i.name_en, COUNT(ri.recipe_id) AS linked_recipes
                FROM ingredients i
                LEFT JOIN recipe_ingredients ri ON ri.ingredient_id = i.id
                WHERE i.name_uk LIKE ? OR i.name_en LIKE ?
                GROUP BY i.id, i.name_uk, i.name_en
                ORDER BY {$orderBy}";

        $pattern = '%' . $search . '%';
        return $this->db->select($sql, [$pattern, $pattern]);
    }

    public function save(string $nameUk, string $nameEn, int $id = 0): void {
        if ($id > 0) {
            $pdo = $this->db->getPdo();
            if ($pdo) {
                $stmt = $pdo->prepare('UPDATE ingredients SET name_uk = ?, name_en = ? WHERE id = ?');
                $stmt->execute([$nameUk, $nameEn, $id]);
            }
        } else {
            $this->db->insert('ingredients', ['name_uk' => $nameUk, 'name_en' => $nameEn]);
        }
    }

    public function isUsedInRecipes(int $id): bool {
        $res = $this->db->select('SELECT COUNT(*) as cnt FROM recipe_ingredients WHERE ingredient_id = ?', [$id]);
        return (int)($res[0]['cnt'] ?? 0) > 0;
    }

    public function delete(int $id): bool {
        return $this->db->delete('ingredients', 'id = ?', [$id]);
    }
}