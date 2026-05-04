<?php
session_start();
if (!isset($_SESSION['user_id'])) header("Location: login.php");

require_once 'classes/Database.php';
require_once 'classes/Order.php';

$database = new Database();
$db = $database->getConnection();
$order = new Order($db);
$stmt = $order->readAll();
?>

<!DOCTYPE html>
<html>
<head>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
</head>
<body class="bg-light">
<div class="container mt-5">
    <h2>Transaction Report</h2>
    <p>Logged in as: <strong><?php echo $_SESSION['user_name']; ?></strong></p>
    
    <table class="table table-bordered table-striped mt-4">
        <thead class="table-dark">
            <tr>
                <th>Order ID</th>
                <th>Customer</th>
                <th>Product</th>
                <th>Amount</th>
                <th>Processed By (Staff)</th>
                <th>Date</th>
            </tr>
        </thead>
        <tbody>
            <?php while ($row = $stmt->fetch(PDO::FETCH_ASSOC)): ?>
            <tr>
                <td><?php echo $row['id']; ?></td>
                <td><?php echo $row['customer_name']; ?></td>
                <td><?php echo $row['product_name']; ?></td>
                <td>$<?php echo $row['amount']; ?></td>
                <td><span class="badge bg-primary"><?php echo $row['staff_name']; ?></span></td>
                <td><?php echo $row['order_date']; ?></td>
            </tr>
            <?php endwhile; ?>
        </tbody>
    </table>
    <a href="logout.php" class="btn btn-danger">Logout</a>
</div>
</body>
</html>