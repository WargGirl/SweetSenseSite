<?php

require_once CLASSES_PATH . '/Page.php';
require_once CLASSES_PATH . '/Auth.php';
require_once CLASSES_PATH . '/RecipeModel.php';
require_once CLASSES_PATH . '/RecipeFactory.php';

class RecipeCreateController {

    public function index(): void {
        $auth = new Auth();
        if (!$auth->check() || ($_SESSION['user_role'] ?? '') !== 'admin') {
            header('Location: index.php?route=catalog');
            exit;
        }

        $currentLang = $_SESSION['lang'] ?? DEFAULT_LANG;
        $isUk = ($currentLang === 'uk');
        $errorMessage = null;

        $recipeModel = new RecipeModel();

        if ($_SERVER['REQUEST_METHOD'] === 'POST') {
            try {
                $factory = new RecipeFactory();
                $builtData = $factory->createFromFormData($_POST, $_FILES, (int)$_SESSION['user_id']);

                $recipeId = $recipeModel->createFullRecipe(
                    $builtData['recipe'],
                    $builtData['ingredients'],
                    $builtData['steps']
                );

                header('Location: index.php?route=recipe&id=' . $recipeId);
                exit;

            } catch (Throwable $e) {
                $errorMessage = $e->getMessage();
            }
        }

        $categories = $recipeModel->getCategories();
        $units = $recipeModel->getUnits();
        
        $allIngs = $recipeModel->getAllIngredientsList();
        $ingredientsList = [];
        foreach ($allIngs as $row) {
            $name = $isUk ? $row['name_uk'] : $row['name_en'];
            if (!empty($name)) {
                $ingredientsList[] = trim($name);
            }
        }

        $page = new Page(__('recipe_create_title'));
        $page->renderHeader();

        require dirname(__DIR__) . '/views/recipe/create.php';

        $page->renderFooter();
    }

    public function edit(): void {
        $auth = new Auth();
        if (!$auth->check() || ($_SESSION['user_role'] ?? '') !== 'admin') {
            header('Location: index.php?route=catalog');
            exit;
        }

        $recipeModel = new RecipeModel();
        $recipeId = (int)($_GET['id'] ?? 0);

        if ($_SERVER['REQUEST_METHOD'] === 'POST') {
            if (!empty($_POST['action_delete'])) {
                $recipeModel->deleteRecipe($recipeId);
                header('Location: index.php?route=catalog');
                exit;
            }

            if (!empty($_POST['action_save'])) {
                $factory = new RecipeFactory();
                $builtData = $factory->createFromFormData($_POST, $_FILES, (int)$_SESSION['user_id']);
                $recipeModel->updateFullRecipe($recipeId, $builtData['recipe'], $builtData['ingredients'], $builtData['steps']);
                header('Location: index.php?route=recipe&id=' . $recipeId);
                exit;
            }
        }

        $recipeData = $recipeModel->getById($recipeId);
        $categories = $recipeModel->getCategories();
        $units = $recipeModel->getUnits();
        $allIngs = $recipeModel->getAllIngredientsList();
        $existingImg = $recipeData['image_url'] ?? '';
        $hasExistingImg = !empty($existingImg);

        $currentLang = $_SESSION['lang'] ?? DEFAULT_LANG;
        $isUk = ($currentLang === 'uk');
        $ingredientsList = [];
        foreach ($allIngs as $row) {
            $name = $isUk ? $row['name_uk'] : $row['name_en'];
            if (!empty($name)) $ingredientsList[] = trim($name);
        }

        $page = new Page(__('recipe_create_title'));
        $page->renderHeader();
        require dirname(__DIR__) . '/views/recipe/create.php';
        $page->renderFooter();
    }
}