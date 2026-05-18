<?php
error_reporting(E_ALL);
ini_set('display_errors', 1);

require_once '../../../config/auth.php';
requireAdminOrStaff('../../../../CarRental_Admin/login.php');
require_once '../../../config/database.php';

$brandName = trim($_POST['BrandName'] ?? '');

if ($brandName === '') {
    die('BrandName đang rỗng');
}

$sql = "INSERT INTO Brands (BrandName) VALUES (?)";
$stmt = $conn->prepare($sql);

if (!$stmt) {
    die('Prepare lỗi: ' . $conn->error);
}

$stmt->bind_param("s", $brandName);

if ($stmt->execute()) {
    header("Location: ../../../../CarRental_Admin/brands/list.php");
    exit();
} else {
    die("Lỗi thêm hãng xe: " . $stmt->error);
}