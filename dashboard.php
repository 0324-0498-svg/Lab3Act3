<?php
session_start();
if (!isset($_SESSION['user_id'])) { header("Location: index.php"); exit; }
 
require_once 'vendor/autoload.php'; 
require_once 'classes/Database.php';

use Phpml\Regression\LeastSquares;

$database = new Database();
$db = $database->getConnection();
 
$user_id = $_SESSION['user_id'];
$role = $_SESSION['role'] ?? 'staff';
$full_name = $_SESSION['full_name'] ?? 'User';
 
$ml_query = "SELECT amount FROM orders ORDER BY id ASC LIMIT 10";
$ml_stmt = $db->query($ml_query);
$ml_data = $ml_stmt->fetchAll(PDO::FETCH_ASSOC);

$samples = [];
$targets = [];
$predictedAmount = 0;

if (count($ml_data) > 0) {
    foreach ($ml_data as $index => $row) {
        $samples[] = [$index + 1]; 
        $targets[] = (float)$row['amount']; 
    }
    $regression = new LeastSquares();
    $regression->train($samples, $targets);
    $predictedAmount = $regression->predict([count($samples) + 1]);
    if ($predictedAmount < 0) $predictedAmount = 0;
}
 
$staff_performance_query = "SELECT u.full_name, COUNT(o.id) as total_processed 
                            FROM users u 
                            LEFT JOIN orders o ON u.id = o.user_id 
                            GROUP BY u.id 
                            ORDER BY total_processed DESC LIMIT 3";
$staff_perf_stmt = $db->query($staff_performance_query);
 
$count_cust = $db->query("SELECT COUNT(*) FROM customers")->fetchColumn();
$total_rev = $db->query("SELECT SUM(amount) FROM orders")->fetchColumn();
$greeting = "Welcome back";
 
$query = "SELECT o.id, o.product_name, o.amount, o.order_date, u.full_name as staff_name 
          FROM orders o 
          LEFT JOIN users u ON o.user_id = u.id 
          ORDER BY o.order_date DESC LIMIT 10";
$stmt = $db->prepare($query);
$stmt->execute();
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Dashboard - ORDERFLOW</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.3/font/bootstrap-icons.min.css">
    <style>
        :root { --sidebar-bg: #0d1b2a; --sidebar-hover: #1b263b; --accent-blue: #3a86ff; }
        body { background-color: #f4f7f6; font-family: 'Segoe UI', sans-serif; }
        .wrapper { display: flex; min-height: 100vh; }
        .sidebar { width: 260px; background-color: var(--sidebar-bg); color: white; position: fixed; height: 100%; z-index: 1000; }
        .sidebar-header { padding: 25px; font-size: 1.5rem; font-weight: bold; border-bottom: 1px solid rgba(255,255,255,0.1); text-align: center; letter-spacing: 2px; }
        .nav-link { padding: 12px 25px; color: #adb5bd !important; text-decoration: none; display: block; border-left: 4px solid transparent; transition: 0.3s; }
        .nav-link:hover, .nav-link.active { background-color: var(--sidebar-hover); color: white !important; border-left: 4px solid var(--accent-blue); }
        .main-content { flex: 1; margin-left: 260px; padding: 40px; }
        .stat-card { background: white; padding: 20px; border-radius: 12px; box-shadow: 0 4px 10px rgba(0,0,0,0.03); border-left: 5px solid var(--accent-blue); height: 100%; transition: 0.3s; }
        .stat-card:hover { transform: translateY(-5px); }
        .ai-card { background: linear-gradient(45deg, #3a86ff, #00b4d8); color: white; border-left: 5px solid #fff; }
        .data-card { background: white; padding: 25px; border-radius: 15px; box-shadow: 0 4px 15px rgba(0,0,0,0.05); height: 100%; }
    </style>
</head>
<body>

<div class="wrapper">
    <nav class="sidebar">
        <div class="sidebar-header text-uppercase">ORDERFLOW</div>
        <div class="nav flex-column mt-3">
            <a href="dashboard.php" class="nav-link active"><i class="bi bi-speedometer2 me-2"></i> Dashboard</a>
            <a href="customers.php" class="nav-link"><i class="bi bi-people me-2"></i> Customers</a>
            <a href="orders.php" class="nav-link"><i class="bi bi-cart-check me-2"></i> Orders</a>
            <a href="reports.php" class="nav-link"><i class="bi bi-graph-up-arrow me-2"></i> Reports</a>
            
            <a href="analytics.php" class="nav-link text-info"><i class="bi bi-cpu-fill me-2"></i> ML Analytics</a>
            
            <?php if ($role === 'admin'): ?>
                <div class="mt-2 mx-2 p-2" style="background: rgba(255,255,255,0.05); border-radius: 8px;">
                    <small class="ms-3 fw-bold text-uppercase" style="font-size: 0.75rem; color: #3a86ff; letter-spacing: 1px;">Admin Tools</small>
                    <a href="manage_users.php" class="nav-link border-0"><i class="bi bi-shield-lock-fill me-2"></i> Control Panel</a>
                </div>
            <?php endif; ?>
 
            <a href="developers.php" class="nav-link"><i class="bi bi-code-slash me-2"></i> Developers</a>
            
            <hr class="mx-3 text-secondary">
            <a href="logout.php" class="nav-link text-danger"><i class="bi bi-box-arrow-right me-2"></i> Logout</a>
        </div>
    </nav>

    <main class="main-content">
        <div class="d-flex justify-content-between align-items-center mb-4">
            <div>
                <h2 class="fw-bold"><?php echo $greeting; ?>, <?php echo htmlspecialchars(explode(' ', $full_name)[0]); ?>!</h2>
                <p class="text-muted small">Barangay Resident Profiling & Order Management System</p>
            </div>
            <span class="badge bg-white text-dark shadow-sm p-2 border">Role: <span class="text-primary text-uppercase"><?php echo $role; ?></span></span>
        </div>
 
        <div class="row g-4 mb-4">
            <div class="col-md-3">
                <div class="stat-card">
                    <div class="text-muted small fw-bold text-uppercase">Total Customers</div>
                    <div class="h3 fw-bold mb-0 mt-2"><?php echo number_format($count_cust); ?></div>
                </div>
            </div>
            <div class="col-md-3">
                <div class="stat-card" style="border-left-color: #2ec4b6;">
                    <div class="text-muted small fw-bold text-uppercase">Total Revenue</div>
                    <div class="h3 fw-bold mb-0 mt-2 text-success">₱<?php echo number_format($total_rev ?? 0, 2); ?></div>
                </div>
            </div>
            <div class="col-md-6">
                <div class="stat-card ai-card shadow-sm position-relative overflow-hidden">
                    <div class="d-flex justify-content-between align-items-start position-relative" style="z-index: 2;">
                        <div>
                            <div class="small fw-bold text-uppercase opacity-75">AI Revenue Forecast (Next Sale)</div>
                            <div class="h2 fw-bold mb-0 mt-1">₱<?php echo number_format($predictedAmount, 2); ?></div>
                        </div>
                        <a href="analytics.php" class="btn btn-sm btn-light text-primary fw-bold">View Model <i class="bi bi-arrow-right"></i></a>
                    </div>
                    <i class="bi bi-robot fs-1 opacity-25 position-absolute" style="right: 10px; bottom: -10px;"></i>
                </div>
            </div>
        </div>
 
        <div class="row g-4 mb-5">
            <div class="col-md-8">
                <h5 class="fw-bold mb-3"><i class="bi bi-clock-history me-2"></i>Recent Transactions</h5>
                <div class="data-card">
                    <div class="table-responsive">
                        <table class="table table-hover align-middle mb-0">
                            <thead class="table-light">
                                <tr style="font-size: 0.85rem;">
                                    <th>Order ID</th>
                                    <th>Product</th>
                                    <th>Amount</th>
                                    <th>Staff</th>
                                </tr>
                            </thead>
                            <tbody>
                                <?php if ($stmt->rowCount() > 0): ?>
                                    <?php while ($row = $stmt->fetch(PDO::FETCH_ASSOC)): ?>
                                        <tr>
                                            <td class="text-muted fw-bold">#<?php echo $row['id']; ?></td>
                                            <td class="fw-semibold"><?php echo htmlspecialchars($row['product_name']); ?></td>
                                            <td class="text-success fw-bold">₱<?php echo number_format($row['amount'], 2); ?></td>
                                            <td><small class="text-muted"><?php echo htmlspecialchars($row['staff_name'] ?? 'System'); ?></small></td>
                                        </tr>
                                    <?php endwhile; ?>
                                <?php else: ?>
                                    <tr><td colspan="4" class="text-center py-4 text-muted small">No transactions found.</td></tr>
                                <?php endif; ?>
                            </tbody>
                        </table>
                    </div>
                </div>
            </div>

            <div class="col-md-4">
                <h5 class="fw-bold mb-3"><i class="bi bi-trophy me-2 text-warning"></i>Top Staff Performance</h5>
                <div class="data-card border-top border-primary border-5">
                    <ul class="list-group list-group-flush mt-2">
                        <?php while($staff = $staff_perf_stmt->fetch(PDO::FETCH_ASSOC)): ?>
                            <li class="list-group-item d-flex justify-content-between align-items-center px-0 py-3 border-bottom">
                                <div>
                                    <div class="fw-bold text-dark"><?php echo htmlspecialchars($staff['full_name']); ?></div>
                                    <small class="text-muted text-uppercase" style="font-size: 0.65rem;">Active Performance</small>
                                </div>
                                <span class="badge bg-primary rounded-pill"><?php echo $staff['total_processed']; ?> Sales</span>
                            </li>
                        <?php endwhile; ?>
                    </ul>
                    <div class="mt-4 p-3 bg-light rounded border text-center">
                        <small class="text-muted d-block italic font-monospace" style="font-size: 0.7rem;">Real-time Staff Metric Tracking</small>
                    </div>
                </div>
            </div>
        </div>
    </main>
</div>

<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>
