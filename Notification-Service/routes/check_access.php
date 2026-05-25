<?php
// Проверка доступа к модулям
if (!function_exists('http_response_code')) {
    require_once __DIR__ . '/../app/Http/http_response_code.php';
}

if (!defined(ACCESS) && ACCESS !== 'permit') {
    http_response_code(403);
    $mess = 'Access forbidden';
    die($mess);
}
