<?php

$request_PERFORMED = array('text', 'channel', 'emails', 'theme', 'route'); // Разрешены только такие имена переменных в запросе клиента (белый список)


defined('ACCESS') or define('ACCESS', 'permit'); // Константа для возможности доступа к модулям


require_once '../config/determine_absolute_PATH.php'; // Определяем путь до основного каталога
// Рекуррентно определяем путь до начального каталога, определяемого константой THIS_DIR (не более 10 итераций)
defined('PATH_ABSOLUTE') or define('PATH_ABSOLUTE', PATH(__DIR__, 'Notification-Service'));


class myGlobals { // todo: Этот класс лучше бы вынести в соответствующий файл/каталог +++
// Через этот класс создается "суперглобальный" массив, соответствующий результату JSON-запроса. При запросах JSON Этот массив используется вместо $_REQUEST
    public static $array_REQUEST;
    public static $json;
}


require_once PATH_ABSOLUTE . '/app/Exceptions/errors_exceptions.php';
if (!function_exists('http_response_code')) {
    require_once PATH_ABSOLUTE . '/app/Http/http_response_code.php';
}
require_once PATH_ABSOLUTE . '/config/parameters.php';
require_once PATH_ABSOLUTE . '/app/Http/Controllers/check_validation/json_decode_ERROR.php';
require_once PATH_ABSOLUTE . '/app/Models/Controllers/die_echo_JSON.php';

$REQUEST_headers = apache_request_headers();

if (isset($REQUEST_headers) && isset($REQUEST_headers['Content-Type'])) {
    $contentType = strtolower($REQUEST_headers['Content-Type']);
    if (substr_count($contentType, 'application/json')) {
// Если был в запросе заголовок с application/json, то отвечаем клиенту  таким же
        define('CONTENT_TYPE_OUR', 'application/json');
        header('Content-Type: application/json; charset=utf-8');
        $requeFormat = 'JSON';
    } elseif (substr_count($contentType, 'application/x-www-form-urlencoded')) {
// Если запрос был в виде обычного HTML (AJAX)
        define('CONTENT_TYPE_OUR', 'application/x-www-form-urlencoded');
        header('Content-Type: text/html; charset=utf-8');
        $requeFormat = 'HTML';
    } else {
        define('CONTENT_TYPE_OUR', 'application/x-www-form-urlencoded');
        http_response_code(501);
        header('Content-Type: text/html; charset=utf-8');
        throw new ErrorException('В запросе установлен неприемлемый заголовок Content-Type');
    }

} else {
    define('CONTENT_TYPE_OUR', 'application/x-www-form-urlencoded');
    header('Content-Type: text/html; charset=utf-8');
    $requeFormat = 'HTML';
    http_response_code(501);
    header('Content-Type: text/html; charset=utf-8');
    throw new ErrorException('В запросе клиента не установлен заголовок Content-Type');
}


$json = '';
$METHOD = '';


// 1. Если GET_запрос
if ($_SERVER['REQUEST_METHOD'] === 'GET') {
    $json = $_GET['json_str'];
    $METHOD = 'GET';
} elseif ($_SERVER['REQUEST_METHOD'] === 'POST') {
// 2. Если POST-запрос
    $json = file_get_contents('php://input');
    $METHOD = 'POST';
} else {
    http_response_code(405);
    throw new ErrorException('Ошибочный запрос клиента. Должен быть GET или POST. 1'); // 1 - работу прекращаем
}


if (!$json || (strlen($json) > MAX_TODO_SIZE)) {
    http_response_code(400);
    throw new ErrorException('Ошибочный запрос клиента: введены слишком длинные значения или они вообще отсутствуют. 1'); // 1 - работу прекращаем
}

// 3. Получаем параметры запроса клиента
if (defined('CONTENT_TYPE_OUR') && CONTENT_TYPE_OUR === 'application/json') { // Если работаем с JSON-запросами и ответами
// Converts JSON into a PHP object
    myGlobals::$array_REQUEST = json_decode(urldecode($json), true);
    myGlobals::$json = urldecode($json);

    if (!myGlobals::$array_REQUEST || json_decode_ERROR()) {
        http_response_code(422);
        throw new ErrorException('Ошибка функции json_decode при обработке запроса клиента: ' . json_decode_ERROR() . ' 1'); // 1 - работу прекращаем
    }
} elseif (defined('CONTENT_TYPE_OUR') && CONTENT_TYPE_OUR === 'application/x-www-form-urlencoded') { // HTML

    myGlobals::$array_REQUEST = $_REQUEST;
} else {
    http_response_code(422);
    throw new ErrorException('Не тот заголовок Content-Type: должен быть либо <b>application/x-www-form-urlencoded</b>, либо <b>application/json</b>.' . ' 1'); // 1 - работу прекращаем
}


// 4. Вызываем методы, в зависимости от роутов
require_once PATH_ABSOLUTE . '/app/Http/Controllers/methods_manager.php';
