<?php
// Выводит из БД все строки для пользователя с заданным id

if (!defined('PATH_ABSOLUTE')) { // Запрет непосредственного доступа к этому модулю
    die('Forbidden POST_api_email_messages_user_id.php.');
}

$path_ABSOLUTE = PATH_ABSOLUTE;
require_once $path_ABSOLUTE . '/routes/check_access.php'; // Запрет непосредственного доступа к этому модулю по НТТР


$mayBe_params = array('user_id'); // Белый список разрешенных имен полей БД, только user_id

// Проводим проверку и валидацию данных из запроса перед тем, как делать SQL-запрос
$checkig_Arr = checking_DATA_CRUD_methods($mayBe_params, $db);
$mayBe_params_val = $checkig_Arr[0];
$params_types_Arr = $checkig_Arr[1];

if ($mayBe_params_val === null) { // Значит, при валидации выявлено несоответствие данных
    http_response_code(422);
    die_echo_JSON($params_types_Arr, 1, 1);
    return;
}

// Если все хорошо, выводим на экран требуемую запись
$public_request = $num_CRUD->POST_api_email_messages_user_id($mayBe_params_val);


// Анализируем результат выполнения публичного запроса и сообщаем пользователю
if ($public_request !== true) {

    if (sizeof($public_request) === 0) { // Если пользователь НЕ существует
        http_response_code(404);
        die_echo_JSON("Пользователь c id=" . myGlobals::$array_REQUEST['user_id'] . " не найден.", 1, 1);

        return; // По идее, излишнее, на всякий случай
    } else {
        print_r($public_request); // Выводим все строки (с сообщениями) из БД для пользователя с заданным user_id (если такой пользователь существует)
//        die_echo_JSON($public_request, 0, 1);
    }

} else {
    http_response_code(406);
    throw new ErrorException('Ошибка при обработке запроса клиента по выводу записи на экран: ' . $public_request . ' 1');
}
