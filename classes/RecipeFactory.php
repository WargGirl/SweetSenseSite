<?php

class RecipeFactory {

    public function createFromFormData(array $postData, array $files, int $authorId): array {

        $uploadDir = dirname(__DIR__) . '/public/assets/uploads/recipes/';
        if (!is_dir($uploadDir)) {
            mkdir($uploadDir, 0777, true);
        }

        $mainImagePath = trim($postData['existing_image'] ?? '');

        if (!empty($files['image']['name']) && ($files['image']['error'] ?? UPLOAD_ERR_NO_FILE) === UPLOAD_ERR_OK) {
            $ext = pathinfo($files['image']['name'], PATHINFO_EXTENSION);
            $newFileName = 'recipe_' . time() . '_' . uniqid() . '.' . $ext;
    
            if (move_uploaded_file($files['image']['tmp_name'], $uploadDir . $newFileName)) {
                $mainImagePath = 'assets/uploads/recipes/' . $newFileName;
            }
        }

        $recipeData = [
            'author_id'    => $authorId,
            'title_uk'     => trim($postData['title_uk'] ?? ''),
            'title_en'     => trim($postData['title_en'] ?? ''),
            'summary_uk'   => trim($postData['desc_uk'] ?? ''),
            'summary_en'   => trim($postData['desc_en'] ?? ''),
            'cooking_time' => (int)($postData['cooking_time'] ?? 0),
            'difficulty'   => $postData['difficulty'] ?? 'easy',
            'image_url'    => $mainImagePath,
            'video_url'    => !empty($postData['video_url']) ? trim($postData['video_url']) : null,
            'afterword_uk' => !empty($postData['outro_uk']) ? trim($postData['outro_uk']) : null,
            'afterword_en' => !empty($postData['outro_en']) ? trim($postData['outro_en']) : null,
            'category_id'  => (int)($postData['category_id'] ?? 0)
        ];

        $ingredients = [];
        foreach ($postData['ingredients'] ?? [] as $ing) {
            if (!empty($ing['name']) && !empty($ing['amount'])) {
                $ingredients[] = [
                    'name'    => trim($ing['name']),
                    'amount'  => (float)$ing['amount'],
                    'unit_id' => (int)($ing['unit_id'] ?? 1)
                ];
            }
        }

        $steps = [];
        foreach ($postData['steps'] ?? [] as $idx => $step) {
            $stepImg = $step['existing_image'] ?? null;

            if (!empty($files['step_images']['name'][$idx]) && ($files['step_images']['error'][$idx] ?? UPLOAD_ERR_NO_FILE) === UPLOAD_ERR_OK) {
                $ext = pathinfo($files['step_images']['name'][$idx], PATHINFO_EXTENSION);
                $stepFileName = 'step_' . time() . "_{$idx}_" . uniqid() . '.' . $ext;
                
                if (move_uploaded_file($files['step_images']['tmp_name'][$idx], $uploadDir . $stepFileName)) {
                    $stepImg = 'assets/uploads/recipes/' . $stepFileName;
                }
            }
        
            $steps[] = [
                'uk'        => $step['uk'] ?? '',
                'en'        => $step['en'] ?? '',
                'tip_uk'    => !empty($step['tip_uk']) ? $step['tip_uk'] : null,
                'tip_en'    => !empty($step['tip_en']) ? $step['tip_en'] : null,
                'image_url' => $stepImg ?: null
            ];
        }

        return [
            'recipe'      => $recipeData,
            'ingredients' => $ingredients,
            'steps'       => $steps
        ];
    }

    public function createViewModel(array $rawRecipe, array $ingredients, array $steps): array {
        $img = $rawRecipe['image_url'] ?? '';
        if (!empty($img) && !str_starts_with($img, 'http') && !str_starts_with($img, '/')) {
            $img = BASE_URL . '/' . ltrim($img, '/');
        } elseif (empty($img)) {
            $img = BASE_URL . '/assets/img/placeholder.jpg';
        }

        return [
            'id' => (int)$rawRecipe['id'],
            'title' => [
                'uk' => $rawRecipe['title_uk'] ?? '',
                'en' => $rawRecipe['title_en'] ?? ''
            ],
            'rating' => [
                'score'         => (float)($rawRecipe['avg_rating'] ?? 5.0),
                'reviews_count' => (int)($rawRecipe['reviews_count'] ?? 0)
            ],
            'meta' => [
                'cooking_time' => (int)($rawRecipe['cooking_time'] ?? 0),
                'difficulty'   => $rawRecipe['difficulty'] ?? 'easy',
                'author'       => $rawRecipe['author_name'] ?? 'SweetSense Bakery'
            ],
            'summary' => [
                'uk' => $rawRecipe['summary_uk'] ?? '',
                'en' => $rawRecipe['summary_en'] ?? ''
            ],
            'image'       => $img,
            'video_url'   => $rawRecipe['video_url'] ?? null,
            'ingredients' => $ingredients,
            'steps'       => $steps,
            'afterword'   => [
                'uk' => $rawRecipe['afterword_uk'] ?? '',
                'en' => $rawRecipe['afterword_en'] ?? ''
            ]
        ];
    }
}