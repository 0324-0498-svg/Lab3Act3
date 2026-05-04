<?php
session_start();
if (!isset($_SESSION['user_id'])) { header("Location: index.php"); exit; }

require_once 'classes/Database.php';
$database = new Database();
$db = $database->getConnection();

$query = "SELECT 
            o.id, 
            o.product_name, 
            o.amount, 
            o.order_date, 
            u.full_name as staff_name 
          FROM orders o 
          LEFT JOIN users u ON o.user_id = u.id 
          ORDER BY o.order_date DESC 
          LIMIT 10";

$stmt = $db->prepare($query);
$stmt->execute();
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>Dashboard - Smart Order System</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <style>
        :root { --sidebar-bg: #0d1b2a; --sidebar-hover: #1b263b; --accent-blue: #3a86ff; }
        body { background-color: #f4f7f6; font-family: 'Segoe UI', sans-serif; }
        .wrapper { display: flex; min-height: 100vh; }
        .sidebar { width: 260px; background-color: var(--sidebar-bg); color: white; position: fixed; height: 100%; }
        .nav-link { padding: 15px 25px; color: #adb5bd !important; text-decoration: none; display: block; border-left: 4px solid transparent; }
        .nav-link:hover, .nav-link.active { background-color: var(--sidebar-hover); color: white !important; border-left: 4px solid var(--accent-blue); }
        .main-content { flex: 1; margin-left: 260px; padding: 40px; }
        .data-card { background: white; padding: 25px; border-radius: 15px; box-shadow: 0 4px 15px rgba(0,0,0,0.05); }
    </style>
</head>
<body>

<div class="wrapper">
    <nav class="sidebar">
        <div class="p-4 fs-4 fw-bold border-bottom border-secondary">SHOPTRAK</div>
        <div class="nav flex-column mt-3">
            <a href="dashboard.php" class="nav-link active">Dashboard</a>
            <a href="customers.php" class="nav-link">Customers</a>
            <a href="orders.php" class="nav-link">Orders</a>
            <a href="reports.php" class="nav-link">Reports</a>
            <a href="developers.php" class="nav-link">Developers</a>
            <hr class="mx-3 text-secondary">
            <a href="logout.php" class="nav-link text-danger">Logout</a>
        </div>
    </nav>

    <main class="main-content">
        <h2 class="fw-bold mb-4">Recent Transactions</h2>

        <div class="data-card">
            <table class="table table-hover">
                <thead class="table-dark">
                    <tr>
                        <th>Order ID</th>
                        <th>Product</th>
                        <th>Amount</th>
                        <th>Processed By</th>
                        <th>Date</th>
                    </tr>
                </thead>
                <tbody>
                    <?php while ($row = $stmt->fetch(PDO::FETCH_ASSOC)): ?>
                        <tr>
                            <td>#<?php echo $row['id']; ?></td>
                            <td><?php echo htmlspecialchars($row['product_name']); ?></td>
                            <td class="text-success fw-bold">₱<?php echo number_format($row['amount'], 2); ?></td>
                            <td><span class="badge bg-primary px-3"><?php echo htmlspecialchars($row['staff_name'] ?? 'System Admin'); ?></span></td>
                            <td><?php echo date('M d, Y', strtotime($row['order_date'])); ?></td>
                        </tr>
                    <?php endwhile; ?>
                </tbody>
            </table>
        </div>
    </main>
</div>

</body>
</html>
