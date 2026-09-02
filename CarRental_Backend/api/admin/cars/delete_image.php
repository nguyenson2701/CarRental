<?php
require_once '../../../config/auth.php';
requireAdminOrStaff('../../../../CarRental_Admin/login.php');
requireCsrf('../../../../CarRental_Admin/cars/list.php');
require_once '../../../config/database.php';

$imageID = (int)($_GET['id'] ?? 0);
$carID = (int)($_GET['car_id'] ?? 0);

if ($imageID <= 0 || $carID <= 0) {
    die('Dữ liệu ảnh không hợp lệ.');
}

$stmtImg = $conn->prepare("
    SELECT ImageURL, IsMain
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

$baseDir = realpath(__DIR__ . '/../../../../CarRental_Frontend/assets/img/cars');
$imagePath = realpath($baseDir . DIRECTORY_SEPARATOR . str_replace(['/', '\\'], DIRECTORY_SEPARATOR, $image['ImageURL']));

$conn->begin_transaction();

try {
    $stmtDelete = $conn->prepare("DELETE FROM CarImages WHERE ImageID = ? AND CarID = ?");
    $stmtDelete->bind_param("ii", $imageID, $carID);
    $stmtDelete->execute();

    if ((int)$image['IsMain'] === 1) {
        $stmtNext = $conn->prepare("
            SELECT ImageID, ImageURL
            FROM CarImages
            WHERE CarID = ?
            ORDER BY ImageID ASC
            LIMIT 1
        ");
        $stmtNext->bind_param("i", $carID);
        $stmtNext->execute();
        $nextImage = $stmtNext->get_result()->fetch_assoc();

        if ($nextImage) {
            $stmtSetNext = $conn->prepare("UPDATE CarImages SET IsMain = 1 WHERE ImageID = ? AND CarID = ?");
            $stmtSetNext->bind_param("ii", $nextImage['ImageID'], $carID);
            $stmtSetNext->execute();

            $stmtCar = $conn->prepare("UPDATE Cars SET MainImage = ?, UpdatedAt = NOW() WHERE CarID = ?");
            $stmtCar->bind_param("si", $nextImage['ImageURL'], $carID);
            $stmtCar->execute();
        } else {
            $empty = '';
            $stmtCar = $conn->prepare("UPDATE Cars SET MainImage = ?, UpdatedAt = NOW() WHERE CarID = ?");
            $stmtCar->bind_param("si", $empty, $carID);
            $stmtCar->execute();
        }
    }

    $conn->commit();
} catch (Throwable $e) {
    $conn->rollback();
    die('Không thể xóa ảnh: ' . $e->getMessage());
}

if ($baseDir && $imagePath && strpos($imagePath, $baseDir . DIRECTORY_SEPARATOR) === 0 && is_file($imagePath)) {
    unlink($imagePath);
}

header("Location: ../../../../CarRental_Admin/cars/edit.php?id=" . $carID);
exit();
?>
