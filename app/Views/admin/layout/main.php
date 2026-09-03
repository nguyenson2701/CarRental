<?php
/**
 * Layout khung admin dung chung. Bien $content (chuoi HTML da render san)
 * va $pageTitle duoc Controller::view() truyen vao.
 *
 * $currentPath dung de highlight muc dang active tren sidebar.
 * $content (chuoi HTML) duoc Controller::view() bom vao truoc khi require
 * file nay - khong khai bao truc tiep o day.
 */

$currentPath = '/' . trim(parse_url($_SERVER['REQUEST_URI'] ?? '/', PHP_URL_PATH), '/');
$pageTitle = $pageTitle ?? 'Quản trị';

function navActive(string $prefix, string $currentPath): string
{
    return str_starts_with($currentPath, $prefix) ? 'active' : '';
}
?>
<!DOCTYPE html>
<html lang="vi">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title><?= htmlspecialchars($pageTitle) ?> - VinaDrive Admin</title>

    <link href="/Carrental/public/admin/vendor/nunito/nunito.css" rel="stylesheet">
    <link href="/Carrental/public/admin/vendor/fontawesome-free/css/all.min.css" rel="stylesheet">
    <link href="/Carrental/public/admin/css/sb-admin-2.min.css" rel="stylesheet">
    <link href="/Carrental/public/admin/css/admin-theme.css" rel="stylesheet">
    <link href="/Carrental/public/admin/css/cars-admin.css" rel="stylesheet">
    <link href="/Carrental/public/admin/css/admin-dashboard.css" rel="stylesheet">
    <link href="/Carrental/public/admin/css/booking-detail.css" rel="stylesheet">
</head>
<body id="page-top">
<div id="wrapper">

    <ul class="navbar-nav bg-gradient-primary sidebar sidebar-dark accordion" id="accordionSidebar">
        <a class="sidebar-brand d-flex align-items-center justify-content-center" href="/Carrental/admin/dashboard">
            <div class="sidebar-brand-icon rotate-n-15"><i class="fas fa-car"></i></div>
            <div class="sidebar-brand-text mx-3">VINADRIVE</div>
        </a>
        <hr class="sidebar-divider my-0">

        <li class="nav-item <?= navActive('/admin/dashboard', $currentPath) ?>">
            <a class="nav-link" href="/Carrental/admin/dashboard">
                <i class="fas fa-fw fa-tachometer-alt"></i><span>Dashboard</span>
            </a>
        </li>

        <hr class="sidebar-divider">
        <div class="sidebar-heading">Quản lý</div>

        <li class="nav-item <?= navActive('/admin/cars', $currentPath) ?>">
            <a class="nav-link" href="/Carrental/admin/cars"><i class="fas fa-fw fa-car"></i><span>Quản lý xe</span></a>
        </li>
        <li class="nav-item <?= navActive('/admin/brands', $currentPath) ?>">
            <a class="nav-link" href="/Carrental/admin/brands"><i class="fas fa-fw fa-tags"></i><span>Hãng xe</span></a>
        </li>
        <li class="nav-item <?= navActive('/admin/bookings', $currentPath) ?>">
            <a class="nav-link" href="/Carrental/admin/bookings"><i class="fas fa-fw fa-calendar-check"></i><span>Đơn đặt xe</span></a>
        </li>
        <li class="nav-item <?= navActive('/admin/users', $currentPath) ?>">
            <a class="nav-link" href="/Carrental/admin/users"><i class="fas fa-fw fa-users"></i><span>Người dùng</span></a>
        </li>
        <li class="nav-item <?= navActive('/admin/menus', $currentPath) ?>">
            <a class="nav-link" href="/Carrental/admin/menus"><i class="fas fa-fw fa-bars"></i><span>Menu</span></a>
        </li>
        <li class="nav-item <?= navActive('/admin/blogs', $currentPath) ?>">
            <a class="nav-link" href="/Carrental/admin/blogs"><i class="fas fa-fw fa-newspaper"></i><span>Blog</span></a>
        </li>

        <hr class="sidebar-divider">
        <div class="sidebar-heading">Website</div>
        <li class="nav-item">
            <a class="nav-link" href="/Carrental/" target="_blank">
                <i class="fas fa-fw fa-home"></i><span>Xem website</span>
            </a>
        </li>
        <li class="nav-item">
            <a class="nav-link" href="/Carrental/logout">
                <i class="fas fa-fw fa-sign-out-alt"></i><span>Đăng xuất</span>
            </a>
        </li>
    </ul>

    <div id="content-wrapper" class="d-flex flex-column">
        <div id="content">
            <nav class="navbar navbar-expand navbar-light bg-white topbar mb-4 static-top shadow">
                <button id="sidebarToggleTop" class="btn btn-link d-md-none rounded-circle mr-3"><i class="fa fa-bars"></i></button>
                <h5 class="mb-0 text-gray-800">Hệ thống quản lý thuê xe</h5>
                <ul class="navbar-nav ml-auto">
                    <li class="nav-item">
                        <span class="mr-2 d-none d-lg-inline text-gray-600 small">
                            <?= htmlspecialchars($_SESSION['name'] ?? 'Admin') ?>
                        </span>
                    </li>
                </ul>
            </nav>

            <div class="container-fluid">
                <?= $content ?>
            </div>
        </div>

        <footer class="sticky-footer bg-white">
            <div class="container my-auto">
                <div class="copyright text-center my-auto"><span>Copyright &copy; VinaDrive <?= date('Y') ?></span></div>
            </div>
        </footer>
    </div>
</div>

<script src="/Carrental/public/admin/vendor/jquery/jquery.min.js"></script>
<script src="/Carrental/public/admin/vendor/bootstrap/js/bootstrap.bundle.min.js"></script>
<script src="/Carrental/public/admin/vendor/jquery-easing/jquery.easing.min.js"></script>
<script src="/Carrental/public/admin/js/sb-admin-2.min.js"></script>
</body>
</html>
