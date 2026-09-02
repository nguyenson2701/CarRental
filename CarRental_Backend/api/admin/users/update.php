<?php
require_once '../../../config/auth.php';
requireAdminOrStaff('../../../../CarRental_Admin/login.php');
require_once '../../../config/database.php';
require_once '../../../helpers/upload.php';

$id = (int)($_POST['UserID'] ?? 0);
$fullName = trim($_POST['FullName'] ?? '');
$email = trim($_POST['Email'] ?? '');
$phone = trim($_POST['Phone'] ?? '');
$newPassword = trim($_POST['PasswordHash'] ?? '');
$address = trim($_POST['Address'] ?? '');
$licenseNumber = trim($_POST['LicenseNumber'] ?? '');
$licenseVerifiedStatus = trim($_POST['LicenseVerifiedStatus'] ?? 'Pending');
$roleID = (int)($_POST['RoleID'] ?? 3);
$status = trim($_POST['Status'] ?? 'Active');

$newPasswordHash = $newPassword !== '' ? password_hash($newPassword, PASSWORD_DEFAULT) : '';

$oldAvatar = trim($_POST['OldAvatar'] ?? '');
$oldLicenseFrontImage = trim($_POST['OldLicenseFrontImage'] ?? '');
$oldLicenseBackImage = trim($_POST['OldLicenseBackImage'] ?? '');

$avatarDir = '../../../../CarRental_Frontend/assets/img/avatars/';
$gplxDir   = '../../../../CarRental_Frontend/assets/img/GPLX/';

$avatar = requireValidImageOrDie('Avatar', $avatarDir, 'avatar', 'Anh dai dien', $oldAvatar);
$licenseFrontImage = requireValidImageOrDie('LicenseFrontImage', $gplxDir, 'license_front', 'Anh GPLX mat truoc', $oldLicenseFrontImage);
$licenseBackImage = requireValidImageOrDie('LicenseBackImage', $gplxDir, 'license_back', 'Anh GPLX mat sau', $oldLicenseBackImage);

/* newPasswordHash rong (chuoi '') nghia la khong doi mat khau -> giu nguyen PasswordHash cu ngay trong SQL */
$sql = "UPDATE users
        SET FullName = ?, Email = ?, Phone = ?,
            PasswordHash = IF(? = '', PasswordHash, ?),
            Address = ?, Avatar = ?, LicenseNumber = ?, LicenseFrontImage = ?, LicenseBackImage = ?, LicenseVerifiedStatus = ?, RoleID = ?, Status = ?, UpdatedAt = NOW()
        WHERE UserID = ?";

$stmt = $conn->prepare($sql);
$stmt->bind_param(
    "sssssssssssisi",
    $fullName,
    $email,
    $phone,
    $newPasswordHash,
    $newPasswordHash,
    $address,
    $avatar,
    $licenseNumber,
    $licenseFrontImage,
    $licenseBackImage,
    $licenseVerifiedStatus,
    $roleID,
    $status,
    $id
);

if ($stmt->execute()) {
    header("Location: ../../../../CarRental_Admin/users/list.php");
    exit();
} else {
    echo "Lỗi cập nhật người dùng: " . $stmt->error;
}
?>