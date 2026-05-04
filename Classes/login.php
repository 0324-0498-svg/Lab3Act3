<?php
session_start();
require_once 'classes/Database.php';

if ($_POST) {
    $database = new Database();
    $db = $database->getConnection();
    
    $user = $_POST['username'];
    $pass = $_POST['password'];

    $query = "SELECT * FROM users WHERE username = ?";
    $stmt = $db->prepare($query);
    $stmt->execute([$user]);
    $row = $stmt->fetch(PDO::FETCH_ASSOC);

    if ($row && password_verify($pass, $row['password'])) {
        $_SESSION['user_id'] = $row['id'];
        $_SESSION['user_name'] = $row['full_name'];
        header("Location: dashboard.php");
    } else {
        $error = "Invalid Credentials";
    }
}
?>