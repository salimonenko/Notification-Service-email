<?php
/* Создает строки данных в таблице БД для ВСЕХ пользователей, которым рассылаются сообщения
Производится в цикле, на каждой итерации рассылается заданное число сообщений. Если очередная итерация прошла успешно, делается следующая итерация.

На каждой итерации вызывается брокер соообщений, который асинхронно (через cURL) управляет рассылкой сообщений из двых очередей: обычной и срочной.
*/

// 1. Запрет непосредственного доступа к этому модулю
if (!defined('PATH_ABSOLUTE')) {
    die('Forbidden POST_api_mes_all.php.');
}
$path_ABSOLUTE = PATH_ABSOLUTE;
// 2. Дполнительный запрет непосредственного доступа к этому модулю по НТТР
require_once $path_ABSOLUTE . '/routes/check_access.php';

$mayBe_params = array("email", "text", "theme"); // Белый список разрешенных имен полей БД, разрешенный для данного метода

// 3. Получаем данные, проводим проверку и валидацию данных из запроса перед тем, как делать SQL-запрос
$checkig_Arr = checking_DATA_CRUD_methods($mayBe_params, $db);
$mayBe_params_val = $checkig_Arr[0];
$params_types_Arr = $checkig_Arr[1];


if ($mayBe_params_val === null) { // Значит, при валидации выявлено несоответствие данных
    die_echo_JSON($params_types_Arr, 1, 1);
    return;
}


// 4. Также делаем проверку данных на разные условия
// 4.1. Макс. длина сообщения
if (strlen($mayBe_params_val['text']) > 2000) {
    http_response_code(406);
    die_echo_JSON('Слишком длинное сообщение (длиннее ' . 2000 . ' символов).', 1, 1);
}

// 5. Если все хорошо, создаем записи в БД о фактах рассылок e-mail
// 5.1. Кому рассылать сообщения: всем пользователям или только отдельным?
$email = myGlobals::$array_REQUEST['email'];

$num = USERS_NUM_TO_TAKE; // Максимальное число пользователей, которым будут отправлены сообщения за один этап

// 6. Определяем общее число строк в таблице БД с пользователями (каждому из них будет разослано сообщение)
$N = $num_CRUD->api_add_count_all_rows();

// 7. Создаем файл-перечень всех отправленных обычных e-mail, на который были отправлены сообщения
//$filename_done = $path_ABSOLUTE . '/app/Models/Controllers/Broker_messaging/' . 'queue_emails_done.csv'; // Перечень всех отправленных обычных e-mail, на который были отправлены сообщения
//$fp_done = fclose(fopen($filename_done, 'w'));

for ($i = 0; $i < $N; $i = $i + $num) {

$num = min($num, $N - $i); // Фактическое число сообщений e-mail (и, соответственно, пользователей), которые будут рассылаться за текущую итерацию цикла

if (!$email) { // Тогда отправляем ВСЕМ пользователям (заданное число, на каждой итерации цикла), имеющимся в таблице "TABLE_NAME"
    $public_request = $num_CRUD->api_add_mes_all($mayBe_params_val, $i, $num);
} else { // Рассылка только для тех, чьи e-mail переданы от клиента

    die('Эта функциональность пока не реализована. Пока возможна отправка сообщений только всем пользователям. Для этого нужно убрать все e-mail из поля ввода e-mail.');
}


// 6. Анализируем результат выполнения публичного запроса
    if ($public_request !== true) {

// 7. Осуществляем псевдорассылку
        $emails_Arr = $public_request[0]; // Массив e-mail, на которые будут рассылаться сообщения
        $first_inserted_ID = $public_request[1]; // id первой вставленной строки с сообщением в БД
        $inserted_str_num = $public_request[2]; // Общее число вставленных строк

        $mes = urlencode($mayBe_params_val['text']);
        $theme = urlencode($mayBe_params_val['theme']);

// 8. Создаем файл-очередь с порцией e-mail, на которые необходимо отправить сообщение. В очереди будут ТОЛЬКО сообщения, рассылаемые на текущей итерации цикла
        $filename = $path_ABSOLUTE . '/app/Models/Controllers/Broker_messaging/' . 'queue_emails.csv';

        $fp = fopen($filename, 'w');
        fputcsv($fp, $emails_Arr, "\n", '"');
        fflush($fp); // Т.к. вскоре этот файл будет читаться, поэтому важно не откладывать запись в него
        fclose($fp);


// 9. Запускаем брокер сообщений
        require_once '/Controllers/Broker_messaging/broker_mess_sender.php';

        broker_mess_sender($first_inserted_ID, $inserted_str_num, $mes, $theme);


// 10. Сообщаем пользователю
//        http_response_code(200);
        if (FLAG_MESS_ECHO) {
            if (!$email) {
                $mes1 = 'Рассылка сообщений '. $i. '...'. ($i + $num - 1) .' пользователям успешно выполнена.'. "\n";
            } else {
                $mes1 = 'Рассылка сообщений пользователям успешно выполнена. Сообщения рассылались пользователям на следующие e-mail: '; // +++
            }
            echo $mes1;
            flush();
        }

    } else {
        http_response_code(406);
        throw new ErrorException('Ошибка при обработке запроса клиента по рассылке e-mail сообщений начислению пользователям: ' . $public_request . ' 1');
    }

}
