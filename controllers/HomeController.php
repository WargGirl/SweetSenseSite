<?php

require_once CLASSES_PATH . '/Page.php';

class HomeController {

    public function index(): void {
        $pageTitle = SITE_NAME . ' - ' . __('nav_home');
        $page = new Page($pageTitle);

        $page->renderHeader();
        require dirname(__DIR__) . '/views/home/index.php';
        $page->renderFooter();
    }
}