<?php
session_start();
if (!isset($_SESSION['user_id'])) { header("Location: index.php"); exit; }

require_once 'classes/Database.php';
$database = new Database();
$db = $database->getConnection();

if (isset($_GET['delete'])) {
    $id = $_GET['delete'];
    $delete_query = "DELETE FROM customers WHERE id = :id";
    $delete_stmt = $db->prepare($delete_query);
    $delete_stmt->bindParam(':id', $id);
    
    if ($delete_stmt->execute()) {
        echo "<script>alert('Customer deleted!'); window.location.href='customers.php';</script>";
    }
}

if ($_SERVER['REQUEST_METHOD'] == 'POST' && isset($_POST['add_customer'])) {
    $name = $_POST['name'];
    $email = $_POST['email'];
    $insert_query = "INSERT INTO customers (name, email) VALUES (:name, :email)";
    $insert_stmt = $db->prepare($insert_query);
    $insert_stmt->execute([':name' => $name, ':email' => $email]);
    header("Location: customers.php");
}

if ($_SERVER['REQUEST_METHOD'] == 'POST' && isset($_POST['edit_customer'])) {
    $id = $_POST['customer_id'];
    $name = $_POST['name'];
    $email = $_POST['email'];
    $update_query = "UPDATE customers SET name = :name, email = :email WHERE id = :id";
    $update_stmt = $db->prepare($update_query);
    $update_stmt->execute([':name' => $name, ':email' => $email, ':id' => $id]);
    header("Location: customers.php");
}

$query = "SELECT * FROM customers ORDER BY id DESC";
$stmt = $db->prepare($query);
$stmt->execute();
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>Customers - Smart Order System</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
    <style>
        :root { --sidebar-bg: #0d1b2a; --sidebar-hover: #1b263b; --accent-blue: #3a86ff; }
        body { background-color: #f4f7f6; font-family: 'Segoe UI', sans-serif; }
        .wrapper { display: flex; min-height: 100vh; }
        .sidebar { width: 260px; background-color: var(--sidebar-bg); color: white; position: fixed; height: 100%; }
        .nav-link { padding: 15px 25px; color: #adb5bd !important; border-left: 4px solid transparent; text-decoration: none; display: block; }
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
            <a href="customers.php" class="nav-link active">Customers</a>
            <a href="orders.php" class="nav-link">Orders</a>
            <a href="reports.php" class="nav-link">Reports</a>
            <a href="developers.php" class="nav-link">Developers</a>
            <a href="logout.php" class="nav-link text-danger mt-5">Logout</a>
        </div>
    </nav>

    <main class="main-content">
        <div class="d-flex justify-content-between mb-4">
            <h2 class="fw-bold">Customer Management</h2>
            <button class="btn btn-primary" data-bs-toggle="modal" data-bs-target="#addCustomerModal">+ Add Customer</button>
        </div>

        <div class="data-card">
            <table class="table table-hover">
                <thead class="table-dark">
                    <tr><th>ID</th><th>Name</th><th>Email</th><th>Actions</th></tr>
                </thead>
                <tbody>
                    <?php while ($row = $stmt->fetch(PDO::FETCH_ASSOC)): ?>
                        <tr>
                            <td><?php echo $row['id']; ?></td>
                            <td><?php echo $row['name']; ?></td>
                            <td><?php echo $row['email']; ?></td>
                            <td>
                                <!-- Edit Button with Data Attributes -->
                                <button class="btn btn-sm btn-warning edit-btn" 
                                        data-id="<?php echo $row['id']; ?>" 
                                        data-name="<?php echo $row['name']; ?>" 
                                        data-email="<?php echo $row['email']; ?>"
                                        data-bs-toggle="modal" 
                                        data-bs-target="#editCustomerModal">Edit</button>
                                
                                <!-- Delete Button -->
                                <a href="customers.php?delete=<?php echo $row['id']; ?>" 
                                   class="btn btn-sm btn-danger" 
                                   onclick="return confirm('Sigurado ka ba na gusto mong burahin ito?')">Delete</a>
                            </td>
                        </tr>
                    <?php endwhile; ?>
                </tbody>
            </table>
        </div>
    </main>
</div>

<div class="modal fade" id="addCustomerModal" tabindex="-1" aria-hidden="true">
  <div class="modal-dialog">
    <div class="modal-content">
      <form method="POST">
        <div class="modal-header"><h5>Add New Customer</h5><button type="button" class="btn-close" data-bs-dismiss="modal"></button></div>
        <div class="modal-body">
          <div class="mb-3"><label>Full Name</label><input type="text" name="name" class="form-control" required></div>
          <div class="mb-3"><label>Email Address</label><input type="email" name="email" class="form-control" required></div>
        </div>
        <div class="modal-footer"><button type="submit" name="add_customer" class="btn btn-primary">Save</button></div>
      </form>
    </div>
  </div>
</div>

<div class="modal fade" id="editCustomerModal" tabindex="-1" aria-hidden="true">
  <div class="modal-dialog">
    <div class="modal-content">
      <form method="POST">
        <div class="modal-header"><h5>Edit Customer</h5><button type="button" class="btn-close" data-bs-dismiss="modal"></button></div>
        <div class="modal-body">
          <input type="hidden" name="customer_id" id="edit_id">
          <div class="mb-3"><label>Full Name</label><input type="text" name="name" id="edit_name" class="form-control" required></div>
          <div class="mb-3"><label>Email Address</label><input type="email" name="email" id="edit_email" class="form-control" required></div>
        </div>
        <div class="modal-footer"><button type="submit" name="edit_customer" class="btn btn-warning">Update Changes</button></div>
      </form>
    </div>
  </div>
</div>

<script>

const editButtons = document.querySelectorAll('.edit-btn');
editButtons.forEach(btn => {
    btn.addEventListener('click', () => {
        document.getElementById('edit_id').value = btn.getAttribute('data-id');
        document.getElementById('edit_name').value = btn.getAttribute('data-name');
        document.getElementById('edit_email').value = btn.getAttribute('data-email');
    });
});
</script>

</body>
</html>
