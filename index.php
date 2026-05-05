<?php
session_start();
require_once 'classes/Database.php';

$database = new Database();
$db = $database->getConnection();

$error = "";

if ($_SERVER['REQUEST_METHOD'] == 'POST' && isset($_POST['login'])) {
    $username = $_POST['username'];
    $password = $_POST['password'];

    $query = "SELECT id, username, password, full_name, role FROM users WHERE username = :username";
    $stmt = $db->prepare($query);
    $stmt->bindParam(':username', $username);
    $stmt->execute();

    $user = $stmt->fetch(PDO::FETCH_ASSOC);

    if ($user && ($password === $user['password'] || password_verify($password, $user['password']))) {
        
        $_SESSION['user_id'] = $user['id'];
        $_SESSION['full_name'] = $user['full_name'];
        $_SESSION['username'] = $user['username'];
        $_SESSION['role'] = $user['role']; 
        header("Location: dashboard.php");
        exit;
    } else {
        $error = "Invalid username or password. Please check your database.";
    }
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Login - ORDERFLOW</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.1/font/bootstrap-icons.css">
    <style>
        body { 
            background: linear-gradient(135deg, #0d1b2a 0%, #1b263b 100%); 
            font-family: 'Segoe UI', sans-serif; 
            height: 100vh; 
            display: flex; 
            align-items: center; 
            justify-content: center; 
            margin: 0; 
        }
        .login-card { 
            background: white; 
            padding: 45px; 
            border-radius: 24px; 
            width: 100%; 
            max-width: 420px; 
            box-shadow: 0 10px 30px rgba(0,0,0,0.5);
            transition: all 0.4s ease-in-out;
        }
        .login-card:hover {
            transform: translateY(-5px);
            box-shadow: 0 0 25px rgba(58, 134, 255, 0.6);
        }
        .brand-wrapper {
            display: flex;
            align-items: center;
            justify-content: center;
            gap: 12px;
            margin-bottom: 8px;
        }
        .brand-logo {
            background-color: #3a86ff;
            color: white;
            width: 45px;
            height: 45px;
            border-radius: 12px;
            display: flex;
            align-items: center;
            justify-content: center;
            font-size: 1.5rem;
        }
        .brand-title { 
            color: #0d1b2a; 
            font-weight: 800; 
            letter-spacing: 2px; 
            margin: 0;
            font-size: 1.8rem;
        }
        .form-control {
            padding: 12px;
            border-radius: 10px;
            background-color: #f8f9fa;
        }
        .btn-login { 
            background-color: #3a86ff; 
            border: none; 
            padding: 14px; 
            font-weight: 600; 
            border-radius: 12px;
            transition: 0.3s; 
        }
        .btn-login:hover { background-color: #2563eb; }
    </style>
</head>
<body>

<div class="login-card">
    <div class="text-center mb-4">
        <div class="brand-wrapper">
            <div class="brand-logo"><i class="bi bi-lightning-charge-fill"></i></div>
            <h1 class="brand-title">ORDERFLOW</h1>
        </div>
        <p class="text-muted small text-uppercase fw-bold">Smart Order Management System</p>
    </div>

    <?php if ($error): ?>
        <div class="alert alert-danger border-0 small py-2 text-center"><?php echo $error; ?></div>
    <?php endif; ?>

    <form method="POST" action="">
        <div class="mb-3">
            <label class="form-label small fw-bold text-muted text-uppercase">Username</label>
            <input type="text" name="username" class="form-control" placeholder="Enter username" required>
        </div>
        <div class="mb-4">
            <label class="form-label small fw-bold text-muted text-uppercase">Password</label>
            <input type="password" name="password" class="form-control" placeholder="Enter password" required>
        </div>
        <button type="submit" name="login" class="btn btn-primary btn-login w-100 shadow-sm">Sign In</button>
    </form>
    
    <div class="mt-4 text-center">
        <p class="text-muted small mb-0">&copy; 2026 ORDERFLOW System</p>
    </div>
</div>

</body>
</html>
