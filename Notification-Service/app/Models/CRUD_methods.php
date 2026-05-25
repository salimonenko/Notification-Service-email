<?php

class CRUD_methods{

    // для соединения с базой данных и имя таблицы
    protected $conn;
    protected $table_name = TABLE_NAME; // "USERS_table";
    protected $table_name_mes = TABLE_NAME_MESS; // "USERS_MESS_table";

    // свойства объекта todo: убрать ? +++
   /* public $user_id;  */

    // конструктор с $db как соединение с базой данных
    public function __construct($db){
        $this->conn = $db;
    }

    public function check_user_id($flag, $user_id_Arr) { // Проверяет наличие пользователя в БД для одного или нескольких id - одним запросом
        try{
            if ($flag === 1) {
                // Вроде бы, выполняется быстрее, чем с использованием SELECT COUNT
            $query = "SELECT EXISTS(SELECT `user_id`  FROM `$this->table_name` WHERE `user_id` = ". $user_id_Arr[0] ." LIMIT 1) AS exist";
//                $query = "SELECT COUNT(DISTINCT user_id)=1 FROM $this->table_name WHERE user_id IN(". $user_id_Arr[0]  .")";

            $returnObj = $this->conn->query($query)->fetch();
            $if_exists = $returnObj;

            if ($if_exists['exist']) { // Если пользователь с таким user_id уже существует в БД
                return 'exists';
            } else {
                return 'NOT_exists';
            }
        }

            if ($flag === 'some') {

                    $users = $user_id_Arr[0];
                    $num = sizeof($user_id_Arr);

                    for ($i=1; $i < $num; $i++) {
                        $users .= ",". $user_id_Arr[$i];
                    }

                $query = "SELECT COUNT(DISTINCT idd) AS count FROM `{$this->table_name_mes}` WHERE idd IN (". $users .") HAVING COUNT(DISTINCT idd) = ". $num;

                    $stmt = $this->conn->prepare($query);
                    if ($stmt->execute()) {
                        return $stmt->fetch(PDO::FETCH_ASSOC);
                    } else {
                        return true;
                    }
            }

        } catch (PDOException $e) {
            return true;
        }

        return false;
    }

    public function check_user_email($user_email) { // Проверяет наличие пользователя в БД пока только для одного или двух id - одним запросом
        try{
                // Вроде бы, выполняется быстрее, чем с использованием SELECT COUNT
                $query = "SELECT EXISTS(SELECT `email`  FROM `$this->table_name` WHERE `email` = '$user_email' LIMIT 1) AS exist";
//                $query = "SELECT COUNT(DISTINCT user_id)=1 FROM $this->table_name WHERE user_id IN(". $user_id_Arr[0]  .")";

                $returnObj = $this->conn->query($query)->fetch();
                $if_exists = $returnObj;

                if($if_exists['exist']){ // Если пользователь с таким user_id уже существует в БД
                    return 'exists';
                }else{
                    return 'NOT_exists';
                }
        } catch (PDOException $e) {
            print_r($this->conn->errorInfo());
            return true;
        }

        return false;
    }


    // Создаем запись в базе данных (добавляем пользователя в таблицу пользователей)
    public function POST_api_add($mayBe_params_val) {

        $mayBe_params_val_keys = array_keys($mayBe_params_val);

        $email = $mayBe_params_val['email'];
		
        $arr = array();
        foreach ($mayBe_params_val as $item) {
            $arr[] = $this->conn->quote($item);
        }

        if (sizeof($arr) !== sizeof($mayBe_params_val)) { // Если Вдруг потерялись значения массива
            http_response_code(500);
            throw new ErrorException('Error: Array sizes are not equal. 1'); // 1 - работу прекращаем
        }

        $mayBe_params_val = $arr;

// Проверяем, существует ли пользователь с таким email
        $x = $this->check_user_email($email);
        if ($x === 'exists') {
            return 'exists';
        }elseif ($x === true) {
            return true;
        }

// Если пользователя с таким email еще нет, до добавляем его
        $field_s = "$this->table_name(". implode(", ", $mayBe_params_val_keys) . ") ";
        $value_s = " VALUES (". implode(", ", $mayBe_params_val). ")";

        // Делаем запрос для вставки Реквизитов, полученных от клиента (в частности, user_id и нулевые значения других полей БД)
//        $query = "INSERT INTO $this->table_name($field1, $field2, $field3) VALUES (3, 5, 7)";  // Шаблон
        $query = "INSERT INTO ". $field_s. $value_s;

        try {
            $this->conn->beginTransaction(); // Оборачиваем в транзакцию (чтобы запись в БД выполнялась на атомарных условиях)
            $returnObj = $this->conn->query($query);
            $this->conn->commit();           // Делаем запись транзакции

            return false;
        } catch (PDOException $e) {
            print_r($this->conn->errorInfo());
            $this->conn->rollBack();         // В случае ошибки делаем откатку транзакций
            throw new ErrorException('Ошибка при обработке запроса клиента на добавление нового пользователя: '. $e);
        }
    }

    // Определяем общее число строк в таблице БД с пользователями
    public function api_add_count_all_rows() {
        $query = "SELECT COUNT(*) AS total_rows FROM `$this->table_name`";

        $returnObj = $this->conn->query($query)->fetch();

        return $returnObj['total_rows'];
    }

    // Вставляем сообщения для пользователей в таблицу БД
    public function api_add_mes_all($mayBe_params_val, $i, $num){

// 1. Получаем идентификаторы и email части пользователей из таблицы БД "TABLE_NAME"
        $query = "SELECT * FROM `$this->table_name` LIMIT ". $i. " , ". $num;

        $this_conn = $this->conn;

        try {
            $returnObj_Arr = $this->conn->query($query)->fetchAll(MYSQLI_ASYNC);
// Массив e-mail, которые будут записаны в БД для последующей отправки сообщений на эти e-mail
            $email_Arr = array_map(function ($item) use ($this_conn){
                return  $this_conn->quote($item->email);
            }, $returnObj_Arr);

        }catch (PDOException $e) {
            print_r($this->conn->errorInfo());
            throw new ErrorException('Ошибка1 при обработке запроса на запись сообщений пользователей в БД: Не удалось получить идентификаторы и email части пользователей: '. $e);
        }
// 2. Записываем сообщения для пользователей в таблицу БД "TABLE_NAME_MESS"
        try{
            $mes = $this->conn->quote($mayBe_params_val['text']);
            $theme = $this->conn->quote($mayBe_params_val['theme']);

            $return_Arr = array_map(function ($item) use ($theme, $mes, $this_conn){
                $email = $this_conn->quote($item->email);

                return  '(DEFAULT, ' . $item->user_id. ',' . $email . ',' . $theme . ',' . $mes . ',' . '0' . ')';
            }, $returnObj_Arr);
            $str = implode(', ', $return_Arr);

// 2.1. Вставляем сообщения для пользователей в таблицу БД
            $query = "INSERT INTO `$this->table_name_mes` (`idd`, `user_id`, `email`, `theme`, `text`, `status`) VALUES ". $str;

            $this->conn->beginTransaction(); // Оборачиваем в транзакцию (чтобы запись в БД выполнялась на атомарных условиях)
//            $returnObj = $this->conn->query($query);
            $num = $this->conn->exec($query);
            $returnObj = $this->conn->lastInsertId(); // Автоматическй ID первой вставляемой в БД строки

            $commit = $this->conn->commit();           // Делаем запись транзакции

                if($commit){ // Если транзакция прошла успешно

// 2.2. На всякий случай, проверяем, совпадает ли число e-mail, на которые ДОЛЖНЫ БЫЛИ записаны в БД для отправки сообщений и фактическое число e-mail, записанное в БД
                    if(sizeof($email_Arr) !== $num){
                        return true;
                    }

                    return array($email_Arr, $returnObj, $num);
                }else{ // В случае ошибки делаем откатку транзакций
                    $this->conn->rollBack();
                    return true;
                }

        }catch (PDOException $e){
            $this->conn->rollBack();         // В случае ошибки делаем откатку транзакций
            print_r($this->conn->errorInfo());
            throw new ErrorException('Ошибка2 при обработке запроса на запись сообщений пользователей в БД: Не удалось записать сообщения для пользователей в таблицу БД'. $e);
        }
        /* (ЭТО ЕСЛИ ДЕЛАТЬ ЧЕРЕЗ ПОДГОТОВКУ ЗАПРОСА И Т.Д. - другой вариант)
        // Подготовляем запрос
        $stmt = $this->conn->prepare($query);
        // Преобразуем опасные символы в безопасные последовательности
        $this->name=htmlspecialchars(strip_tags($this->num));
        // bind values
        $stmt->bindParam(":num", $this->num);
        // execute query
        if($stmt->execute()){
            return true;
        }*/
    }


    // Списание средств (отдельный метод). Не используется
    public function POST_api_withdraw($mayBe_params_val){
        // Пока пустая заготовка на будущее
    }


    public function api_email_sending($status_Arr) {

        $status_Arr_indexes_Arr = array_keys($status_Arr);

// Проверяем, существуют ли пользователи с такими idd
        $x = $this->check_user_id('some', $status_Arr_indexes_Arr);

        if ($x === true) { // Если ошибка
            print_r($this->conn->errorInfo());
            return true;
        }

        // $x = Array([count] => 1) - шаблон
        $count = $x['count']; // Число e-mail


        if ($count != sizeof($status_Arr)) { // Хотя бы один пользователь не найден
            return 'NOT_exists';
        }


// Если ошибок не было, можно делать обновление статусов сообщений данных пользователей
        $str1 = '';
        for ($i = 0; $i < sizeof($status_Arr); $i++) {
            $str1 .= ' WHEN `idd` = ' . $status_Arr_indexes_Arr[$i] . " THEN " . $status_Arr[$status_Arr_indexes_Arr[$i]] . " ";
        }

        $str2 = "(" . $status_Arr_indexes_Arr[0];
        for ($i = 1; $i < sizeof($status_Arr); $i++) {
            $str2 .= "," . $status_Arr_indexes_Arr[$i];
        }
        $str2 .= ")";

        $query1 = "UPDATE `$this->table_name_mes`
                            SET `status` = CASE " .
            $str1 .
            " END 
                            WHERE `idd` IN " .
            $str2;

        /*        $query1 = "UPDATE `$this->table_name_mes`
                                    SET `status` = CASE ".
                                        " WHEN `idd` = 1 THEN 7
                                        WHEN `idd` = 2 THEN 6
                                        WHEN `idd` = 3 THEN 5 "."
                                    END
                                    WHERE `idd` IN ". "(1, 2, 3)";
        */
        try {
            $this->conn->beginTransaction();
            $returnObj1 = $this->conn->query($query1);

            $this->conn->commit();

            return false;

        } catch (PDOException $e) {
            $this->conn->rollBack();
            print_r($this->conn->errorInfo());
            throw new ErrorException('Ошибка при обработке запроса на обновление статусов email-сообщений пользователей: ' . $e);
        }
    }


    public function POST_api_email_messages_user_id($mayBe_params_val) {
        $user_id = $mayBe_params_val['user_id'];

/*        // Проверяем, существует ли пользователь с таким user_id (если нет, то прекращаем работу)
                $x = $this->check_user_id(1, array($user_id));
                if($x === 'NOT_exists'){
                    return 'NOT_exists';
                }elseif ($x === true){
                    return true;
                }
        */
        $query = "SELECT * FROM `$this->table_name_mes` WHERE `user_id` = " . $user_id;

        try {
            $stmt = $this->conn->prepare($query);
            if ($stmt->execute()) {
                return $stmt->fetchAll();
            } else {
                return true;
            }
        } catch (PDOException $e) {
            throw new ErrorException('Ошибка при доступе к записи БД. ' . $e);
        }
    }

// Удаляем таблицу из БД. Не используется
    public function POST_drop_table() {

        $query = "SHOW TABLES LIKE '$this->table_name'";
        try {
            $returnObj = $this->conn->query($query);

            if (sizeof($returnObj->fetchAll()) > 0) { // Если таблица существует

                $query = "DROP TABLE IF EXISTS $this->table_name";
                $returnObj = $this->conn->query($query);

                try { // Для РНР 8.0
// Специально вызываем метод на только что удаленной таблице. Если она действительно удалена, то будет ошибка (в РНР 5.3)
                    return $returnObj->fetchAll();
                } catch (PDOException $er) { // (для РНР 5.3)
                    return 'table_DROPED';
                }

            } else { // Если она уже была удалена
                return 'NOT_exists';
            }

        } catch (PDOException $e) { // Если в работе БД что-то пошло не так
            throw new ErrorException('Ошибка при обработке запроса клиента на проверку присутствия таблицы в базе данных или ее удаление: ' . $e);
        }
    }

}


class show_DATA_types extends CRUD_methods{

    public function show_DATA_types_MySQL(){ // В будущем, возможно, делать запрос к БД для определения фактических типов полей +++
/****    ЭТО КОРРЕКТНО РАБОТАЕТ В рнр 5.3. А в РНР 8 ЧАСТИЧНО(!) ИЗМЕНИЛСЯ(!) формат данных, выводимых методом fetchall(). Так портят язык РНР.  ****/
// Два вида запроса, на выбор
  /*      $query = array("SHOW COLUMNS  FROM $this->table_name FROM REST_CRUD_test",
            $query = "SELECT   COLUMN_NAME, DATA_TYPE FROM  INFORMATION_SCHEMA.COLUMNS WHERE  TABLE_SCHEMA = 'REST_CRUD_test' AND  TABLE_NAME = '$this->table_name'");
*/

        return field_MAX_TYPE_SIZE('');
    }

}
