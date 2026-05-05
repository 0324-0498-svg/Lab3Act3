<?php
require_once 'vendor/autoload.php';
use Phpml\Regression\LeastSquares;

class MLModel {
    private $db;

    public function __construct($db_conn) {
        $this->db = $db_conn;
    }

    public function preprocessData($limit = 15) {
        $query = "SELECT amount, order_date FROM orders ORDER BY order_date ASC LIMIT $limit";
        return $this->db->query($query)->fetchAll(PDO::FETCH_ASSOC);
    }

    public function predictSales($history) {
        $samples = [];
        $targets = [];
        
        foreach ($history as $index => $row) {
            $samples[] = [$index + 1];
            $targets[] = (float)$row['amount'];
        }

        if (empty($samples)) return 0;

        $regression = new LeastSquares();
        $regression->train($samples, $targets);
        
        $nextIndex = count($samples) + 1;
        $prediction = $regression->predict([$nextIndex]);
        
        return $prediction < 0 ? 0 : $prediction;
    }

    public function getTopBuyers() {
        $query = "SELECT c.name, COUNT(o.id) as total_orders 
                  FROM customers c 
                  JOIN orders o ON c.id = o.customer_id 
                  GROUP BY c.id 
                  ORDER BY total_orders DESC LIMIT 3";
        return $this->db->query($query)->fetchAll(PDO::FETCH_ASSOC);
    }

    public function getCustomerClusters() {
        $query = "SELECT name, 
                  CASE 
                    WHEN order_count >= 5 THEN 'High Value'
                    WHEN order_count BETWEEN 2 AND 4 THEN 'Medium Value'
                    ELSE 'Low/New'
                  END as cluster_group
                  FROM (SELECT c.name, COUNT(o.id) as order_count 
                        FROM customers c 
                        JOIN orders o ON c.id = o.customer_id 
                        GROUP BY c.id) as sub";
        return $this->db->query($query)->fetchAll(PDO::FETCH_ASSOC);
    }
}
