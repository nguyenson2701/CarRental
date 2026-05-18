<?php
require_once '../../../config/auth.php';
requireAdminOrStaff('../../../../CarRental_Admin/login.php');
require_once '../../../config/database.php';

$imageID = (int)($_POST['ImageID'] ?? 0);
$carID = (int)($_POST['CarID'] ?? 0);

if ($imageID <= 0 || $carID <= 0) {
    die('Dữ liệu ảnh không hợp lệ.');
}

$stmtImg = $conn->prepare("
    SELECT ImageURL
    FROM CarImages
    WHERE ImageID = ? AND CarID = ?
    LIMIT 1
");
$stmtImg->bind_param("ii", $imageID, $carID);
$stmtImg->execute();
$image = $stmtImg->get_result()->fetch_assoc();

if (!$image) {
    die('Không tìm thấy ảnh.');
}

$conn->begin_transaction();

try {
    $stmtReset = $conn->prepare("UPDATE CarImages SET IsMain = 0 WHERE CarID = ?");
    $stmtReset->bind_param("i", $carID);
    $stmtReset->execute();

    $stmtMain = $conn->prepare("UPDATE CarImages SET IsMain = 1 WHERE ImageID = ? AND CarID = ?");
    $stmtMain->bind_param("ii", $imageID, $carID);
    $stmtMain->execute();

    $stmtCar = $conn->prepare("UPDATE Cars SET MainImage = ?, UpdatedAt = NOW() WHERE CarID = ?");
    $stmtCar->bind_param("si", $image['ImageURL'], $carID);
    $stmtCar->execute();

    $conn->commit();
} catch (Throwable $e) {
    $conn->rollback();
    die('Không thể đặt ảnh chính: ' . $e->getMessage());
}

header("Location: ../../../../CarRental_Admin/cars/edit.php?id=" . $carID);
exit();
?>
