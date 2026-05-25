<?php

class Database{

    // Определяем database credentials
    private $host = SERVERNAME; // "localhost";
    private $db_name = DATABASE; // "SENDING_EMAILS_test";
    private $username = USERNAME; // "root";
    private $password = PASSWORD; // "";
    public $conn;

    // Соединение с БД
    public function getConnection(){

        $this->conn = null;

        $dsn_Options = array(PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION);

        try{
            $this->conn = new PDO("mysql:host=" . $this->host . ";dbname=" . $this->db_name, $this->username, $this->password, $dsn_Options);
            $this->conn->exec("set names utf8mb4");
        }catch(PDOException $exception){
            http_response_code(502);
            echo "Connection error: " . $exception->getMessage();
        }

        return $this->conn;
    }
}


