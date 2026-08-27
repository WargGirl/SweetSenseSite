<?php

require_once CLASSES_PATH . '/Page.php';
require_once CLASSES_PATH . '/IngredientModel.php';
require_once CLASSES_PATH . '/CategoryModel.php';
require_once CLASSES_PATH . '/XmlBackupService.php';
require_once CLASSES_PATH . '/Database.php';
require_once CLASSES_PATH . '/IngredientFactory.php';
require_once CLASSES_PATH . '/CategoryFactory.php';
require_once CLASSES_PATH . '/DatabaseAdapter.php';
require_once CLASSES_PATH . '/LoggingDatabaseDecorator.php';

class AdminController {

    public function index(): void {
        $page = new Page(__('admin_title'));
        $page->renderHeader();

        $this->handlePostActions();

        $activeTab = $_GET['tab'] ?? 'ingredients';
        if (!in_array($activeTab, ['ingredients', 'categories', 'users', 'xml_backup'], true)) {
            $activeTab = 'ingredients';
        }

        $db = new Database();
        $pdo = $db->getPdo();

        $ingredientModel = new IngredientModel();
        $search = trim($_GET['ingredient_q'] ?? '');
        $sort = $_GET['ingredient_sort'] ?? 'name_asc';
        $ingredients = $ingredientModel->getAll($search, $sort);

        $editIngredient = null;
        if (isset($_GET['edit_ingredient'])) {
            foreach ($ingredients as $ing) {
                if ((int)$ing['id'] === (int)$_GET['edit_ingredient']) {
                    $editIngredient = $ing;
                    break;
                }
            }
        }

        $categoryModel = new CategoryModel();
        $categorySearch = trim($_GET['category_q'] ?? '');
        $categorySort = $_GET['category_sort'] ?? 'name_asc';
        $categories = $categoryModel->getAll($categorySearch, $categorySort);

        $editCategory = null;
        if (isset($_GET['edit_category'])) {
            foreach ($categories as $cat) {
                if ((int)$cat['id'] === (int)$_GET['edit_category']) {
                    $editCategory = $cat;
                    break;
                }
            }
        }

        $userSearch = trim($_GET['user_q'] ?? '');
        $userSort = $_GET['user_sort'] ?? 'created_desc';
        $userSortColumns = [
            'created_desc' => 'u.created_at DESC', 
            'created_asc'  => 'u.created_at ASC',
            'name_asc'     => 'u.username ASC', 
            'name_desc'    => 'u.username DESC',
        ];
        $userOrderBy = $userSortColumns[$userSort] ?? $userSortColumns['created_desc'];
        
        $userStmt = $pdo->prepare(
            "SELECT u.id, u.username, u.email, u.display_name, u.role, u.status, u.warnings, u.created_at
             FROM users u
             WHERE u.username LIKE ? OR u.email LIKE ? OR u.display_name LIKE ?
             ORDER BY {$userOrderBy}"
        );
        $userPattern = '%' . $userSearch . '%';
        $userStmt->execute([$userPattern, $userPattern, $userPattern]);
        $users = $userStmt->fetchAll(PDO::FETCH_ASSOC);

        $xmlService = new XmlBackupService();
        $xmlTableHtml = ($activeTab === 'xml_backup') ? $xmlService->parseAndRenderHtmlTable() : '';

        $message = $_SESSION['admin_message'] ?? null;
        $isError = (bool)($_SESSION['admin_message_error'] ?? false);
        unset($_SESSION['admin_message'], $_SESSION['admin_message_error']);

        require dirname(__DIR__) . '/views/admin/index.php';

        $page->renderFooter();
    }

    private function handlePostActions(): void {
        if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
            return;
        }

        if (!empty($_POST['ingredient_action'])) {
            $ingredientFactory = new IngredientFactory();
            $ingredient = $ingredientFactory->createFromFormData($_POST);
            
            $loggedDb = new LoggingDatabaseDecorator(new MySqlDatabaseAdapter());
            $model = new IngredientModel($loggedDb);

            if ($_POST['ingredient_action'] === 'save') {
                $model->save($ingredient['name_uk'], $ingredient['name_en'], $ingredient['id']);
                $_SESSION['admin_message'] = $ingredient['id'] > 0 ? __('admin_ingredient_updated') : __('admin_ingredient_added');
            } elseif ($_POST['ingredient_action'] === 'delete' && !$model->isUsedInRecipes($ingredient['id'])) {
                $model->delete($ingredient['id']);
                $_SESSION['admin_message'] = __('admin_ingredient_deleted');
            }

            header('Location: index.php?route=admin&tab=ingredients');
            exit;
        }

        if (!empty($_POST['category_action'])) {
            $categoryFactory = new CategoryFactory();
            $category = $categoryFactory->createFromFormData($_POST);
            
            $loggedDb = new LoggingDatabaseDecorator(new MySqlDatabaseAdapter());
            $catModel = new CategoryModel($loggedDb);

            if ($_POST['category_action'] === 'save') {
                $catModel->save($category['name_uk'], $category['name_en'], $category['id']);
                $_SESSION['admin_message'] = $category['id'] > 0 ? __('admin_category_updated') : __('admin_category_added');
            }

            header('Location: index.php?route=admin&tab=categories');
            exit;
        }

        if (($_POST['xml_action'] ?? '') === 'export') {
            try {
                $db = new Database();
                $xmlService = new XmlBackupService();
                $xmlService->exportUsersToXml($db->getPdo());
                $_SESSION['admin_message'] = __('admin_xml_export_success');
            } catch (Throwable $e) {
                $_SESSION['admin_message'] = __('admin_xml_export_error') . $e->getMessage();
                $_SESSION['admin_message_error'] = true;
            }

            header('Location: index.php?route=admin&tab=xml_backup');
            exit;
        }
    }
}