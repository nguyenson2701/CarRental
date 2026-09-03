<?php
// Da chuyen sang MVC - xem app/Controllers/Admin/BrandController.php
$id = (int) ($_GET['id'] ?? 0);
header('Location: /Carrental/admin/brands/' . $id . '/edit');
exit();
