<?php
require_once '../../../config/auth.php';
requireAdminOrStaff('../../../../CarRental_Admin/login.php');
require_once '../../../config/database.php';

$id = (int)($_GET['id'] ?? 0);

if ($id > 0) {
    $stmt = $conn->prepare("DELETE FROM Brands WHERE BrandID = ?");
    $stmt->bind_param("i", $id);
    $stmt->execute();
}

header("Location: ../../../../CarRental_Admin/brands/list.php");
exit();