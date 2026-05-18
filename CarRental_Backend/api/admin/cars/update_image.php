<?php
require_once '../../../config/auth.php';
requireAdminOrStaff('../../../../CarRental_Admin/login.php');
require_once '../../../config/database.php';

$imageID = (int)($_POST['ImageID'] ?? 0);
$carID = (int)($_POST['CarID'] ?? 0);
$imageType = trim($_POST['ImageType'] ?? 'gallery');

$allowedTypes = ['gallery', 'front', 'back', 'interior'];
if ($imageID <= 0 || $carID <= 0 || !in_array($imageType, $allowedTypes, true)) {
    die('Dữ liệu ảnh không hợp lệ.');
}

$stmt = $conn->prepare("
    UPDATE CarImages
    SET ImageType = ?
    WHERE ImageID = ? AND CarID = ?
");
$stmt->bind_param("sii", $imageType, $imageID, $carID);

if (!$stmt->execute()) {
    die('Không thể cập nhật loại ảnh: ' . $stmt->error);
}

header("Location: ../../../../CarRental_Admin/cars/edit.php?id=" . $carID);
exit();
?>
