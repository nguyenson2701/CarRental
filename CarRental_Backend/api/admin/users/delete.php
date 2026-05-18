<?php
require_once '../../../config/auth.php';
requireAdminOrStaff('../../../../CarRental_Admin/login.php');
require_once '../../../config/database.php';

$id = (int)($_GET['id'] ?? 0);

if ($id > 0) {
    $stmt = $conn->prepare("DELETE FROM users WHERE UserID = ?");
    $stmt->bind_param("i", $id);
    $stmt->execute();
}

header("Location: ../../../../CarRental_Admin/users/list.php");
exit();