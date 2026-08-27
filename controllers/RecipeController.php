<?php

use App\Services\HtmlConverter;

require_once CLASSES_PATH . '/Page.php';
require_once CLASSES_PATH . '/ErrorPage.php';
require_once CLASSES_PATH . '/RecipeModel.php';
require_once CLASSES_PATH . '/ReviewModel.php';
require_once CLASSES_PATH . '/ReviewFactory.php';
require_once CLASSES_PATH . '/RecipeFactory.php';
require_once CLASSES_PATH . '/Auth.php';
require_once CLASSES_PATH . '/CartModel.php';

class RecipeController {

    public function index(): void {
        $recipeId = isset($_GET['id']) ? (int)$_GET['id'] : 1;
        $currentLang = $_SESSION['lang'] ?? DEFAULT_LANG;

        $recipeModel = new RecipeModel();
        $rawRecipe = $recipeModel->getRecipeById($recipeId);

        if (!$rawRecipe) {
            http_response_code(404);
            $page = new ErrorPage(SITE_NAME . ' - 404');
            $page->renderHeader();
            $page->renderNotFound('error_recipe_title', 'error_recipe_text');
            $page->renderFooter();
            return;
        }

        $ingredients = $recipeModel->getIngredients($recipeId, $currentLang);
        $steps = $recipeModel->getSteps($recipeId);

        $recipeFactory = new RecipeFactory();
        $recipe = $recipeFactory->createViewModel($rawRecipe, $ingredients, $steps);

        if ($_SERVER['REQUEST_METHOD'] === 'POST' && !empty($_POST['ingredients'])) {
            $auth = new Auth();
            if (!$auth->check()) {
                header('Location: index.php?route=login');
                exit;
            }

            $userId = (int)$_SESSION['user_id'];
            $cartModel = new CartModel();
            $addedCount = 0;

            foreach ($_POST['ingredients'] as $index => $item) {
                if (isset($item['selected']) && isset($recipe['ingredients'][$index])) {
                    $ingData = $recipe['ingredients'][$index];
                    $qty = (float)($item['missing_qty'] ?? $ingData['amount'] ?? 1);

                    $cartModel->addOrUpdate(
                        $userId,
                        (int)$ingData['ingredient_id'],
                        $recipeId,
                        (int)$ingData['unit_id'],
                        $qty
                    );
                    $addedCount++;
                }
            }

            if ($addedCount > 0) {
                $_SESSION['flash_added'] = true;
            }

            header("Location: index.php?route=recipe&id=" . $recipeId);
            exit;
        }

        $reviewModel = new ReviewModel();
        if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['action']) && $_POST['action'] === 'add_review') {
            if (!empty($_SESSION['user_id'])) {
                $reviewFactory = new ReviewFactory();
                $revData = $reviewFactory->createFromFormData($recipeId, (int)$_SESSION['user_id'], $_POST);

                $reviewModel->saveReview($revData['recipe_id'], $revData['user_id'], $revData['rating'], $revData['comment']);
            }

            header("Location: index.php?route=recipe&id=" . $recipeId . "#reviews-section");
            exit;
        }

        $currentUserId = (int)($_SESSION['user_id'] ?? 0);
        $reviews = $reviewModel->getByRecipe($recipeId, $currentUserId);
        $avgRating = $reviewModel->getAverageRating($recipeId);

        if ($avgRating > 0) {
            $recipe['rating']['score'] = $avgRating;
            $recipe['rating']['reviews_count'] = count($reviews);
        }

        $pageTitle = ($recipe['title'][$currentLang] ?? $recipe['title']['uk']) . " - " . SITE_NAME;
        $page = new Page($pageTitle);
        $page->renderHeader();

        require dirname(__DIR__) . '/views/recipe/index.php';

        $page->renderFooter();
    }

    public function export(): void {
        $recipeId = (int)($_GET['id'] ?? 0);
        if ($recipeId <= 0) {
            http_response_code(400);
            exit('Invalid recipe ID.');
        }

        $currentLang = $_SESSION['lang'] ?? DEFAULT_LANG;
        $recipeModel = new RecipeModel();
        $recipe = $recipeModel->getRecipeById($recipeId);

        if (!$recipe) {
            http_response_code(404);
            exit('Recipe not found.');
        }

        $ingredients = $recipeModel->getIngredients($recipeId, $currentLang);
        $steps = $recipeModel->getSteps($recipeId);

        $title = (string)($currentLang === 'en' ? $recipe['title_en'] : $recipe['title_uk']);
        $summary = (string)($currentLang === 'en' ? $recipe['summary_en'] : $recipe['summary_uk']);
        $afterword = (string)($currentLang === 'en' ? $recipe['afterword_en'] : $recipe['afterword_uk']);

        $html = '<h1>' . htmlspecialchars($title, ENT_QUOTES, 'UTF-8') . '</h1>';
        $html .= '<p>' . htmlspecialchars((string)$summary, ENT_QUOTES, 'UTF-8') . '</p>';
        $html .= '<p>Cooking time: ' . (int)$recipe['cooking_time'] . '</p>';
        $html .= '<h2>Ingredients</h2><ul>';

        foreach ($ingredients as $ingredient) {
            $name = $ingredient['name'];
            $unit = $ingredient['unit'];
            $html .= '<li>' . htmlspecialchars($name, ENT_QUOTES, 'UTF-8') . ': '
                . htmlspecialchars($ingredient['amount'] . ' ' . $unit, ENT_QUOTES, 'UTF-8') . '</li>';
        }

        $html .= '</ul><h2>Steps</h2>';
        foreach ($steps as $step) {
            $stepTitle = (string)($currentLang === 'en' ? $step['title_en'] : $step['title_uk']);
            $instruction = (string)($currentLang === 'en' ? $step['desc_en'] : $step['desc_uk']);
            $tip = (string)($currentLang === 'en' ? $step['tip_en'] : $step['tip_uk']);
            $html .= '<h2>' . (int)$step['number'] . '. ' . htmlspecialchars($stepTitle, ENT_QUOTES, 'UTF-8') . '</h2>';
            $html .= '<p>' . HtmlConverter::textToHtml($instruction) . '</p>';
            if ($tip !== '') {
                $html .= '<p>Tip: ' . HtmlConverter::textToHtml($tip) . '</p>';
            }
        }

        if ($afterword !== '') {
            $html .= '<h2>Notes</h2><p>' . HtmlConverter::textToHtml($afterword) . '</p>';
        }

        $fileName = 'recipe-' . $recipeId . '.txt';
        header('Content-Type: text/plain; charset=UTF-8');
        header('Content-Disposition: attachment; filename="' . $fileName . '"');
        header('X-Content-Type-Options: nosniff');
        echo HtmlConverter::htmlToText($html);
        exit;
    }
}