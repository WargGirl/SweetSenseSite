<?php

interface RecipeSortStrategyInterface {
    public function sort(array $recipes): array;
}

class SortByRatingStrategy implements RecipeSortStrategyInterface {
    public function sort(array $recipes): array {
        uasort($recipes, function ($a, $b) {
            $ratingA = (float)($a['avg_rating'] ?? 0);
            $ratingB = (float)($b['avg_rating'] ?? 0);

            if ($ratingA === $ratingB) {
                return ((int)($b['reviews_count'] ?? 0)) <=> ((int)($a['reviews_count'] ?? 0));
            }

            return $ratingB <=> $ratingA;
        });

        return $recipes;
    }
}

class SortByPopularityStrategy implements RecipeSortStrategyInterface {
    public function sort(array $recipes): array {
        uasort($recipes, function ($a, $b) {
            $countA = (int)($a['reviews_count'] ?? 0);
            $countB = (int)($b['reviews_count'] ?? 0);

            if ($countA === $countB) {
                return ((float)($b['avg_rating'] ?? 0)) <=> ((float)($a['avg_rating'] ?? 0));
            }

            return $countB <=> $countA;
        });

        return $recipes;
    }
}

class SortByCookingTimeStrategy implements RecipeSortStrategyInterface {
    public function sort(array $recipes): array {
        uasort($recipes, function ($a, $b) {
            $timeA = (int)($a['cooking_time'] ?? 0);
            $timeB = (int)($b['cooking_time'] ?? 0);

            return $timeA <=> $timeB;
        });

        return $recipes;
    }
}

class SortByComplexityStrategy implements RecipeSortStrategyInterface {
    private const COMPLEXITY_WEIGHTS = [
        'easy'   => 1,
        'medium' => 2,
        'hard'   => 3,
    ];

    public function sort(array $recipes): array {
        uasort($recipes, function ($a, $b) {
            $diffA = self::COMPLEXITY_WEIGHTS[$a['difficulty'] ?? 'easy'] ?? 1;
            $diffB = self::COMPLEXITY_WEIGHTS[$b['difficulty'] ?? 'easy'] ?? 1;

            return $diffA <=> $diffB;
        });

        return $recipes;
    }
}

class RecipeSorter {
    private RecipeSortStrategyInterface $strategy;

    public function __construct(RecipeSortStrategyInterface $strategy) {
        $this->strategy = $strategy;
    }

    public function setStrategy(RecipeSortStrategyInterface $strategy): void {
        $this->strategy = $strategy;
    }

    public function sort(array $recipes): array {
        return $this->strategy->sort($recipes);
    }
}