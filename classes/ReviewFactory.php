<?php

class ReviewFactory {

    public function createFromFormData(int $recipeId, int $userId, array $postData): array {
        $rating = (int)($postData['rating'] ?? 5);
        $comment = trim($postData['comment'] ?? '');

        return [
            'recipe_id' => $recipeId,
            'user_id'   => $userId,
            'rating'    => max(1, min(5, $rating)),
            'comment'   => $comment !== '' ? $comment : null
        ];
    }
}