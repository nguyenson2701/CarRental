<?php
require_once '../../../config/auth.php';
requireAdminOrStaff('../../../../CarRental_Admin/login.php');
require_once '../../../config/database.php';

$bookingID = (int)($_POST['BookingID'] ?? 0);
$newStatus = trim($_POST['Status'] ?? '');
$redirect = trim($_POST['Redirect'] ?? 'list');

if ($bookingID <= 0 || $newStatus !== 'Cancelled') {
    die('Du lieu cap nhat khong hop le.');
}

$conn->begin_transaction();

try {
    $stmt = $conn->prepare("
        SELECT BookingID, Status, CarID
        FROM bookings
        WHERE BookingID = ?
        FOR UPDATE
    ");
    $stmt->bind_param("i", $bookingID);
    $stmt->execute();
    $booking = $stmt->get_result()->fetch_assoc();

    if (!$booking) {
        throw new Exception('Khong tim thay don.');
    }

    $currentStatus = $booking['Status'];
    $carID = (int)$booking['CarID'];

    if (!in_array($currentStatus, ['Pending', 'Confirmed'], true)) {
        throw new Exception("Khong the huy don o trang thai $currentStatus.");
    }

    $stmtBooking = $conn->prepare("
        UPDATE bookings
        SET Status = 'Cancelled',
            ReturnStatus = CASE
                WHEN ReturnStatus = 'Pending' THEN 'NotReturned'
                ELSE ReturnStatus
            END,
            UpdatedAt = NOW()
        WHERE BookingID = ?
    ");
    $stmtBooking->bind_param("i", $bookingID);

    if (!$stmtBooking->execute()) {
        throw new Exception('Khong the huy don.');
    }

    $stmtPayments = $conn->prepare("
        UPDATE payments
        SET Status = 'Cancelled',
            Note = CONCAT(COALESCE(NULLIF(Note, ''), 'Thanh toan'), ' - Don da huy')
        WHERE BookingID = ?
          AND Status = 'Pending'
    ");
    $stmtPayments->bind_param("i", $bookingID);
    $stmtPayments->execute();

    $stmtCar = $conn->prepare("
        UPDATE cars c
        SET c.Status = 'Available',
            c.UpdatedAt = NOW()
        WHERE c.CarID = ?
          AND c.Status <> 'Maintenance'
          AND NOT EXISTS (
              SELECT 1
              FROM bookings b
              WHERE b.CarID = c.CarID
                AND b.BookingID <> ?
                AND b.Status IN ('Pending', 'Confirmed', 'Paid')
                AND b.EndDate >= CURDATE()
          )
    ");
    $stmtCar->bind_param("ii", $carID, $bookingID);
    $stmtCar->execute();

    $conn->commit();
} catch (Throwable $e) {
    $conn->rollback();
    die($e->getMessage());
}

$target = $redirect === 'detail'
    ? "../../../../CarRental_Admin/bookings/detail.php?id=$bookingID&updated=1"
    : "../../../../CarRental_Admin/bookings/list.php?updated=1";

header("Location: $target");
exit();
