<?php
class User {
    private $conn;
    private $table_name = "users";

    public $id;
    public $username;
    public $password;
    public $full_name;

    public function __construct($db) {
        $this->conn = $db;
    }

    public function login($username, $password) {
       
        $query = "SELECT id, username, password, full_name FROM " . $this->table_name . " WHERE username = ? LIMIT 0,1";
        $stmt = $this->conn->prepare($query);
        $stmt->execute([$username]);
        
        $row = $stmt->fetch(PDO::FETCH_ASSOC);
        
        if ($row && $password == $row['password']) {
            $this->id = $row['id'];
            $this->full_name = $row['full_name'];
            return true;
        }
        return false;
    }
}
?>
