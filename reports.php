<?php
session_start();
if (!isset($_SESSION['user_id'])) { header("Location: index.php"); exit; }

require_once 'classes/Database.php';
$database = new Database();
$db = $database->getConnection();

$summary_query = "SELECT 
                    COUNT(id) as total_orders, 
                    SUM(amount) as total_revenue 
                  FROM orders";
$summary_stmt = $db->prepare($summary_query);
$summary_stmt->execute();
$summary = $summary_stmt->fetch(PDO::FETCH_ASSOC);

$staff_query = "SELECT 
                    u.full_name, 
                    COUNT(o.id) as orders_handled, 
                    SUM(o.amount) as total_generated 
                FROM users u 
                LEFT JOIN orders o ON u.id = o.user_id 
                GROUP BY u.id 
                ORDER BY orders_handled DESC";
$staff_stmt = $db->prepare($staff_query);
$staff_stmt->execute();
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>Reports - Smart Order System</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <style>
        :root { --sidebar-bg: #0d1b2a; --sidebar-hover: #1b263b; --accent-blue: #3a86ff; }
        body { background-color: #f4f7f6; font-family: 'Segoe UI', sans-serif; }
        .wrapper { display: flex; min-height: 100vh; }
        .sidebar { width: 260px; background-color: var(--sidebar-bg); color: white; position: fixed; height: 100%; }
        .nav-link { padding: 15px 25px; color: #adb5bd !important; text-decoration: none; display: block; border-left: 4px solid transparent; }
        .nav-link:hover, .nav-link.active { background-color: var(--sidebar-hover); color: white !important; border-left: 4px solid var(--accent-blue); }
        .main-content { flex: 1; margin-left: 260px; padding: 40px; }
        .report-card { background: white; padding: 20px; border-radius: 15px; box-shadow: 0 4px 15px rgba(0,0,0,0.05); text-align: center; }
        .stats-val { font-size: 2rem; font-weight: bold; color: var(--accent-blue); }
    </style>
</head>
<body>
<div class="wrapper">
    <nav class="sidebar">
        <div class="p-4 fs-4 fw-bold border-bottom border-secondary">SHOPTRAK</div>
        <div class="nav flex-column mt-3">
            <a href="dashboard.php" class="nav-link">Dashboard</a>
            <a href="customers.php" class="nav-link">Customers</a>
            <a href="orders.php" class="nav-link">Orders</a>
            <a href="reports.php" class="nav-link active">Reports</a>
            <a href="developers.php" class="nav-link">Developers</a>
            <hr class="mx-3 text-secondary">
            <a href="logout.php" class="nav-link text-danger">Logout</a>
        </div>
    </nav>

    <main class="main-content">
        <h2 class="fw-bold mb-4">Sales Analytics</h2>
        
        <div class="row mb-5">
            <div class="col-md-6">
                <div class="report-card">
                    <div class="text-muted text-uppercase small">Total Orders</div>
                    <div class="stats-val"><?php echo number_format($summary['total_orders']); ?></div>
                </div>
            </div>
            <div class="col-md-6">
                <div class="report-card">
                    <div class="text-muted text-uppercase small">Total Revenue</div>
                    <div class="stats-val text-success">₱<?php echo number_format($summary['total_revenue'], 2); ?></div>
                </div>
            </div>
        </div>

        <h4 class="fw-bold mb-3">Staff Performance</h4>
        <div class="bg-white p-4 rounded-4 shadow-sm">
            <table class="table table-hover align-middle">
                <thead class="table-light">
                    <tr>
                        <th>Staff Name</th>
                        <th>Orders Processed</th>
                        <th>Total Sales Generated</th>
                    </tr>
                </thead>
                <tbody>
                    <?php while($staff = $staff_stmt->fetch(PDO::FETCH_ASSOC)): ?>
                    <tr>
                        <td class="fw-bold"><?php echo $staff['full_name']; ?></td>
                        <td><?php echo $staff['orders_handled']; ?> transactions</td>
                        <td class="text-success fw-bold">₱<?php echo number_format($staff['total_generated'], 2); ?></td>
                    </tr>
                    <?php endwhile; ?>
                </tbody>
            </table>
        </div>
    </main>
</div>
</body>
</html>
