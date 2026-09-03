<?php
// Da chuyen sang MVC - xem app/Controllers/Admin/CarController.php
$id = (int) ($_GET['id'] ?? 0);
header('Location: /Carrental/admin/cars/' . $id . '/edit');
exit();
