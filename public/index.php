<?php

require_once __DIR__ . '/../includes/init.php';
require_once CLASSES_PATH . '/Database.php';
require_once CLASSES_PATH . '/Router.php';

$db = new Database();
$pdo = $db->getPdo();

$router = Router::getInstance();

$router->add('home', ['HomeController', 'index']);
$router->add('users', ['UserController', 'index']);
$router->add('admin', ['AdminController', 'index']);
$router->add('catalog', ['CatalogController', 'index']);
$router->add('cart', ['CartController', 'index']);
$router->add('recipe', ['RecipeController', 'index']);
$router->add('recipe_export', ['RecipeController', 'export']);
$router->add('recipe_create', ['RecipeCreateController', 'index']);
$router->add('support', ['SupportController', 'index']);
$router->add('login', ['AuthController', 'login']);
$router->add('register', ['AuthController', 'register']);
$router->add('profile', ['AuthController', 'profile']);
$router->add('logout', ['AuthController', 'logout']);
$router->add('recipe_edit', ['RecipeCreateController', 'edit']);
$router->dispatch($pdo);