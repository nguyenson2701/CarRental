<?php
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

require_once __DIR__ . '/../../CarRental_Backend/config/database.php';

if (!isset($pageTitle)) $pageTitle = 'VinaDrive';
if (!isset($activePage)) $activePage = '';

/* Lấy menu cấp 1 đang bật */
$menuResult = $conn->query("
    SELECT MenuID, MenuName, URL, ParentID, DisplayOrder, IsActive
    FROM menus
    WHERE IsActive = 1 AND (ParentID IS NULL OR ParentID = 0)
    ORDER BY DisplayOrder ASC, MenuID ASC
");

$headerNotifications = [];
$headerNotificationCount = 0;
if (!empty($_SESSION['user_id'])) {
    $headerUserID = (int)$_SESSION['user_id'];

    $stmtHeaderPayments = $conn->prepare("
        SELECT COUNT(*) AS total
        FROM payments p
        INNER JOIN bookings b ON p.BookingID = b.BookingID
        WHERE b.UserID = ?
          AND p.Status = 'Pending'
    ");
    $stmtHeaderPayments->bind_param('i', $headerUserID);
    $stmtHeaderPayments->execute();
    $pendingPayments = (int)($stmtHeaderPayments->get_result()->fetch_assoc()['total'] ?? 0);

    if ($pendingPayments > 0) {
        $headerNotifications[] = [
            'icon' => 'fa-credit-card',
            'text' => $pendingPayments . ' khoản thanh toán đang chờ',
            'url' => 'my-payments.php',
        ];
        $headerNotificationCount += $pendingPayments;
    }

    $stmtHeaderBookings = $conn->prepare("
        SELECT COUNT(*) AS total
        FROM bookings
        WHERE UserID = ?
          AND Status IN ('Pending', 'Confirmed')
    ");
    $stmtHeaderBookings->bind_param('i', $headerUserID);
    $stmtHeaderBookings->execute();
    $activeBookings = (int)($stmtHeaderBookings->get_result()->fetch_assoc()['total'] ?? 0);

    if ($activeBookings > 0) {
        $headerNotifications[] = [
            'icon' => 'fa-calendar-check',
            'text' => $activeBookings . ' đơn đặt xe đang hoạt động',
            'url' => 'my-bookings.php',
        ];
        $headerNotificationCount += $activeBookings;
    }

    $stmtHeaderReturns = $conn->prepare("
        SELECT COUNT(*) AS total
        FROM bookings
        WHERE UserID = ?
          AND Status = 'Confirmed'
          AND (ReturnStatus IS NULL OR ReturnStatus = '' OR ReturnStatus = 'NotReturned')
          AND EndDate <= CURDATE()
    ");
    $stmtHeaderReturns->bind_param('i', $headerUserID);
    $stmtHeaderReturns->execute();
    $returnableBookings = (int)($stmtHeaderReturns->get_result()->fetch_assoc()['total'] ?? 0);

    if ($returnableBookings > 0) {
        $headerNotifications[] = [
            'icon' => 'fa-undo-alt',
            'text' => $returnableBookings . ' đơn có thể gửi trả xe',
            'url' => 'my-bookings.php',
        ];
        $headerNotificationCount += $returnableBookings;
    }
}
?>
<!DOCTYPE html>
<html lang="vi">
<head>
    <meta charset="utf-8">
    <title><?php echo htmlspecialchars($pageTitle); ?></title>
    <meta content="width=device-width, initial-scale=1.0" name="viewport">

    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Lato:ital,wght@0,400;0,700;0,900;1,400;1,700;1,900&family=Montserrat:ital,wght@0,100..900;1,100..900&display=swap" rel="stylesheet">

    <link rel="stylesheet" href="https://use.fontawesome.com/releases/v5.15.4/css/all.css"/>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.4.1/font/bootstrap-icons.css" rel="stylesheet">

    <link href="lib/animate/animate.min.css" rel="stylesheet">
    <link href="lib/owlcarousel/assets/owl.carousel.min.css" rel="stylesheet">
    <link href="assets/css/bootstrap.min.css" rel="stylesheet">
    <link href="assets/css/style.css?v=5" rel="stylesheet">

    <?php if (!empty($pageStyles)): ?>
        <?php foreach ($pageStyles as $style): ?>
            <link href="<?php echo htmlspecialchars($style); ?>" rel="stylesheet">
        <?php endforeach; ?>
    <?php endif; ?>
</head>
<body>

<div id="spinner" class="bg-white position-fixed translate-middle w-100 vh-100 top-50 start-50 d-none align-items-center justify-content-center">
    <div class="spinner-border text-primary" style="width: 3rem; height: 3rem;" role="status">
        <span class="sr-only">Loading...</span>
    </div>
</div>

<div class="container-fluid topbar bg-secondary d-none d-xl-block w-100">
    <div class="container">
        <div class="row gx-0 align-items-center" style="height: 45px;">
            <div class="col-lg-6 text-center text-lg-start mb-lg-0">
                <div class="d-flex flex-wrap">
                    <a href="#" class="text-muted me-4">
                        <i class="fas fa-map-marker-alt text-primary me-2"></i>20 Nguyễn Duy Trinh, Nghệ An
                    </a>
                    <a href="tel:+01234567890" class="text-muted me-4">
                        <i class="fas fa-phone-alt text-primary me-2"></i>+01234567890
                    </a>
                    <a href="mailto:example@gmail.com" class="text-muted me-0">
                        <i class="fas fa-envelope text-primary me-2"></i>example@gmail.com
                    </a>
                </div>
            </div>
            <div class="col-lg-6 text-center text-lg-end">
                <div class="d-flex align-items-center justify-content-end">
                    <a href="#" class="btn btn-light btn-sm-square rounded-circle me-3"><i class="fab fa-facebook-f"></i></a>
                    <a href="#" class="btn btn-light btn-sm-square rounded-circle me-3"><i class="fab fa-tiktok"></i></a>
                    <a href="#" class="btn btn-light btn-sm-square rounded-circle me-3"><i class="fab fa-instagram"></i></a>
                    <a href="#" class="btn btn-light btn-sm-square rounded-circle me-0"><i class="fab fa-linkedin-in"></i></a>
                </div>
            </div>
        </div>
    </div>
</div>

<div class="container-fluid nav-bar sticky-top px-0 px-lg-4 py-2 py-lg-0">
    <div class="container">
        <nav class="navbar navbar-expand-lg navbar-light">
            <a href="index.php" class="navbar-brand p-0">
                <h1 class="display-6 text-primary">
                    <i class="fas fa-car-alt me-3"></i>VinaDrive
                </h1>
            </a>

            <button class="navbar-toggler" type="button" data-bs-toggle="collapse" data-bs-target="#navbarCollapse">
                <span class="fa fa-bars"></span>
            </button>

            <div class="collapse navbar-collapse" id="navbarCollapse">
                <div class="navbar-nav mx-auto py-0">
                    <?php if ($menuResult && $menuResult->num_rows > 0): ?>
                        <?php while ($menu = $menuResult->fetch_assoc()): ?>
                            <?php
                                $menuName = trim($menu['MenuName'] ?? '');
                                $menuUrl  = trim($menu['URL'] ?? '#');

                                $isActive = false;
                                $activePageNormalized = strtolower(trim($activePage));
                                $menuNameNormalized   = strtolower($menuName);
                                $menuFileNormalized   = strtolower(trim(pathinfo($menuUrl, PATHINFO_FILENAME)));

                                if ($activePageNormalized !== '') {
                                    if ($activePageNormalized === $menuNameNormalized || $activePageNormalized === $menuFileNormalized) {
                                        $isActive = true;
                                    }
                                } else {
                                    $currentFile = basename(parse_url($_SERVER['REQUEST_URI'] ?? '', PHP_URL_PATH));
                                    if ($currentFile === basename($menuUrl)) {
                                        $isActive = true;
                                    }
                                }
                            ?>
                            <a href="<?php echo htmlspecialchars($menuUrl); ?>"
                               class="nav-item nav-link <?php echo $isActive ? 'active' : ''; ?>">
                                <?php echo htmlspecialchars($menuName); ?>
                            </a>
                        <?php endwhile; ?>
                    <?php else: ?>
                        <a href="index.php" class="nav-item nav-link <?php echo ($activePage === 'trang chủ' || $activePage === 'index') ? 'active' : ''; ?>">Trang chủ</a>
                        <a href="about.php" class="nav-item nav-link <?php echo ($activePage === 'giới thiệu' || $activePage === 'about') ? 'active' : ''; ?>">Giới thiệu</a>
                        <a href="vehicle.php" class="nav-item nav-link <?php echo ($activePage === 'thuê xe' || $activePage === 'vehicle') ? 'active' : ''; ?>">Thuê xe</a>
                        <a href="blog.php" class="nav-item nav-link <?php echo ($activePage === 'blog') ? 'active' : ''; ?>">Blog</a>
                        <a href="contact.php" class="nav-item nav-link <?php echo ($activePage === 'liên hệ' || $activePage === 'contact') ? 'active' : ''; ?>">Liên hệ</a>
                    <?php endif; ?>
                </div>

                <div class="d-flex align-items-center gap-2 header-actions">
                    <?php if (!empty($_SESSION['user_id'])): ?>

                        <div class="dropdown notification-menu">
                            <button class="btn btn-outline-primary header-icon-btn position-relative"
                                    type="button"
                                    data-bs-toggle="dropdown"
                                    aria-expanded="false"
                                    title="Thong bao">
                                <i class="fas fa-bell"></i>
                                <?php if ($headerNotificationCount > 0): ?>
                                    <span class="header-badge"><?php echo min($headerNotificationCount, 99); ?></span>
                                <?php endif; ?>
                            </button>
                            <div class="dropdown-menu dropdown-menu-end notification-dropdown shadow">
                                <div class="dropdown-header fw-bold text-dark">Thông báo</div>
                                <?php if (!empty($headerNotifications)): ?>
                                    <?php foreach ($headerNotifications as $notice): ?>
                                        <a class="dropdown-item notification-item" href="<?php echo htmlspecialchars($notice['url']); ?>">
                                            <i class="fas <?php echo htmlspecialchars($notice['icon']); ?> text-primary me-2"></i>
                                            <span><?php echo htmlspecialchars($notice['text']); ?></span>
                                        </a>
                                    <?php endforeach; ?>
                                <?php else: ?>
                                    <div class="dropdown-item-text text-muted small">Chưa có thông báo mới.</div>
                                <?php endif; ?>
                            </div>
                        </div>

                        <div class="dropdown user-menu">
                            <button class="btn btn-primary header-user-btn"
                                    type="button"
                                    data-bs-toggle="dropdown"
                                    aria-expanded="false">
                                <i class="fas fa-user"></i>
                                <span class="header-user-name"><?php echo htmlspecialchars($_SESSION['name'] ?? 'Tai khoan'); ?></span>
                                <i class="fas fa-chevron-down small"></i>
                            </button>
                            <div class="dropdown-menu dropdown-menu-end user-dropdown shadow">
                                <a class="dropdown-item" href="profile.php">
                                    <i class="fas fa-user-circle me-2 text-primary"></i> Tài khoản
                                </a>
                                <a class="dropdown-item" href="my-bookings.php">
                                    <i class="fas fa-calendar-check me-2 text-primary"></i> Đơn đặt xe
                                </a>
                                <a class="dropdown-item" href="my-payments.php">
                                    <i class="fas fa-credit-card me-2 text-primary"></i> Thanh toán
                                </a>

                                <?php if ((int)($_SESSION['role_id'] ?? 0) === 1 || (int)($_SESSION['role_id'] ?? 0) === 2): ?>
                                    <div class="dropdown-divider"></div>
                                    <a class="dropdown-item" href="../CarRental_Admin/index.php">
                                        <i class="fas fa-tools me-2 text-danger"></i> Trang quản trị
                                    </a>
                                <?php endif; ?>

                                <div class="dropdown-divider"></div>
                                <a class="dropdown-item text-danger" href="../CarRental_Backend/api/auth/logout.php">
                                    <i class="fas fa-sign-out-alt me-2"></i> Đăng xuất
                                </a>
                            </div>
                        </div>

                    <?php else: ?>
                        <a href="login.php" class="btn btn-primary rounded-pill py-2 px-4">
                            Đăng nhập
                        </a>
                    <?php endif; ?>
                </div>
            </div>
        </nav>
    </div>
</div>
