<?php
// Da chuyen sang MVC - xem app/Controllers/Frontend/CarController.php
$carId = (int) ($_GET['car_id'] ?? 0);
header('Location: /Carrental/vehicle/' . $carId . '/book');
exit();
