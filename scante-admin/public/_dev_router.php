<?php
// PHP built-in server router (copia de router.php, nome alternativo p/ contornar lock)
$uri = urldecode(parse_url($_SERVER['REQUEST_URI'], PHP_URL_PATH));
if ($uri !== '/' && file_exists(__DIR__ . $uri)) {
    return false;
}
require __DIR__ . '/index.php';
