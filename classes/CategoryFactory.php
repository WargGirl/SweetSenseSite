<?php

class CategoryFactory {
    public function createFromFormData(array $data): array {
        return [
            'id'      => (int)($data['category_id'] ?? 0),
            'name_uk' => trim($data['name_uk'] ?? ''),
            'name_en' => trim($data['name_en'] ?? '')
        ];
    }
}