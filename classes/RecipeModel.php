<?php

require_once __DIR__ . '/DatabaseAdapter.php';
require_once __DIR__ . '/Cache.php';

class RecipeModel {
    private DatabaseAdapterInterface $db;

    public function __construct(?DatabaseAdapterInterface $db = null) {
        $this->db = $db ?? new MySqlDatabaseAdapter();
    }

    public function getRecipeById(int $recipeId): ?array {
        $sql = "SELECT 
                    r.*, 
                    u.display_name AS author_name,
                    COALESCE(AVG(rev.rating), 0) AS avg_rating,
                    COUNT(rev.id) AS reviews_count
                FROM recipes r
                LEFT JOIN users u ON r.author_id = u.id
                LEFT JOIN reviews rev ON r.id = rev.recipe_id
                WHERE r.id = ?
                GROUP BY r.id, u.display_name";
                
        $rows = $this->db->select($sql, [$recipeId]);
        return $rows[0] ?? null;
    }

    public function getById(int $recipeId): ?array {
        $recipe = $this->getRecipeById($recipeId);
        if (!$recipe) {
            return null;
        }

        $catRow = $this->db->select("SELECT category_id FROM recipe_categories WHERE recipe_id = ? LIMIT 1", [$recipeId]);
        $recipe['category_id'] = $catRow[0]['category_id'] ?? null;

        $recipe['image_url'] = $recipe['image_url'] ?? $recipe['image'] ?? '';  

        $recipe['title'] = [
            'uk' => $recipe['title_uk'] ?? $recipe['title'] ?? '',
            'en' => $recipe['title_en'] ?? ''
        ];
        $recipe['summary'] = [
            'uk' => $recipe['description_uk'] ?? $recipe['summary_uk'] ?? $recipe['description'] ?? '',
            'en' => $recipe['description_en'] ?? $recipe['summary_en'] ?? ''
        ];
        $recipe['meta'] = [
            'cooking_time' => $recipe['cooking_time'] ?? 0,
            'difficulty'   => $recipe['difficulty'] ?? 'easy'
        ];
        $recipe['afterword'] = [
            'uk' => $recipe['outro_uk'] ?? $recipe['afterword_uk'] ?? '',
            'en' => $recipe['outro_en'] ?? $recipe['afterword_en'] ?? ''
        ];

        $recipe['ingredients'] = $this->getIngredients($recipeId);
        $recipe['steps']       = $this->getSteps($recipeId);

        return $recipe;
    }

    public function getIngredients(int $recipeId, string $currentLang = 'uk'): array {
        $sql = "SELECT 
                    ri.ingredient_id,
                    ri.unit_id,
                    i.name_uk, 
                    i.name_en, 
                    ri.amount, 
                    u.short_uk, 
                    u.short_en
                FROM recipe_ingredients ri
                JOIN ingredients i ON ri.ingredient_id = i.id
                JOIN units u ON ri.unit_id = u.id
                WHERE ri.recipe_id = ?";
        $raw = $this->db->select($sql, [$recipeId]);
        $ingredients = [];

        foreach ($raw as $ing) {
            $ingredients[] = [
                'ingredient_id' => (int)$ing['ingredient_id'],
                'unit_id'       => (int)$ing['unit_id'],
                'name_uk'       => $ing['name_uk'],
                'name_en'       => $ing['name_en'],
                'name'          => ($currentLang === 'en') ? $ing['name_en'] : $ing['name_uk'],
                'amount'        => (float)$ing['amount'],
                'unit'          => ($currentLang === 'en') ? $ing['short_en'] : $ing['short_uk']
            ];
        }

        return $ingredients;
    }

    public function getSteps(int $recipeId): array {
        $sql = "SELECT 
                    step_number AS number,
                    title_uk,
                    title_en,
                    instruction_uk AS desc_uk,
                    instruction_en AS desc_en,
                    instruction_uk AS uk,
                    instruction_en AS en,
                    image_url,
                    tip_uk,
                    tip_en
                FROM recipe_steps
                WHERE recipe_id = ?
                ORDER BY step_number ASC";
        return $this->db->select($sql, [$recipeId]);
    }

    public function getCategories(): array {
        return $this->db->select("SELECT id, name_uk, name_en FROM categories ORDER BY id ASC");
    }

    public function getUnits(): array {
        return $this->db->select("SELECT id, short_uk, short_en FROM units ORDER BY id ASC");
    }

    public function getAllIngredientsList(): array {
        return $this->db->select("SELECT name_uk, name_en FROM ingredients ORDER BY id ASC");
    }

    public function createFullRecipe(array $recipeData, array $ingredients, array $steps): int {
        $recipeId = $this->db->insert('recipes', $recipeData);

        if (!empty($recipeData['category_id'])) {
            $this->db->insert('recipe_categories', [
                'recipe_id'   => $recipeId,
                'category_id' => $recipeData['category_id']
            ]);
        }

        $this->saveIngredientsAndSteps($recipeId, $ingredients, $steps);
        Cache::clear();

        return $recipeId;
    }

    public function updateFullRecipe(int $recipeId, array $recipeData, array $ingredients, array $steps): void {
        $pdo = $this->db->getPdo();
        if ($pdo) {
            $categoryId = $recipeData['category_id'] ?? null;
            unset($recipeData['category_id']);

            if (empty($recipeData['image_url'])) {
                unset($recipeData['image_url']);
            }

            $setClauses = [];
            $params = [];
            foreach ($recipeData as $col => $val) {
                $setClauses[] = "{$col} = ?";
                $params[] = $val;
            }
            $params[] = $recipeId;

            $stmt = $pdo->prepare("UPDATE recipes SET " . implode(', ', $setClauses) . " WHERE id = ?");
            $stmt->execute($params);

            if ($categoryId) {
                $this->db->delete('recipe_categories', 'recipe_id = ?', [$recipeId]);
                $this->db->insert('recipe_categories', [
                    'recipe_id'   => $recipeId,
                    'category_id' => $categoryId
                ]);
            }

            $this->db->delete('recipe_ingredients', 'recipe_id = ?', [$recipeId]);
            $this->db->delete('recipe_steps', 'recipe_id = ?', [$recipeId]);
            $this->saveIngredientsAndSteps($recipeId, $ingredients, $steps);

            Cache::clear();
        }
    }

    public function deleteRecipe(int $recipeId): void {
        $recipe = $this->getRecipeById($recipeId);
        $steps  = $this->getSteps($recipeId);

        $filesToDelete = [];

        if (!empty($recipe['image_url']) && !str_contains($recipe['image_url'], 'placeholder')) {
        $filesToDelete[] = $recipe['image_url'];
        }

        foreach ($steps as $step) {
            if (!empty($step['image_url'])) {
                $filesToDelete[] = $step['image_url'];
            }
        }

        $publicDir = dirname(__DIR__) . '/public/';
        foreach ($filesToDelete as $relPath) {
            $cleanPath = ltrim(str_replace(['/SweetSense/public/', BASE_URL], '', $relPath), '/');
            $fullPath  = $publicDir . $cleanPath;

            if (file_exists($fullPath) && is_file($fullPath)) {
                unlink($fullPath);
            }
        }

        $this->db->delete('recipe_categories', 'recipe_id = ?', [$recipeId]);
        $this->db->delete('recipe_ingredients', 'recipe_id = ?', [$recipeId]);
        $this->db->delete('recipe_steps', 'recipe_id = ?', [$recipeId]);
        $this->db->delete('reviews', 'recipe_id = ?', [$recipeId]);
        $this->db->delete('recipes', 'id = ?', [$recipeId]);

        Cache::clear();
    }

    private function saveIngredientsAndSteps(int $recipeId, array $ingredients, array $steps): void {
        foreach ($ingredients as $ing) {
            if (empty(trim($ing['name'] ?? ''))) continue;

            $existing = $this->db->select("SELECT id FROM ingredients WHERE name_uk = ? OR name_en = ?", [$ing['name'], $ing['name']]);
            $ingId = !empty($existing) ? (int)$existing[0]['id'] : $this->db->insert('ingredients', ['name_uk' => $ing['name'], 'name_en' => $ing['name']]);

            $this->db->insert('recipe_ingredients', [
                'recipe_id'     => $recipeId,
                'ingredient_id' => $ingId,
                'unit_id'       => $ing['unit_id'],
                'amount'        => $ing['amount']
            ]);
        }

        foreach ($steps as $i => $step) {
            $this->db->insert('recipe_steps', [
                'recipe_id'      => $recipeId,
                'step_number'    => $i + 1,
                'title_uk'       => 'Крок ' . ($i + 1),
                'title_en'       => 'Step ' . ($i + 1),
                'instruction_uk' => $step['uk'] ?? $step['desc_uk'] ?? '',
                'instruction_en' => $step['en'] ?? $step['desc_en'] ?? '',
                'tip_uk'         => !empty($step['tip_uk']) ? $step['tip_uk'] : null,
                'tip_en'         => !empty($step['tip_en']) ? $step['tip_en'] : null,
                'image_url'      => !empty($step['image_url']) ? $step['image_url'] : null
            ]);
        }
    }
}