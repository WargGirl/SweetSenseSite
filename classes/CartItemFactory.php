<?php

class CartItemFactory {

    public function createCartItem(int $userId, int $ingredientId, int $recipeId, int $unitId, float $qty): array {
        return [
            'user_id'       => $userId,
            'ingredient_id' => $ingredientId,
            'recipe_id'     => $recipeId,
            'unit_id'       => $unitId,
            'quantity'      => max(0.01, $qty)
        ];
    }
}