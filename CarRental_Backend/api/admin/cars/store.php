<?php
require_once '../../../config/auth.php';
requireAdminOrStaff('../../../../CarRental_Admin/login.php');
require_once '../../../config/database.php';
require_once '../../../helpers/upload.php';

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

if ($carName === '' || $brandID <= 0 || $typeID <= 0) {
    die('Dữ liệu xe không hợp lệ.');
}

$sql = "INSERT INTO Cars
        (CarName, BrandID, TypeID, Year, LicensePlate, Color, Seats, Transmission, FuelType, PricePerDay, DepositAmount, Status, Description, MainImage, FolderName, Location, Mileage, CreatedAt, UpdatedAt)
        VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, '', '', ?, ?, NOW(), NOW())";

$stmt = $conn->prepare($sql);
$stmt->bind_param(
    "siiississddsssi",
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
    $mileage
);

if (!$stmt->execute()) {
    die("Lỗi thêm xe: " . $stmt->error);
}

$carID = $stmt->insert_id;

/* Tạo folder riêng cho xe */
$folderName = 'car_' . $carID;
$baseUploadDir = '../../../../CarRental_Frontend/assets/img/cars/';
$carFolderPath = $baseUploadDir . $folderName;

if (!is_dir($carFolderPath)) {
    if (!mkdir($carFolderPath, 0777, true)) {
        die('Không thể tạo thư mục ảnh cho xe.');
    }
}

/* Lưu folder vào bảng Cars */
$updateFolder = $conn->prepare("UPDATE Cars SET FolderName = ? WHERE CarID = ?");
$updateFolder->bind_param("si", $folderName, $carID);
$updateFolder->execute();

/* Upload nhiều ảnh */
$allowedExt = ['jpg', 'jpeg', 'png', 'gif', 'webp'];
$firstImage = '';

if (!empty($_FILES['Images']['name'][0])) {
    foreach ($_FILES['Images']['name'] as $index => $originalName) {
        if ($_FILES['Images']['error'][$index] !== 0) {
            continue;
        }

        $ext = strtolower(pathinfo($originalName, PATHINFO_EXTENSION));
        $tmpName = $_FILES['Images']['tmp_name'][$index];
        if (!isRealImageUpload($tmpName, $ext, $allowedExt)) {
            continue;
        }

        $newFileName = moveValidatedUpload($tmpName, $carFolderPath, 'img_' . ($index + 1), $ext);

        if ($newFileName !== false) {
            $imageUrl = $folderName . '/' . $newFileName;
            $isMain = ($firstImage === '') ? 1 : 0;
            $imageType = 'gallery';

            if ($firstImage === '') {
                $firstImage = $imageUrl;
            }

            $stmtImg = $conn->prepare("
                INSERT INTO CarImages (CarID, ImageURL, IsMain, UploadedAt, ImageType)
                VALUES (?, ?, ?, NOW(), ?)
            ");
            $stmtImg->bind_param("isis", $carID, $imageUrl, $isMain, $imageType);
            $stmtImg->execute();
        }
    }
}

/* Cập nhật MainImage cho xe */
if ($firstImage !== '') {
    $updateMain = $conn->prepare("UPDATE Cars SET MainImage = ? WHERE CarID = ?");
    $updateMain->bind_param("si", $firstImage, $carID);
    $updateMain->execute();
}

header("Location: ../../../../CarRental_Admin/cars/list.php");
exit();
?>