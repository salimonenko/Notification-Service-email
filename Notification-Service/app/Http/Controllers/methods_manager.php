<?php
// 1. Запрет непосредственного доступа к этому модулю
if (!defined('PATH_ABSOLUTE')) {
    http_response_code(403);
    die('Forbidden.');
}

$path_ABSOLUTE = PATH_ABSOLUTE;
// 2. Дополнительный запрет непосредственного доступа к этому модулю по НТТР
require_once $path_ABSOLUTE . '/routes/check_access.php';


$allowed_HTTP_METHODS = array('GET', 'POST');

if (!in_array($METHOD, $allowed_HTTP_METHODS)) { // Проверка на всякий случай, мало ли что
    http_response_code(405);
    throw new ErrorException('Ошибочный запрос клиента: неверный НТТР-метод. 1'); // 1 - работу прекращаем
}

// 3. Заголовки
header("Access-Control-Allow-Origin: *");
//header("Content-Type:  text/html; charset=UTF-8");
header("Access-Control-Allow-Methods: POST");
header("Access-Control-Max-Age: 3600");
header("Access-Control-Allow-Headers: Content-Type, Access-Control-Allow-Headers, Authorization, X-Requested-With");

// 4. Создаем БД и таблицы в ней, если их еще нет
require_once $path_ABSOLUTE . '/database/create_database.php';
// 5. соединение с базой данных
require_once $path_ABSOLUTE . '/database/database.php';
// 6. Подключаем класс для обработки запросов и передачи их в БД
require_once $path_ABSOLUTE . '/app/Models/CRUD_methods.php';
// 7. Прочие функции
require_once $path_ABSOLUTE . '/app/Http/Controllers/check_validation/validate_request_DATA.php';
require_once $path_ABSOLUTE . '/app/Http/Controllers/check_validation/check_request_DATA.php';
require_once $path_ABSOLUTE . '/database/Controllers/check_MySQL_DATA_types.php';
require_once $path_ABSOLUTE . '/app/Models/Controllers/checking_DATA_CRUD_methods.php';


$database = new Database();
$db = $database->getConnection();

if (!$db) {
    http_response_code(500);
    throw new ErrorException('Не получилось установить соединение с базой данных. 1'); // 1 - работу прекращаем
}


try {
    $num_CRUD = new CRUD_methods($db);

// 8. Разрешенные названия переменных (последние два названия добавлены для сохранения соотв. элементов при тестировании)
    $allowed_params = array('user_id', 'telefon', 'email', 'emails', 'theme', 'route', 'text', 'status', 'route_from_test', 'xhr_status');

// 9. Убираем из массива неразрешенные переменные (соответствуют индексам)
    foreach (myGlobals::$array_REQUEST as $index => $el) {
        if (!in_array($index, $allowed_params)) {
            unset(myGlobals::$array_REQUEST[$index]);
        }
    }

// 10. Получаем сырой роут
    $route = myGlobals::$array_REQUEST['route'];
// 11. Выделяем из него базовую часть (идущую после знака =)
    $route = substr($route, strpos($route, '=') + 1); // Что-то типа /api/add_user

// 12. Вызываем модель, в зависимости от роута
    if ($route) {

        switch ($route) {
            /* case '/api/deposit':
                 include_once $path_ABSOLUTE. '/app/Models/POST_api_deposit.php';
                 break;
             case '/api/withdraw':
                 include_once $path_ABSOLUTE. '/app/Models/POST_api_withdraw.php';
                 break;
             case '/api/transfer':
                 include_once $path_ABSOLUTE. '/app/Models/POST_api_transfer.php';
                 break;*/
            case '/api/add_user':
                include_once $path_ABSOLUTE . '/app/Models/POST_api_add_user.php';
                break;
            case '/api/add_user_mes_all':
                include_once $path_ABSOLUTE . '/app/Models/POST_api_add_mes_all.php';
                break;

            case '/api/email_messages_user_id':
                include_once $path_ABSOLUTE . '/app/Models/POST_api_email_messages_user_id.php';
                break;

/*            // Удалить таблицу из БД
            case '/DROP_TABLE':
                include_once $path_ABSOLUTE . '/app/Models/DROP_TABLE.php';
                break;

// Для просмотра отметок + + + (без пробелов)  во всех файлах проекта и вывода их клиенту. Их я оставлял, если где требуется доработка
            case '/CHECK_PROBLEMS':
                include_once $path_ABSOLUTE . '/app/Models/Testing/CHECK_PROBLEMS.php';
                break;
// Тестирование (покрытие тестами). Перечень тестовых запросов содержится в файле
            case '/TESTING':
                include_once $path_ABSOLUTE . '/app/Models/Testing/TESTING.php';
                break;
// Сравнивает результаты текущего и эталонного тестирования
            case '/testing_CHECK':
                include_once $path_ABSOLUTE . '/app/Models/Testing/testing_CHECK.php';
                break;*/

            default:
                http_response_code(406);
                throw new ErrorException("Ошибка: выбран неверный запрос на сервер: недопустимое значение параметра route: " . $route, 1, 1);
        }
    } else {
        http_response_code(400);
        throw new ErrorException('route не определен или определен неверно. ');
    }

} catch (ErrorException $er) {
//    http_response_code(502);
    throw new ErrorException('В файле ' . __FILE__ . ', стр.' . __LINE__ . '.<br/>' . $er . '<br/>');
}

