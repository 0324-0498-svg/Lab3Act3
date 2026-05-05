<?php
session_start();
if (!isset($_SESSION['user_id'])) { header("Location: index.php"); exit; }
require_once 'classes/Database.php';

$database = new Database();
$db = $database->getConnection();
$role = $_SESSION['role'] ?? 'staff';
 
if ($_SERVER['REQUEST_METHOD'] == 'POST' && isset($_POST['submit_customer'])) {
    $name = $_POST['name'];
    $email = $_POST['email'];

    $insert_query = "INSERT INTO customers (name, email) VALUES (:name, :email)";
    $insert_stmt = $db->prepare($insert_query);
    $insert_stmt->bindParam(':name', $name);
    $insert_stmt->bindParam(':email', $email);
    
    if ($insert_stmt->execute()) {
        header("Location: customers.php?status=success");
        exit;
    }
}
 
$query = "SELECT c.*, COUNT(o.id) as order_count 
          FROM customers c 
          LEFT JOIN orders o ON c.id = o.customer_id 
          GROUP BY c.id 
          ORDER BY c.name ASC";
$stmt = $db->query($query);
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Customers - ORDERFLOW</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.3/font/bootstrap-icons.min.css">
    <style>
        :root { --sidebar-bg: #0d1b2a; --sidebar-hover: #1b263b; --accent-blue: #3a86ff; }
        body { background-color: #f4f7f6; font-family: 'Segoe UI', sans-serif; }
        .wrapper { display: flex; min-height: 100vh; }
        .sidebar { width: 260px; background-color: var(--sidebar-bg); color: white; position: fixed; height: 100%; z-index: 1000; }
        .sidebar-header { padding: 25px; font-size: 1.5rem; font-weight: bold; border-bottom: 1px solid rgba(255,255,255,0.1); text-align: center; letter-spacing: 2px; }
        .nav-link { padding: 12px 25px; color: #adb5bd !important; border-left: 4px solid transparent; text-decoration: none; display: block; }
        .nav-link:hover, .nav-link.active { background-color: var(--sidebar-hover); color: white !important; border-left: 4px solid var(--accent-blue); }
        .main-content { flex: 1; margin-left: 260px; padding: 40px; }
        .data-card { background: white; padding: 25px; border-radius: 15px; box-shadow: 0 4px 15px rgba(0,0,0,0.05); }
        .badge-ai { font-size: 0.7rem; text-transform: uppercase; letter-spacing: 0.5px; padding: 5px 10px; border-radius: 5px; }
    </style>
</head>
<body>
<div class="wrapper">
    <nav class="sidebar">
        <div class="sidebar-header text-uppercase">ORDERFLOW</div>
        <div class="nav flex-column mt-3">
            <a href="dashboard.php" class="nav-link"><i class="bi bi-speedometer2 me-2"></i> Dashboard</a>
            <a href="customers.php" class="nav-link active"><i class="bi bi-people me-2"></i> Customers</a>
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
                <h2 class="fw-bold m-0">Customer Directory</h2>
                <p class="text-muted small">Manage residents and track purchase behavior.</p>
            </div>
            <button type="button" class="btn btn-primary d-flex align-items-center shadow-sm" data-bs-toggle="modal" data-bs-target="#addCustomerModal">
                <i class="bi bi-person-plus-fill me-2"></i> Add New Customer
            </button>
        </div>

        <?php if (isset($_GET['status']) && $_GET['status'] == 'success'): ?>
            <div class="alert alert-success alert-dismissible fade show border-0 shadow-sm mb-4" role="alert">
                <i class="bi bi-check-circle-fill me-2"></i> Customer registered successfully!
                <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
            </div>
        <?php endif; ?>

        <div class="data-card">
            <div class="table-responsive">
                <table class="table table-hover align-middle">
                    <thead class="table-dark">
                        <tr>
                            <th class="ps-3">ID</th>
                            <th>Customer Name & Insights</th>
                            <th>Email Address</th>
                            <th class="text-center">Total Orders</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php while ($row = $stmt->fetch(PDO::FETCH_ASSOC)): 
                           
                            $count = $row['order_count'];
                            if ($count >= 5) {
                                $class = "Frequent Buyer";
                                $badge = "bg-success text-white";
                                $icon = "bi-star-fill";
                            } elseif ($count >= 2) {
                                $class = "Occasional Buyer";
                                $badge = "bg-info text-dark";
                                $icon = "bi-bag-check";
                            } else {
                                $class = "New Customer";
                                $badge = "bg-light text-muted border";
                                $icon = "bi-person-plus";
                            }
                        ?>
                            <tr>
                                <td class="ps-3 text-muted fw-bold">#<?php echo $row['id']; ?></td>
                                <td>
                                    <div class="fw-bold text-dark"><?php echo htmlspecialchars($row['name']); ?></div>
                                    <span class="badge badge-ai <?php echo $badge; ?> mt-1">
                                        <i class="bi <?php echo $icon; ?> me-1"></i><?php echo $class; ?>
                                    </span>
                                </td>
                                <td><i class="bi bi-envelope me-2 text-muted"></i><?php echo htmlspecialchars($row['email']); ?></td>
                                <td class="text-center">
                                    <span class="fw-bold h6 mb-0"><?php echo $count; ?></span>
                                </td>
                            </tr>
                        <?php endwhile; ?>
                    </tbody>
                </table>
            </div>
        </div>
    </main>
</div>
 
<div class="modal fade" id="addCustomerModal" tabindex="-1" aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered">
        <div class="modal-content" style="border-radius: 15px;">
            <div class="modal-header border-0 pb-0">
                <h5 class="fw-bold"><i class="bi bi-person-plus me-2"></i>Register New Customer</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <form method="POST">
                <div class="modal-body p-4">
                    <div class="mb-3">
                        <label class="form-label small fw-bold text-muted text-uppercase">Full Name</label>
                        <input type="text" name="name" class="form-control form-control-lg" placeholder="e.g. Juan Dela Cruz" required>
                    </div>
                    <div class="mb-3">
                        <label class="form-label small fw-bold text-muted text-uppercase">Email Address</label>
                        <input type="email" name="email" class="form-control form-control-lg" placeholder="juan@example.com" required>
                    </div>
                </div>
                <div class="modal-footer border-0 pt-0">
                    <button type="button" class="btn btn-light px-4" data-bs-dismiss="modal">Cancel</button>
                    <button type="submit" name="submit_customer" class="btn btn-primary px-4 shadow-sm">Save Customer</button>
                </div>
            </form>
        </div>
    </div>
</div>

<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>
