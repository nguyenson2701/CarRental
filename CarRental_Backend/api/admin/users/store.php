<?php
require_once '../../../config/auth.php';
requireAdminOrStaff('../../../../CarRental_Admin/login.php');
require_once '../../../config/database.php';

$fullName = trim($_POST['FullName'] ?? '');
$email = trim($_POST['Email'] ?? '');
$phone = trim($_POST['Phone'] ?? '');
$password = trim($_POST['PasswordHash'] ?? '');
$address = trim($_POST['Address'] ?? '');
$licenseNumber = trim($_POST['LicenseNumber'] ?? '');
$licenseVerifiedStatus = trim($_POST['LicenseVerifiedStatus'] ?? 'Pending');
$roleID = (int)($_POST['RoleID'] ?? 3);
$status = trim($_POST['Status'] ?? 'Active');

$avatar = '';
$licenseFrontImage = '';
$licenseBackImage = '';

$avatarDir = '../../../../CarRental_Frontend/assets/img/avatars/';
$gplxDir   = '../../../../CarRental_Frontend/assets/img/GPLX/';
if (!is_dir($avatarDir)) {
    mkdir($avatarDir, 0777, true);
}

if (!is_dir($gplxDir)) {
    mkdir($gplxDir, 0777, true);
}

if (!empty($_FILES['Avatar']['name'])) {
    $avatar = time() . '_avatar_' . basename($_FILES['Avatar']['name']);
    move_uploaded_file($_FILES['Avatar']['tmp_name'], $avatarDir . $avatar);
}

if (!empty($_FILES['LicenseFrontImage']['name'])) {
    $licenseFrontImage = time() . '_front_' . basename($_FILES['LicenseFrontImage']['name']);
    move_uploaded_file($_FILES['LicenseFrontImage']['tmp_name'], $gplxDir . $licenseFrontImage);
}

if (!empty($_FILES['LicenseBackImage']['name'])) {
    $licenseBackImage = time() . '_back_' . basename($_FILES['LicenseBackImage']['name']);
    move_uploaded_file($_FILES['LicenseBackImage']['tmp_name'], $gplxDir . $licenseBackImage);
}

$sql = "INSERT INTO users
        (FullName, Email, Phone, PasswordHash, Address, Avatar, LicenseNumber, LicenseFrontImage, LicenseBackImage, LicenseVerifiedStatus, RoleID, Status, CreatedAt, UpdatedAt)
        VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, NOW(), NOW())";

$stmt = $conn->prepare($sql);
$stmt->bind_param(
    "ssssssssssis",
    $fullName,
    $email,
    $phone,
    $password,
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