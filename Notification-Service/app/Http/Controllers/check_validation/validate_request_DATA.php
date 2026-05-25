<?php

// Проводим валидацию параметров перед тем, как делать SQL-запрос
function validate_request_DATA($mayBe_params_val, $params_types_Arr){
/* Проверяется:
 * 1. Тип полей данных в БД на его соответствие имеющимся типам данных в MySQL
 * 2. Размер полей данных в БД на соответствие заданному размеру
 */
    $mess = ''; // Сообщение об ошибках для вывода клиенту

    foreach($mayBe_params_val as $item){
        $key = array_search($item, $mayBe_params_val);


        if(isset($params_types_Arr[$key])){
            $types_Arr = $params_types_Arr[$key];
            $type_data = $types_Arr[0];
            $max_len_data = $types_Arr[1];

            if(isset($types_Arr[2])){
                $cheking_Function = $types_Arr[2];
            }else{
                $cheking_Function = function ($x){return true;};
            }

            if(int_types_MySQL($type_data) && (!$cheking_Function($item) || !is_numeric($item))){
                http_response_code(422);
                $mess .= 'Неверный тип параметра запроса: '. $key. ' = '. $item .' (' . gettype($item). '), тогда как требуется неотрицательное число целого типа.';
            }

            if(float_types_MySQL($type_data) && (!$cheking_Function($item) || !is_numeric($item))){
                http_response_code(422);
                $mess .= 'Неверный тип параметра запроса: '. $key. ' = '. $item .' (' . gettype($item). '), тогда как требуется неотрицательное число - целое или десятичное с разделительной точкой (типа float).';
            }
// Остальные типы переменных (например, char) из запроса нужно анализировать дополнительно! +++


            if(mb_strlen($item, INTERNAL_ENC) > $max_len_data){
                http_response_code(413);
                $mess .= 'Слишком большая длина параметра запроса: '. $key. ' = '. $item. ' (превышает '. $max_len_data. ' символов)';
            }

        }else{
            http_response_code(520);
            $mess .= 'Странная ошибка. Несуществующий индекс: '. ' ['. $key.']'. '<br/>';
        }
    }
    return $mess; // Если непустое, значит, были проблемы
}

// Возвращает массив типов параметров
function params_Types($public_request_validate, $mayBe_params){
/* Ранее было: $public_request_validate - массив вида:
   Array
(
    [0] => Array
        (
            [Field] => ID
            [0] => ID
            [Type] => int(6) unsigned      (В РНР 8 выводится  [Type] => int unsigned  , без указания макс. размера поля)
            [1] => int(6) unsigned                             [1] => int unsigned
            [Null] => NO
            [2] => NO
            [Key] => PRI
            [3] => PRI
            [Default] =>
            [4] =>
            [Extra] => auto_increment
            [5] => auto_increment
        )

    [1] => Array
        (.....

        )

    ..........

Теперь: $public_request_validate = array(ID_type, title_type, description_type, status_type);
 *
 * $mayBe_params - массив вида:
   Array
(
    [0] => ID
    [1] => title
    [2] => description
    [3] => status
)
 */
    $params_Types = array(); // Массив типов параметров запроса, исходя из форматов полей в базе данных


    foreach ($mayBe_params as $field){
        if(!array_key_exists($field, $public_request_validate)){
            http_response_code(500);
            throw new ErrorException('Предполагаемое поле базы данных из запроса НЕ ЗАДАНО в белом списке: <b>'. $field. '</b> 1');
        }else{
            $field_type_size = field_MAX_TYPE_SIZE($field);
            $params_Types[$field] = $field_type_size[$field];
        }
    }
// Возвращает массив типов ТРЕБУЕМЫХ (т.е. только тех, которые пойдут в SQL-запрос) параметров вида
    /* Array
    (
        [title] => Array
            (
                [0] => char
                [1] => 50
            )

        [description] => Array
            (
                [0] => char
                [1] => 200
            )

        [status] => Array
            (
                [0] => int
                [1] => 1
            )

    ) */
return $params_Types;
}


