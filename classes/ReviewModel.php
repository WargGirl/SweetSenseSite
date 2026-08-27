<?php

require_once __DIR__ . '/DatabaseAdapter.php';

class ReviewModel {
    private DatabaseAdapterInterface $db;

    public function __construct(?DatabaseAdapterInterface $db = null) {
        $this->db = $db ?? new MySqlDatabaseAdapter();
    }

    public function getUserReview(int $recipeId, int $userId): ?array {
        $res = $this->db->select(
            "SELECT * FROM reviews WHERE recipe_id = ? AND user_id = ? LIMIT 1",
            [$recipeId, $userId]
        );

        return !empty($res) ? $res[0] : null;
    }

    public function saveReview(int $recipeId, int $userId, int $rating, ?string $comment): void {
        $rating = max(1, min(5, $rating));
        $comment = !empty(trim($comment ?? '')) ? trim($comment) : null;

        $existing = $this->getUserReview($recipeId, $userId);

        if ($existing) {
            $this->db->select(
                "UPDATE reviews SET rating = ?, comment = ?, created_at = NOW() WHERE id = ?",
                [$rating, $comment, $existing['id']]
            );
        } else {
            $this->db->insert('reviews', [
                'recipe_id' => $recipeId,
                'user_id'   => $userId,
                'rating'    => $rating,
                'comment'   => $comment
            ]);
        }
    }

    public function getByRecipe(int $recipeId, int $currentUserId = 0): array {
        $sql = "
            SELECT 
                r.id,
                r.user_id,
                r.rating,
                r.comment,
                r.created_at,
                u.display_name,
                u.username
            FROM reviews r
            JOIN users u ON r.user_id = u.id
            WHERE r.recipe_id = ?
            ORDER BY (r.user_id = ?) DESC, r.created_at DESC
        ";

        return $this->db->select($sql, [$recipeId, $currentUserId]);
    }

    public function getAverageRating(int $recipeId): float {
        $res = $this->db->select(
            "SELECT AVG(rating) as avg_rating FROM reviews WHERE recipe_id = ?",
            [$recipeId]
        );

        return round((float)($res[0]['avg_rating'] ?? 0), 1);
    }
}