<?php
// Получение и проверка данных запроса (перед валидацией)

function check_request_DATA($mayBe_params){
/* $mayBe_params - массив вида (индексы условные):
  Array
(
    [0] => title
    [1] => description
    [2] => status
)
 */

    $mayBe_params_val = array();
/* 1. Присутствует ли в запросе параметр, имеющийся в массиве
 * 2. Не превышает ли его длина максимально заданную (потом еще будут уточняющие поверки, исходя из максимальных размеров полей БД)
 */

    for($i = 0; $i < sizeof($mayBe_params); $i++){
        if(!isset(myGlobals::$array_REQUEST[$mayBe_params[$i]]) || strlen(myGlobals::$array_REQUEST[$mayBe_params[$i]]) > MAX_TODO_SIZE){
            http_response_code(500);
            throw new ErrorException('Bad value <b>'. $mayBe_params[$i]. '</b>: этот параметр отсутствует в запросе клиента или его длина превышает '. MAX_TODO_SIZE. '. 1'); // 1 - работу прекращаем
        }else{
            $mayBe_params_val[$mayBe_params[$i]] = myGlobals::$array_REQUEST[$mayBe_params[$i]];
        }
    }

    if(sizeof($mayBe_params) !== sizeof($mayBe_params_val)){ // Если в новый массив попало не 3 параметра, а больше или меньше
        http_response_code(500);
        Exception_response('Error: Array sizes are not equal', 1); // Прекратить работу
    }

/* Массив $mayBe_params_val вида:
  Array
(
    [title] => qwe
    [description] => ee5666йцушщ
    [status] => 3
)
 */
return array($mayBe_params, $mayBe_params_val);
 }


