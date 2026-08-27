<?php

class Router {
    private static ?Router $instance = null;
    private array $routes = [];

    private function __construct() {}
    private function __clone() {}
    public function __wakeup() {
        throw new \Exception("Cannot unserialize a singleton.");
    }

    public static function getInstance(): Router {
        if (self::$instance === null) {
            self::$instance = new self();
        }
        return self::$instance;
    }

    public function add(string $route, array|callable $handler): void {
        $this->routes[trim($route, '/')] = $handler;
    }

    public function dispatch(PDO $pdo, ?string $route = null): void {
        if ($route === null) {
            $route = $_GET['route'] ?? 'home';
        }
        $route = trim($route, '/');
        if ($route === '') {
            $route = 'home';
        }

        if (isset($this->routes[$route])) {
            $handler = $this->routes[$route];

            if (is_callable($handler)) {
                call_user_func($handler, $pdo);
            } elseif (is_array($handler)) {
                [$controllerClass, $method] = $handler;
                $controllerFile = dirname(__DIR__) . "/controllers/{$controllerClass}.php";

                if (file_exists($controllerFile)) {
                    require_once $controllerFile;
                    $controller = new $controllerClass($pdo);
                    $controller->$method();
                } else {
                    http_response_code(500);
                    echo "Controller {$controllerClass} not found at {$controllerFile}";
                }
            }
        } else {
            http_response_code(404);
            echo "<h1>404 — Сторінку не знайдено</h1><p>Маршрут [{$route}] не зареєстровано.</p>";
        }
    }
}