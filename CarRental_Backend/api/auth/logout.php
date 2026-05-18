<?php
session_start();

// Xóa toàn bộ session
$_SESSION = [];

// Hủy session
session_destroy();

// Xóa cookie session (nếu có)
if (ini_get("session.use_cookies")) {
    $params = session_get_cookie_params();
    setcookie(
        session_name(),
        '',
        time() - 42000,
        $params["path"],
        $params["domain"],
        $params["secure"],
        $params["httponly"]
    );
}

// Điều hướng về trang đăng nhập
header("Location: ../../../CarRental_Frontend/login.php");
exit();