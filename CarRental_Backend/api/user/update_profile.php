<?php
require_once '../../config/auth.php';
requireLogin('../../../CarRental_Frontend/login.php');
require_once '../../config/database.php';

$userID = (int)($_SESSION['user_id'] ?? 0);

$fullName = trim($_POST['FullName'] ?? '');
$email = trim($_POST['Email'] ?? '');
$phone = trim($_POST['Phone'] ?? '');
$address = trim($_POST['Address'] ?? '');
$licenseNumber = trim($_POST['LicenseNumber'] ?? '');

if ($userID <= 0 || $fullName === '' || $email === '' || $phone === '') {
    header("Location: ../../../CarRental_Frontend/profile.php?error=" . urlencode("Vui lòng nhập đầy đủ thông tin bắt buộc."));
    exit();
}

if (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
    header("Location: ../../../CarRental_Frontend/profile.php?error=" . urlencode("Email không hợp lệ."));
    exit();
}

/* Lấy thông tin user cũ */
$stmtOld = $conn->prepare("
    SELECT Avatar, LicenseFrontImage, LicenseBackImage
    FROM users
    WHERE UserID = ?
    LIMIT 1
");
$stmtOld->bind_param("i", $userID);
$stmtOld->execute();
$resultOld = $stmtOld->get_result();
$oldUser = $resultOld->fetch_assoc();

if (!$oldUser) {
    header("Location: ../../../CarRental_Frontend/profile.php?error=" . urlencode("Không tìm thấy tài khoản."));
    exit();
}

$avatarPath = $oldUser['Avatar'] ?? '';
$frontPath  = $oldUser['LicenseFrontImage'] ?? '';
$backPath   = $oldUser['LicenseBackImage'] ?? '';

/* Đường dẫn upload thật trên server */
$avatarDir = '../../../CarRental_Frontend/assets/img/avatars/';
$gplxDir   = '../../../CarRental_Frontend/assets/img/GPLX/';

/* Tạo thư mục nếu chưa có */
if (!is_dir($avatarDir)) {
    mkdir($avatarDir, 0777, true);
}

if (!is_dir($gplxDir)) {
    mkdir($gplxDir, 0777, true);
}

/* Hàm upload ảnh */
function uploadImageIfExists($fileKey, $prefix, $oldPath, $uploadDir, $dbPrefix) {
    if (!isset($_FILES[$fileKey]) || $_FILES[$fileKey]['error'] !== 0) {
        return $oldPath;
    }

    $tmpName = $_FILES[$fileKey]['tmp_name'];
    $originalName = basename($_FILES[$fileKey]['name']);
    $ext = strtolower(pathinfo($originalName, PATHINFO_EXTENSION));
    $allowed = ['jpg', 'jpeg', 'png', 'webp'];

    if (!in_array($ext, $allowed, true)) {
        return $oldPath;
    }

    $newName = $prefix . '_' . time() . '_' . rand(1000, 9999) . '.' . $ext;
    $target = $uploadDir . $newName;

    if (move_uploaded_file($tmpName, $target)) {
        return $dbPrefix . $newName;
    }

    return $oldPath;
}

/* Upload avatar */
$avatarPath = uploadImageIfExists(
    'Avatar',
    'avatar_' . $userID,
    $avatarPath,
    $avatarDir,
    'assets/img/avatars/'
);

/* Upload GPLX mặt trước */
$frontPath = uploadImageIfExists(
    'LicenseFrontImage',
    'license_front_' . $userID,
    $frontPath,
    $gplxDir,
    'assets/img/GPLX/'
);

/* Upload GPLX mặt sau */
$backPath = uploadImageIfExists(
    'LicenseBackImage',
    'license_back_' . $userID,
    $backPath,
    $gplxDir,
    'assets/img/GPLX/'
);

/* Kiểm tra email bị trùng với user khác */
$stmtCheck = $conn->prepare("
    SELECT UserID
    FROM users
    WHERE Email = ? AND UserID <> ?
    LIMIT 1
");
$stmtCheck->bind_param("si", $email, $userID);
$stmtCheck->execute();
$resultCheck = $stmtCheck->get_result();

if ($resultCheck->num_rows > 0) {
    header("Location: ../../../CarRental_Frontend/profile.php?error=" . urlencode("Email đã được sử dụng bởi tài khoản khác."));
    exit();
}

/* Cập nhật dữ liệu */
$stmt = $conn->prepare("
    UPDATE users
    SET FullName = ?, Email = ?, Phone = ?, Address = ?, Avatar = ?, LicenseNumber = ?, LicenseFrontImage = ?, LicenseBackImage = ?, UpdatedAt = NOW()
    WHERE UserID = ?
");
$stmt->bind_param(
    "ssssssssi",
    $fullName,
    $email,
    $phone,
    $address,
    $avatarPath,
    $licenseNumber,
    $frontPath,
    $backPath,
    $userID
);

if ($stmt->execute()) {
    $_SESSION['name'] = $fullName;
    header("Location: ../../../CarRental_Frontend/profile.php?success=1");
    exit();
}

header("Location: ../../../CarRental_Frontend/profile.php?error=" . urlencode("Không thể cập nhật hồ sơ."));
exit();
?>
