<?php
/**
 * Layout khung Frontend dung chung, port tu
 * CarRental_Frontend/includes/{header,footer}.php cu (giu nguyen menu
 * DB-driven, thong bao header, dropdown tai khoan, AI chat widget).
 *
 * Bien $content, $pageTitle, $activePage duoc Controller::view() truyen
 * vao. $pageStyles/$pageScripts (mang duong dan) la tuy chon cho tung
 * trang rieng.
 */

use App\Core\Database;

$pageTitle = $pageTitle ?? 'VinaDrive';
$activePage = $activePage ?? '';
$pageStyles = $pageStyles ?? [];
$pageScripts = $pageScripts ?? [];

$db = Database::connection();

$menuResult = $db->query("
    SELECT MenuID, MenuName, URL, ParentID, DisplayOrder, IsActive
    FROM menus
    WHERE IsActive = 1 AND (ParentID IS NULL OR ParentID = 0)
    ORDER BY DisplayOrder ASC, MenuID ASC
");

$headerNotifications = [];
$headerNotificationCount = 0;
if (!empty($_SESSION['user_id'])) {
    $headerUserID = (int) $_SESSION['user_id'];

    $stmtHeaderPayments = $db->prepare("
        SELECT COUNT(*) AS total FROM payments p
        INNER JOIN bookings b ON p.BookingID = b.BookingID
        WHERE b.UserID = ? AND p.Status = 'Pending'
    ");
    $stmtHeaderPayments->bind_param('i', $headerUserID);
    $stmtHeaderPayments->execute();
    $pendingPayments = (int) ($stmtHeaderPayments->get_result()->fetch_assoc()['total'] ?? 0);

    if ($pendingPayments > 0) {
        $headerNotifications[] = ['icon' => 'fa-credit-card', 'text' => $pendingPayments . ' khoản thanh toán đang chờ', 'url' => '/Carrental/my-payments'];
        $headerNotificationCount += $pendingPayments;
    }

    $stmtHeaderBookings = $db->prepare("SELECT COUNT(*) AS total FROM bookings WHERE UserID = ? AND Status IN ('Pending', 'Confirmed')");
    $stmtHeaderBookings->bind_param('i', $headerUserID);
    $stmtHeaderBookings->execute();
    $activeBookings = (int) ($stmtHeaderBookings->get_result()->fetch_assoc()['total'] ?? 0);

    if ($activeBookings > 0) {
        $headerNotifications[] = ['icon' => 'fa-calendar-check', 'text' => $activeBookings . ' đơn đặt xe đang hoạt động', 'url' => '/Carrental/my-bookings'];
        $headerNotificationCount += $activeBookings;
    }

    $stmtHeaderReturns = $db->prepare("
        SELECT COUNT(*) AS total FROM bookings
        WHERE UserID = ? AND Status = 'Confirmed'
          AND (ReturnStatus IS NULL OR ReturnStatus = '' OR ReturnStatus = 'NotReturned')
          AND EndDate <= CURDATE()
    ");
    $stmtHeaderReturns->bind_param('i', $headerUserID);
    $stmtHeaderReturns->execute();
    $returnableBookings = (int) ($stmtHeaderReturns->get_result()->fetch_assoc()['total'] ?? 0);

    if ($returnableBookings > 0) {
        $headerNotifications[] = ['icon' => 'fa-undo-alt', 'text' => $returnableBookings . ' đơn có thể gửi trả xe', 'url' => '/Carrental/my-bookings'];
        $headerNotificationCount += $returnableBookings;
    }
}
?>
<!DOCTYPE html>
<html lang="vi">
<head>
    <meta charset="utf-8">
    <title><?= htmlspecialchars($pageTitle) ?></title>
    <meta content="width=device-width, initial-scale=1.0" name="viewport">

    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Lato:ital,wght@0,400;0,700;0,900;1,400;1,700;1,900&family=Montserrat:ital,wght@0,100..900;1,100..900&display=swap" rel="stylesheet">

    <link rel="stylesheet" href="https://use.fontawesome.com/releases/v5.15.4/css/all.css"/>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.4.1/font/bootstrap-icons.css" rel="stylesheet">

    <link href="/Carrental/CarRental_Frontend/lib/animate/animate.min.css" rel="stylesheet">
    <link href="/Carrental/CarRental_Frontend/lib/owlcarousel/assets/owl.carousel.min.css" rel="stylesheet">
    <link href="/Carrental/CarRental_Frontend/assets/css/bootstrap.min.css" rel="stylesheet">
    <link href="/Carrental/CarRental_Frontend/assets/css/style.css?v=8" rel="stylesheet">

    <?php foreach ($pageStyles as $style): ?>
        <link href="<?= htmlspecialchars($style) ?>" rel="stylesheet">
    <?php endforeach; ?>
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
                    <a href="#" class="text-muted me-4"><i class="fas fa-map-marker-alt text-primary me-2"></i>20 Nguyễn Duy Trinh, Nghệ An</a>
                    <a href="tel:+01234567890" class="text-muted me-4"><i class="fas fa-phone-alt text-primary me-2"></i>+01234567890</a>
                    <a href="mailto:example@gmail.com" class="text-muted me-0"><i class="fas fa-envelope text-primary me-2"></i>example@gmail.com</a>
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
            <a href="/Carrental/" class="navbar-brand p-0">
                <h1 class="display-6 text-primary"><i class="fas fa-car-alt me-3"></i>VinaDrive</h1>
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
                                $menuUrl = trim($menu['URL'] ?? '#');
                                $activePageNormalized = strtolower(trim($activePage));
                                $menuNameNormalized = strtolower($menuName);
                                $menuFileNormalized = strtolower(trim(pathinfo($menuUrl, PATHINFO_FILENAME)));
                                $isActive = $activePageNormalized !== '' && ($activePageNormalized === $menuNameNormalized || $activePageNormalized === $menuFileNormalized);
                            ?>
                            <a href="<?= htmlspecialchars($menuUrl) ?>" class="nav-item nav-link <?= $isActive ? 'active' : '' ?>"><?= htmlspecialchars($menuName) ?></a>
                        <?php endwhile; ?>
                    <?php else: ?>
                        <a href="/Carrental/" class="nav-item nav-link <?= ($activePage === 'index') ? 'active' : '' ?>">Trang chủ</a>
                        <a href="/Carrental/about" class="nav-item nav-link <?= ($activePage === 'about') ? 'active' : '' ?>">Giới thiệu</a>
                        <a href="/Carrental/vehicle" class="nav-item nav-link <?= ($activePage === 'vehicle') ? 'active' : '' ?>">Thuê xe</a>
                        <a href="/Carrental/blog" class="nav-item nav-link <?= ($activePage === 'blog') ? 'active' : '' ?>">Blog</a>
                        <a href="/Carrental/contact" class="nav-item nav-link <?= ($activePage === 'contact') ? 'active' : '' ?>">Liên hệ</a>
                    <?php endif; ?>
                </div>

                <div class="d-flex align-items-center gap-2 header-actions">
                    <?php if (!empty($_SESSION['user_id'])): ?>
                        <div class="dropdown notification-menu">
                            <button class="btn btn-outline-primary header-icon-btn position-relative" type="button" data-bs-toggle="dropdown" aria-expanded="false" title="Thong bao">
                                <i class="fas fa-bell"></i>
                                <?php if ($headerNotificationCount > 0): ?>
                                    <span class="header-badge"><?= min($headerNotificationCount, 99) ?></span>
                                <?php endif; ?>
                            </button>
                            <div class="dropdown-menu dropdown-menu-end notification-dropdown shadow">
                                <div class="dropdown-header fw-bold text-dark">Thông báo</div>
                                <?php if (!empty($headerNotifications)): ?>
                                    <?php foreach ($headerNotifications as $notice): ?>
                                        <a class="dropdown-item notification-item" href="<?= htmlspecialchars($notice['url']) ?>">
                                            <i class="fas <?= htmlspecialchars($notice['icon']) ?> text-primary me-2"></i>
                                            <span><?= htmlspecialchars($notice['text']) ?></span>
                                        </a>
                                    <?php endforeach; ?>
                                <?php else: ?>
                                    <div class="dropdown-item-text text-muted small">Chưa có thông báo mới.</div>
                                <?php endif; ?>
                            </div>
                        </div>

                        <div class="dropdown user-menu">
                            <button class="btn btn-primary header-user-btn" type="button" data-bs-toggle="dropdown" aria-expanded="false">
                                <i class="fas fa-user"></i>
                                <span class="header-user-name"><?= htmlspecialchars($_SESSION['name'] ?? 'Tai khoan') ?></span>
                                <i class="fas fa-chevron-down small"></i>
                            </button>
                            <div class="dropdown-menu dropdown-menu-end user-dropdown shadow">
                                <a class="dropdown-item" href="/Carrental/profile"><i class="fas fa-user-circle me-2 text-primary"></i> Tài khoản</a>
                                <a class="dropdown-item" href="/Carrental/my-bookings"><i class="fas fa-calendar-check me-2 text-primary"></i> Đơn đặt xe</a>
                                <a class="dropdown-item" href="/Carrental/my-payments"><i class="fas fa-credit-card me-2 text-primary"></i> Thanh toán</a>

                                <?php if ((int) ($_SESSION['role_id'] ?? 0) === 1 || (int) ($_SESSION['role_id'] ?? 0) === 2): ?>
                                    <div class="dropdown-divider"></div>
                                    <a class="dropdown-item" href="/Carrental/admin/dashboard"><i class="fas fa-tools me-2 text-danger"></i> Trang quản trị</a>
                                <?php endif; ?>

                                <div class="dropdown-divider"></div>
                                <a class="dropdown-item text-danger" href="/Carrental/logout"><i class="fas fa-sign-out-alt me-2"></i> Đăng xuất</a>
                            </div>
                        </div>
                    <?php else: ?>
                        <a href="/Carrental/login" class="btn btn-primary rounded-pill py-2 px-4">Đăng nhập</a>
                    <?php endif; ?>
                </div>
            </div>
        </nav>
    </div>
</div>

<?= $content ?>

<div class="container-fluid footer py-5 wow fadeIn" data-wow-delay="0.2s">
    <div class="container py-5">
        <div class="row g-5">
            <div class="col-md-6 col-lg-4">
                <h5 class="text-white mb-4">VinaDrive</h5>
                <p class="text-white-50">Dịch vụ thuê xe nhanh chóng, an toàn và tiện lợi.</p>
            </div>
            <div class="col-md-6 col-lg-4">
                <h5 class="text-white mb-4">Liên hệ</h5>
                <p class="text-white-50 mb-2"><i class="fa fa-map-marker-alt me-2"></i>20 Nguyễn Duy Trinh, Nghệ An</p>
                <p class="text-white-50 mb-2"><i class="fa fa-phone-alt me-2"></i>+01234567890</p>
                <p class="text-white-50 mb-0"><i class="fa fa-envelope me-2"></i>example@gmail.com</p>
            </div>
            <div class="col-md-6 col-lg-4">
                <h5 class="text-white mb-4">Điều hướng</h5>
                <a class="btn btn-link text-white-50" href="/Carrental/">Trang chủ</a>
                <a class="btn btn-link text-white-50" href="/Carrental/about">Giới thiệu</a>
                <a class="btn btn-link text-white-50" href="/Carrental/vehicle">Thuê xe</a>
                <a class="btn btn-link text-white-50" href="/Carrental/contact">Liên hệ</a>
            </div>
        </div>
    </div>
</div>

<div class="container-fluid copyright py-4">
    <div class="container text-center">
        <span>© <?= date('Y') ?> VinaDrive. All rights reserved.</span>
    </div>
</div>

<div class="ai-chat-widget" data-ai-chat>
    <button class="ai-chat-toggle" type="button" aria-label="Mở trợ lý AI" aria-expanded="false">
        <i class="fas fa-comments"></i>
        <span class="ai-chat-notice">AI</span>
    </button>
    <section class="ai-chat-panel" aria-label="Trợ lý AI VinaDrive" hidden>
        <div class="ai-chat-header">
            <div class="ai-chat-avatar"><i class="fas fa-robot"></i></div>
            <div>
                <h6>Trợ lý AI VinaDrive</h6>
                <p>Sẵn sàng hỗ trợ khách hàng</p>
            </div>
            <button class="ai-chat-close" type="button" aria-label="Đóng trợ lý AI"><i class="fas fa-times"></i></button>
        </div>
        <div class="ai-chat-body" role="log" aria-live="polite">
            <div class="ai-message ai-message-bot"><span>Xin chào! Mình có thể tư vấn cách đặt xe, giấy tờ cần thiết, thanh toán và trả xe. Bạn cần hỗ trợ gì?</span></div>
        </div>
        <div class="ai-chat-suggestions" aria-label="Câu hỏi gợi ý">
            <button type="button" data-ai-question="Có xe nào còn trống?">Xe còn trống</button>
            <button type="button" data-ai-question="Có xe nào dưới 1 triệu mỗi ngày?">Dưới 1 triệu</button>
            <button type="button" data-ai-question="Đơn của tôi">Đơn của tôi</button>
        </div>
        <form class="ai-chat-form">
            <input type="text" name="message" autocomplete="off" placeholder="Nhập câu hỏi của bạn..." aria-label="Nhập câu hỏi">
            <button type="submit" aria-label="Gửi câu hỏi"><i class="fas fa-paper-plane"></i></button>
        </form>
    </section>
</div>

<script src="https://ajax.googleapis.com/ajax/libs/jquery/3.6.4/jquery.min.js"></script>
<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.0.0/dist/js/bootstrap.bundle.min.js"></script>
<script src="/Carrental/CarRental_Frontend/lib/wow/wow.min.js"></script>
<script src="/Carrental/CarRental_Frontend/lib/owlcarousel/owl.carousel.min.js"></script>
<script src="/Carrental/CarRental_Frontend/assets/js/main.js?v=8"></script>
<?php foreach ($pageScripts as $script): ?>
    <script src="<?= htmlspecialchars($script) ?>"></script>
<?php endforeach; ?>
</body>
</html>
