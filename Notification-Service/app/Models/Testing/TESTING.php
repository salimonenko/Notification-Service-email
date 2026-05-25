<?php
// Обслуживание тестирования

if(!defined('PATH_ABSOLUTE')){die('Forbidden.');} // Запрет непосредственного доступа к этому модулю
require_once PATH_ABSOLUTE . '/routes/check_access.php'; // Запрет непосредственного доступа к этому модулю

$route = myGlobals::$array_REQUEST['route'];

$route_Arr = routes_REG($route);
$route_without_PARENTHESIS = $route_Arr[0];
$route_parameters = $route_Arr[1];



$path_testing_REZULTS = PATH_ABSOLUTE . PATH_TESTING_REZULTS;

// 1. Если параметров нет
if(!$route_parameters){
    if(file_exists($path_testing_REZULTS)){
        unlink($path_testing_REZULTS); // Перед началом тестирования удаляем файл с результатами
    }
    die('<script>TESTING_this();</script>'); // Функция JS, запускающая прохождение тестов
}

//2. Если передан параметр {save} (сохранить результат каждого тестирования)
if($route_parameters === 'save'){

    // Убираем из строкового выражения объекта элемент route (т.к. для сохранения результатов тестирования он не нужен)
//    if(isset(myGlobals::$json['route'])){
       myGlobals::$json = preg_replace('/,\s*\"route\":\"[^"]*\"/', '', strval(myGlobals::$json)); // Удаляем route из строки, к-рая может быть объектом
//    }
	

    if(!file_put_contents($path_testing_REZULTS, myGlobals::$json.PHP_EOL, FILE_APPEND)){
//   file_put_contents($path_testing_REZULTS, urldecode(json_encode(myGlobals::$array_REQUEST)). PHP_EOL, FILE_APPEND); // Выдает последовательности вида \u4301
        http_response_code(500);
        die_echo_JSON("Ошибка: не удалось записать данные в файл результатов тестирования.", 1, 1);
    }else{
        http_response_code(200);
        die_echo_JSON("ОК: тестовый запрос завершен.", 1, 1);
    }

}



/*
// Экранирование спец. символов в ключах получаемого JSON
function myJson_encode($array){
    $key_val_Arr = array_map(function ($k, $v) {
        echo $k. ' => '. $v.'<br>';
        return '"'. $k.'":"'.urldecode($v).'"';}, array_keys($array), array_values($array));

    return '{'. implode(',', $key_val_Arr). '}';
}
*/
