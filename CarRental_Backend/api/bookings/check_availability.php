<?php
require_once __DIR__ . '/../../config/database.php';
require_once __DIR__ . '/../../helpers/bookings.php';

$carID = (int)($_GET['car_id'] ?? 0);
$startDate = trim($_GET['start_date'] ?? '');
$endDate = trim($_GET['end_date'] ?? '');

if ($carID <= 0 || $startDate === '' || $endDate === '') {
    bookingJsonResponse([
        'available' => false,
        'message' => 'Thieu du lieu kiem tra.'
    ], 400);
}

if (!bookingValidDate($startDate) || !bookingValidDate($endDate) || bookingRentalDays($startDate, $endDate) < 1) {
    bookingJsonResponse([
        'available' => false,
        'message' => 'Ngay tra phai sau ngay nhan.'
    ], 400);
}

if (bookingHasOverlap($conn, $carID, $startDate, $endDate)) {
    bookingJsonResponse([
        'available' => false,
        'message' => 'Xe da co lich trong khoang thoi gian nay.'
    ]);
}

bookingJsonResponse([
    'available' => true,
    'message' => 'Xe con trong, co the dat.'
]);
