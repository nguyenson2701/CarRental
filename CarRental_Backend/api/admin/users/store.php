<?php
require_once '../../../config/auth.php';
requireAdminOrStaff('../../../../CarRental_Admin/login.php');
require_once '../../../config/database.php';
require_once '../../../helpers/upload.php';

$fullName = trim($_POST['FullName'] ?? '');
$email = trim($_POST['Email'] ?? '');
$phone = trim($_POST['Phone'] ?? '');
$password = trim($_POST['PasswordHash'] ?? '');
if ($password === '') {
    die('Vui long nhap mat khau.');
}
$passwordHash = password_hash($password, PASSWORD_DEFAULT);
$address = trim($_POST['Address'] ?? '');
$licenseNumber = trim($_POST['LicenseNumber'] ?? '');
$licenseVerifiedStatus = trim($_POST['LicenseVerifiedStatus'] ?? 'Pending');
$roleID = (int)($_POST['RoleID'] ?? 3);
$status = trim($_POST['Status'] ?? 'Active');

$avatarDir = '../../../../CarRental_Frontend/assets/img/avatars/';
$gplxDir   = '../../../../CarRental_Frontend/assets/img/GPLX/';

$avatar = requireValidImageOrDie('Avatar', $avatarDir, 'avatar', 'Anh dai dien', '');
$licenseFrontImage = requireValidImageOrDie('LicenseFrontImage', $gplxDir, 'license_front', 'Anh GPLX mat truoc', '');
$licenseBackImage = requireValidImageOrDie('LicenseBackImage', $gplxDir, 'license_back', 'Anh GPLX mat sau', '');

$sql = "INSERT INTO users
        (FullName, Email, Phone, PasswordHash, Address, Avatar, LicenseNumber, LicenseFrontImage, LicenseBackImage, LicenseVerifiedStatus, RoleID, Status, CreatedAt, UpdatedAt)
        VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, NOW(), NOW())";

$stmt = $conn->prepare($sql);
$stmt->bind_param(
    "ssssssssssis",
    $fullName,
    $email,
    $phone,
    $passwordHash,
    $address,
    $avatar,
    $licenseNumber,
    $licenseFrontImage,
    $licenseBackImage,
    $licenseVerifiedStatus,
    $roleID,
    $status
);

if ($stmt->execute()) {
    header("Location: ../../../../CarRental_Admin/users/list.php");
    exit();
} else {
    echo "Lỗi thêm người dùng: " . $stmt->error;
}
?>