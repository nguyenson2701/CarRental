<?php
// Da chuyen sang MVC - xem app/Controllers/Frontend/BookingController.php
$bookingId = (int) ($_GET['booking_id'] ?? 0);
header('Location: /Carrental/my-bookings/' . $bookingId . '/return');
exit();
