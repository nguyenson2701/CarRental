<?php
require_once '../../CarRental_Backend/api/bookings/auto_update_status.php';
require_once '../../CarRental_Backend/config/auth.php';
requireAdminOrStaff('../login.php');
require_once '../../CarRental_Backend/config/database.php';

$sql = "
    SELECT 
        b.BookingID,
        b.UserID,
        b.CarID,
        b.StartDate,
        b.EndDate,
        b.PickupLocation,
        b.ReturnLocation,
        b.RentalDays,
        b.PricePerDay,
        b.DepositAmount,
        b.DiscountAmount,
        b.TotalPrice,
        b.TotalPenalty,
        b.Status,
        b.ReturnStatus,
        b.Note,
        b.CreatedAt,
        u.FullName,
        u.Email,
        u.Phone,
        c.CarName,
        c.LicensePlate,
        COALESCE(ps.TotalPaid, 0) AS TotalPaid,
        COALESCE(ps.TotalPending, 0) AS TotalPending,
        ip.PaymentID AS InitialPaymentID,
        ip.Amount AS InitialAmount,
        ip.PaymentType AS InitialPaymentType,
        ip.Status AS InitialPaymentStatus,
        ip.PaymentMethod AS InitialPaymentMethod,
        ip.PaymentDate AS InitialPaymentDate,
        fp.PaymentID AS FinalPaymentID,
        fp.Amount AS FinalAmount,
        fp.Status AS FinalPaymentStatus,
        fp.PaymentMethod AS FinalPaymentMethod,
        fp.PaymentDate AS FinalPaymentDate
    FROM bookings b
    LEFT JOIN users u ON b.UserID = u.UserID
    LEFT JOIN cars c ON b.CarID = c.CarID
    LEFT JOIN (
        SELECT
            BookingID,
            SUM(CASE WHEN Status = 'Paid' THEN Amount ELSE 0 END) AS TotalPaid,
            SUM(CASE WHEN Status = 'Pending' THEN Amount ELSE 0 END) AS TotalPending
        FROM payments
        GROUP BY BookingID
    ) ps ON ps.BookingID = b.BookingID
    LEFT JOIN payments ip ON ip.PaymentID = (
        SELECT p.PaymentID
        FROM payments p
        WHERE p.BookingID = b.BookingID
          AND p.PaymentType IN ('Deposit', 'Rental')
        ORDER BY
            CASE p.PaymentType
                WHEN 'Deposit' THEN 1
                WHEN 'Rental' THEN 2
                ELSE 3
            END,
            p.PaymentID ASC
        LIMIT 1
    )
    LEFT JOIN payments fp ON fp.BookingID = b.BookingID AND fp.PaymentType = 'Final'
    ORDER BY b.BookingID DESC
";

$result = $conn->query($sql);

function formatMoney($value) {
    return number_format((float)$value, 0, ',', '.') . ' VNĐ';
}

function bookingBadgeClass($status) {
    switch ($status) {
        case 'Pending':
            return 'warning';
        case 'Confirmed':
            return 'info';
        case 'Paid':
            return 'primary';
        case 'Cancelled':
            return 'danger';
        case 'Completed':
            return 'success';
        default:
            return 'secondary';
    }
}

function bookingStatusText($status) {
    switch ($status) {
        case 'Pending':
            return 'Chưa xử lý';
        case 'Confirmed':
            return 'Đã xác nhận';
        case 'Paid':
            return 'Đã thanh toán';
        case 'Cancelled':
            return 'Đã hủy';
        case 'Completed':
            return 'Hoàn thành';
        default:
            return $status;
    }
}

function paymentTypeText($type) {
    switch ($type) {
        case 'Deposit':
            return 'Tiền cọc';
        case 'Rental':
            return 'Tiền thuê';
        case 'Final':
            return 'Thanh toán cuối';
        default:
            return $type;
    }
}
?>
<!DOCTYPE html>
<html lang="vi">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Quản lý đơn đặt xe</title>

    <link href="../vendor/fontawesome-free/css/all.min.css" rel="stylesheet">
    <link href="../css/sb-admin-2.min.css" rel="stylesheet">

    <style>
        .booking-card {
            border: none;
            border-radius: 16px;
            box-shadow: 0 0.2rem 1rem rgba(58,59,69,.08);
        }
        .table-booking th {
            white-space: nowrap;
            vertical-align: middle;
        }
        .table-booking td {
            vertical-align: middle;
        }
        .customer-box {
            min-width: 180px;
        }
        .car-box {
            min-width: 180px;
        }
        .date-box {
            min-width: 150px;
        }
        .money-box {
            min-width: 130px;
        }
        .note-text {
            max-width: 220px;
            white-space: normal;
            color: #5a5c69;
        }
        .filter-bar .form-control,
        .filter-bar .form-select,
        .filter-bar select {
            height: 42px;
        }
    </style>
</head>
<body id="page-top">
<div id="wrapper">

        <?php include '../partials/sidebar.php'; ?>


    <div id="content-wrapper" class="d-flex flex-column">
        <div id="content">

            <?php include '../partials/topbar.php'; ?>
            <div class="container-fluid">
                <div class="d-sm-flex align-items-center justify-content-between mb-4">
                    <h1 class="h3 mb-0 text-gray-800">Quản lý đơn đặt xe</h1>
                </div>

                <?php if (isset($_GET['updated'])): ?>
                    <div class="alert alert-success">Cập nhật trạng thái đơn đặt xe thành công.</div>
                <?php endif; ?>
                <?php if (isset($_GET['final_paid'])): ?>
                    <div class="alert alert-success">Admin đã xác nhận thanh toán cuối và hoàn tất đơn.</div>
                <?php endif; ?>
                <?php if (isset($_GET['payment_paid'])): ?>
                    <div class="alert alert-success">Admin đã xác nhận thanh toán thành công.</div>
                <?php endif; ?>

                <div class="card booking-card">
                    <div class="card-body">
                        <div class="row filter-bar mb-4">
                            <div class="col-md-6 mb-2">
                                <input type="text" id="searchInput" class="form-control" placeholder="Tìm theo mã đơn, tên khách, email, tên xe, biển số...">
                            </div>
                            <div class="col-md-3 mb-2">
                                <select id="statusFilter" class="form-control">
                                    <option value="">Tất cả trạng thái</option>
                                    <option value="pending">Chưa giải quyết</option>
                                    <option value="confirmed">Đã xác nhận</option>
                                    <option value="paid">Đã thanh toán</option>
                                    <option value="cancelled">Đã hủy</option>
                                    <option value="completed">Hoàn thành</option>
                                </select>
                            </div>
                        </div>

                        <div class="table-responsive">
                            <table class="table table-bordered table-hover table-booking" id="bookingsTable" width="100%" cellspacing="0">
                                <thead class="thead-light">
                                    <tr>
                                        <th>Mã đơn</th>
                                        <th>Khách hàng</th>
                                        <th>Xe</th>
                                        <th>Ngày thuê</th>
                                        <th>Địa điểm</th>
                                        <th>Tiền</th>
                                        <th>Trạng thái</th>
                                        <th>Ghi chú</th>
                                        <th width="170">Thao tác</th>
                                    </tr>
                                </thead>
                                <tbody>
                                <?php if ($result && $result->num_rows > 0): ?>
                                    <?php while ($row = $result->fetch_assoc()): ?>
                                        <tr>
                                            <td>
                                                <strong>#<?php echo (int)$row['BookingID']; ?></strong><br>
                                                <small class="text-muted"><?php echo htmlspecialchars($row['CreatedAt']); ?></small>
                                            </td>

                                            <td class="customer-box">
                                                <strong><?php echo htmlspecialchars($row['FullName'] ?? 'N/A'); ?></strong><br>
                                                <small><?php echo htmlspecialchars($row['Email'] ?? ''); ?></small><br>
                                                <small><?php echo htmlspecialchars($row['Phone'] ?? ''); ?></small>
                                            </td>

                                            <td class="car-box">
                                                <strong><?php echo htmlspecialchars($row['CarName'] ?? 'N/A'); ?></strong><br>
                                                <small>Biển số: <?php echo htmlspecialchars($row['LicensePlate'] ?? ''); ?></small>
                                            </td>

                                            <td class="date-box">
                                                <strong>Nhận:</strong> <?php echo htmlspecialchars($row['StartDate']); ?><br>
                                                <strong>Trả:</strong> <?php echo htmlspecialchars($row['EndDate']); ?><br>
                                                <small><?php echo (int)$row['RentalDays']; ?> ngày</small>
                                            </td>

                                            <td>
                                                <strong>Nhận:</strong> <?php echo htmlspecialchars($row['PickupLocation']); ?><br>
                                                <strong>Trả:</strong> <?php echo htmlspecialchars($row['ReturnLocation']); ?>
                                            </td>

                                            <td class="money-box">
                                                <?php
                                                    $totalReceivable = (float)$row['TotalPrice'] + (float)($row['TotalPenalty'] ?? 0);
                                                    $totalPaid = (float)($row['TotalPaid'] ?? 0);
                                                    $totalPending = (float)($row['TotalPending'] ?? 0);
                                                    $remainingReceivable = max(0, $totalReceivable - $totalPaid);
                                                ?>
                                                <strong>Tổng tiền:</strong> <?php echo formatMoney($totalReceivable); ?><br>
                                                <small class="text-success">Đã thu: <?php echo formatMoney($totalPaid); ?></small><br>
                                                <small class="text-danger">Còn lại: <?php echo formatMoney($remainingReceivable); ?></small>
                                                <?php if ($totalPending > 0): ?>
                                                    <br><small class="text-warning">Đang chờ: <?php echo formatMoney($totalPending); ?></small>
                                                <?php endif; ?>
                                            </td>

                                            <td>
                                                <span class="badge badge-<?php echo bookingBadgeClass($row['Status']); ?> badge-status">
                                                    <?php echo htmlspecialchars(bookingStatusText($row['Status'])); ?>
                                                </span>
                                            </td>

                                            <td class="note-text">
                                                <?php echo !empty($row['Note']) ? nl2br(htmlspecialchars($row['Note'])) : '<span class="text-muted">Không có</span>'; ?>
                                            </td>

                                            <td>
                                                <?php $current = $row['Status']; ?>

                                                <?php if (($row['ReturnStatus'] ?? '') === 'Pending'): ?>
                                                    <a href="return_check.php?id=<?php echo (int)$row['BookingID']; ?>"
                                                       class="btn btn-success btn-sm btn-block mb-2">
                                                        Kiểm tra trả xe
                                                    </a>
                                                <?php endif; ?>

                                                <?php if (($row['InitialPaymentStatus'] ?? '') === 'Pending' && !in_array($current, ['Cancelled', 'Completed'], true)): ?>
                                                    <form action="../../CarRental_Backend/api/admin/payments/confirm.php" method="POST" class="mb-2">
                                                        <input type="hidden" name="PaymentID" value="<?php echo (int)$row['InitialPaymentID']; ?>">
                                                        <input type="hidden" name="Redirect" value="list">
                                                        <div class="small text-muted mb-1">
                                                            <?php echo htmlspecialchars(paymentTypeText($row['InitialPaymentType'])); ?>:
                                                            <?php echo formatMoney($row['InitialAmount']); ?>
                                                        </div>
                                                        <select name="PaymentMethod" class="form-control form-control-sm mb-2" required>
                                                            <option value="Cash">Tiền mặt</option>
                                                            <option value="BankTransfer">Chuyển khoản</option>
                                                        </select>
                                                        <button type="submit" class="btn btn-warning btn-sm btn-block">
                                                            Xác nhận thanh toán
                                                        </button>
                                                    </form>
                                                <?php endif; ?>

                                                <?php if (($row['ReturnStatus'] ?? '') === 'Approved' && ($row['FinalPaymentStatus'] ?? '') === 'Pending'): ?>
                                                    <form action="../../CarRental_Backend/api/admin/payments/confirm_final.php" method="POST" class="mb-2">
                                                        <input type="hidden" name="PaymentID" value="<?php echo (int)$row['FinalPaymentID']; ?>">
                                                        <input type="hidden" name="Redirect" value="list">
                                                        <div class="small text-muted mb-1">
                                                            Cuối: <?php echo formatMoney($row['FinalAmount']); ?>
                                                        </div>
                                                        <select name="PaymentMethod" class="form-control form-control-sm mb-2" required>
                                                            <option value="Cash">Tiền mặt</option>
                                                            <option value="BankTransfer">Chuyển khoản</option>
                                                        </select>
                                                        <button type="submit" class="btn btn-warning btn-sm btn-block">
                                                            Xác nhận TT cuối
                                                        </button>
                                                    </form>
                                                <?php endif; ?>

                                                <form action="../../CarRental_Backend/api/admin/bookings/update.php" method="POST" class="mb-2">
                                                    <input type="hidden" name="BookingID" value="<?php echo (int)$row['BookingID']; ?>">

                                                    <select name="Status" class="form-control form-control-sm mb-2"
                                                        <?php echo in_array($current, ['Paid', 'Completed', 'Cancelled']) ? 'disabled' : ''; ?>>

                                                        <?php if ($current === 'Pending'): ?>
                                                            <option value="Pending" selected>Chưa giải quyết</option>
                                                            <option value="Cancelled">Đã hủy</option>

                                                        <?php elseif ($current === 'Confirmed'): ?>
                                                            <option value="Confirmed" selected>Đã xác nhận</option>
                                                            <option value="Cancelled">Đã hủy</option>

                                                        <?php elseif ($current === 'Paid'): ?>
                                                            <option value="Paid" selected>Đã thanh toán</option>

                                                        <?php elseif ($current === 'Completed'): ?>
                                                            <option value="Completed" selected>Hoàn thành</option>

                                                        <?php elseif ($current === 'Cancelled'): ?>
                                                            <option value="Cancelled" selected>Đã hủy</option>

                                                        <?php else: ?>
                                                            <option value="<?php echo htmlspecialchars($current); ?>" selected>
                                                                <?php echo htmlspecialchars($current); ?>
                                                            </option>
                                                        <?php endif; ?>
                                                    </select>

                                                    <button type="submit" class="btn btn-primary btn-sm btn-block"
                                                        <?php echo in_array($current, ['Paid', 'Completed', 'Cancelled']) ? 'disabled' : ''; ?>>
                                                        Cập nhật
                                                    </button>
                                                    <a href="detail.php?id=<?php echo (int)$row['BookingID']; ?>" class="btn btn-info btn-sm btn-block mb-2">
                                                        Xem chi tiết
                                                    </a>
                                                </form>
                                                
                                            </td>
                                        </tr>
                                    <?php endwhile; ?>
                                <?php else: ?>
                                    <tr>
                                        <td colspan="9" class="text-center text-muted">Chưa có đơn đặt xe nào.</td>
                                    </tr>
                                <?php endif; ?>
                                </tbody>
                            </table>
                        </div>

                    </div>
                </div>
            </div>
        </div>

        <?php include '../partials/footer.php'; ?>
    </div>
</div>

<script src="../vendor/jquery/jquery.min.js"></script>
<script src="../vendor/bootstrap/js/bootstrap.bundle.min.js"></script>
<script src="../vendor/jquery-easing/jquery.easing.min.js"></script>
<script src="../js/sb-admin-2.min.js"></script>

<script>
document.addEventListener("DOMContentLoaded", function () {
    const searchInput = document.getElementById("searchInput");
    const statusFilter = document.getElementById("statusFilter");
    const table = document.getElementById("bookingsTable");

    if (!table) return;

    const rows = table.querySelectorAll("tbody tr");

    function filterRows() {
        const keyword = (searchInput?.value || "").toLowerCase().trim();
        const status = (statusFilter?.value || "").toLowerCase();

        rows.forEach(row => {
            const rowText = row.innerText.toLowerCase();
            const badge = row.querySelector(".badge-status");
            const rowStatus = badge ? badge.innerText.toLowerCase() : "";

            const matchKeyword = rowText.includes(keyword);
            const matchStatus = status === "" || rowStatus === status;

            row.style.display = (matchKeyword && matchStatus) ? "" : "none";
        });
    }

    searchInput?.addEventListener("keyup", filterRows);
    statusFilter?.addEventListener("change", filterRows);
});
</script>
</body>
</html>
