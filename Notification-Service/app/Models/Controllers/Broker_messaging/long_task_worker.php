<?php

ob_implicit_flush(true);
//ob_end_flush();

defined('ACCESS') or define('ACCESS', 'permit');

require_once '../../../../config/determine_absolute_PATH.php'; // Определяем путь до основного каталога
defined('PATH_ABSOLUTE') or define('PATH_ABSOLUTE', PATH(__DIR__ , 'Notification-Service'));
require_once PATH_ABSOLUTE . '/config/parameters.php';
if(!function_exists('http_response_code')) {require_once PATH_ABSOLUTE . '/app/Http/http_response_code.php';}

// 1. Получаем GET-параметры
if (isset($_GET['path_ABSOLUTE'])) {
    $path_ABSOLUTE = $_GET['path_ABSOLUTE'];
} else {
    $path_ABSOLUTE = '';
    http_response_code(510);
    throw new ErrorException("Ошибка: cURL в GET-запросе почему-то не передала переменную path_ABSOLUTE.\n 1");
}

if (isset($_GET['first_inserted_ID'])) {
    $first_inserted_ID = $_GET['first_inserted_ID'];
} else {
    $first_inserted_ID = '';
    http_response_code(510);
    throw new ErrorException("Ошибка: cURL в GET-запросе почему-то не передала переменную first_inserted_ID.\n 1");
}

if (isset($_GET['inserted_str_num'])) {
    $inserted_str_num = $_GET['inserted_str_num'];
} else {
    $inserted_str_num = '';
    http_response_code(510);
    throw new ErrorException("Ошибка: cURL в GET-запросе почему-то не передала переменную inserted_str_num.\n 1");
}

if (isset($_GET['mes'])) {
    $mes = ($_GET['mes']);
} else {
    $mes = '';
    http_response_code(510);
    throw new ErrorException("Ошибка: cURL в GET-запросе почему-то не передала переменную mes.\n 1");
}

if (isset($_GET['theme'])) {
    $theme = $_GET['theme'];
} else {
    $theme = '';
    http_response_code(510);
    throw new ErrorException("Ошибка: cURL в GET-запросе почему-то не передала переменную theme.\n 1");
}


// 2. Подключаем модули
require_once $path_ABSOLUTE . '/app/Exceptions/errors_exceptions.php';
if (!function_exists('http_response_code')) {
    require_once $path_ABSOLUTE . '/app/Http/http_response_code.php';
}
require_once $path_ABSOLUTE . '/config/parameters.php';
//  соединение с базой данных
require $path_ABSOLUTE . '/database/database.php';
// Подключаем класс для обработки запросов и передачи их в БД
require_once $path_ABSOLUTE . '/app/Models/CRUD_methods.php';
require_once $path_ABSOLUTE . '/app/Models/Controllers/die_echo_JSON.php';
// Функция, непосредственно отправляющая e-mail (заглушка)
require_once 'send_repeat_email.php';


if(FLAG_MESS_ECHO){
    echo "Начинаем итерацию работы обычной очереди рассылки сообщений... \n";
    flush(); // Не будет работать при AJAX, работает только при непосредственном запуске брокера сообщений. Но, все же актуально для сохранения порядка выводимых сообщений
}

// Функция делает рассылку обычных (несрочных) сообщений e-mail
function longRunningTask($path_ABSOLUTE, $first_inserted_ID, $inserted_str_num, $mes, $theme) {
/*  e-mail в очередь. После этого обновляет соответствующую строку в БД, изменяя в ней статус сообщения:
        0 - не отправлялось;
        1 - в очереди (сообщение принято и ожидает отправки);
        2 - отправлено (передано шлюзу/провайдеру);   <-  фактически используется только это (в целях эмуляции)
        3 - доставлено (подтверждено провайдером);
       -1 - отброшено (ошибка доставки, несуществующий номер/email и т.д.).
*/

// 1. Открываем файл-очередь с обычными e-mail (некритичными), на которые необходимо отправить сообщение
    $filename = $path_ABSOLUTE . '/app/Models/Controllers/Broker_messaging/' . 'queue_emails.csv';

    $fp = fopen($filename, 'r');

    $status_Arr = array();

// 2. Делаем рассылку сообщений на каждый e-mail
    for ($i = $first_inserted_ID; $i < $first_inserted_ID + $inserted_str_num; $i++) {

        $email_Arr = fgetcsv($fp, 1000, "\n", '"'); // Берем ОДИН e-mail из файла-очереди обычных сообщений
        if ($email_Arr !== false) {

            $email = trim($email_Arr[0]);

            if(FLAG_MESS_ECHO){
                echo "Рассылка сообщений из обычной очереди (worker): отправляем для пользователя с id=" . $i . "  e-mail на <b>" . $email . "</b>\n";
            }

            $status = 0; // Для начала
            $status = send_repeat_email($status, $email, $mes, $theme); // На основе политики Exactly-once

            $status_Arr[$i] = $status;

            flush();
        }
        usleep(1); // Чтобы не отправлять все сообщения сразу без перерыва. И для демонстрации асинхронности
    }
    fclose($fp);

// Если все хорошо, обновляем  записи в БД (с учетом текущих статусов отправки сообщений)
    $database = new Database();
    $db = $database->getConnection();

    if (!$db) {
        http_response_code(500);
        throw new ErrorException("Не получилось установить соединение с базой данных для long_task_worker.php.\n. 1"); // 1 - работу прекращаем
    }

    try {
        $num_CRUD = new CRUD_methods($db);

        $public_request = $num_CRUD->api_email_sending($status_Arr);

// Анализируем результат выполнения публичного запроса и сообщаем пользователю
        if ($public_request !== true) {

            if ($public_request === 'NOT_exists') { // Если пользователь(и) НЕ существует в БД
                http_response_code(500);
                die_echo_JSON("Ошибка сервера: При попытке изменения статуса отправленных сообщений как минимум, один из пользователей не наден(ы). Поэтому изменение статуса не выполнено.", 1, 1);

                return; // По идее, излишнее, на всякий случай
            } elseif ($public_request === false) { // Если все хорошо, значит, все сообщения в рамках одного этапы были успешно отправлены

//                fputcsv($fp_done, array(implode(PHP_EOL, $email_done_Arr)), "\n", '"');
//            die_echo_JSON("Сообщения пользователей успешно отправлены, статусы сообщений соответственно изменены.", 1, 1);
            }

        } else {
            http_response_code(502);
            throw new ErrorException('Ошибка при обработке запроса на изменение статуса отправленных сообщений.  1');
        }


    } catch (PDOException $exception) {
//    http_response_code(502);
        throw new ErrorException('В файле ' . __FILE__ . ', стр.' . __LINE__ . ".\n" . $exception . ".\n");
    }

// Очищаем файл-очередь обычных сообщений (перед следующем обращением к этой функции, для рассылки следующей порции обычных сообщений, он будет создан заново)
    fclose(fopen($filename, 'w'));

    if(FLAG_MESS_ECHO){
        echo "Рассылка очередной порции сообщений из обычной очереди (worker) завершена\n\n";
        flush();
    }
}

longRunningTask($path_ABSOLUTE, $first_inserted_ID, $inserted_str_num, $mes, $theme);
