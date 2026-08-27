<?php

class IngredientFactory {
    public function createFromFormData(array $data): array {
        return [
            'id'      => (int)($data['ingredient_id'] ?? 0),
            'name_uk' => trim($data['name_uk'] ?? ''),
            'name_en' => trim($data['name_en'] ?? '')
        ];
    }
}