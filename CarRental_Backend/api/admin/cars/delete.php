<?php
require_once '../../../config/auth.php';
requireAdminOrStaff('../../../../CarRental_Admin/login.php');
requireCsrf('../../../../CarRental_Admin/cars/list.php');
require_once '../../../config/database.php';

function deleteFolderRecursive($folderPath) {
    if (!is_dir($folderPath)) {
        return;
    }

    $items = array_diff(scandir($folderPath), ['.', '..']);

    foreach ($items as $item) {
        $fullPath = $folderPath . DIRECTORY_SEPARATOR . $item;

        if (is_dir($fullPath)) {
            deleteFolderRecursive($fullPath);
        } else {
            unlink($fullPath);
        }
    }

    rmdir($folderPath);
}

$id = (int)($_GET['id'] ?? 0);

if ($id > 0) {
    /* Lấy folder ảnh của xe */
    $stmtCar = $conn->prepare("SELECT FolderName FROM Cars WHERE CarID = ?");
    $stmtCar->bind_param("i", $id);
    $stmtCar->execute();
    $resultCar = $stmtCar->get_result();
    $car = $resultCar->fetch_assoc();

    if ($car && !empty($car['FolderName'])) {
        $folderPath = '../../../../CarRental_Frontend/assets/img/cars/' . $car['FolderName'];
        deleteFolderRecursive($folderPath);
    }

    /* Xóa xe */
    $stmt = $conn->prepare("DELETE FROM Cars WHERE CarID = ?");
    $stmt->bind_param("i", $id);
    $stmt->execute();
}

header("Location: ../../../../CarRental_Admin/cars/list.php");
exit();
?>