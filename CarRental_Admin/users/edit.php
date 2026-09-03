<?php
// Da chuyen sang MVC - xem app/Controllers/Admin/UserController.php
$id = (int) ($_GET['id'] ?? 0);
header('Location: /Carrental/admin/users/' . $id . '/edit');
exit();
