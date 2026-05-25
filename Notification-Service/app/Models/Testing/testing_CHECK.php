<?php
// Производит сравнение результатов тестирования: текущего и эталонного наборов - по сохраненным кодам ответов сервера

if(!defined('PATH_ABSOLUTE')){die('Forbidden.');} // Запрет непосредственного доступа к этому модулю
$path_ABSOLUTE = PATH_ABSOLUTE;
require_once $path_ABSOLUTE . '/routes/check_access.php'; // Запрет непосредственного доступа к этому модулю по НТТР

$path = realpath(PATH_ABSOLUTE. PATH_TESTING_REZULTS);

if(!file_exists($path)){
    http_response_code(404);
    die_echo_JSON('Файл с результатами предыдущего (текущего) тестирования '.  PATH_TESTING_REZULTS. ' не найден в каталогах этого проекта.', 1, 1);
}

$path_correct = realpath(PATH_ABSOLUTE. PATH_TESTING_REZULTS_CORR);
if(!file_exists($path)){
    http_response_code(404);
    die_echo_JSON('Файл с результатами эталонного тестирования '.  PATH_TESTING_REZULTS_CORR. ' не найден в каталогах этого проекта.', 1, 1);
}

$results = file($path);
$results_correct = file($path_correct);

if(file_put_contents(PATH_ABSOLUTE. PATH_TESTING_JSON, '') === false){ // Создаем файл для результатов сравнения заново
        http_response_code(500);
        die_echo_JSON("Ошибка: не удалось записать данные JSON в файл результатов тестирования.", 1, 1);
    }

$results_correct_SIZE = sizeof($results_correct);
$results_output = array();

for($i=0; $i < sizeof($results); $i++){ // Перебираем строчки файла с результатами текущего тестирования
    $rez_JSON_str = $results[$i];
    $rez_JSON_obj = json_decode($rez_JSON_str); // Преобразуем строку JSON в объект JSON

    $xhr_status = $rez_JSON_obj->xhr_status;

// Вместо того, чтобы создавать объект и сравнивать каждое из его свойств у двух тестовых наборов, быстрее будет сравнить строки (в текстовом виде)
    
    $pos = strrpos($rez_JSON_str, 'xhr_status')-2;
    $sbstr = substr($rez_JSON_str, 0, $pos); // Убираем из строки все до последних символов xhr_status

// Содержит ли элемент массива $results_correct такую подстроку?
    $xhr_status_correct = 0; // Если подстрока $sbstr из файла с текущими результатами не найдена ни в одной строке файла с эталонными результатами
    $flag_line_exist = false;

        for($j=0; $j < $results_correct_SIZE; $j++){
            if(isset($results_correct[$j])){
                if(substr_count($results_correct[$j], $sbstr) > 0){ // Этот элемент эталонного массива содержит подстроку элемента массива текущего тестирования
                    try{
                        $rez_correct_JSON_obj = json_decode($results_correct[$j]);
                        $xhr_status_correct = $rez_correct_JSON_obj->xhr_status;
                    }catch(ErrorException $er){
                        $xhr_status_correct = -1; // Если строка не может быть преобразована в корректный JSON
                    }

                    unset($results_correct[$j]); // Удаляем, чтобы при последующих итерациях цикла по $i его уже не анализировать
                    break;
                }
            }
        }
        $rez_JSON_obj->xhr_status_correct = $xhr_status_correct; // Добавляем дополнительное поле


$results_output[] = $rez_JSON_obj;
}

    $rez_JSON_str_new = preg_replace_callback('/\\\u([01-9a-fA-F]{4})/', 'prepareUTF8',
        json_encode( $results_output )
    );
/*  Тоже работает, по идее
    $rez_JSON_str_new = preg_replace_callback(
        '/\\\u([0-9a-fA-F]{4})/',
        create_function('$match', 'return mb_convert_encoding("&#" . intval($match[1], 16) . ";", "UTF-8", "HTML-ENTITIES");'),
        json_encode($rez_JSON_obj)
    );
*/


// Сохраняем результаты
    if(file_put_contents(PATH_ABSOLUTE. PATH_TESTING_JSON, $rez_JSON_str_new. PHP_EOL, FILE_APPEND) === false){
        http_response_code(500);
        die_echo_JSON("Ошибка: не удалось записать данные JSON в файл результатов тестирования.", 1, 1);
    }

die_echo_JSON($results_output, 0, 1); // Возвращаем клиенту результаты сравнения в формате JSON


function prepareUTF8($matches){ // Декодируем обратно в символы (например, русскоязычные) из последовательностей вида \u3420
   return json_decode('"\u'.$matches[1].'"');
}
