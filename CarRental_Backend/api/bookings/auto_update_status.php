<?php
require_once __DIR__ . '/../../config/database.php';
require_once __DIR__ . '/helpers.php';

$blockingStatusSql = bookingStatusSql(BOOKING_BLOCKING_STATUSES, 'b');
$activeRentalStatusSql = bookingStatusSql(BOOKING_ACTIVE_RENTAL_STATUSES, 'b');

$conn->query("
    UPDATE cars c
    SET c.Status = 'Available'
    WHERE c.Status = 'Booked'
      AND NOT EXISTS (
          SELECT 1
          FROM bookings b
          WHERE b.CarID = c.CarID
            AND $blockingStatusSql
            AND b.EndDate >= CURDATE()
      )
");

$conn->query("
    UPDATE cars c
    SET c.Status = 'Booked'
    WHERE c.Status <> 'Maintenance'
      AND EXISTS (
          SELECT 1
          FROM bookings b
          WHERE b.CarID = c.CarID
            AND $activeRentalStatusSql
            AND b.EndDate >= CURDATE()
      )
");
