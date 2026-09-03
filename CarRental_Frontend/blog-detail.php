<?php
// Da chuyen sang MVC - xem app/Controllers/Frontend/BlogController.php
$slug = trim($_GET['slug'] ?? '');
header('Location: /Carrental/blog/' . urlencode($slug));
exit();
