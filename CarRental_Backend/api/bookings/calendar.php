<?php
require_once __DIR__ . '/../../config/database.php';
require_once __DIR__ . '/../../helpers/bookings.php';

$carID = (int)($_GET['car_id'] ?? 0);

if ($carID <= 0) {
    bookingJsonList([]);
}

$stmt = $conn->prepare("
    SELECT BookingID, StartDate, EndDate, Status
    FROM bookings
    WHERE CarID = ?
      AND " . bookingStatusSql() . "
    ORDER BY StartDate ASC
");
$stmt->bind_param("i", $carID);
$stmt->execute();
$result = $stmt->get_result();

$data = [];
while ($row = $result->fetch_assoc()) {
    $data[] = $row;
}

bookingJsonList($data);
