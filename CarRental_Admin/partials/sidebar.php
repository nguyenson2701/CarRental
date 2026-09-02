<?php
/*
 * Duong dan tuyet doi duoc tinh dong tu SCRIPT_NAME thay vi hardcode ten thu muc
 * (truoc day hardcode "/carrental%20-%20Copy/..." - sai ngay khi thu muc project
 * doi ten, khien toan bo link sidebar 404). Hoat dong dung voi bat ky ten thu muc
 * goc nao ma project duoc dat trong htdocs.
 */
$scriptPath = $_SERVER['SCRIPT_NAME'] ?? '';
$adminMarkerPos = strpos($scriptPath, '/CarRental_Admin/');
if ($adminMarkerPos === false && substr($scriptPath, -strlen('/CarRental_Admin/index.php')) === '/CarRental_Admin/index.php') {
    $adminMarkerPos = strlen($scriptPath) - strlen('/CarRental_Admin/index.php');
}
$projectRoot = $adminMarkerPos !== false ? substr($scriptPath, 0, $adminMarkerPos) : '';
$adminBase = $projectRoot . '/CarRental_Admin';
$frontendBase = $projectRoot . '/CarRental_Frontend';
$backendBase = $projectRoot . '/CarRental_Backend';

$currentPath = $_SERVER['REQUEST_URI'] ?? '';

function isActive($folder) {
    global $currentPath, $adminBase;
    return strpos($currentPath, "$adminBase/$folder/") !== false ? 'active' : '';
}

function isDashboard() {
    global $currentPath, $adminBase;
    return strpos($currentPath, "$adminBase/index.php") !== false
        || rtrim($currentPath, '/') === $adminBase
        ? 'active'
        : '';
}
?>

<ul class="navbar-nav bg-gradient-primary sidebar sidebar-dark accordion" id="accordionSidebar">

    <a class="sidebar-brand d-flex align-items-center justify-content-center" href="<?php echo htmlspecialchars($adminBase); ?>/index.php">
        <div class="sidebar-brand-icon rotate-n-15">
            <i class="fas fa-car"></i>
        </div>
        <div class="sidebar-brand-text mx-3">VINADRIVE</div>
    </a>

    <hr class="sidebar-divider my-0">

    <li class="nav-item <?= isDashboard() ?>">
        <a class="nav-link" href="<?php echo htmlspecialchars($adminBase); ?>/index.php">
            <i class="fas fa-fw fa-tachometer-alt"></i>
            <span>Dashboard</span>
        </a>
    </li>

    <hr class="sidebar-divider">

    <div class="sidebar-heading">Quản lý</div>

    <li class="nav-item <?= isActive('cars') ?>">
        <a class="nav-link" href="<?php echo htmlspecialchars($adminBase); ?>/cars/list.php">
            <i class="fas fa-fw fa-car"></i>
            <span>Quản lý xe</span>
        </a>
    </li>

    <li class="nav-item <?= isActive('brands') ?>">
        <a class="nav-link" href="<?php echo htmlspecialchars($adminBase); ?>/brands/list.php">
            <i class="fas fa-fw fa-tags"></i>
            <span>Hãng xe</span>
        </a>
    </li>

    <li class="nav-item <?= isActive('bookings') ?>">
        <a class="nav-link" href="<?php echo htmlspecialchars($adminBase); ?>/bookings/list.php">
            <i class="fas fa-fw fa-calendar-check"></i>
            <span>Đơn đặt xe</span>
        </a>
    </li>

    <li class="nav-item <?= isActive('users') ?>">
        <a class="nav-link" href="<?php echo htmlspecialchars($adminBase); ?>/users/list.php">
            <i class="fas fa-fw fa-users"></i>
            <span>Người dùng</span>
        </a>
    </li>

    <li class="nav-item <?= isActive('menus') ?>">
        <a class="nav-link" href="<?php echo htmlspecialchars($adminBase); ?>/menus/list.php">
            <i class="fas fa-fw fa-bars"></i>
            <span>Menu</span>
        </a>
    </li>

    <li class="nav-item <?= isActive('blogs') ?>">
        <a class="nav-link" href="<?php echo htmlspecialchars($adminBase); ?>/blogs/list.php">
            <i class="fas fa-fw fa-newspaper"></i>
            <span>Blog</span>
        </a>
    </li>

    <hr class="sidebar-divider">

    <div class="sidebar-heading">Website</div>

    <li class="nav-item">
        <a class="nav-link" href="<?php echo htmlspecialchars($frontendBase); ?>/index.php" target="_blank">
            <i class="fas fa-fw fa-home"></i>
            <span>Xem website</span>
        </a>
    </li>

    <li class="nav-item">
        <a class="nav-link" href="<?php echo htmlspecialchars($backendBase); ?>/api/auth/logout.php">
            <i class="fas fa-fw fa-sign-out-alt"></i>
            <span>Đăng xuất</span>
        </a>
    </li>

    <hr class="sidebar-divider d-none d-md-block">

    <div class="text-center d-none d-md-inline">
        <button class="rounded-circle border-0" id="sidebarToggle"></button>
    </div>

</ul>
