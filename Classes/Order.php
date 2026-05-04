<?php
class Order {
    private $conn;
    private $table = "orders";

    public function __construct($db) {
        $this->conn = $db;
    }

    public function readAll() {
        $query = "SELECT 
                    o.id, o.product_name, o.amount, o.order_date,
                    c.name as customer_name,
                    u.full_name as staff_name
                  FROM " . $this->table . " o
                  JOIN customers c ON o.customer_id = c.id
                  JOIN users u ON o.user_id = u.id
                  ORDER BY o.order_date DESC";
        
        $stmt = $this->conn->prepare($query);
        $stmt->execute();
        return $stmt;
    }

    public function create($customer_id, $user_id, $product, $amount) {
        $query = "INSERT INTO " . $this->table . " (customer_id, user_id, product_name, amount) VALUES (?, ?, ?, ?)";
        $stmt = $this->conn->prepare($query);
        return $stmt->execute([$customer_id, $user_id, $product, $amount]);
    }
}
?>