<?php
session_start();
require_once '../../config/database.php';

if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    $email = trim($_POST['email'] ?? '');
    $password = trim($_POST['password'] ?? '');

    $sql = "SELECT * FROM users WHERE Email = ?";
    $stmt = $conn->prepare($sql);
    $stmt->bind_param("s", $email);
    $stmt->execute();
    $result = $stmt->get_result();

    if ($result->num_rows > 0) {
        $user = $result->fetch_assoc();

        // debug tạm nếu cần
        // echo "<pre>";
        // print_r($user);
        // echo "</pre>";
        // exit();

        // bạn đang dùng mật khẩu thường
        if ($password === $user['PasswordHash']) {
            $roleID = (int)($user['RoleID'] ?? 0);

            $_SESSION['user_id'] = $user['UserID'];
            $_SESSION['role_id'] = $roleID;
            $_SESSION['name'] = $user['FullName'];

            if ($roleID === 1 || $roleID === 2) {
                header("Location: ../../../CarRental_Admin/index.php");
                exit();
            } elseif ($roleID === 3) {
                header("Location: ../../../CarRental_Frontend/index.php");
                exit();
            } else {
                $_SESSION['login_error'] = "Tài khoản chưa được gán quyền hợp lệ!";
                header("Location: ../../../CarRental_Admin/login.php");
                exit();
            }
        } else {
            $_SESSION['login_error'] = "Sai mật khẩu!";
            header("Location: ../../../CarRental_Admin/login.php");
            exit();
        }
    } else {
        $_SESSION['login_error'] = "Email không tồn tại!";
        header("Location: ../../../CarRental_Admin/login.php");
        exit();
    }
}
?>