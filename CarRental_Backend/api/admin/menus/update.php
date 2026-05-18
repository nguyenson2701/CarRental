<?php
require_once '../../../config/auth.php';
requireAdminOrStaff('../../../../CarRental_Admin/login.php');
require_once '../../../config/database.php';

$id = (int)($_POST['MenuID'] ?? 0);
$menuName = trim($_POST['MenuName'] ?? '');
$url = trim($_POST['URL'] ?? '');
$parentID = ($_POST['ParentID'] === '' ? null : (int)$_POST['ParentID']);
$displayOrder = (int)($_POST['DisplayOrder'] ?? 0);
$isActive = (int)($_POST['IsActive'] ?? 1);

if ($id <= 0 || $menuName === '' || $url === '') {
    die('Dữ liệu không hợp lệ.');
}

$stmt = $conn->prepare("
    UPDATE menus
    SET MenuName = ?, URL = ?, ParentID = ?, DisplayOrder = ?, IsActive = ?
    WHERE MenuID = ?
");
$stmt->bind_param("ssiiii", $menuName, $url, $parentID, $displayOrder, $isActive, $id);

if ($stmt->execute()) {
    header("Location: ../../../../CarRental_Admin/menus/list.php?success=1");
    exit();
}

die("Lỗi cập nhật menu: " . $stmt->error);