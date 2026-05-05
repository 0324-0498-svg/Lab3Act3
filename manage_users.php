<?php
session_start();
if (!isset($_SESSION['user_id']) || $_SESSION['role'] !== 'admin') { 
    header("Location: dashboard.php"); 
    exit; 
}

require_once 'classes/Database.php';
$database = new Database();
$db = $database->getConnection();

if (isset($_GET['delete'])) {
    $id = $_GET['delete'];
    
    if ($id != $_SESSION['user_id']) {
        $stmt = $db->prepare("DELETE FROM users WHERE id = :id");
        $stmt->execute(['id' => $id]);
        header("Location: manage_users.php?msg=deleted");
    } else {
        header("Location: manage_users.php?msg=error_self");
    }
}

$stmt = $db->query("SELECT * FROM users WHERE id != " . $_SESSION['user_id'] . " ORDER BY full_name ASC");
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>Control Panel - ORDERFLOW</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.3/font/bootstrap-icons.min.css">
    <style>
        :root { --sidebar-bg: #0d1b2a; }
        body { background-color: #f4f7f6; font-family: 'Segoe UI', sans-serif; }
        .wrapper { display: flex; min-height: 100vh; }
        .sidebar { width: 260px; background-color: var(--sidebar-bg); color: white; position: fixed; height: 100%; }
        .main-content { flex: 1; margin-left: 260px; padding: 40px; }
        .user-card { background: white; padding: 25px; border-radius: 15px; box-shadow: 0 4px 15px rgba(0,0,0,0.05); }
    </style>
</head>
<body>
<div class="wrapper">
    <nav class="sidebar p-3">
        <h4 class="text-center fw-bold mb-4">ORDERFLOW</h4>
        <div class="nav flex-column">
            <a href="dashboard.php" class="nav-link text-white"><i class="bi bi-speedometer2 me-2"></i> Dashboard</a>
            <a href="orders.php" class="nav-link text-white"><i class="bi bi-cart-check me-2"></i> Orders</a>
            <a href="manage_users.php" class="nav-link text-white active bg-primary rounded"><i class="bi bi-shield-lock-fill me-2"></i> Control Panel</a>
            <hr>
            <a href="logout.php" class="nav-link text-danger"><i class="bi bi-box-arrow-right me-2"></i> Logout</a>
        </div>
    </nav>

    <main class="main-content">
        <h2 class="fw-bold mb-4">User Access Management</h2>
        
        <?php if (isset($_GET['msg']) && $_GET['msg'] === 'deleted'): ?>
            <div class="alert alert-warning alert-dismissible fade show" role="alert">
                User and all associated orders have been permanently deleted.
                <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
            </div>
        <?php endif; ?>

        <div class="user-card">
            <table class="table table-hover align-middle">
                <thead class="table-dark">
                    <tr>
                        <th>Staff Name</th>
                        <th>Role</th>
                        <th class="text-end">Actions</th>
                    </tr>
                </thead>
                <tbody>
                    <?php while ($user = $stmt->fetch(PDO::FETCH_ASSOC)): ?>
                        <tr>
                            <td class="fw-bold"><?php echo htmlspecialchars($user['full_name']); ?></td>
                            <td><span class="badge bg-light text-dark border text-uppercase small"><?php echo $user['role']; ?></span></td>
                            <td class="text-end">
                                <a href="?delete=<?php echo $user['id']; ?>" 
                                   class="btn btn-danger btn-sm" 
                                   onclick="return confirm('WARNING: Deleting this user will also delete ALL their orders and transaction history. Continue?')">
                                    <i class="bi bi-trash3 me-1"></i> Delete Permanent
                                </a>
                            </td>
                        </tr>
                    <?php endwhile; ?>
                </tbody>
            </table>
        </div>
    </main>
</div>
<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>
