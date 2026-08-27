<?php

class CatalogModel {
    private $db;

    public function __construct($db = null) {
        $this->db = $db ?? ($GLOBALS['db'] ?? ($GLOBALS['pdo'] ?? null));
    }

    public function getRecipes(
        string $searchQuery = '', 
        int $categoryId = 0, 
        string $currentLang = 'uk',
        array $includeIngredients = [],
        array $excludeIngredients = [],
        ?int $timeFrom = null,
        ?int $timeTo = null,
        ?float $minRating = null,
        string $difficulty = ''
    ): array {
        $params = [];
        $conditions = [];

        $sql = "SELECT 
                    r.id, 
                    r.title_uk, 
                    r.title_en, 
                    r.summary_uk, 
                    r.summary_en, 
                    r.image_url, 
                    r.cooking_time,
                    r.difficulty,
                    cat.name_uk AS category_name_uk,
                    cat.name_en AS category_name_en,
                    COALESCE(AVG(rev.rating), 0) AS avg_rating,
                    COUNT(DISTINCT rev.id) AS reviews_count
                FROM recipes r
                LEFT JOIN recipe_categories rc_join ON rc_join.recipe_id = r.id
                LEFT JOIN categories cat ON cat.id = rc_join.category_id
                LEFT JOIN reviews rev ON rev.recipe_id = r.id";

        if ($categoryId > 0) {
            $sql .= " INNER JOIN recipe_categories rc ON rc.recipe_id = r.id AND rc.category_id = ?";
            $params[] = $categoryId;
        }

        if ($searchQuery !== '') {
            $searchPattern = '%' . $searchQuery . '%';
            $titleCol = ($currentLang === 'en') ? 'r.title_en' : 'r.title_uk';
            $summaryCol = ($currentLang === 'en') ? 'r.summary_en' : 'r.summary_uk';

            $conditions[] = "({$titleCol} LIKE ? OR {$summaryCol} LIKE ?)";
            $params[] = $searchPattern;
            $params[] = $searchPattern;
        }

        if ($timeFrom !== null && $timeFrom > 0) {
            $conditions[] = "r.cooking_time >= ?";
            $params[] = $timeFrom;
        }

        if ($timeTo !== null && $timeTo > 0) {
            $conditions[] = "r.cooking_time <= ?";
            $params[] = $timeTo;
        }

        if (!empty($difficulty)) {
            $conditions[] = "r.difficulty = ?";
            $params[] = $difficulty;
        }

        $cleanExclude = array_filter(array_map('intval', $excludeIngredients));
        if (!empty($cleanExclude)) {
            $placeholders = implode(',', array_fill(0, count($cleanExclude), '?'));
            $conditions[] = "r.id NOT IN (
                SELECT recipe_id FROM recipe_ingredients WHERE ingredient_id IN ({$placeholders})
            )";
            foreach ($cleanExclude as $exId) {
                $params[] = $exId;
            }
        }

        $cleanInclude = array_filter(array_map('intval', $includeIngredients));
        if (!empty($cleanInclude)) {
            $placeholders = implode(',', array_fill(0, count($cleanInclude), '?'));
            $incCount = count($cleanInclude);
            
            $conditions[] = "r.id IN (
                SELECT recipe_id 
                FROM recipe_ingredients 
                WHERE ingredient_id IN ({$placeholders}) 
                GROUP BY recipe_id 
                HAVING COUNT(DISTINCT ingredient_id) = {$incCount}
            )";
            foreach ($cleanInclude as $incId) {
                $params[] = $incId;
            }
        }

        if (!empty($conditions)) {
            $sql .= " WHERE " . implode(' AND ', $conditions);
        }

        $sql .= " GROUP BY r.id";

        if ($minRating !== null && $minRating > 0) {
            $sql .= " HAVING avg_rating >= ?";
            $params[] = $minRating;
        }

        $sql .= " ORDER BY r.id DESC";

        if ($this->db && method_exists($this->db, 'select')) {
            $rawRecipes = $this->db->select($sql, $params);
        } elseif ($this->db instanceof PDO) {
            $stmt = $this->db->prepare($sql);
            $stmt->execute($params);
            $rawRecipes = $stmt->fetchAll(PDO::FETCH_ASSOC);
        } else {
            $rawRecipes = [];
        }

        $filteredRecipes = [];
        $timeUnit = ($currentLang === 'en') ? 'min' : 'хв';

        foreach ($rawRecipes as $row) {
            $filteredRecipes[$row['id']] = [
                'id' => (int)$row['id'],
                'title' => [
                    'uk' => $row['title_uk'],
                    'en' => $row['title_en']
                ],
                'description' => [
                    'uk' => $row['summary_uk'],
                    'en' => $row['summary_en']
                ],
                'category_name_uk' => $row['category_name_uk'] ?? '',
                'category_name_en' => $row['category_name_en'] ?? '',
                'image' => $row['image_url'],
                'time' => $row['cooking_time'] . ' ' . $timeUnit,
                'cooking_time'  => (int)$row['cooking_time'],
                'difficulty'    => $row['difficulty'] ?? 'easy',
                'avg_rating'    => (float)$row['avg_rating'],
                'reviews_count' => (int)$row['reviews_count']
            ];
        }

        return $filteredRecipes;
    }

    public function getCategories(): array {
        $sql = "SELECT id, name_uk, name_en FROM categories ORDER BY name_uk ASC";
        if ($this->db && method_exists($this->db, 'select')) {
            return $this->db->select($sql);
        } elseif ($this->db instanceof PDO) {
            return $this->db->query($sql)->fetchAll(PDO::FETCH_ASSOC);
        }
        return [];
    }

    public function getAllIngredients(string $currentLang = 'uk'): array {
        $nameCol = ($currentLang === 'en') ? 'name_en' : 'name_uk';
        $sql = "SELECT id, {$nameCol} AS name FROM ingredients ORDER BY {$nameCol} ASC";

        if ($this->db && method_exists($this->db, 'select')) {
            return $this->db->select($sql);
        } elseif ($this->db instanceof PDO) {
            return $this->db->query($sql)->fetchAll(PDO::FETCH_ASSOC);
        }
        return [];
    }
}