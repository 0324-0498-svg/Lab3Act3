<?php
session_start();
if (!isset($_SESSION['user_id'])) { header("Location: index.php"); exit; }

require_once 'classes/Database.php';
$database = new Database();
$db = $database->getConnection();

if ($_SERVER['REQUEST_METHOD'] == 'POST' && isset($_POST['add_order'])) {
    $customer_id = $_POST['customer_id'];
    $product_name = $_POST['product_name'];
    $amount = $_POST['amount'];
    $user_id = $_SESSION['user_id']; // DYNAMIC: Kukunin ang ID ng sino ang naka-login

    $query = "INSERT INTO orders (customer_id, product_name, amount, user_id, order_date) 
              VALUES (:customer_id, :product_name, :amount, :user_id, NOW())";
    $stmt = $db->prepare($query);
    $stmt->execute([
        ':customer_id' => $customer_id,
        ':product_name' => $product_name,
        ':amount' => $amount,
        ':user_id' => $user_id
    ]);
    header("Location: orders.php");
}

$orders_query = "SELECT o.*, c.name as customer_name, u.full_name as staff_name 
                 FROM orders o 
                 JOIN customers c ON o.customer_id = c.id 
                 JOIN users u ON o.user_id = u.id 
                 ORDER BY o.id DESC";
$orders_stmt = $db->prepare($orders_query);
$orders_stmt->execute();

$customers_stmt = $db->prepare("SELECT id, name FROM customers");
$customers_stmt->execute();
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>Orders - Smart Order System</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
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
            <a href="dashboard.php" class="nav-link">Dashboard</a>
            <a href="customers.php" class="nav-link">Customers</a>
            <a href="orders.php" class="nav-link active">Orders</a>
            <a href="reports.php" class="nav-link">Reports</a>
            <a href="developers.php" class="nav-link">Developers</a>
            <hr class="mx-3 text-secondary">
            <a href="logout.php" class="nav-link text-danger">Logout</a>
        </div>
    </nav>

    <main class="main-content">
        <div class="d-flex justify-content-between mb-4">
            <h2 class="fw-bold">Order Transactions</h2>
            <button class="btn btn-primary" data-bs-toggle="modal" data-bs-target="#addOrderModal">+ New Order</button>
        </div>

        <div class="data-card">
            <table class="table table-hover">
                <thead class="table-dark">
                    <tr>
                        <th>ID</th>
                        <th>Customer</th>
                        <th>Product</th>
                        <th>Amount</th>
                        <th>Staff</th>
                        <th>Date</th>
                    </tr>
                </thead>
                <tbody>
                    <?php while ($row = $orders_stmt->fetch(PDO::FETCH_ASSOC)): ?>
                        <tr>
                            <td>#<?php echo $row['id']; ?></td>
                            <td><?php echo htmlspecialchars($row['customer_name']); ?></td>
                            <td><?php echo htmlspecialchars($row['product_name']); ?></td>
                            <td class="text-success fw-bold">₱<?php echo number_format($row['amount'], 2); ?></td>
                            <td><?php echo htmlspecialchars($row['staff_name']); ?></td>
                            <td><?php echo date('M d, Y', strtotime($row['order_date'])); ?></td>
                        </tr>
                    <?php endwhile; ?>
                </tbody>
            </table>
        </div>
    </main>
</div>

<div class="modal fade" id="addOrderModal" tabindex="-1" aria-hidden="true">
  <div class="modal-dialog">
    <div class="modal-content">
      <form method="POST">
        <div class="modal-header"><h5>Create New Order</h5><button type="button" class="btn-close" data-bs-dismiss="modal"></button></div>
        <div class="modal-body">
          <div class="mb-3">
            <label>Select Customer</label>
            <select name="customer_id" class="form-select" required>
                <option value="">-- Choose --</option>
                <?php while($c = $customers_stmt->fetch(PDO::FETCH_ASSOC)): ?>
                    <option value="<?php echo $c['id']; ?>"><?php echo htmlspecialchars($c['name']); ?></option>
                <?php endwhile; ?>
            </select>
          </div>
          <div class="mb-3"><label>Product Name</label><input type="text" name="product_name" class="form-control" required></div>
          <div class="mb-3"><label>Amount (₱)</label><input type="number" step="0.01" name="amount" class="form-control" required></div>
        </div>
        <div class="modal-footer"><button type="submit" name="add_order" class="btn btn-primary w-100">Confirm Transaction</button></div>
      </form>
    </div>
  </div>
</div>
</body>
</html>
