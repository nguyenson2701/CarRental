<?php
require_once '../../../config/auth.php';
requireAdminOrStaff('../../../../CarRental_Admin/login.php');
require_once '../../../config/database.php';

$paymentID = (int)($_POST['PaymentID'] ?? 0);
$paymentMethod = trim($_POST['PaymentMethod'] ?? '');
$redirect = trim($_POST['Redirect'] ?? 'list');

$allowedMethods = ['Cash', 'BankTransfer'];

if ($paymentID <= 0 || !in_array($paymentMethod, $allowedMethods, true)) {
    die('Du lieu thanh toan khong hop le.');
}

$stmt = $conn->prepare("
    SELECT
        p.PaymentID,
        p.BookingID,
        p.Status AS PaymentStatus,
        p.PaymentType,
        b.Status AS BookingStatus,
        b.CarID
    FROM payments p
    INNER JOIN bookings b ON p.BookingID = b.BookingID
    WHERE p.PaymentID = ?
      AND p.PaymentType IN ('Deposit', 'Rental')
    LIMIT 1
");
$stmt->bind_param("i", $paymentID);
$stmt->execute();
$payment = $stmt->get_result()->fetch_assoc();

if (!$payment) {
    die('Khong tim thay khoan thanh toan.');
}

$bookingID = (int)$payment['BookingID'];

if ($payment['PaymentStatus'] === 'Paid') {
    $target = $redirect === 'detail'
        ? "../../../../CarRental_Admin/bookings/detail.php?id=$bookingID&payment_paid=1"
        : "../../../../CarRental_Admin/bookings/list.php?payment_paid=1";
    header("Location: $target");
    exit();
}

if ($payment['PaymentStatus'] !== 'Pending') {
    die('Khoan thanh toan nay chua du dieu kien xac nhan.');
}

if (in_array($payment['BookingStatus'], ['Cancelled', 'Completed'], true)) {
    die('Don nay khong con du dieu kien xac nhan thanh toan.');
}

$transactionCode = 'ADM' . time() . rand(1000, 9999);
$carID = (int)$payment['CarID'];

$conn->begin_transaction();

try {
    $stmtUpdate = $conn->prepare("
        UPDATE payments
        SET PaymentMethod = ?,
            TransactionCode = ?,
            PaymentDate = NOW(),
            Status = 'Paid'
        WHERE PaymentID = ?
          AND Status = 'Pending'
          AND PaymentType IN ('Deposit', 'Rental')
    ");
    $stmtUpdate->bind_param("ssi", $paymentMethod, $transactionCode, $paymentID);

    if (!$stmtUpdate->execute() || $stmtUpdate->affected_rows <= 0) {
        throw new Exception('Khong the cap nhat thanh toan.');
    }

    $newBookingStatus = $payment['PaymentType'] === 'Deposit' ? 'Confirmed' : 'Paid';

    $stmtBooking = $conn->prepare("
        UPDATE bookings
        SET Status = ?,
            UpdatedAt = NOW()
        WHERE BookingID = ?
    ");
    $stmtBooking->bind_param("si", $newBookingStatus, $bookingID);
    $stmtBooking->execute();

    $stmtCar = $conn->prepare("
        UPDATE cars
        SET Status = 'Booked',
            UpdatedAt = NOW()
        WHERE CarID = ?
    ");
    $stmtCar->bind_param("i", $carID);
    $stmtCar->execute();

    $conn->commit();
} catch (Throwable $e) {
    $conn->rollback();
    die($e->getMessage());
}

$target = $redirect === 'detail'
    ? "../../../../CarRental_Admin/bookings/detail.php?id=$bookingID&payment_paid=1"
    : "../../../../CarRental_Admin/bookings/list.php?payment_paid=1";

header("Location: $target");
exit();
