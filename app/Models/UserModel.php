<?php
// UserModel.php
require_once __DIR__ . '/../Config/Database.php';

class UserModel {
    private $db;
    public function __construct(){
        $this->db = (new Database())->connect();
    }

    public function findByUsername($username){
        $sql = "SELECT * FROM users WHERE username = :u LIMIT 1";
        $stmt = $this->db->prepare($sql);
        $stmt->execute([':u' => $username]);
        return $stmt->fetch();
    }
}
