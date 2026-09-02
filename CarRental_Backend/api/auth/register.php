<?php
require_once '../../config/auth.php';
require_once '../../config/database.php';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    requireCsrf('../../../CarRental_Frontend/register.php');

    $fullName = trim($_POST['full_name'] ?? '');
    $email = trim($_POST['email'] ?? '');
    $phone = trim($_POST['phone'] ?? '');
    $address = trim($_POST['address'] ?? '');
    $password = trim($_POST['password'] ?? '');
    $confirmPassword = trim($_POST['confirm_password'] ?? '');

    if ($fullName === '' || $email === '' || $phone === '' || $password === '' || $confirmPassword === '') {
        $_SESSION['register_error'] = "Vui lòng nhập đầy đủ thông tin bắt buộc!";
        header("Location: ../../../CarRental_Frontend/register.php");
        exit();
    }

    if (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
        $_SESSION['register_error'] = "Email không hợp lệ!";
        header("Location: ../../../CarRental_Frontend/register.php");
        exit();
    }

    if ($password !== $confirmPassword) {
        $_SESSION['register_error'] = "Mật khẩu nhập lại không khớp!";
        header("Location: ../../../CarRental_Frontend/register.php");
        exit();
    }

    if (strlen($password) < 6) {
        $_SESSION['register_error'] = "Mật khẩu phải có ít nhất 6 ký tự!";
        header("Location: ../../../CarRental_Frontend/register.php");
        exit();
    }

    // Kiểm tra email hoặc phone đã tồn tại chưa
    $checkSql = "SELECT UserID FROM users WHERE Email = ? OR Phone = ?";
    $checkStmt = $conn->prepare($checkSql);
    $checkStmt->bind_param("ss", $email, $phone);
    $checkStmt->execute();
    $checkResult = $checkStmt->get_result();

    if ($checkResult->num_rows > 0) {
        $_SESSION['register_error'] = "Email hoặc số điện thoại đã tồn tại!";
        header("Location: ../../../CarRental_Frontend/register.php");
        exit();
    }

    /*
      RoleID:
      1 = Admin
      2 = Staff
      3 = Customer
    */
    $roleID = 3;
    $status = 'Active';

    $passwordHash = password_hash($password, PASSWORD_DEFAULT);

    $sql = "INSERT INTO users
            (FullName, Email, Phone, PasswordHash, Address, RoleID, Status, CreatedAt, UpdatedAt)
            VALUES (?, ?, ?, ?, ?, ?, ?, NOW(), NOW())";

    $stmt = $conn->prepare($sql);
    $stmt->bind_param("sssssis", $fullName, $email, $phone, $passwordHash, $address, $roleID, $status);

    if ($stmt->execute()) {
        $_SESSION['register_success'] = "Đăng ký thành công! Bạn có thể đăng nhập ngay.";
        header("Location: ../../../CarRental_Frontend/login.php");
        exit();
    } else {
        $_SESSION['register_error'] = "Đăng ký thất bại: " . $stmt->error;
        header("Location: ../../../CarRental_Frontend/register.php");
        exit();
    }
}

header("Location: ../../../CarRental_Frontend/register.php");
exit();