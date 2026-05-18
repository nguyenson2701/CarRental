<?php
require_once '../../../config/auth.php';
requireAdminOrStaff('../../../../CarRental_Admin/login.php');
require_once '../../../config/database.php';

$bookingID = (int)($_POST['BookingID'] ?? 0);

$damageFee = (float)($_POST['DamageFee'] ?? 0);
$cleaningFee = (float)($_POST['CleaningFee'] ?? 0);
$otherFee = (float)($_POST['OtherFee'] ?? 0);
$penaltyReason = trim($_POST['PenaltyReason'] ?? '');

if ($bookingID <= 0) {
    die('Dữ liệu không hợp lệ.');
}

$stmt = $conn->prepare("
    SELECT BookingID, CarID, OvertimeFee, TotalPrice, DepositAmount
    FROM bookings
    WHERE BookingID = ?
    LIMIT 1
");
$stmt->bind_param("i", $bookingID);
$stmt->execute();
$booking = $stmt->get_result()->fetch_assoc();

if (!$booking) {
    die('Không tìm thấy đơn.');
}

$totalPenalty = (float)$booking['OvertimeFee'] + $damageFee + $cleaningFee + $otherFee;
$remainingAmount = max(0, (float)$booking['TotalPrice'] - (float)$booking['DepositAmount']);
$finalAmount = $remainingAmount + $totalPenalty;

$conn->begin_transaction();

$stmtUpdate = $conn->prepare("
    UPDATE bookings
    SET DamageFee = ?,
        CleaningFee = ?,
        OtherFee = ?,
        TotalPenalty = ?,
        PenaltyReason = ?,
        ReturnStatus = 'Approved',
        UpdatedAt = NOW()
    WHERE BookingID = ?
");
$stmtUpdate->bind_param(
    "ddddsi",
    $damageFee,
    $cleaningFee,
    $otherFee,
    $totalPenalty,
    $penaltyReason,
    $bookingID
);

if (!$stmtUpdate->execute()) {
    $conn->rollback();
    die('Không thể xác nhận trả xe.');
}

if ($finalAmount > 0) {
    $paymentType = 'Final';
    $status = 'Pending';
    $note = 'Thanh toán cuối: tiền còn lại ' . number_format($remainingAmount, 0, ',', '.') .
        ' VNĐ, tiền phạt ' . number_format($totalPenalty, 0, ',', '.') . ' VNĐ';
    if ($penaltyReason !== '') {
        $note .= '. Lý do phạt: ' . $penaltyReason;
    }

    $stmtExisting = $conn->prepare("
        SELECT PaymentID
        FROM payments
        WHERE BookingID = ? AND PaymentType = 'Final'
        LIMIT 1
    ");
    $stmtExisting->bind_param("i", $bookingID);
    $stmtExisting->execute();
    $existingFinal = $stmtExisting->get_result()->fetch_assoc();

    if ($existingFinal) {
        $stmtPay = $conn->prepare("
            UPDATE payments
            SET Amount = ?, Status = 'Pending', Note = ?, PaymentMethod = NULL, TransactionCode = '', PaymentDate = NULL
            WHERE PaymentID = ?
        ");
        $stmtPay->bind_param("dsi", $finalAmount, $note, $existingFinal['PaymentID']);
    } else {
        $stmtPay = $conn->prepare("
            INSERT INTO payments
            (BookingID, Amount, PaymentMethod, PaymentType, TransactionCode, PaymentDate, Status, Note)
            VALUES (?, ?, NULL, ?, '', NULL, ?, ?)
        ");

        $stmtPay->bind_param(
            "idsss",
            $bookingID,
            $finalAmount,
            $paymentType,
            $status,
            $note
        );
    }

    if (!$stmtPay->execute()) {
        $conn->rollback();
        die('Không thể tạo thanh toán cuối.');
    }
} else {
    $stmtComplete = $conn->prepare("
        UPDATE bookings
        SET Status = 'Completed', UpdatedAt = NOW()
        WHERE BookingID = ?
    ");
    $stmtComplete->bind_param("i", $bookingID);
    $stmtComplete->execute();

    $stmtCar = $conn->prepare("
        UPDATE cars
        SET Status = 'Available', UpdatedAt = NOW()
        WHERE CarID = ?
    ");
    $stmtCar->bind_param("i", $booking['CarID']);
    $stmtCar->execute();
}

$conn->commit();

header("Location: ../../../../CarRental_Admin/bookings/list.php?returned=1");
exit();
