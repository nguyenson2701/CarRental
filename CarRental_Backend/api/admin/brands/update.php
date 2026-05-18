<?php
require_once '../../../config/auth.php';
requireAdminOrStaff('../../../../CarRental_Admin/login.php');
require_once '../../../config/database.php';

$id = (int)($_POST['BrandID'] ?? 0);
$brandName = trim($_POST['BrandName'] ?? '');

$sql = "UPDATE Brands SET BrandName = ? WHERE BrandID = ?";
$stmt = $conn->prepare($sql);
$stmt->bind_param("si", $brandName, $id);

if ($stmt->execute()) {
    header("Location: ../../../../CarRental_Admin/brands/list.php");
    exit();
} else {
    echo "Lỗi cập nhật hãng xe: " . $stmt->error;
}