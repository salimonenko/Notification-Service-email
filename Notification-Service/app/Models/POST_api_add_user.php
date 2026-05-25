<?php
// Добавляет строку данных с информацией о пользователе в БД

// 1. Запрет непосредственного доступа к этому модулю
if (!defined('PATH_ABSOLUTE')) {
    http_response_code(403);
    die('Forbidden POST_api_add_user.php.');
}
// 2. Запрет непосредственного доступа к этому модулю по НТТР
$path_ABSOLUTE = PATH_ABSOLUTE;
require_once $path_ABSOLUTE . '/routes/check_access.php';

$mayBe_params = array("email", "telefon"); // Белый список разрешенных имен полей БД, разрешенный для данного метода

// 3. Проводим проверку и валидацию данных из запроса перед тем, как делать SQL-запрос
$checkig_Arr = checking_DATA_CRUD_methods($mayBe_params, $db);
$mayBe_params_val = $checkig_Arr[0];
$params_types_Arr = $checkig_Arr[1];


if ($mayBe_params_val === null) { // Значит, при валидации выявлено несоответствие данных
    http_response_code(422);
    die_echo_JSON($params_types_Arr, 1, 1);
    return;
}


// 4. Также делаем проверку данных на разные условия
// 4.1. Корректность email
if (!filter_var($mayBe_params_val['email'], FILTER_VALIDATE_EMAIL)) {
    http_response_code(406);
    die_echo_JSON('Некорректный E-mail адрес.', 1, 1);
}


// 5. Если все хорошо, создаем запись
$public_request = $num_CRUD->POST_api_add($mayBe_params_val);

// 6. Анализируем результат выполнения публичного запроса и сообщаем пользователю
if ($public_request !== true) {

    if ($public_request === 'exists') { // Если пользователь уже существует
        http_response_code(400);
        die_echo_JSON("Пользователь c email = " . myGlobals::$array_REQUEST['email'] . " уже существует.", 1, 1);
        return;
    }

    http_response_code(201);
    die_echo_JSON("Пользователь c email = " . myGlobals::$array_REQUEST['email'] . " успешно добавлен.", 1, 1);
    return;

} else {
    http_response_code(406);
    throw new ErrorException('Ошибка при обработке запроса клиента по добавлению пользователя: ' . $public_request . ' 1');
}
