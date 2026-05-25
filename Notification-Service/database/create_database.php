<?php

require_once __DIR__ . '/../routes/check_access.php' ;

require_once PATH_ABSOLUTE . '/config/parameters.php';

$servername = SERVERNAME; // "localhost"
$database = DATABASE; // "SENDING_EMAILS_test"
$username = USERNAME; // "root" или "" - в зависимости от версии РНР
$password = PASSWORD; // ""

$database_table = TABLE_NAME; // "USERS_table";
$database_table_mess = TABLE_NAME_MESS; // "USERS_MESS_table"

// 1. ********  Актуально при первом запуске, когда еще нет базы данных  **********************
// Создание соединения
$conn = @(new mysqli($servername, $username, $password));
// Проверка соединения
    if ($conn->connect_error) {
        http_response_code(500);
        throw new ErrorException("Ошибка подключения: " . $conn->connect_error. ' 1');
    }

// Создание базы данных, если ее еще нет
$sql = "CREATE DATABASE IF NOT EXISTS $database";
    if ($conn->query($sql) !== TRUE) {
        http_response_code(500);
        throw new ErrorException("Ошибка создания базы данных: " . $conn->connect_error . ' 1');
    }
$conn->close();

// 2. Создание нового соединения - для созданной базы данных
$conn_t = new mysqli($servername, $username, $password, $database);
/* Проверить соединение */
if ($conn_t->connect_errno) {
    $mes = "Соединение не удалось:\n". $conn_t->connect_error;
    http_response_code(500);
    throw new ErrorException($mes. '. 1'); // 1 - работу прекращаем
}

// 3. Создание таблицы пользователей в базе данных
$sql = "CREATE TABLE IF NOT EXISTS $database_table (user_id INT(6) UNSIGNED AUTO_INCREMENT PRIMARY KEY, telefon CHAR(15) NOT NULL, email CHAR(50) NOT NULL)";

$mes = '';
if (!mysqli_query($conn_t, $sql)) {
    $mes .= "ERROR: Не удалось выполнить $sql. " . mysqli_error($conn_t).'<br/>';
}

// 4. Создание таблицы СООБЩЕНИЙ пользователей в базе данных
$sql = "CREATE TABLE IF NOT EXISTS $database_table_mess (idd INT(6) UNSIGNED AUTO_INCREMENT PRIMARY KEY, user_id INT(6) UNSIGNED,  email CHAR(50) NOT NULL, theme CHAR(200) NOT NULL, text TEXT (2000), status INT(2))";

$mes = '';
    if (!mysqli_query($conn_t, $sql)) {
        $mes .= "ERROR: Не удалось выполнить $sql. " . mysqli_error($conn_t).'<br/>';
    }
// 5. Закрыть подключение
$conn_t->close();

    if ($mes !== '') {
        http_response_code(500);
        throw new ErrorException($mes. '. 1'); // 1 - работу прекращаем
    }
