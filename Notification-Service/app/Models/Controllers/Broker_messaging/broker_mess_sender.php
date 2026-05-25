<?php
/*  Брокер сообщений. Программа  асинхронно запускает две функции:
long_task_worker.php - обработчик рассылок из обычной очереди
long_task_worker1.php - обработчик рассылок из срочной очереди (заготовка)
Каждая из этих функций управляет рассылкой e-mail.

Реализована асинхронность через библиотеку cURL. Брокер по очереди асинхронно запускает каждую из функций (можно добавить еще функции, больше, чем две).
*/

//header('Content-type: text/html; charset=utf-8');

ob_implicit_flush(true);
//ob_end_flush();

function broker_mess_sender($first_inserted_ID, $inserted_str_num, $mes, $theme) {
// 0. Исходные данные:
    $timeout = 10; // Таймаут функции cURL
    $file_log_NAME = 'errors_cURL.log'; // Имя лог-файла ошибок и диагн. сообщений

/******    ДЛЯ БЛОКИРОВКИ НЕПОСРЕДСТВЕННОГО ЗАПУСКА ЭТОГО ФАЙЛА НУЖНО ЗАКОММЕНТИРОВАТЬ ВЕСЬ РАЗДЕЛ 1   ******//* 1. Подгружаем для случая (тестовой проверки), когда потребуется запускать этот файл непосредственно
defined('ACCESS') or define('ACCESS', 'permit');
require_once __DIR__ . '../../../../../config/determine_absolute_PATH.php'; // Определяем путь до основного каталога
defined('PATH_ABSOLUTE') or define('PATH_ABSOLUTE', PATH(__DIR__ , 'Notification-Service'));
if(!function_exists('http_response_code')) {require_once PATH_ABSOLUTE . '/app/Http/http_response_code.php';} */
/**************************************************************************************************************/

// 2. Запрет непосредственного доступа к этому модулю
    if (!defined('PATH_ABSOLUTE')) {
        die('Forbidden broker_mess_sender.php.');
    }

// 3. Параметры и исходные переменные:
    $timeout_ERROR = '';
    $base_name = basename(PATH_ABSOLUTE);
    $protocol = (!empty($_SERVER['HTTPS'])) ? 'https' : 'http';
    $host = $_SERVER['HTTP_HOST'];
    $dir_rel = substr($_SERVER['PHP_SELF'], 0, strpos($_SERVER['PHP_SELF'], $base_name)) . $base_name . str_replace(PATH_ABSOLUTE, '', __DIR__);
    $proto_host_dir_rel = $protocol . '://' . $host . $dir_rel;


    $path_ABSOLUTE = PATH_ABSOLUTE; // Чтобы каждый раз не обращаться к константе. Для ускорения работы


    $urls = array(
// Обработчик обычной очереди отправки сообщений
        $proto_host_dir_rel . '/long_task_worker.php?first_inserted_ID=' . $first_inserted_ID . '&inserted_str_num=' . $inserted_str_num . '&path_ABSOLUTE=' . $path_ABSOLUTE . '&mes=' . $mes . '&theme=' . $theme,
// Обработчик срочной очереди
        $proto_host_dir_rel . '/long_task_worker1.php?path_ABSOLUTE=' . $path_ABSOLUTE
    ); // Можно добавить дополнительные URL


// 4. Проверяем доступность подключаемых файлов (по URL)
    $not_existent_FILES_Arr = array_filter($urls, function ($item) {
        return @file_get_contents($item, null, null, null, 10) === false;
    });

    if (sizeof($not_existent_FILES_Arr)) { // Если вдруг по ошибке были заданы имена отсутствующих на сервере файлов
        header('Content-type: text/html; charset=utf-8');
        http_response_code(404);
        throw new ErrorException('Следующие файлы-программы НЕ существуют: ' . implode(', ', $not_existent_FILES_Arr) . '. 1');
    }


// 5. Запускаем cURL в мультирежиме
    $mh = curl_multi_init();
    $map = (object)array();

// 6. Задаем параметры для cURL
    foreach ($urls as $url) {
        $ch = curl_init($url);
        curl_setopt($ch, CURLOPT_HEADER, FALSE);
        curl_setopt($ch, CURLOPT_TIMEOUT, $timeout); // Таймаут для работы cURL
//curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1); // Если задать, то вывод будет НЕасинхронным
        curl_multi_add_handle($mh, $ch);
        $map->$ch = $url;
    }

// 7. Асинхронный запуск URL из массива $urls при помощи cURL (ниже - перебираем их в цикле по очереди)
    do {
        $status = curl_multi_exec($mh, $unfinishedHandles);
        if ($status !== CURLM_OK) {
            $err = 'Caught Exception: Ошибка в функции curl_multi_exec()' . PHP_EOL;
            if (error_get_last()) {
                $timeout_ERROR .= implode(PHP_EOL, error_get_last()) . ' - ' . date("d-m-Y H:i:s") . PHP_EOL;
            }
            $timeout_ERROR .= $err;
            file_put_contents($file_log_NAME, $timeout_ERROR . ' - ' . date("d-m-Y H:i:s") . PHP_EOL, FILE_APPEND);
            throw new ErrorException($err);
        }
// 7.1. Перебор URL
        while (($info = curl_multi_info_read($mh)) !== false) {
            if ($info['msg'] === CURLMSG_DONE) {
                $handle = $info['handle'];
                curl_multi_remove_handle($mh, $handle);
                $url = $map->$handle;


                if ($info['result'] === CURLE_OK) {
                    $statusCode = curl_getinfo($handle, CURLINFO_HTTP_CODE);

                    if(FLAG_MESS_ECHO){
                        echo "Запрос по адресу " . urldecode($url) . " завершился и вернул HTTP-статус " . $statusCode . "." . PHP_EOL;
                    }
                    curl_multi_getcontent($handle);

                } else {
                    if ($info['result'] === 28) { // 28 - это код окончания работы cURL
                        $timeout_ERROR = 'Превышен таймаут (' . $timeout . ' сек.), заданный для cURL.';
                    } else {
                        $timeout_ERROR = 'Ошибка cURL: ' . $info['result'];
                    }

                }
            }
        }
// 7.2. Контроль ошибок
        if ($unfinishedHandles) {
            if (($updatedHandles = curl_multi_select($mh)) === -1) {
                if (error_get_last()) {
                    $timeout_ERROR .= implode(PHP_EOL, error_get_last()) . ' - ' . date("d-m-Y H:i:s") . PHP_EOL;
                    file_put_contents($file_log_NAME, $timeout_ERROR . ' - ' . date("d-m-Y H:i:s") . PHP_EOL, FILE_APPEND);
                    throw new ErrorException('Caught Exception: Ошибка в функции curl_multi_select()');
                }
            }
        }
    } while ($unfinishedHandles);

    curl_multi_close($mh);


// 8. Обработка сообщений об ошибках:
    if ($timeout_ERROR) {
        echo $timeout_ERROR;
        file_put_contents($file_log_NAME, $timeout_ERROR . ' - ' . date("d-m-Y H:i:s") . PHP_EOL, FILE_APPEND);
    }

}
