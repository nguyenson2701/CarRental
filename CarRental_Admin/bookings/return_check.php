<?php
// Da chuyen sang MVC - xem app/Controllers/Admin/BookingController.php
$id = (int) ($_GET['id'] ?? 0);
header('Location: /Carrental/admin/bookings/' . $id . '/return-check');
exit();
