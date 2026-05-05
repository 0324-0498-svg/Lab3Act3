<?php
session_start();
if (!isset($_SESSION['user_id'])) { header("Location: index.php"); exit; }

require_once 'classes/Database.php';
$database = new Database();
$db = $database->getConnection();
$role = $_SESSION['role'] ?? 'staff';

// --- SUMMARY DATA ---
$summary_query = "SELECT 
                    COUNT(id) as total_orders, 
                    SUM(amount) as total_revenue 
                  FROM orders";
$summary_stmt = $db->prepare($summary_query);
$summary_stmt->execute();
$summary = $summary_stmt->fetch(PDO::FETCH_ASSOC);

// --- STAFF PERFORMANCE (ORDERED HIGHEST TO LOWEST) ---
$staff_query = "SELECT 
                    u.full_name, 
                    COUNT(o.id) as orders_handled, 
                    SUM(o.amount) as total_generated 
                FROM users u 
                LEFT JOIN orders o ON u.id = o.user_id 
                GROUP BY u.id 
                ORDER BY orders_handled DESC, total_generated DESC"; // Inayos ang order dito
$staff_stmt = $db->prepare($staff_query);
$staff_stmt->execute();
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Reports - ORDERFLOW</title>
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
        .report-card { 
            background: white; 
            padding: 25px; 
            border-radius: 15px; 
            box-shadow: 0 4px 15px rgba(0,0,0,0.05); 
            text-align: center;
            border-top: 4px solid var(--accent-blue);
        }
        .stats-val { font-size: 2.2rem; font-weight: bold; color: var(--sidebar-bg); }
        .table-container { background: white; padding: 25px; border-radius: 15px; box-shadow: 0 4px 15px rgba(0,0,0,0.05); }
    </style>
</head>
<body>

<div class="wrapper">
    <nav class="sidebar">
        <div class="sidebar-header text-uppercase">ORDERFLOW</div>
        <div class="nav flex-column mt-3">
            <a href="dashboard.php" class="nav-link"><i class="bi bi-speedometer2 me-2"></i> Dashboard</a>
            <a href="customers.php" class="nav-link"><i class="bi bi-people me-2"></i> Customers</a>
            <a href="orders.php" class="nav-link"><i class="bi bi-cart-check me-2"></i> Orders</a>
            <a href="reports.php" class="nav-link active"><i class="bi bi-graph-up-arrow me-2"></i> Reports</a>
            
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
        <div class="mb-4">
            <h2 class="fw-bold">Business Analytics</h2>
            <p class="text-muted">Ranked performance based on total orders processed.</p>
        </div>
        
        <div class="row mb-5">
            <div class="col-md-6">
                <div class="report-card">
                    <div class="text-muted text-uppercase small fw-bold">Total Orders Processed</div>
                    <div class="stats-val"><?php echo number_format($summary['total_orders']); ?></div>
                </div>
            </div>
            <div class="col-md-6">
                <div class="report-card">
                    <div class="text-muted text-uppercase small fw-bold">Gross Revenue</div>
                    <div class="stats-val text-success">₱<?php echo number_format($summary['total_revenue'] ?? 0, 2); ?></div>
                </div>
            </div>
        </div>

        <h4 class="fw-bold mb-3"><i class="bi bi-trophy me-2 text-warning"></i>Staff Performance Ranking</h4>
        <div class="table-container">
            <table class="table table-hover align-middle">
                <thead class="table-dark">
                    <tr>
                        <th style="width: 50px;">#</th>
                        <th>Staff Name</th>
                        <th class="text-center">Orders Handled</th>
                        <th class="text-end">Revenue Generated</th>
                    </tr>
                </thead>
                <tbody>
                    <?php 
                    $rank = 1;
                    if($staff_stmt->rowCount() > 0): 
                        while($staff = $staff_stmt->fetch(PDO::FETCH_ASSOC)): ?>
                        <tr>
                            <td><span class="text-muted"><?php echo $rank++; ?></span></td>
                            <td class="fw-bold"><?php echo htmlspecialchars($staff['full_name']); ?></td>
                            <td class="text-center">
                                <span class="badge bg-primary rounded-pill px-3"><?php echo $staff['orders_handled']; ?></span>
                            </td>
                            <td class="text-end text-success fw-bold">
                                ₱<?php echo number_format($staff['total_generated'] ?? 0, 2); ?>
                            </td>
                        </tr>
                        <?php endwhile; ?>
                    <?php else: ?>
                        <tr><td colspan="4" class="text-center text-muted">No data available.</td></tr>
                    <?php endif; ?>
                </tbody>
            </table>
        </div>
    </main>
</div>

</body>
</html>
