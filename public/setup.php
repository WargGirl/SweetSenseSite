<?php

require_once __DIR__ . '/../includes/init.php';
require_once CLASSES_PATH . '/DatabaseSetup.php';

$message = '';
$isError = false;

try {
    new DatabaseSetup();
    $message = 'Базу даних успішно створено та ініціалізовано початковими даними.';
} catch (RuntimeException $e) {
    http_response_code(500);
    $message = $e->getMessage();
    $isError = true;
}
?>
<!DOCTYPE html>
<html lang="uk">
<head>
    <meta charset="UTF-8">
    <title><?= htmlspecialchars(SITE_NAME) ?> - Ініціалізація БД</title>
</head>
<body>
    <main>
        <h1><?= htmlspecialchars($message) ?></h1>
        <?php if ($isError): ?>
            <p>Перевірте, чи запущено MySQL у XAMPP, і повторіть спробу.</p>
        <?php endif; ?>
    </main>
</body>
</html>