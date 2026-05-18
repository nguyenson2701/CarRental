<?php
require_once '../CarRental_Backend/api/bookings/auto_update_status.php';
require_once '../CarRental_Backend/config/auth.php';
requireAdminOrStaff('login.php');
require_once '../CarRental_Backend/config/database.php';

$currentPage = 'dashboard';

function countTable($conn, $table) {
    $rs = $conn->query("SELECT COUNT(*) AS total FROM `$table`");
    if (!$rs) return 0;
    return (int)$rs->fetch_assoc()['total'];
}

function queryValue($conn, $sql) {
    $rs = $conn->query($sql);
    if (!$rs) return 0;
    $row = $rs->fetch_assoc();
    return $row ? (float)array_values($row)[0] : 0;
}

function money($value) {
    return number_format((float)$value, 0, ',', '.') . ' VNĐ';
}

function bookingStatusText($status) {
    switch ($status) {
        case 'Pending': return 'Chưa xử lý';
        case 'Confirmed': return 'Đã xác nhận';
        case 'Paid': return 'Đã thanh toán';
        case 'Cancelled': return 'Đã hủy';
        case 'Completed': return 'Hoàn thành';
        default: return $status;
    }
}

function bookingBadgeClass($status) {
    switch ($status) {
        case 'Pending': return 'warning';
        case 'Confirmed': return 'info';
        case 'Paid': return 'primary';
        case 'Cancelled': return 'danger';
        case 'Completed': return 'success';
        default: return 'secondary';
    }
}

function carStatusText($status) {
    switch ($status) {
        case 'Available': return 'Sẵn sàng';
        case 'Booked': return 'Đã thuê';
        case 'Maintenance': return 'Bảo dưỡng';
        default: return $status;
    }
}

function userStatusBadgeClass($status) {
    switch ($status) {
        case 'Active': return 'success';
        case 'Inactive': return 'secondary';
        case 'Banned': return 'danger';
        default: return 'secondary';
    }
}

function userStatusText($status) {
    switch ($status) {
        case 'Active': return 'Hoạt động';
        case 'Inactive': return 'Không hoạt động';
        case 'Banned': return 'Bị cấm';
        default: return $status;
    }
}

function carBadgeClass($status) {
    switch ($status) {
        case 'Available': return 'success';
        case 'Booked': return 'danger';
        case 'Maintenance': return 'warning';
        default: return 'secondary';
    }
}

$totalCars     = countTable($conn, 'cars');
$totalBrands   = countTable($conn, 'brands');
$totalUsers    = countTable($conn, 'users');
$totalBookings = countTable($conn, 'bookings');
$totalMenus    = countTable($conn, 'menus');

$pendingBookingsCount = (int)queryValue($conn, "
    SELECT COUNT(*)
    FROM bookings
    WHERE Status = 'Pending'
");
$pendingReturnsCount = (int)queryValue($conn, "
    SELECT COUNT(*)
    FROM bookings
    WHERE ReturnStatus = 'Pending'
");
$pendingFinalPaymentsCount = (int)queryValue($conn, "
    SELECT COUNT(*)
    FROM bookings b
    INNER JOIN payments p ON p.BookingID = b.BookingID
    WHERE b.ReturnStatus = 'Approved'
      AND p.PaymentType = 'Final'
      AND p.Status = 'Pending'
");
$maintenanceCarsCount = (int)queryValue($conn, "
    SELECT COUNT(*)
    FROM cars
    WHERE Status = 'Maintenance'
");

$paidStatusSql = "Status IN ('Paid', 'Completed')";
$cars = $conn->query("
    SELECT 
        CarID, CarName, LicensePlate, Color, Seats, PricePerDay, Status, MainImage
    FROM cars
    ORDER BY CarID DESC
    LIMIT 5
");

$bookings = $conn->query("
    SELECT 
        b.BookingID,
        b.StartDate,
        b.EndDate,
        b.RentalDays,
        b.TotalPrice,
        b.Status,
        b.CreatedAt,
        u.FullName,
        u.Email,
        u.Phone,
        c.CarName,
        c.LicensePlate
    FROM bookings b
    LEFT JOIN users u ON b.UserID = u.UserID
    LEFT JOIN cars c ON b.CarID = c.CarID
    ORDER BY b.BookingID DESC
    LIMIT 5
");

$revenuePeriod = $_GET['revenue_period'] ?? 'month';
if (!in_array($revenuePeriod, ['today', 'month', 'year'], true)) {
    $revenuePeriod = 'month';
}

$revenueSearch = trim($_GET['revenue_q'] ?? '');
$revenueDate = trim($_GET['revenue_date'] ?? '');
if ($revenueDate !== '' && !preg_match('/^\d{4}-\d{2}-\d{2}$/', $revenueDate)) {
    $revenueDate = '';
}
$revenuePeriodSql = "YEAR(b.CreatedAt) = YEAR(CURRENT_DATE())";
$revenuePeriodLabel = 'Năm nay';
if ($revenueDate !== '') {
    $safeRevenueDate = $conn->real_escape_string($revenueDate);
    $revenuePeriodSql = "DATE(b.CreatedAt) = '$safeRevenueDate'";
    $revenuePeriodLabel = 'Ngày ' . date('d/m/Y', strtotime($revenueDate));
} elseif ($revenuePeriod === 'today') {
    $revenuePeriodSql = "DATE(b.CreatedAt) = CURRENT_DATE()";
    $revenuePeriodLabel = 'Hôm nay';
} elseif ($revenuePeriod === 'month') {
    $revenuePeriodSql = "MONTH(b.CreatedAt) = MONTH(CURRENT_DATE()) AND YEAR(b.CreatedAt) = YEAR(CURRENT_DATE())";
    $revenuePeriodLabel = 'Tháng này';
}

$revenueSearchSql = '';
if ($revenueSearch !== '') {
    $keyword = '%' . $conn->real_escape_string($revenueSearch) . '%';
    $revenueSearchSql = "
      AND (
        CAST(b.BookingID AS CHAR) LIKE '$keyword'
        OR u.FullName LIKE '$keyword'
        OR u.Phone LIKE '$keyword'
        OR c.CarName LIKE '$keyword'
        OR c.LicensePlate LIKE '$keyword'
      )
    ";
}

$selectedRevenue = queryValue($conn, "
    SELECT COALESCE(SUM(b.TotalPrice), 0)
    FROM bookings b
    LEFT JOIN users u ON b.UserID = u.UserID
    LEFT JOIN cars c ON b.CarID = c.CarID
    WHERE b.$paidStatusSql
      AND $revenuePeriodSql
      $revenueSearchSql
");
$selectedRevenueCount = (int)queryValue($conn, "
    SELECT COUNT(*)
    FROM bookings b
    LEFT JOIN users u ON b.UserID = u.UserID
    LEFT JOIN cars c ON b.CarID = c.CarID
    WHERE b.$paidStatusSql
      AND $revenuePeriodSql
      $revenueSearchSql
");

$revenueBookings = $conn->query("
    SELECT 
        b.BookingID,
        b.TotalPrice,
        b.Status,
        b.CreatedAt,
        u.FullName,
        u.Phone,
        c.CarName,
        c.LicensePlate
    FROM bookings b
    LEFT JOIN users u ON b.UserID = u.UserID
    LEFT JOIN cars c ON b.CarID = c.CarID
    WHERE b.$paidStatusSql
      AND $revenuePeriodSql
      $revenueSearchSql
    ORDER BY b.CreatedAt DESC, b.BookingID DESC
    LIMIT 20
");
$revenueRows = [];
if ($revenueBookings) {
    while ($row = $revenueBookings->fetch_assoc()) {
        $revenueRows[] = $row;
    }
}

$users = $conn->query("
    SELECT UserID, Avatar, FullName, Email, Phone, RoleID, Status
    FROM users
    ORDER BY UserID DESC
    LIMIT 5
");

?>

<!DOCTYPE html>
<html lang="vi">

<head>
    <meta charset="utf-8">
    <title>Dashboard quản trị</title>
    <meta name="viewport" content="width=device-width, initial-scale=1">

    <link href="vendor/fontawesome-free/css/all.min.css" rel="stylesheet">
    <link href="css/sb-admin-2.min.css" rel="stylesheet">
    <link href="css/admin-dashboard.css" rel="stylesheet">
</head>

<body id="page-top">

<div id="wrapper">

    <?php include 'partials/sidebar.php'; ?>

    <div id="content-wrapper" class="d-flex flex-column">

        <div id="content">

            <?php include 'partials/topbar.php'; ?>

            <div class="container-fluid">

                <div class="dashboard-heading">
                    <div class="dashboard-heading-actions">
                        <a href="bookings/list.php" class="btn btn-danger btn-sm">
                            <i class="fas fa-calendar-check mr-1"></i> Quản lý đơn
                        </a>
                        <a href="cars/list.php" class="btn btn-outline-primary btn-sm">
                            <i class="fas fa-car mr-1"></i> Quản lý xe
                        </a>
                    </div>
                </div>

                <?php if (isset($_GET['updated'])): ?>
                    <div class="alert alert-success alert-dismissible fade show" role="alert">
                        Cập nhật trạng thái đơn đặt xe thành công.
                        <button type="button" class="close" data-dismiss="alert" aria-label="Đóng">
                            <span aria-hidden="true">&times;</span>
                        </button>
                    </div>
                <?php endif; ?>
                <?php if (isset($_GET['final_paid'])): ?>
                    <div class="alert alert-success alert-dismissible fade show" role="alert">
                        Admin đã xác nhận thanh toán cuối và hoàn tất đơn.
                        <button type="button" class="close" data-dismiss="alert" aria-label="Đóng">
                            <span aria-hidden="true">&times;</span>
                        </button>
                    </div>
                <?php endif; ?>

                <section class="dashboard-card notification-card mb-4">
                    <div class="dashboard-card-header">
                        <div>
                            <h2>Thông báo</h2>
                            <p>Các việc cần xử lý trong hệ thống</p>
                        </div>
                        <a href="bookings/list.php" class="btn btn-sm btn-outline-primary">Xem đơn đặt xe</a>
                    </div>
                    <div class="dashboard-card-body">
                        <div class="row notification-list">
                            <div class="col-xl-3 col-md-6 mb-3 mb-xl-0">
                                <a href="bookings/list.php" class="notification-item notification-warning">
                                    <span class="notification-icon"><i class="fas fa-clock"></i></span>
                                    <span>
                                        <strong><?= $pendingBookingsCount ?></strong>
                                        <small>Đơn mới chờ xử lý</small>
                                    </span>
                                </a>
                            </div>
                            <div class="col-xl-3 col-md-6 mb-3 mb-xl-0">
                                <a href="bookings/list.php" class="notification-item notification-info">
                                    <span class="notification-icon"><i class="fas fa-clipboard-check"></i></span>
                                    <span>
                                        <strong><?= $pendingReturnsCount ?></strong>
                                        <small>Đơn chờ kiểm tra trả xe</small>
                                    </span>
                                </a>
                            </div>
                            <div class="col-xl-3 col-md-6 mb-3 mb-md-0">
                                <a href="bookings/list.php" class="notification-item notification-danger">
                                    <span class="notification-icon"><i class="fas fa-cash-register"></i></span>
                                    <span>
                                        <strong><?= $pendingFinalPaymentsCount ?></strong>
                                        <small>Thanh toán cuối chờ xác nhận</small>
                                    </span>
                                </a>
                            </div>
                            <div class="col-xl-3 col-md-6">
                                <a href="cars/list.php" class="notification-item notification-secondary">
                                    <span class="notification-icon"><i class="fas fa-tools"></i></span>
                                    <span>
                                        <strong><?= $maintenanceCarsCount ?></strong>
                                        <small>Xe đang bảo dưỡng</small>
                                    </span>
                                </a>
                            </div>
                        </div>
                    </div>
                </section>

                <div class="row dashboard-stats">

                    <div class="col-xl-3 col-md-6 mb-4">
                        <a href="cars/list.php" class="text-decoration-none">
                            <div class="stat-card stat-primary">
                                <div>
                                    <div class="stat-label">Tổng xe</div>
                                    <div class="stat-value"><?= $totalCars ?></div>
                                </div>
                                <span class="stat-icon"><i class="fas fa-car"></i></span>
                            </div>
                        </a>
                    </div>

                    <div class="col-xl-3 col-md-6 mb-4">
                        <a href="bookings/list.php" class="text-decoration-none">
                            <div class="stat-card stat-danger">
                                <div>
                                    <div class="stat-label">Đơn đặt xe</div>
                                    <div class="stat-value"><?= $totalBookings ?></div>
                                </div>
                                <span class="stat-icon"><i class="fas fa-file-invoice"></i></span>
                            </div>
                        </a>
                    </div>

                    <div class="col-xl-3 col-md-6 mb-4">
                        <a href="users/list.php" class="text-decoration-none">
                            <div class="stat-card stat-warning">
                                <div>
                                    <div class="stat-label">Người dùng</div>
                                    <div class="stat-value"><?= $totalUsers ?></div>
                                </div>
                                <span class="stat-icon"><i class="fas fa-users"></i></span>
                            </div>
                        </a>
                    </div>

                    <div class="col-xl-3 col-md-6 mb-4">
                        <a href="brands/list.php" class="text-decoration-none">
                            <div class="stat-card stat-success">
                                <div>
                                    <div class="stat-label">Hãng xe</div>
                                    <div class="stat-value"><?= $totalBrands ?></div>
                                </div>
                                <span class="stat-icon"><i class="fas fa-tags"></i></span>
                            </div>
                        </a>
                    </div>

                </div>

                <!-- DOANH THU -->
                <section class="dashboard-card revenue-card mb-4">
                    <div class="dashboard-card-header">
                        <div>
                            <h2>Doanh thu</h2>
                            <p><?= htmlspecialchars($revenuePeriodLabel) ?> · <?= $selectedRevenueCount ?> đơn đã thu tiền</p>
                        </div>
                        <div class="revenue-total"><?= money($selectedRevenue) ?></div>
                    </div>
                    <div class="dashboard-card-toolbar">
                        <form method="GET" class="row align-items-end">
                            <div class="col-lg-2 col-md-4 mb-3 mb-lg-0">
                                <label class="font-weight-bold text-gray-800 mb-1">Kỳ doanh thu</label>
                                <select name="revenue_period" class="custom-select" onchange="this.form.submit()">
                                    <option value="today" <?= $revenuePeriod === 'today' ? 'selected' : '' ?>>Hôm nay</option>
                                    <option value="month" <?= $revenuePeriod === 'month' ? 'selected' : '' ?>>Tháng này</option>
                                    <option value="year" <?= $revenuePeriod === 'year' ? 'selected' : '' ?>>Năm nay</option>
                                </select>
                            </div>

                            <div class="col-lg-2 col-md-4 mb-3 mb-lg-0">
                                <label class="font-weight-bold text-gray-800 mb-1">Ngày thu</label>
                                <input type="date"
                                       name="revenue_date"
                                       class="form-control"
                                       value="<?= htmlspecialchars($revenueDate) ?>"
                                       onchange="this.form.submit()">
                            </div>

                            <div class="col-lg-8 col-md-4 mb-3 mb-lg-0">
                                <label class="font-weight-bold text-gray-800 mb-1">Tìm kiếm đơn</label>
                                <div class="input-group">
                                    <input type="text"
                                           name="revenue_q"
                                           class="form-control"
                                           value="<?= htmlspecialchars($revenueSearch) ?>"
                                           placeholder="Nhập mã đơn, khách hàng, số điện thoại, xe hoặc biển số">
                                    <div class="input-group-append">
                                        <button class="btn btn-dark" type="submit">
                                            <i class="fas fa-search mr-1"></i> Tìm
                                        </button>
                                    </div>
                                </div>
                            </div>

                        </form>
                    </div>

                    <div class="dashboard-card-body p-0">
                        <div class="table-responsive revenue-list-scroll">
                        <table class="table admin-table table-hover table-sm mb-0">
                            <thead class="thead-light">
                                <tr>
                                    <th>Mã đơn</th>
                                    <th>Khách hàng</th>
                                    <th>Xe</th>
                                    <th>Ngày thu</th>
                                    <th class="text-right">Số tiền</th>
                                    <th>Trạng thái</th>
                                    <th>Chi tiết</th>
                                </tr>
                            </thead>
                            <tbody>
                            <?php if (!empty($revenueRows)): ?>
                                <?php foreach ($revenueRows as $row): ?>
                                    <tr>
                                        <td><strong>#<?= (int)$row['BookingID'] ?></strong></td>
                                        <td class="revenue-compact-text">
                                            <?= htmlspecialchars($row['FullName'] ?? 'N/A') ?><br>
                                            <small class="text-muted"><?= htmlspecialchars($row['Phone'] ?? '') ?></small>
                                        </td>
                                        <td class="revenue-compact-text">
                                            <?= htmlspecialchars($row['CarName'] ?? 'N/A') ?><br>
                                            <small class="text-muted"><?= htmlspecialchars($row['LicensePlate'] ?? '') ?></small>
                                        </td>
                                        <td><?= htmlspecialchars($row['CreatedAt']) ?></td>
                                        <td class="text-right font-weight-bold text-success"><?= money($row['TotalPrice']) ?></td>
                                        <td>
                                            <span class="badge badge-<?= bookingBadgeClass($row['Status']) ?>">
                                                <?= bookingStatusText($row['Status']) ?>
                                            </span>
                                        </td>
                                        <td>
                                            <a href="bookings/detail.php?id=<?= (int)$row['BookingID'] ?>"
                                               class="btn btn-info btn-sm">
                                                <i class="fas fa-eye mr-1"></i> Xem
                                            </a>
                                        </td>
                                    </tr>
                                <?php endforeach; ?>
                            <?php else: ?>
                                <tr>
                                    <td colspan="7" class="text-center text-muted">Không tìm thấy đơn doanh thu phù hợp</td>
                                </tr>
                            <?php endif; ?>
                            </tbody>
                        </table>
                        </div>
                    </div>
                </section>

                <div class="row dashboard-content">
                    <!-- XE -->
                    <div class="col-xl-6 mb-4">
                        <div class="dashboard-card h-100">
                            <div class="dashboard-card-header">
                                <div>
                                    <h2>Xe mới nhất</h2>
                                    <p>5 xe được thêm gần đây</p>
                                </div>
                                <a href="cars/list.php" class="btn btn-sm btn-outline-primary">Xem tất cả</a>
                            </div>

                            <div class="dashboard-card-body table-responsive p-0">
                                <table class="table admin-table table-hover mb-0">
                            <thead class="thead-light">
                                <tr>
                                    <th>ID</th>
                                    <th>Ảnh</th>
                                    <th>Tên xe</th>
                                    <th>Biển số</th>
                                    <th>Màu</th>
                                    <th>Số ghế</th>
                                    <th>Giá/ngày</th>
                                    <th>Trạng thái</th>
                                </tr>
                            </thead>
                            <tbody>
                            <?php if ($cars && $cars->num_rows > 0): ?>
                                <?php while ($row = $cars->fetch_assoc()): ?>
                                    <tr>
                                        <td><?= $row['CarID'] ?></td>
                                        <td>
                                            <?php if (!empty($row['MainImage'])): ?>
                                                <img class="admin-car-thumb" src="../CarRental_Frontend/assets/img/cars/<?= htmlspecialchars($row['MainImage']) ?>" alt="Car">
                                            <?php else: ?>
                                                <span class="text-muted">Chưa có ảnh</span>
                                            <?php endif; ?>
                                        </td>
                                        <td><?= htmlspecialchars($row['CarName']) ?></td>
                                        <td><?= htmlspecialchars($row['LicensePlate']) ?></td>
                                        <td><?= htmlspecialchars($row['Color']) ?></td>
                                        <td><?= htmlspecialchars($row['Seats']) ?></td>
                                        <td><?= money($row['PricePerDay']) ?></td>
                                        <td>
                                            <span class="badge badge-<?= carBadgeClass($row['Status']) ?>">
                                                <?= carStatusText($row['Status']) ?>
                                            </span>
                                        </td>
                                    </tr>
                                <?php endwhile; ?>
                            <?php else: ?>
                                <tr><td colspan="8" class="text-center">Không có dữ liệu xe</td></tr>
                            <?php endif; ?>
                            </tbody>
                        </table>
                            </div>
                        </div>
                    </div>

                    <!-- BOOKING -->
                    <div class="col-xl-6 mb-4">
                        <div class="dashboard-card h-100">
                            <div class="dashboard-card-header">
                                <div>
                                    <h2>Đơn gần đây</h2>
                                    <p>5 đơn đặt xe mới nhất</p>
                                </div>
                                <a href="bookings/list.php" class="btn btn-sm btn-outline-danger">Xem tất cả</a>
                            </div>

                            <div class="dashboard-card-body table-responsive p-0">
                                <table class="table admin-table table-hover mb-0">
                            <thead class="thead-light">
                                <tr>
                                    <th>Mã đơn</th>
                                    <th>Khách hàng</th>
                                    <th>Xe</th>
                                    <th>Ngày thuê</th>
                                    <th>Tổng tiền</th>
                                    <th>Trạng thái</th>
                                </tr>
                            </thead>
                            <tbody>
                            <?php if ($bookings && $bookings->num_rows > 0): ?>
                                <?php while ($row = $bookings->fetch_assoc()): ?>
                                    <tr>
                                        <td>
                                            <strong>#<?= $row['BookingID'] ?></strong><br>
                                            <small><?= htmlspecialchars($row['CreatedAt']) ?></small>
                                        </td>
                                        <td>
                                            <strong><?= htmlspecialchars($row['FullName'] ?? 'N/A') ?></strong><br>
                                            <small><?= htmlspecialchars($row['Email'] ?? '') ?></small>
                                        </td>
                                        <td>
                                            <?= htmlspecialchars($row['CarName'] ?? 'N/A') ?><br>
                                            <small><?= htmlspecialchars($row['LicensePlate'] ?? '') ?></small>
                                        </td>
                                        <td>
                                            <?= htmlspecialchars($row['StartDate']) ?> -
                                            <?= htmlspecialchars($row['EndDate']) ?><br>
                                            <small><?= (int)$row['RentalDays'] ?> ngày</small>
                                        </td>
                                        <td><?= money($row['TotalPrice']) ?></td>
                                        <td>
                                            <span class="badge badge-<?= bookingBadgeClass($row['Status']) ?>">
                                                <?= bookingStatusText($row['Status']) ?>
                                            </span>
                                        </td>
                                    </tr>
                                <?php endwhile; ?>
                            <?php else: ?>
                                <tr><td colspan="6" class="text-center">Chưa có đơn đặt xe</td></tr>
                            <?php endif; ?>
                            </tbody>
                        </table>
                            </div>
                        </div>
                    </div>
                </div>

                <!-- USER -->
                <div class="dashboard-card mb-4">
                    <div class="dashboard-card-header">
                        <div>
                            <h2>Người dùng mới nhất</h2>
                            <p>5 tài khoản được tạo gần đây</p>
                        </div>
                        <a href="users/list.php" class="btn btn-sm btn-outline-warning">Xem tất cả</a>
                    </div>

                    <div class="dashboard-card-body table-responsive p-0">
                        <table class="table admin-table table-hover mb-0">
                            <thead class="thead-light">
                                <tr>
                                    <th>ID</th>
                                    <th>Avatar</th>
                                    <th>Họ tên</th>
                                    <th>Email</th>
                                    <th>Điện thoại</th>
                                    <th>Quyền</th>
                                    <th>Trạng thái</th>
                                </tr>
                            </thead>
                            <tbody>
                            <?php if ($users && $users->num_rows > 0): ?>
                                <?php while ($row = $users->fetch_assoc()): ?>
                                    <tr>
                                        <td><?= $row['UserID'] ?></td>
                                        <td>
                                            <?php if (!empty($row['Avatar'])): ?>
                                                <img class="admin-avatar-thumb" src="../CarRental_Frontend/assets/img/avatars/<?= htmlspecialchars($row['Avatar']) ?>" alt="Avatar">
                                            <?php else: ?>
                                                Chưa có
                                            <?php endif; ?>
                                        </td>
                                        <td><?= htmlspecialchars($row['FullName']) ?></td>
                                        <td><?= htmlspecialchars($row['Email']) ?></td>
                                        <td><?= htmlspecialchars($row['Phone']) ?></td>
                                        <td>
                                            <?php
                                            if ($row['RoleID'] == 1) echo 'Quản trị viên';
                                            elseif ($row['RoleID'] == 2) echo 'Nhân viên';
                                            else echo 'Khách hàng';
                                            ?>
                                        </td>
                                        <td>
                                            <span class="badge badge-<?= userStatusBadgeClass($row['Status']) ?>">
                                                <?= userStatusText($row['Status']) ?>
                                            </span>
                                        </td>
                                    </tr>
                                <?php endwhile; ?>
                            <?php else: ?>
                                <tr><td colspan="7" class="text-center">Không có dữ liệu người dùng</td></tr>
                            <?php endif; ?>
                            </tbody>
                        </table>
                    </div>
                </div>
            </div>
        </div>

        <?php include 'partials/footer.php'; ?>

    </div>
</div>

<script src="vendor/jquery/jquery.min.js"></script>
<script src="vendor/bootstrap/js/bootstrap.bundle.min.js"></script>
<script src="vendor/jquery-easing/jquery.easing.min.js"></script>
<script src="js/sb-admin-2.min.js"></script>

</body>
</html>
