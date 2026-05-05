<?php
// 1. I-load ang Composer Autoload at ang iyong OOP Classes
require_once 'vendor/autoload.php'; 
require_once 'classes/Database.php';
require_once 'classes/Auth.php';
require_once 'classes/Customer.php';
require_once 'classes/Order.php';

// 2. Simulan ang Auth at i-check ang Session
Auth::checkSession(); 

// Machine Learning Library
use Phpml\Regression\LeastSquares;

$database = new Database();
$db = $database->getConnection();

// 3. I-initialize ang mga Entity at Transaction Classes
$customerObj = new Customer($db);
$orderObj = new Order($db);

$role = $_SESSION['role'] ?? 'staff';
$full_name = $_SESSION['full_name'] ?? 'User';

// --- MACHINE LEARNING LOGIC: SALES FORECASTING ---
$ml_data = $orderObj->getRecentAmounts(10); // Gamit ang method mula sa Order class

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

    $nextIndex = count($samples) + 1;
    $predictedAmount = $regression->predict([$nextIndex]);
    if ($predictedAmount < 0) $predictedAmount = 0; 
}

// --- DASHBOARD STATS GAMIT ANG CLASSES ---
$total_sales = $orderObj->getTotalRevenue();
$total_orders = $orderObj->getTotalCount();
$total_customers = $customerObj->getTotalCount();

// --- RECENT TRANSACTIONS ---
$recent_transactions = $orderObj->getRecentTransactions(10);
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Dashboard - Smart Order</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.3/font/bootstrap-icons.min.css">
    <style>
        :root { --sidebar-bg: #0d1b2a; --accent-blue: #3a86ff; }
        body { background-color: #f4f7f6; font-family: 'Segoe UI', sans-serif; }
        .sidebar { width: 260px; background-color: var(--sidebar-bg); color: white; position: fixed; height: 100%; }
        .main-content { margin-left: 260px; padding: 40px; }
        .stat-card { border: none; border-radius: 15px; transition: transform 0.3s; background: white; }
        .stat-card:hover { transform: translateY(-5px); }
        .ai-card { background: linear-gradient(45deg, #3a86ff, #00b4d8); color: white; border: none; border-radius: 15px; }
        .nav-link { color: #adb5bd !important; transition: 0.3s; }
        .nav-link:hover, .nav-link.active { color: white !important; }
    </style>
</head>
<body>

<div class="sidebar p-3 shadow">
    <h4 class="text-center fw-bold text-uppercase mb-4 mt-2">OrderFlow</h4>
    <nav class="nav flex-column">
        <a href="dashboard.php" class="nav-link active mb-2"><i class="bi bi-speedometer2 me-2"></i> Dashboard</a>
        <a href="customers.php" class="nav-link mb-2"><i class="bi bi-people me-2"></i> Customers</a>
        <a href="orders.php" class="nav-link mb-2"><i class="bi bi-cart me-2"></i> Orders</a>
        
        <?php if ($role === 'admin'): ?>
            <hr class="text-secondary">
            <small class="text-uppercase text-muted fw-bold mb-2" style="font-size: 0.7rem;">Admin Only</small>
            <a href="users.php" class="nav-link mb-2"><i class="bi bi-shield-lock me-2"></i> User Management</a>
        <?php endif; ?>

        <a href="logout.php" class="nav-link text-danger mt-5"><i class="bi bi-box-arrow-right me-2"></i> Logout</a>
    </nav>
</div>

<main class="main-content">
    <div class="d-flex justify-content-between align-items-center mb-4">
        <div>
            <h2 class="fw-bold">Hi, <?php echo htmlspecialchars(explode(' ', $full_name)[0]); ?>!</h2>
            <p class="text-muted">Here is what's happening with your smart system today.</p>
        </div>
        <span class="badge <?php echo ($role === 'admin' ? 'bg-danger' : 'bg-primary'); ?> px-3 py-2 shadow-sm">
            <i class="bi bi-person-badge me-1"></i> <?php echo strtoupper($role); ?>
        </span>
    </div>
    
    <div class="row g-4">
        <div class="col-md-3">
            <div class="card stat-card shadow-sm p-3">
                <div class="text-muted small fw-bold">TOTAL REVENUE</div>
                <h3 class="fw-bold mt-1">₱<?php echo number_format($total_sales, 2); ?></h3>
                <i class="bi bi-cash-stack text-success fs-1 position-absolute end-0 bottom-0 m-3 opacity-25"></i>
            </div>
        </div>

        <div class="col-md-3">
            <div class="card stat-card shadow-sm p-3">
                <div class="text-muted small fw-bold">CUSTOMERS</div>
                <h3 class="fw-bold mt-1"><?php echo $total_customers; ?></h3>
                <i class="bi bi-people text-info fs-1 position-absolute end-0 bottom-0 m-3 opacity-25"></i>
            </div>
        </div>

        <!-- AI PREDICTION CARD -->
        <div class="col-md-6">
            <div class="card ai-card shadow-lg p-3">
                <div class="d-flex justify-content-between align-items-center">
                    <div class="small fw-bold opacity-75 text-uppercase">AI Sales Forecast (Next Order)</div>
                    <i class="bi bi-robot fs-4"></i>
                </div>
                <h2 class="fw-bold mt-1">₱<?php echo number_format($predictedAmount, 2); ?></h2>
                <div class="small mt-2 font-monospace opacity-75">Algorithm: Least Squares Linear Regression</div>
            </div>
        </div>
    </div>

    <!-- Recent Transactions Table -->
    <div class="mt-5 p-4 bg-white rounded-4 shadow-sm">
        <div class="d-flex justify-content-between align-items-center mb-4">
            <h5 class="fw-bold mb-0">Recent Transactions</h5>
            <a href="orders.php" class="btn btn-sm btn-outline-primary rounded-pill px-3">View All</a>
        </div>
        <div class="table-responsive">
            <table class="table table-hover align-middle">
                <thead class="table-light">
                    <tr>
                        <th>Product</th>
                        <th>Amount</th>
                        <th>Date</th>
                        <th>Staff</th>
                    </tr>
                </thead>
                <tbody>
                    <?php foreach ($recent_transactions as $order): ?>
                    <tr>
                        <td class="fw-bold"><?php echo htmlspecialchars($order['product_name']); ?></td>
                        <td class="text-success fw-bold">₱<?php echo number_format($order['amount'], 2); ?></td>
                        <td class="text-muted small"><?php echo date('M d, Y', strtotime($order['order_date'])); ?></td>
                        <td><span class="badge bg-light text-dark border"><?php echo htmlspecialchars($order['staff_name']); ?></span></td>
                    </tr>
                    <?php endforeach; ?>
                </tbody>
            </table>
        </div>
    </div>
</main>

<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>
