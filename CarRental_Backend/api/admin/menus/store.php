<?php
require_once '../../../config/auth.php';
requireAdminOrStaff('../../../../CarRental_Admin/login.php');
require_once '../../../config/database.php';

$menuName = trim($_POST['MenuName'] ?? '');
$url = trim($_POST['URL'] ?? '');
$parentID = ($_POST['ParentID'] === '' ? null : (int)$_POST['ParentID']);
$displayOrder = (int)($_POST['DisplayOrder'] ?? 0);
$isActive = (int)($_POST['IsActive'] ?? 1);

if ($menuName === '' || $url === '') {
    die('Thiếu tên menu hoặc URL.');
}

$stmt = $conn->prepare("
    INSERT INTO menus (MenuName, URL, ParentID, DisplayOrder, IsActive)
    VALUES (?, ?, ?, ?, ?)
");
$stmt->bind_param("ssiii", $menuName, $url, $parentID, $displayOrder, $isActive);

if ($stmt->execute()) {
    header("Location: ../../../../CarRental_Admin/menus/list.php?success=1");
    exit();
}

die("Lỗi thêm menu: " . $stmt->error);