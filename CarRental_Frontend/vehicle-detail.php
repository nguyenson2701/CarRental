<?php
// Da chuyen sang MVC - xem app/Controllers/Frontend/CarController.php
$id = (int) ($_GET['id'] ?? 0);
header('Location: /Carrental/vehicle/' . $id);
exit();
