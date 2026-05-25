<?php
// Пока не используется

// Обновляет строку данных в БД
/* По сути, этот модуль дублируется с POST_api_deposit.php. С одной стороны, можно было бы использовать ОДИН модуль, чтобы не дублировть код. А с другой стороны, совремЁЁнные ожидания (разного рода SOLID, DRY, KISS и прочая ***) говорят, что, вроде как, для каждого метода должен быть свой модуль. Ведь при списании средств могут быть (или появиться в будущем) какие-то особые условия/требования, чем при начислении. Поэтому код практически продублирован, из расчета на будущее. */

if(!defined('PATH_ABSOLUTE')){die('Forbidden.');} // Запрет непосредственного доступа к этому модулю
$path_ABSOLUTE = PATH_ABSOLUTE;
require_once $path_ABSOLUTE . '/routes/check_access.php'; // Запрет непосредственного доступа к этому модулю по НТТР


$mayBe_params = array("user_id", "amount", "comment"); // Белый список разрешенных имен полей БД, разрешенный для данного метода

// Проводим проверку и валидацию данных из запроса перед тем, как делать SQL-запрос
$checkig_Arr = checking_DATA_CRUD_methods($mayBe_params, $db);
$mayBe_params_val = $checkig_Arr[0];
$params_types_Arr = $checkig_Arr[1];

if($mayBe_params_val === null){ // Значит, при валидации выявлено несоответствие данных
    die_echo_JSON($params_types_Arr, 1, 1);
    return;
}

if($mayBe_params_val['amount'] < 0){
    http_response_code(406);
    die_echo_JSON('Сумма списания средств должна быть неотрицательным числом.', 1, 1);
}

$mayBe_params_val['amount'] = -1* $mayBe_params_val['amount'];

// Если все хорошо, создаем запись (используем один и тот же метод, что и для начисления, только сумма будет отрицательной, а не положительной)
$public_request = $num_CRUD->POST_api_deposit($mayBe_params_val);

    // Анализируем результат выполнения публичного запроса и сообщаем пользователю
if($public_request !== true){

    if($public_request === 'NOT_exists'){ // Если пользователь НЕ существует
        http_response_code(404);
        die_echo_JSON("Пользователь user_id=". myGlobals::$array_REQUEST['user_id'] ." не найден.", 1, 1);
        return; // По идее, излишнее, на всякий случай
    }elseif ($public_request === 0){ // Если не изменена ни одна строчка БД
        http_response_code(409);
        die_echo_JSON("Конфликт при списании депозита пользователю user_id=". myGlobals::$array_REQUEST['user_id'] .": запрошенная сумма не списана.", 1, 1);
    }else{
        http_response_code(200);
        die_echo_JSON("Списание депозита пользователю user_id=". myGlobals::$array_REQUEST['user_id'] ." успешно выполнено.", 1, 1);
        return;
    }

}else{
    http_response_code(406);
    throw new ErrorException('Ошибка при обработке запроса клиента по списанию депозита: '. $public_request . ' 1');
}
