<?php
// Da chuyen sang MVC - xem app/Controllers/Frontend/CarController.php
$keyword = trim($_GET['keyword'] ?? '');
$target = '/Carrental/vehicle' . ($keyword !== '' ? '?keyword=' . urlencode($keyword) : '');
header('Location: ' . $target);
exit();
