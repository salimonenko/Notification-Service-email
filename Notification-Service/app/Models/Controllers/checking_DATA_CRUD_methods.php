<?php

function checking_DATA_CRUD_methods($mayBe_params, $db) {
    $mayBe_Arr = check_request_DATA($mayBe_params);

//    $mayBe_params = $mayBe_Arr[0];
    $mayBe_params_val = $mayBe_Arr[1];

    $num_show_DATA = new show_DATA_types($db);

// Показываем, какие поля БД какие типы данных имеют и их максимальные размеры
    $public_request_validate = $num_show_DATA->show_DATA_types_MySQL();

    $params_types_Arr = params_Types($public_request_validate, $mayBe_params); // Определяем соответствующие типы полей БД

// Проводим валидацию параметров перед тем, как делать SQL-запрос
    $mess = validate_request_DATA($mayBe_params_val, $params_types_Arr);

    if ($mess) {
        return array(null, $mess);
    }

    return array($mayBe_params_val, $params_types_Arr);
}

