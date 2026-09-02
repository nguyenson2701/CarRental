<?php
require_once __DIR__ . '/../../config/auth.php';
requireLogin('../../../CarRental_Frontend/login.php');
require_once __DIR__ . '/../../config/database.php';
require_once __DIR__ . '/helpers.php';
require_once __DIR__ . '/../../helpers/upload.php';

$userID = (int)($_SESSION['user_id'] ?? 0);
$bookingID = (int)($_POST['BookingID'] ?? 0);
$actualReturnDate = bookingNormalizeDateTime($_POST['ActualReturnDate'] ?? '');
$returnNote = trim($_POST['ReturnNote'] ?? '');

if ($userID <= 0 || $bookingID <= 0 || $actualReturnDate === '' || !bookingValidDateTime($actualReturnDate)) {
    die('Du lieu khong hop le.');
}

$stmt = $conn->prepare("
    SELECT b.BookingID, b.UserID, b.CarID, b.EndDate, b.PricePerDay, b.ReturnStatus
    FROM bookings b
    WHERE b.BookingID = ?
      AND b.UserID = ?
      AND b.Status IN ('Confirmed', 'Paid')
    LIMIT 1
");
$stmt->bind_param('ii', $bookingID, $userID);
$stmt->execute();
$booking = $stmt->get_result()->fetch_assoc();

if (!$booking) {
    die('Khong tim thay don hop le.');
}

if (in_array($booking['ReturnStatus'], ['Pending', 'Approved'], true)) {
    die('Don nay da gui yeu cau tra xe.');
}

$uploadDir = __DIR__ . '/../../../CarRental_Frontend/assets/img/returns/';
if (!is_dir($uploadDir) && !mkdir($uploadDir, 0777, true)) {
    die('Khong the tao thu muc upload anh tra xe.');
}

function uploadReturnImage($fileKey, $prefix, $uploadDir) {
    $fileName = safeUploadImage($fileKey, $uploadDir, $prefix, ['jpg', 'jpeg', 'png', 'webp']);

    if ($fileName === null || $fileName === false) {
        return '';
    }

    return 'assets/img/returns/' . $fileName;
}

$frontImage = uploadReturnImage('ReturnFrontImage', 'front_' . $bookingID, $uploadDir);
$backImage = uploadReturnImage('ReturnBackImage', 'back_' . $bookingID, $uploadDir);

if ($frontImage === '' || $backImage === '') {
    header('Location: ../../../CarRental_Frontend/return-car.php?booking_id=' . $bookingID . '&error=' . urlencode('Vui long tai anh dau xe va sau xe.'));
    exit();
}

$endTime = strtotime($booking['EndDate'] . ' 23:59:59');
$actualTime = strtotime($actualReturnDate);
$overtimeFee = 0;

if ($actualTime > $endTime) {
    $diffMinutes = (int)ceil(($actualTime - $endTime) / 60);

    if ($diffMinutes > 30) {
        $overtimeHours = (int)ceil($diffMinutes / 60);
        $overtimeFee = $overtimeHours <= 6
            ? $overtimeHours * 50000
            : ceil($overtimeHours / 24) * (float)$booking['PricePerDay'];
    }
}

$stmtUpdate = $conn->prepare("
    UPDATE bookings
    SET ActualReturnDate = ?,
        OvertimeFee = ?,
        ReturnFrontImage = ?,
        ReturnBackImage = ?,
        ReturnNote = ?,
        ReturnStatus = 'Pending',
        UpdatedAt = NOW()
    WHERE BookingID = ?
");
$stmtUpdate->bind_param('sdsssi', $actualReturnDate, $overtimeFee, $frontImage, $backImage, $returnNote, $bookingID);

if ($stmtUpdate->execute()) {
    header('Location: ../../../CarRental_Frontend/my-bookings.php?return_request=1');
    exit();
}

die('Khong the gui yeu cau tra xe.');
