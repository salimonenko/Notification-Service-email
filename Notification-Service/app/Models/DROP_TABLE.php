<?php
// Не используется

if(!defined('PATH_ABSOLUTE')){die('Forbidden.');} // Запрет непосредственного доступа к этому модулю

$path_ABSOLUTE = PATH_ABSOLUTE;

require_once $path_ABSOLUTE . '/routes/check_access.php'; // Запрет непосредственного доступа к этому модулю

$public_request = $num_CRUD->POST_drop_table();

// Анализируем результат выполнения публичного запроса и сообщаем пользователю
if($public_request !== 'NOT_exists'){

    if($public_request === 'table_DROPED'){ // Для РНР 5.3
        http_response_code(200);
        echo "Таблица ". " успешно удалена из базы данных.";
	}elseif($public_request === array() ){ // Для РНР 8.0
		http_response_code(200);
		echo "Таблица ". " успешно удалена из базы данных.";
    }else{
        http_response_code(500);
        echo $public_request;
    }

    }else{
        http_response_code(200);
        echo 'Таблица уже была удалена из базы данных, больше ее нет.';
    }



