<?php

/**
 * Autoloader don gian theo chuan PSR-4, khong can Composer.
 * Namespace "App\..." -> thu muc "app/..." (App\Core\Router -> app/Core/Router.php).
 */
spl_autoload_register(function (string $class): void {
    $prefix = 'App\\';
    if (!str_starts_with($class, $prefix)) {
        return;
    }

    $relative = substr($class, strlen($prefix));
    $relativePath = str_replace('\\', '/', $relative) . '.php';
    $file = __DIR__ . '/' . $relativePath;

    if (is_file($file)) {
        require $file;
    }
});
