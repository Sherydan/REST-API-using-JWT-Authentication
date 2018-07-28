<?php
    class DbConnect{
        private $server = 'localhost';
        private $dbname = 'jwtapi';
        private $dbuser = 'root';
        private $dbpass = 'root';
        public function connect(){
            try{
                $conn = new PDO('mysql:host='. $this->server . ';dbname='. $this->dbname, $this->dbuser, $this->dbpass);
                $conn->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
                return $conn;
            } catch (Exception $e){
                echo "Database error: ". $e->getMessage();
            }
        }   
    }

    $db = new DbConnect;
    $db->connect();
?>