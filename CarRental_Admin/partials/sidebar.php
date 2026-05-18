<?php
$currentPath = $_SERVER['REQUEST_URI'] ?? '';

function isActive($folder) {
    global $currentPath;
    return strpos($currentPath, "/carrental%20-%20Copy/CarRental_Admin/$folder/") !== false ? 'active' : '';
}

function isDashboard() {
    global $currentPath;
    return strpos($currentPath, "/carrental%20-%20Copy/CarRental_Admin/index.php") !== false
        || rtrim($currentPath, '/') === '/carrental%20-%20Copy/CarRental_Admin'
        ? 'active'
        : '';
}
?>

<ul class="navbar-nav bg-gradient-primary sidebar sidebar-dark accordion" id="accordionSidebar">

    <a class="sidebar-brand d-flex align-items-center justify-content-center" href="/carrental%20-%20Copy/CarRental_Admin/index.php">
        <div class="sidebar-brand-icon rotate-n-15">
            <i class="fas fa-car"></i>
        </div>
        <div class="sidebar-brand-text mx-3">VINADRIVE</div>
    </a>

    <hr class="sidebar-divider my-0">

    <li class="nav-item <?= isDashboard() ?>">
        <a class="nav-link" href="/carrental%20-%20Copy/CarRental_Admin/index.php">
            <i class="fas fa-fw fa-tachometer-alt"></i>
            <span>Dashboard</span>
        </a>
    </li>

    <hr class="sidebar-divider">

    <div class="sidebar-heading">Quản lý</div>

    <li class="nav-item <?= isActive('cars') ?>">
        <a class="nav-link" href="/carrental%20-%20Copy/CarRental_Admin/cars/list.php">
            <i class="fas fa-fw fa-car"></i>
            <span>Quản lý xe</span>
        </a>
    </li>

    <li class="nav-item <?= isActive('brands') ?>">
        <a class="nav-link" href="/carrental%20-%20Copy/CarRental_Admin/brands/list.php">
            <i class="fas fa-fw fa-tags"></i>
            <span>Hãng xe</span>
        </a>
    </li>

    <li class="nav-item <?= isActive('bookings') ?>">
        <a class="nav-link" href="/carrental%20-%20Copy/CarRental_Admin/bookings/list.php">
            <i class="fas fa-fw fa-calendar-check"></i>
            <span>Đơn đặt xe</span>
        </a>
    </li>

    <li class="nav-item <?= isActive('users') ?>">
        <a class="nav-link" href="/carrental%20-%20Copy/CarRental_Admin/users/list.php">
            <i class="fas fa-fw fa-users"></i>
            <span>Người dùng</span>
        </a>
    </li>

    <li class="nav-item <?= isActive('menus') ?>">
        <a class="nav-link" href="/carrental%20-%20Copy/CarRental_Admin/menus/list.php">
            <i class="fas fa-fw fa-bars"></i>
            <span>Menu</span>
        </a>
    </li>

    <li class="nav-item <?= isActive('blogs') ?>">
        <a class="nav-link" href="/carrental%20-%20Copy/CarRental_Admin/blogs/list.php">
            <i class="fas fa-fw fa-newspaper"></i>
            <span>Blog</span>
        </a>
    </li>

    <hr class="sidebar-divider">

    <div class="sidebar-heading">Website</div>

    <li class="nav-item">
        <a class="nav-link" href="/carrental%20-%20Copy/CarRental_Frontend/index.php" target="_blank">
            <i class="fas fa-fw fa-home"></i>
            <span>Xem website</span>
        </a>
    </li>

    <li class="nav-item">
        <a class="nav-link" href="/carrental%20-%20Copy/CarRental_Backend/api/auth/logout.php">
            <i class="fas fa-fw fa-sign-out-alt"></i>
            <span>Đăng xuất</span>
        </a>
    </li>

    <hr class="sidebar-divider d-none d-md-block">

    <div class="text-center d-none d-md-inline">
        <button class="rounded-circle border-0" id="sidebarToggle"></button>
    </div>

</ul>
