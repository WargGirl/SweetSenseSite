<?php

require_once __DIR__ . '/Database.php';

class CartModel {
    private Database $db;

    public function __construct() {
        $this->db = new Database();
    }

    public function addOrUpdate(int $userId, int $ingredientId, int $recipeId, int $unitId, float $amount): void {
        $existing = $this->db->select(
            "SELECT id, amount FROM user_cart WHERE user_id = ? AND ingredient_id = ? AND recipe_id = ?",
            [$userId, $ingredientId, $recipeId]
        );

        if (!empty($existing)) {
            $newAmount = (float)$existing[0]['amount'] + $amount;
            $this->db->delete('user_cart', 'id = ?', [$existing[0]['id']]);
            $this->db->insert('user_cart', [
                'user_id'       => $userId,
                'ingredient_id' => $ingredientId,
                'recipe_id'     => $recipeId,
                'unit_id'       => $unitId,
                'amount'        => $newAmount
            ]);
        } else {
            $this->db->insert('user_cart', [
                'user_id'       => $userId,
                'ingredient_id' => $ingredientId,
                'recipe_id'     => $recipeId,
                'unit_id'       => $unitId,
                'amount'        => $amount
            ]);
        }
    }

    public function getGroupedCart(int $userId, string $lang = 'uk'): array {
        $sql = "
            SELECT 
                uc.ingredient_id,
                uc.amount,
                i.name_uk,
                i.name_en,
                u.short_uk,
                u.short_en,
                r.title_uk AS recipe_uk,
                r.title_en AS recipe_en
            FROM user_cart uc
            JOIN ingredients i ON uc.ingredient_id = i.id
            JOIN units u ON uc.unit_id = u.id
            JOIN recipes r ON uc.recipe_id = r.id
            WHERE uc.user_id = ?
            ORDER BY i.name_uk ASC
        ";

        $rows = $this->db->select($sql, [$userId]);
        $grouped = [];

        foreach ($rows as $row) {
            $ingId = (int)$row['ingredient_id'];
            $ingName = ($lang === 'en') ? $row['name_en'] : $row['name_uk'];
            $unitName = ($lang === 'en') ? $row['short_en'] : $row['short_uk'];
            $recipeTitle = ($lang === 'en') ? $row['recipe_en'] : $row['recipe_uk'];

            if (!isset($grouped[$ingId])) {
                $grouped[$ingId] = [
                    'ingredient_id' => $ingId,
                    'name'          => $ingName,
                    'unit'          => $unitName,
                    'total_qty'     => 0,
                    'breakdown'     => []
                ];
            }

            $grouped[$ingId]['total_qty'] += (float)$row['amount'];
            $grouped[$ingId]['breakdown'][] = [
                'recipe' => $recipeTitle,
                'qty'    => (float)$row['amount']
            ];
        }

        return array_values($grouped);
    }

    public function clearCart(int $userId): bool {
        return $this->db->delete('user_cart', 'user_id = ?', [$userId]);
    }
}