<?php
// Da chuyen sang MVC - xem app/Controllers/Admin/MenuController.php
$id = (int) ($_GET['id'] ?? 0);
header('Location: /Carrental/admin/menus/' . $id . '/edit');
exit();
