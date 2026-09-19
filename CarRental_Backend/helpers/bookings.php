<?php

const BOOKING_BLOCKING_STATUSES = ['Pending', 'Confirmed', 'Paid'];
const BOOKING_ACTIVE_RENTAL_STATUSES = ['Confirmed', 'Paid'];

function bookingStatusSql(array $statuses = BOOKING_BLOCKING_STATUSES, string $alias = ''): string {
    $prefix = $alias !== '' ? $alias . '.' : '';
    $quoted = array_map(static fn($status) => "'" . addslashes($status) . "'", $statuses);
    return $prefix . 'Status IN (' . implode(', ', $quoted) . ')';
}

function bookingJsonResponse(array $payload, int $statusCode = 200): void {
    http_response_code($statusCode);
    header('Content-Type: application/json; charset=utf-8');
    echo json_encode($payload, JSON_UNESCAPED_UNICODE);
    exit();
}

function bookingJsonList(array $payload): void {
    header('Content-Type: application/json; charset=utf-8');
    echo json_encode($payload, JSON_UNESCAPED_UNICODE);
    exit();
}

function bookingValidDate(string $date): bool {
    $dt = DateTime::createFromFormat('Y-m-d', $date);
    return $dt && $dt->format('Y-m-d') === $date;
}

function bookingValidDateTime(string $dateTime): bool {
    $normalized = str_replace('T', ' ', trim($dateTime));
    $dt = DateTime::createFromFormat('Y-m-d H:i', $normalized);
    return $dt && $dt->format('Y-m-d H:i') === $normalized;
}

function bookingNormalizeDateTime(string $dateTime): string {
    return str_replace('T', ' ', trim($dateTime));
}

function bookingHasOverlap(mysqli $conn, int $carID, string $startDate, string $endDate, ?int $excludeBookingID = null): bool {
    $excludeSql = $excludeBookingID ? 'AND BookingID <> ?' : '';
    $sql = "
        SELECT BookingID
        FROM bookings
        WHERE CarID = ?
          AND " . bookingStatusSql() . "
          AND (? < EndDate)
          AND (? > StartDate)
          $excludeSql
        LIMIT 1
    ";

    $stmt = $conn->prepare($sql);
    if ($excludeBookingID) {
        $stmt->bind_param('issi', $carID, $startDate, $endDate, $excludeBookingID);
    } else {
        $stmt->bind_param('iss', $carID, $startDate, $endDate);
    }
    $stmt->execute();

    return $stmt->get_result()->num_rows > 0;
}

function bookingRentalDays(string $startDate, string $endDate): int {
    $start = strtotime($startDate);
    $end = strtotime($endDate);
    if (!$start || !$end || $end <= $start) {
        return 0;
    }

    return max(1, (int)ceil(($end - $start) / 86400));
}
