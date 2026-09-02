<?php
require_once __DIR__ . '/../../config/database.php';
require_once __DIR__ . '/../../config/auth.php';
require_once __DIR__ . '/../../helpers/bookings.php';

requireLogin('../../../CarRental_Frontend/login.php');

$userID = (int)($_SESSION['user_id'] ?? 0);
$carID = (int)($_POST['CarID'] ?? 0);
$startDate = trim($_POST['StartDate'] ?? '');
$endDate = trim($_POST['EndDate'] ?? '');
$pickupLocation = trim($_POST['PickupLocation'] ?? '');
$returnLocation = trim($_POST['ReturnLocation'] ?? '');
$note = trim($_POST['Note'] ?? '');

if ($userID <= 0 || $carID <= 0 || $startDate === '' || $endDate === '' || $pickupLocation === '' || $returnLocation === '') {
    die('Thieu du lieu dat xe.');
}

$rentalDays = bookingRentalDays($startDate, $endDate);
if (!bookingValidDate($startDate) || !bookingValidDate($endDate) || $rentalDays < 1) {
    die('Thoi gian dat xe khong hop le.');
}

$conn->begin_transaction();

try {
    $stmtCar = $conn->prepare("
        SELECT CarID, CarName, PricePerDay, DepositAmount, Status
        FROM cars
        WHERE CarID = ?
        FOR UPDATE
    ");
    $stmtCar->bind_param('i', $carID);
    $stmtCar->execute();
    $car = $stmtCar->get_result()->fetch_assoc();

    if (!$car) {
        throw new Exception('Khong tim thay xe.');
    }

    if ($car['Status'] === 'Maintenance') {
        throw new Exception('Xe dang bao tri, khong the dat.');
    }

    if (bookingHasOverlap($conn, $carID, $startDate, $endDate)) {
        throw new Exception('Xe da co lich trong khoang thoi gian nay.');
    }

    $pricePerDay = (float)$car['PricePerDay'];
    $depositAmount = (float)$car['DepositAmount'];
    $discountAmount = 0;
    $totalPrice = max(0, ($rentalDays * $pricePerDay) - $discountAmount);

    $stmt = $conn->prepare("
        INSERT INTO bookings
        (
            UserID, CarID, StartDate, EndDate, PickupLocation, ReturnLocation,
            RentalDays, PricePerDay, DepositAmount, DiscountAmount, TotalPrice,
            Status, Note, CreatedAt, UpdatedAt
        )
        VALUES
        (?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, 'Pending', ?, NOW(), NOW())
    ");

    $stmt->bind_param(
        'iissssidddds',
        $userID,
        $carID,
        $startDate,
        $endDate,
        $pickupLocation,
        $returnLocation,
        $rentalDays,
        $pricePerDay,
        $depositAmount,
        $discountAmount,
        $totalPrice,
        $note
    );

    if (!$stmt->execute()) {
        throw new Exception('Loi dat xe: ' . $stmt->error);
    }

    $bookingID = (int)$stmt->insert_id;
    $paymentType = 'Deposit';
    $status = 'Pending';
    $paymentNote = 'Thanh toan tien coc giu xe: ' . $car['CarName'];

    $stmtPay = $conn->prepare("
        INSERT INTO payments
        (BookingID, Amount, PaymentMethod, PaymentType, TransactionCode, PaymentDate, Status, Note)
        VALUES (?, ?, NULL, ?, '', NULL, ?, ?)
    ");
    $stmtPay->bind_param('idsss', $bookingID, $depositAmount, $paymentType, $status, $paymentNote);

    if (!$stmtPay->execute()) {
        throw new Exception('Khong the tao khoan thanh toan: ' . $stmtPay->error);
    }

    $conn->commit();
    header('Location: ../../../CarRental_Frontend/my-payments.php?booking_success=1');
    exit();
} catch (Throwable $e) {
    $conn->rollback();
    die($e->getMessage());
}
