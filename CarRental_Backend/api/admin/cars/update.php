<?php
require_once '../../../config/auth.php';
requireAdminOrStaff('../../../../CarRental_Admin/login.php');
require_once '../../../config/database.php';

$id            = (int)($_POST['CarID'] ?? 0);
$carName       = trim($_POST['CarName'] ?? '');
$brandID       = (int)($_POST['BrandID'] ?? 0);
$typeID        = (int)($_POST['TypeID'] ?? 0);
$year          = (int)($_POST['Year'] ?? 0);
$licensePlate  = trim($_POST['LicensePlate'] ?? '');
$color         = trim($_POST['Color'] ?? '');
$seats         = (int)($_POST['Seats'] ?? 0);
$transmission  = trim($_POST['Transmission'] ?? '');
$fuelType      = trim($_POST['FuelType'] ?? '');
$pricePerDay   = (float)($_POST['PricePerDay'] ?? 0);
$depositAmount = (float)($_POST['DepositAmount'] ?? 0);
$status        = trim($_POST['Status'] ?? 'Available');
$description   = trim($_POST['Description'] ?? '');
$location      = trim($_POST['Location'] ?? '');
$mileage       = (int)($_POST['Mileage'] ?? 0);

if ($id <= 0 || $carName === '' || $brandID <= 0 || $typeID <= 0) {
    die('Dữ liệu cập nhật không hợp lệ.');
}

/* Lấy thông tin xe cũ */
$stmtCar = $conn->prepare("SELECT CarID, FolderName, MainImage FROM Cars WHERE CarID = ?");
$stmtCar->bind_param("i", $id);
$stmtCar->execute();
$resultCar = $stmtCar->get_result();
$car = $resultCar->fetch_assoc();

if (!$car) {
    die('Không tìm thấy xe.');
}

/* Cập nhật thông tin xe */
$sql = "UPDATE Cars SET
        CarName = ?, BrandID = ?, TypeID = ?, Year = ?, LicensePlate = ?, Color = ?, Seats = ?, Transmission = ?, FuelType = ?, PricePerDay = ?, DepositAmount = ?, Status = ?, Description = ?, Location = ?, Mileage = ?, UpdatedAt = NOW()
        WHERE CarID = ?";

$stmt = $conn->prepare($sql);
$stmt->bind_param(
    "siiississddsssii",
    $carName,
    $brandID,
    $typeID,
    $year,
    $licensePlate,
    $color,
    $seats,
    $transmission,
    $fuelType,
    $pricePerDay,
    $depositAmount,
    $status,
    $description,
    $location,
    $mileage,
    $id
);

if (!$stmt->execute()) {
    die("Lỗi cập nhật xe: " . $stmt->error);
}

/* Chuẩn bị folder ảnh */
$folderName = trim($car['FolderName'] ?? '');
if ($folderName === '') {
    $folderName = 'car_' . $id;
    $stmtFolder = $conn->prepare("UPDATE Cars SET FolderName = ? WHERE CarID = ?");
    $stmtFolder->bind_param("si", $folderName, $id);
    $stmtFolder->execute();
}

$baseUploadDir = '../../../../CarRental_Frontend/assets/img/cars/';
$carFolderPath = $baseUploadDir . $folderName;

if (!is_dir($carFolderPath)) {
    mkdir($carFolderPath, 0777, true);
}

/* Kiểm tra xe đã có ảnh chính chưa */
$hasMain = !empty($car['MainImage']);
$allowedExt = ['jpg', 'jpeg', 'png', 'gif', 'webp'];

/* Nếu có ảnh mới thì thêm vào */
if (!empty($_FILES['Images']['name'][0])) {
    foreach ($_FILES['Images']['name'] as $index => $originalName) {
        if ($_FILES['Images']['error'][$index] !== 0) {
            continue;
        }

        $ext = strtolower(pathinfo($originalName, PATHINFO_EXTENSION));
        if (!in_array($ext, $allowedExt, true)) {
            continue;
        }

        $safeName = preg_replace('/[^a-zA-Z0-9_\-\.]/', '_', basename($originalName));
        $newFileName = time() . '_' . ($index + 1) . '_' . $safeName;
        $targetPath = $carFolderPath . '/' . $newFileName;

        if (move_uploaded_file($_FILES['Images']['tmp_name'][$index], $targetPath)) {
            $imageUrl = $folderName . '/' . $newFileName;
            $isMain = $hasMain ? 0 : 1;
            $imageType = 'gallery';

            $stmtImg = $conn->prepare("
                INSERT INTO CarImages (CarID, ImageURL, IsMain, UploadedAt, ImageType)
                VALUES (?, ?, ?, NOW(), ?)
            ");
            $stmtImg->bind_param("isis", $id, $imageUrl, $isMain, $imageType);
            $stmtImg->execute();

            if (!$hasMain) {
                $stmtMain = $conn->prepare("UPDATE Cars SET MainImage = ? WHERE CarID = ?");
                $stmtMain->bind_param("si", $imageUrl, $id);
                $stmtMain->execute();
                $hasMain = true;
            }
        }
    }
}

header("Location: ../../../../CarRental_Admin/cars/list.php");
exit();
?>