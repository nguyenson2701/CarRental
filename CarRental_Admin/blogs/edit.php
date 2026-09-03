<?php
// Da chuyen sang MVC - xem app/Controllers/Admin/BlogController.php
$id = (int) ($_GET['id'] ?? 0);
header('Location: /Carrental/admin/blogs/' . $id . '/edit');
exit();
