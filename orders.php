<?php
session_start();
if (!isset($_SESSION['user_id'])) { header("Location: index.php"); exit; }
require_once 'classes/Database.php';
$database = new Database();
$db = $database->getConnection();
$role = $_SESSION['role'] ?? 'staff';

if (isset($_GET['delete_id'])) {
    if ($role === 'admin') {
        $delete_id = $_GET['delete_id'];
        $del_stmt = $db->prepare("DELETE FROM orders WHERE id = :id");
        if ($del_stmt->execute(['id' => $delete_id])) {
            header("Location: orders.php?msg=deleted");
            exit;
        }
    } else {
        header("Location: orders.php?msg=unauthorized");
        exit;
    }
}
 
if ($_SERVER['REQUEST_METHOD'] == 'POST' && isset($_POST['submit_order'])) {
    $customer_id = $_POST['customer_id'];
    $product_name = $_POST['product_name'];
    $amount = $_POST['amount'];
    $user_id = $_SESSION['user_id'];

    $insert_query = "INSERT INTO orders (customer_id, product_name, amount, user_id, order_date) 
                     VALUES (:customer_id, :product, :amount, :user_id, NOW())";
    $insert_stmt = $db->prepare($insert_query);
    $insert_stmt->bindParam(':customer_id', $customer_id);
    $insert_stmt->bindParam(':product', $product_name);
    $insert_stmt->bindParam(':amount', $amount);
    $insert_stmt->bindParam(':user_id', $user_id);
    
    if ($insert_stmt->execute()) {
        header("Location: orders.php?status=success");
        exit;
    }
}
 
$cust_stmt = $db->query("SELECT id, name FROM customers ORDER BY name ASC");
$customers = $cust_stmt->fetchAll(PDO::FETCH_ASSOC);

$query = "SELECT o.*, u.full_name as staff_name, u.role as staff_role, c.name as customer_name 
          FROM orders o 
          LEFT JOIN users u ON o.user_id = u.id 
          LEFT JOIN customers c ON o.customer_id = c.id 
          ORDER BY o.order_date DESC";
$stmt = $db->query($query);
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Orders - ORDERFLOW</title>
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
        .badge-admin { background-color: #cfe2ff; color: #084298; border: 1px solid #b6d4fe; } 
        .badge-staff { background-color: #f8f9fa; color: #212529; border: 1px solid #dee2e6; } 
    </style>
</head>
<body>
<div class="wrapper">
    <nav class="sidebar">
        <div class="sidebar-header text-uppercase">ORDERFLOW</div>
        <div class="nav flex-column mt-3">
            <a href="dashboard.php" class="nav-link"><i class="bi bi-speedometer2 me-2"></i> Dashboard</a>
            <a href="customers.php" class="nav-link"><i class="bi bi-people me-2"></i> Customers</a>
            <a href="orders.php" class="nav-link active"><i class="bi bi-cart-check me-2"></i> Orders</a>
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

    <main class="main-content">
        <div class="d-flex justify-content-between align-items-center mb-4">
            <h2 class="fw-bold m-0">Order Management</h2>
            <button type="button" class="btn btn-primary d-flex align-items-center" data-bs-toggle="modal" data-bs-target="#addOrderModal">
                <i class="bi bi-plus-lg me-2"></i> Add New Order
            </button>
        </div>

        <?php if (isset($_GET['msg']) && $_GET['msg'] === 'deleted'): ?>
            <div class="alert alert-success alert-dismissible fade show" role="alert">
                Order has been successfully removed.
                <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
            </div>
        <?php endif; ?>

        <?php if (isset($_GET['msg']) && $_GET['msg'] === 'unauthorized'): ?>
            <div class="alert alert-danger alert-dismissible fade show" role="alert">
                <strong>Error!</strong> You do not have permission to delete orders.
                <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
            </div>
        <?php endif; ?>

        <div class="data-card">
            <table class="table table-hover align-middle">
                <thead class="table-dark">
                    <tr>
                        <th>Order ID</th>
                        <th>Customer</th>
                        <th>Product</th>
                        <th>Amount</th>
                        <th>Staff</th>
                        <th>Date</th>
                        <?php if ($role === 'admin'): ?>
                            <th class="text-center">Action</th>
                        <?php endif; ?>
                    </tr>
                </thead>
                <tbody>
                    <?php while ($row = $stmt->fetch(PDO::FETCH_ASSOC)): 
                        $badgeClass = ($row['staff_role'] === 'admin') ? 'badge-admin' : 'badge-staff';
                    ?>
                        <tr>
                            <td>#<?php echo $row['id']; ?></td>
                            <td class="fw-bold"><?php echo htmlspecialchars($row['customer_name'] ?? 'Walk-in'); ?></td>
                            <td><?php echo htmlspecialchars($row['product_name']); ?></td>
                            <td class="fw-bold text-primary">₱<?php echo number_format($row['amount'], 2); ?></td>
                            <td>
                                <span class="badge <?php echo $badgeClass; ?> px-3 py-2 rounded-pill">
                                    <?php echo htmlspecialchars($row['staff_name'] ?? 'System'); ?>
                                </span>
                            </td>
                            <td><?php echo date('M d, Y', strtotime($row['order_date'])); ?></td>
                            
                            <?php if ($role === 'admin'): ?>
                                <td class="text-center">
                                    <a href="?delete_id=<?php echo $row['id']; ?>" 
                                       class="btn btn-sm btn-outline-danger border-0" 
                                       onclick="return confirm('Sigurado ka bang buburahin ang order na ito?')">
                                        <i class="bi bi-trash3"></i>
                                    </a>
                                </td>
                            <?php endif; ?>
                        </tr>
                    <?php endwhile; ?>
                </tbody>
            </table>
        </div>
    </main>
</div>

 
<div class="modal fade" id="addOrderModal" tabindex="-1" aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered">
        <div class="modal-content" style="border-radius: 15px;">
            <div class="modal-header border-0 pb-0">
                <h5 class="fw-bold">Create New Order</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <form method="POST">
                <div class="modal-body p-4">
                    <div class="mb-3">
                        <label class="form-label small fw-bold text-muted">SELECT CUSTOMER</label>
                        <select name="customer_id" class="form-select" required>
                            <option value="" disabled selected>Choose a customer...</option>
                            <?php foreach ($customers as $cust): ?>
                                <option value="<?php echo $cust['id']; ?>"><?php echo htmlspecialchars($cust['name']); ?></option>
                            <?php endforeach; ?>
                        </select>
                    </div>
                    <div class="mb-3">
                        <label class="form-label small fw-bold text-muted">PRODUCT NAME</label>
                        <input type="text" name="product_name" class="form-control" placeholder="e.g. pencil na di nasulat" required>
                    </div>
                    <div class="mb-3">
                        <label class="form-label small fw-bold text-muted">AMOUNT (₱)</label>
                        <input type="number" step="0.01" name="amount" class="form-control" placeholder="0.00" required>
                    </div>
                </div>
                <div class="modal-footer border-0 pt-0">
                    <button type="button" class="btn btn-light px-4" data-bs-dismiss="modal">Cancel</button>
                    <button type="submit" name="submit_order" class="btn btn-primary px-4">Submit Order</button>
                </div>
            </form>
        </div>
    </div>
</div>

<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>
