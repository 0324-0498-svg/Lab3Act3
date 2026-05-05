<?php
session_start();
if (!isset($_SESSION['user_id'])) { header("Location: index.php"); exit; }

require_once 'classes/Database.php';
$database = new Database();
$db = $database->getConnection();

$user_id = $_SESSION['user_id'];
$role = $_SESSION['role'] ?? 'staff';
$full_name = $_SESSION['full_name'] ?? 'User';

$check_orders = $db->prepare("SELECT COUNT(*) FROM orders WHERE user_id = :uid");
$check_orders->execute([':uid' => $user_id]);
$order_count = $check_orders->fetchColumn();

$greeting = ($order_count > 0) ? "Welcome back" : "Welcome";

$count_cust = $db->query("SELECT COUNT(*) FROM customers")->fetchColumn();
$total_rev = $db->query("SELECT SUM(amount) FROM orders")->fetchColumn();

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
        .stat-card { 
            background: white; 
            padding: 20px; 
            border-radius: 12px; 
            box-shadow: 0 4px 10px rgba(0,0,0,0.03); 
            border-left: 5px solid var(--accent-blue); 
            height: 100%;
        }
        .data-card { background: white; padding: 25px; border-radius: 15px; box-shadow: 0 4px 15px rgba(0,0,0,0.05); }
    </style>
</head>
<body>

<div class="wrapper">
    <!-- Pangnavigation sa sidebar -->
    <nav class="sidebar">
        <div class="sidebar-header text-uppercase">ORDERFLOW</div>
        <div class="nav flex-column mt-3">
            <a href="dashboard.php" class="nav-link active"><i class="bi bi-speedometer2 me-2"></i> Dashboard</a>
            <a href="customers.php" class="nav-link"><i class="bi bi-people me-2"></i> Customers</a>
            <a href="orders.php" class="nav-link"><i class="bi bi-cart-check me-2"></i> Orders</a>
            <a href="reports.php" class="nav-link"><i class="bi bi-graph-up-arrow me-2"></i> Reports</a>

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

    <!-- Main Content -->
    <main class="main-content">
        <div class="d-flex justify-content-between align-items-center mb-4">
            <div>
                <h2 class="fw-bold"><?php echo $greeting; ?>, <?php echo htmlspecialchars(explode(' ', $full_name)[0]); ?>!</h2>
                <p class="text-muted">Monitoring system activity for today, <?php echo date('F d, Y'); ?>.</p>
            </div>
            <?php if ($role === 'admin'): ?>
                <span class="badge bg-primary px-3 py-2 shadow-sm"><i class="bi bi-person-badge-fill me-1"></i> ADMIN ACCESS</span>
            <?php endif; ?>
        </div>

        <!-- status griod -->
        <div class="row g-4 mb-5">
            <div class="col-md-4">
                <div class="stat-card">
                    <div class="text-muted small fw-bold text-uppercase">Total Customers</div>
                    <div class="h3 fw-bold mb-0 mt-2"><?php echo number_format($count_cust); ?></div>
                </div>
            </div>
            <div class="col-md-4">
                <div class="stat-card" style="border-left-color: #2ec4b6;">
                    <div class="text-muted small fw-bold text-uppercase">Total Revenue</div>
                    <div class="h3 fw-bold mb-0 mt-2 text-success">₱<?php echo number_format($total_rev ?? 0, 2); ?></div>
                </div>
            </div>
            <div class="col-md-4">
                <div class="stat-card" style="border-left-color: #ff9f1c;">
                    <div class="text-muted small fw-bold text-uppercase">Recent Activities</div>
                    <div class="h3 fw-bold mb-0 mt-2">Active</div>
                </div>
            </div>
        </div>

        <!-- mga recent Transactions Table -->
        <div class="d-flex align-items-center mb-3">
            <h4 class="fw-bold mb-0">Recent Transactions</h4>
            <a href="orders.php" class="ms-auto text-decoration-none small fw-bold">View All Orders <i class="bi bi-arrow-right"></i></a>
        </div>
        
        <div class="data-card">
            <div class="table-responsive">
                <table class="table table-hover align-middle">
                    <thead class="table-dark">
                        <tr>
                            <th>Order ID</th>
                            <th>Product Name</th>
                            <th>Amount</th>
                            <th>Processed By</th>
                            <th>Date</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php while ($row = $stmt->fetch(PDO::FETCH_ASSOC)): ?>
                            <tr>
                                <td><span class="text-muted fw-bold">#<?php echo $row['id']; ?></span></td>
                                <td class="fw-bold"><?php echo htmlspecialchars($row['product_name']); ?></td>
                                <td class="text-success fw-bold">₱<?php echo number_format($row['amount'], 2); ?></td>
                                <td>
                                    <span class="badge bg-light text-dark border rounded-pill px-3">
                                        <i class="bi bi-person me-1"></i>
                                        <?php echo htmlspecialchars($row['staff_name'] ?? 'System'); ?>
                                    </span>
                                </td>
                                <td class="text-muted small"><?php echo date('M d, Y | h:i A', strtotime($row['order_date'])); ?></td>
                            </tr>
                        <?php endwhile; ?>
                        <?php if ($stmt->rowCount() == 0): ?>
                            <tr><td colspan="5" class="text-center py-4 text-muted">No recent transactions found.</td></tr>
                        <?php endif; ?>
                    </tbody>
                </table>
            </div>
        </div>
    </main>
</div>

</body>
</html>
