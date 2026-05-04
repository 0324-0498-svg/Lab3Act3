<?php
session_start();
require_once 'classes/Database.php';

$database = new Database();
$db = $database->getConnection();

$error = "";

if ($_SERVER['REQUEST_METHOD'] == 'POST' && isset($_POST['login'])) {
    $username = $_POST['username'];
    $password = $_POST['password'];

    $query = "SELECT id, username, password, full_name FROM users WHERE username = :username";
    $stmt = $db->prepare($query);
    $stmt->bindParam(':username', $username);
    $stmt->execute();

    $user = $stmt->fetch(PDO::FETCH_ASSOC);

    if ($user && password_verify($password, $user['password'])) {
        $_SESSION['user_id'] = $user['id'];
        $_SESSION['full_name'] = $user['full_name'];
        $_SESSION['username'] = $user['username'];

        header("Location: dashboard.php");
        exit;
    } else {
        $error = "Invalid username or password.";
    }
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Login - Smart Order Management System</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <style>
        body { background-color: #0d1b2a; font-family: 'Segoe UI', sans-serif; height: 100vh; display: flex; align-items: center; justify-content: center; margin: 0; }
        .login-card { background: white; padding: 40px; border-radius: 20px; box-shadow: 0 10px 30px rgba(0,0,0,0.5); width: 100%; max-width: 400px; }
        .login-header { text-align: center; margin-bottom: 30px; }
        .login-header h1 { color: #0d1b2a; font-weight: 800; letter-spacing: -1px; margin-bottom: 5px; }
        .btn-login { background-color: #3a86ff; border: none; padding: 12px; font-weight: 600; transition: 0.3s; }
        .btn-login:hover { background-color: #2563eb; transform: translateY(-2px); }
        .form-control { border-radius: 10px; padding: 12px; border: 1px solid #dee2e6; }
    </style>
</head>
<body>

<div class="login-card">
    <div class="login-header">
        <h1>SHOPTRAK</h1>
        <p class="text-muted">Smart Order Management System</p>
    </div>

    <?php if ($error): ?>
        <div class="alert alert-danger small py-2 text-center"><?php echo $error; ?></div>
    <?php endif; ?>

    <form method="POST" action="">
        <div class="mb-3">
            <label class="form-label small fw-bold text-muted text-uppercase">Username</label>
            <input type="text" name="username" class="form-control" placeholder="Enter your username" required>
        </div>
        <div class="mb-4">
            <label class="form-label small fw-bold text-muted text-uppercase">Password</label>
            <input type="password" name="password" class="form-control" placeholder="••••••••" required>
        </div>
        <button type="submit" name="login" class="btn btn-login btn-primary w-100">Sign In</button>
    </form>
    
    <div class="mt-4 text-center">
        <p class="small text-muted mb-0">Group 7 Project | April 2026</p>
    </div>
</div>

</body>
</html>
