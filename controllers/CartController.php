<?php

require_once CLASSES_PATH . '/Page.php';
require_once CLASSES_PATH . '/Auth.php';
require_once CLASSES_PATH . '/CartModel.php';
require_once CLASSES_PATH . '/CartItemFactory.php';

class CartController {
    public function index(): void {
        $auth = new Auth();

        if (!$auth->check()) {
            header('Location: index.php?route=login');
            exit;
        }

        $userId = (int)$_SESSION['user_id'];
        $currentLang = $_SESSION['lang'] ?? DEFAULT_LANG;
        $cartModel = new CartModel();

        if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['action']) && $_POST['action'] === 'clear') {
            $cartModel->clearCart($userId);
            header('Location: index.php?route=cart');
            exit;
        }

        $groupedItems = $cartModel->getGroupedCart($userId, $currentLang);
        $pageTitle = SITE_NAME . ' - ' . __('nav_cart');

        $page = new Page($pageTitle);
        $page->renderHeader();

        require dirname(__DIR__) . '/views/cart/index.php';

        $page->renderFooter();
    }
}