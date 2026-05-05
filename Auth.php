<?php 
class Auth { 
    public static function checkSession() {
         
        if (session_status() === PHP_SESSION_NONE) {
            session_start();
        }
 
        if (!isset($_SESSION['user_id'])) {
          
            header("Location: index.php");
            exit;
        }
    }
 
    public static function logout() {
        if (session_status() === PHP_SESSION_NONE) {
            session_start();
        }
         
        $_SESSION = array();
 
        if (ini_get("session.use_cookies")) {
            $params = session_get_cookie_params();
            setcookie(session_name(), '', time() - 42000,
                $params["path"], $params["domain"],
                $params["secure"], $params["httponly"]
            );
        }
 
        session_destroy();
 
        header("Location: index.php");
        exit;
    }
}
