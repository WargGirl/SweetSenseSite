<?php

require_once CLASSES_PATH . '/Page.php';
require_once CLASSES_PATH . '/CatalogModel.php';
require_once CLASSES_PATH . '/RecipeSortStrategy.php';

class CatalogController {
    public function index(): void {
        $pageTitle = "SweetSense - " . __('catalog_title');
        $page = new Page($pageTitle);
        $page->renderHeader();

        $currentLang = $_SESSION['lang'] ?? DEFAULT_LANG;
        $searchQuery = trim($_GET['q'] ?? '');
        $selectedCategory = (int)($_GET['category'] ?? 0);
        $selectedSort = $_GET['sort'] ?? 'rating';

        $timeFrom = !empty($_GET['time_from']) ? (int)$_GET['time_from'] : null;
        $timeTo = !empty($_GET['time_to']) ? (int)$_GET['time_to'] : null;
        $minRating = !empty($_GET['min_rating']) ? (float)$_GET['min_rating'] : null;
        $difficulty = trim($_GET['difficulty'] ?? '');

        $includeIngredients = isset($_GET['include_ingredients']) && is_array($_GET['include_ingredients'])
            ? array_map('intval', $_GET['include_ingredients'])
            : [];

        $excludeIngredients = isset($_GET['exclude_ingredients']) && is_array($_GET['exclude_ingredients'])
            ? array_map('intval', $_GET['exclude_ingredients'])
            : [];

        $model = new CatalogModel();
        $rawRecipes = $model->getRecipes(
            $searchQuery, 
            $selectedCategory, 
            $currentLang, 
            $includeIngredients, 
            $excludeIngredients,
            $timeFrom,
            $timeTo,
            $minRating,
            $difficulty
        );
        $categories = $model->getCategories();
        $allIngredients = $model->getAllIngredients($currentLang);

        $strategy = match ($selectedSort) {
            'popularity' => new SortByPopularityStrategy(),
            'time'       => new SortByCookingTimeStrategy(),
            'difficulty' => new SortByComplexityStrategy(),
            default      => new SortByRatingStrategy(),
        };

        $sorter = new RecipeSorter($strategy);
        $recipes = $sorter->sort($rawRecipes);

        require dirname(__DIR__) . '/views/catalog/index.php';

        $page->renderFooter();
    }
}