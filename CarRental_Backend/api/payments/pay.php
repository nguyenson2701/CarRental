<?php
require_once __DIR__ . '/../../config/auth.php';
requireLogin('../../../CarRental_Frontend/login.php');
require_once __DIR__ . '/../../config/database.php';

$userID = (int)($_SESSION['user_id'] ?? 0);
$paymentID = (int)($_POST['PaymentID'] ?? 0);
$paymentMethod = trim($_POST['PaymentMethod'] ?? '');

if ($userID <= 0 || $paymentID <= 0 || $paymentMethod === '') {
    die('Dữ liệu thanh toán không hợp lệ.');
}

$stmt = $conn->prepare("
    SELECT p.PaymentID, p.BookingID, p.Status, p.PaymentType, b.UserID, b.Status AS BookingStatus, b.ReturnStatus, b.CarID
    FROM payments p
    INNER JOIN bookings b ON p.BookingID = b.BookingID
    WHERE p.PaymentID = ?
      AND b.UserID = ?
    LIMIT 1
");
$stmt->bind_param("ii", $paymentID, $userID);
$stmt->execute();
$payment = $stmt->get_result()->fetch_assoc();

if (!$payment) {
    die('Không tìm thấy khoản thanh toán.');
}

if ($payment['Status'] === 'Paid') {
    header("Location: ../../../CarRental_Frontend/my-payments.php?paid=1");
    exit();
}

if (in_array($payment['PaymentType'], ['Deposit', 'Rental'], true) && in_array($payment['BookingStatus'], ['Cancelled', 'Completed'], true)) {
    die('Đơn này không còn đủ điều kiện thanh toán.');
}

if ($payment['PaymentType'] === 'Final' && $payment['ReturnStatus'] !== 'Approved') {
    die('Đơn này chưa được admin xác nhận trả xe.');
}

if ($payment['PaymentType'] === 'Final') {
    die('Thanh toán cuối sẽ do admin xác nhận bằng tiền mặt hoặc chuyển khoản.');
}

$transactionCode = 'PAY' . time() . rand(1000, 9999);

$conn->begin_transaction();

try {
    $stmtUpdate = $conn->prepare("
        UPDATE payments
        SET PaymentMethod = ?,
            TransactionCode = ?,
            PaymentDate = NOW(),
            Status = 'Paid'
        WHERE PaymentID = ?
    ");
    $stmtUpdate->bind_param("ssi", $paymentMethod, $transactionCode, $paymentID);

    if (!$stmtUpdate->execute()) {
        throw new Exception('Không thể cập nhật thanh toán.');
    }

    if ($payment['PaymentType'] === 'Deposit') {
        $bookingID = (int)$payment['BookingID'];
        $carID = (int)$payment['CarID'];

        $stmtBooking = $conn->prepare("
            UPDATE bookings
            SET Status = 'Confirmed',
                UpdatedAt = NOW()
            WHERE BookingID = ?
        ");
        $stmtBooking->bind_param("i", $bookingID);
        $stmtBooking->execute();

        $stmtCar = $conn->prepare("
            UPDATE cars
            SET Status = 'Booked',
                UpdatedAt = NOW()
            WHERE CarID = ?
        ");
        $stmtCar->bind_param("i", $carID);
        $stmtCar->execute();
    }

    /* Hỗ trợ payment Rental cũ: thanh toán xong chuyển booking Paid */
    if ($payment['PaymentType'] === 'Rental') {
        $bookingID = (int)$payment['BookingID'];
        $carID = (int)$payment['CarID'];

        $stmtBooking = $conn->prepare("
            UPDATE bookings
            SET Status = 'Paid',
                UpdatedAt = NOW()
            WHERE BookingID = ?
        ");
        $stmtBooking->bind_param("i", $bookingID);
        $stmtBooking->execute();

        $stmtCar = $conn->prepare("
            UPDATE cars
            SET Status = 'Booked',
                UpdatedAt = NOW()
            WHERE CarID = ?
        ");
        $stmtCar->bind_param("i", $carID);
        $stmtCar->execute();
    }

    if ($payment['PaymentType'] === 'Final') {
        $bookingID = (int)$payment['BookingID'];
        $carID = (int)$payment['CarID'];

        $stmtBooking = $conn->prepare("
            UPDATE bookings
            SET Status = 'Completed',
                UpdatedAt = NOW()
            WHERE BookingID = ?
        ");
        $stmtBooking->bind_param("i", $bookingID);
        $stmtBooking->execute();

        $stmtCar = $conn->prepare("
            UPDATE cars
            SET Status = 'Available',
                UpdatedAt = NOW()
            WHERE CarID = ?
        ");
        $stmtCar->bind_param("i", $carID);
        $stmtCar->execute();
    }

    $conn->commit();
} catch (Throwable $e) {
    $conn->rollback();
    die($e->getMessage());
}

header("Location: ../../../CarRental_Frontend/my-payments.php?paid=1");
exit();
