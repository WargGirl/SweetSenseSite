<?php

define('ROOT_PATH', dirname(__DIR__));

define('INCLUDES_PATH', ROOT_PATH . '/includes');
define('CLASSES_PATH', ROOT_PATH . '/classes');
define('LANG_PATH', ROOT_PATH . '/lang');

define('SITE_NAME', 'SweetSense');
define('DEFAULT_LANG', 'uk');
define('BASE_URL', getenv('RAILWAY_ENVIRONMENT') ? '' : '/SweetSense/public');

define('DB_HOST', getenv('DB_HOST') ?: 'localhost');
define('DB_USER', getenv('DB_USER') ?: 'root');
define('DB_PASS', getenv('DB_PASS') ?: '');
define('DB_NAME', getenv('DB_NAME') ?: 'counter');
define('DB_PORT', getenv('DB_PORT') ?: 3306);
