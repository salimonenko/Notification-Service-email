<?php
// Изменять данные можно только в 1-м разделе
if (!defined('ACCESS') && ACCESS !== 'permit') { // Запрет непосредственного доступа к этому модулю
    die('Forbidden parameters.php.');
}

mb_internal_encoding("utf-8");
define('INTERNAL_ENC', mb_internal_encoding()); // Определяем кодировку UTF-8 везде
mb_regex_encoding("utf-8");


// 1. Можно изменять:
define('MAX_TODO_SIZE', 2000); // Максимальный размер поля в строке БД, попадающих в БД при помощи SQL-запроса
define('USERS_NUM_TO_TAKE', 3); // Число пользователей, которым производится отправка e-mail в рамках ОДНОГО SQL-запроса к БД
define('FLAG_MESS_ECHO', true); // Выводить ли вспомогательные сообщения
define('MAIL_NUMBER_ATTEMPS', 5); // Максимальное число попыток повторной отправки e-mail при неудачах (если код возврате неравен 3)
define('MAIL_TIMEOUT_ATTEMPS', 1); // Максимальное время ожидания в СЕКУНДАХ при повторной отправке e-mail при неудачах (если код возврате неравен 3)

/********    БАЗА ДАННЫХ    **************/
define('SERVERNAME', "localhost");
define('USERNAME', "root");

define('DATABASE', "SENDING_EMAILS_test"); // Имя БД
define('TABLE_NAME', "USERS_table"); // Имя таблицы пользователей БД
define('TABLE_NAME_MESS', "USERS_MESS_table"); // Имя таблицы СООБЩЕНИЙ пользователей БД


/* Временно задаем типы и размеры полей БД. Потом можно бы справить, использовав метод fetch_field_direct() +++
 Впрочем, fetch_field_direct(1)->max_length - Максимальная ширина поля результирующего набора. Но, начиная с PHP 8.1, это значение всегда равно 0. Поэтому для РНР 8 это бесполезно (а в РНР 5.3 не работает). Т.е. лучше бы как-то доработать...
 То же касается метода fetch_field. Поэтому, с учетом глупых перемен в РНР, надежнее будет задавать эти данные жестко, НЕ определять их из запросов к БД.
 */
/****    ТИПЫ И МАКСИМАЛЬНЫЕ РАЗМЕРЫ ПОЛЕЙ БАЗЫ ДАННЫХ MySQL:   ****/
function field_MAX_TYPE_SIZE($field) {

    $fields_TYPES = array('user_id' =>  array('int', 6, 'not_NEGATIVE'), /* 3-й параметр - проверочная функция */
                          'telefon'  =>  array('VARCHAR', 15),
                          'email' =>  array('VARCHAR', 50),
                          'text' =>  array('TEXT', 2000),
                          'theme' =>  array('TEXT', 200),
                          'status' =>  array('int', 2),
                         );

    if ($field === '') { // Если требуется вернуть типы и макс. размеры для ВСЕХ полей, наверное, имеющихся в БД
        return $fields_TYPES;
    }

    if (!array_key_exists($field, $fields_TYPES)) {
        http_response_code(500);
        throw new ErrorException('Неверный индекс массива при валидации данных запроса: '. $field);
    }


return array($field => $fields_TYPES[$field]);
}


// ******************************************************************************************************

// 2. Изменять НЕЛЬЗЯ:
define('THIS_DIR', 'Notification-Service'); // Каталог, содержащий ВСЕ файлы и подкаталоги этого проекта - системы рассылки сообщений. Имя должно быть уникальным

/********    АВТОМАТИЗИРОВАННОЕ ТЕСТИРОВАНИЕ    **************/
define('PATH_TESTING_REZULTS', '/app/Models/Testing/Data/testing_REZULTS.txt'); // Относит. путь к файлу с результатами автом. тестирования
define('PATH_TESTING_REZULTS_CORR', '/app/Models/Testing/Data/testing_REZULTS_correct.txt'); // Относит. путь к файлу с эталонными результатами автом. тестирования
define('PATH_TESTING_PARAMETERS', '/app/Models/Testing/Data/data_for_testing.js'); // Относит. путь к файлу с параметрами для тестирования
define('PATH_TESTING_JSON', '/app/Models/Testing/Data/testing_REZULTS.JSON'); // Относит. путь к файлу с результатами сравнения текущего тестирования и эталонного


/********    БАЗА ДАННЫХ    **************/
if(PHP_MAJOR_VERSION >= 7){
    define('PASSWORD', "root");
}else{
    define('PASSWORD', "");
}





function not_NEGATIVE($number) {
    return (is_numeric($number) && $number >= 0);
}
