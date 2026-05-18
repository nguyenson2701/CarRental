<?php
require_once '../../CarRental_Backend/config/auth.php';
requireAdminOrStaff('../login.php');
require_once '../../CarRental_Backend/config/database.php';

$id = (int)($_GET['id'] ?? 0);
if ($id <= 0) {
    header("Location: list.php");
    exit();
}

$stmt = $conn->prepare("
    SELECT 
        b.*,
        u.FullName,
        u.Email,
        u.Phone,
        u.Address,
        c.CarName,
        c.LicensePlate,
        c.MainImage,
        c.Transmission,
        c.FuelType,
        c.Seats,
        c.Color,
        c.Location,
        fp.PaymentID AS FinalPaymentID,
        fp.Amount AS FinalAmount,
        fp.Status AS FinalPaymentStatus,
        fp.PaymentMethod AS FinalPaymentMethod,
        fp.PaymentDate AS FinalPaymentDate,
        fp.TransactionCode AS FinalTransactionCode
    FROM bookings b
    LEFT JOIN users u ON b.UserID = u.UserID
    LEFT JOIN cars c ON b.CarID = c.CarID
    LEFT JOIN payments fp ON fp.BookingID = b.BookingID AND fp.PaymentType = 'Final'
    WHERE b.BookingID = ?
    LIMIT 1
");
$stmt->bind_param("i", $id);
$stmt->execute();
$result = $stmt->get_result();
$booking = $result->fetch_assoc();

if (!$booking) {
    header("Location: list.php");
    exit();
}

function formatMoney($value) {
    return number_format((float)$value, 0, ',', '.') . ' VNĐ';
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

function paymentStatusText($status) {
    switch ($status) {
        case 'Pending':
            return 'Chờ thanh toán';
        case 'Paid':
            return 'Đã thanh toán';
        case 'Failed':
            return 'Thất bại';
        case 'Cancelled':
            return 'Đã hủy';
        default:
            return $status ?: 'Chưa tạo';
    }
}

function paymentStatusClass($status) {
    switch ($status) {
        case 'Paid':
            return 'success';
        case 'Pending':
            return 'warning';
        case 'Failed':
        case 'Cancelled':
            return 'danger';
        default:
            return 'secondary';
    }
}

function paymentMethodText($method) {
    switch ($method) {
        case 'Cash':
            return 'Tiền mặt';
        case 'BankTransfer':
            return 'Chuyển khoản';
        case 'VNPay':
            return 'VNPay';
        case 'Momo':
            return 'MoMo';
        default:
            return $method ?: 'Chưa chọn';
    }
}

function statusLabel($status) {
    switch ($status) {
        case 'Pending':
            return ['Chưa giải quyết', 'warning'];
        case 'Confirmed':
            return ['Đã xác nhận', 'info'];
        case 'Paid':
            return ['Đã thanh toán', 'primary'];
        case 'Cancelled':
            return ['Đã hủy', 'danger'];
        case 'Completed':
            return ['Hoàn thành', 'success'];
        default:
            return [$status, 'secondary'];
    }
}

[$statusText, $statusClass] = statusLabel($booking['Status']);

$rentalDays = (int)($booking['RentalDays'] ?? 0);
$pricePerDay = (float)($booking['PricePerDay'] ?? 0);
$rentalSubtotal = max(0, $rentalDays * $pricePerDay);
$discountAmount = (float)($booking['DiscountAmount'] ?? 0);
$rentalTotal = (float)($booking['TotalPrice'] ?? 0);
$depositAmount = (float)($booking['DepositAmount'] ?? 0);
$overtimeFee = (float)($booking['OvertimeFee'] ?? 0);
$damageFee = (float)($booking['DamageFee'] ?? 0);
$cleaningFee = (float)($booking['CleaningFee'] ?? 0);
$otherFee = (float)($booking['OtherFee'] ?? 0);
$totalPenalty = isset($booking['TotalPenalty']) && $booking['TotalPenalty'] !== null
    ? (float)$booking['TotalPenalty']
    : ($overtimeFee + $damageFee + $cleaningFee + $otherFee);
$grandTotal = $rentalTotal + $totalPenalty;
$remainingRentalAmount = max(0, $rentalTotal - $depositAmount);
$computedFinalAmount = $remainingRentalAmount + $totalPenalty;
$displayFinalAmount = isset($booking['FinalAmount']) && $booking['FinalAmount'] !== null
    ? (float)$booking['FinalAmount']
    : $computedFinalAmount;

$paymentRows = [];
$paidAmount = 0;
$paymentStmt = $conn->prepare("
    SELECT PaymentID, Amount, PaymentMethod, PaymentType, TransactionCode, PaymentDate, Status, Note
    FROM payments
    WHERE BookingID = ?
    ORDER BY
        CASE PaymentType
            WHEN 'Deposit' THEN 1
            WHEN 'Rental' THEN 2
            WHEN 'Final' THEN 3
            ELSE 4
        END,
        PaymentID ASC
");
$paymentStmt->bind_param("i", $id);
$paymentStmt->execute();
$paymentResult = $paymentStmt->get_result();
while ($payment = $paymentResult->fetch_assoc()) {
    $paymentRows[] = $payment;
    if (($payment['Status'] ?? '') === 'Paid') {
        $paidAmount += (float)$payment['Amount'];
    }
}
$remainingAmount = max(0, $grandTotal - $paidAmount);
?>
<!DOCTYPE html>
<html lang="vi">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Chi tiết đơn đặt xe</title>

    <link href="../vendor/fontawesome-free/css/all.min.css" rel="stylesheet">
    <link href="../css/sb-admin-2.min.css" rel="stylesheet">
    <link href="../assets/css/booking-detail.css" rel="stylesheet">
</head>
<body id="page-top">
<div id="wrapper">

    <?php include '../partials/sidebar.php'; ?>

    <div id="content-wrapper" class="d-flex flex-column">
        <div id="content">

            <?php include '../partials/topbar.php'; ?>

            <div class="container-fluid booking-detail-page">
                <div class="detail-page-header">
                    <div>
                        <span class="detail-kicker">Đơn đặt xe #<?php echo (int)$booking['BookingID']; ?></span>
                        <h1>Chi tiết đơn đặt xe</h1>
                    </div>
                    <div class="detail-header-actions">
                        <a href="list.php" class="btn btn-light btn-sm">
                            <i class="fas fa-arrow-left mr-1"></i> Quay lại
                        </a>
                    </div>
                </div>

                <?php if (isset($_GET['final_paid'])): ?>
                    <div class="alert alert-success">Admin đã xác nhận thanh toán cuối và hoàn tất đơn.</div>
                <?php endif; ?>
                <?php if (isset($_GET['payment_paid'])): ?>
                    <div class="alert alert-success">Admin đã xác nhận thanh toán thành công.</div>
                <?php endif; ?>

                <section class="detail-hero mb-4">
                    <div class="detail-hero-main">
                        <span class="badge badge-<?php echo $statusClass; ?> badge-lg"><?php echo htmlspecialchars($statusText); ?></span>
                        <h2><?php echo htmlspecialchars($booking['CarName'] ?? 'N/A'); ?></h2>
                        <div class="hero-meta">
                            <span><i class="fas fa-user mr-1"></i><?php echo htmlspecialchars($booking['FullName'] ?? 'N/A'); ?></span>
                            <span><i class="fas fa-calendar-alt mr-1"></i><?php echo htmlspecialchars($booking['StartDate']); ?> - <?php echo htmlspecialchars($booking['EndDate']); ?></span>
                            <span><i class="fas fa-map-marker-alt mr-1"></i><?php echo htmlspecialchars($booking['PickupLocation']); ?></span>
                        </div>
                    </div>
                    <div class="detail-hero-total">
                        <span>Tổng chi phí</span>
                        <strong><?php echo formatMoney($grandTotal); ?></strong>
                    </div>
                </section>

                <div class="row">
                    <div class="col-lg-5 mb-4">
                        <div class="car-image-box">
                            <?php if (!empty($booking['MainImage'])): ?>
                                <img src="../../CarRental_Frontend/assets/img/cars/<?php echo htmlspecialchars($booking['MainImage']); ?>" alt="Car">
                            <?php else: ?>
                                <div class="car-image-placeholder">
                                    <i class="fas fa-car"></i>
                                    <span>Chưa có ảnh xe</span>
                                </div>
                            <?php endif; ?>
                            <div class="car-body">
                                <div class="car-compact-title">
                                    <h5 class="font-weight-bold"><?php echo htmlspecialchars($booking['CarName'] ?? 'N/A'); ?></h5>
                                    <span class="car-plate"><?php echo htmlspecialchars($booking['LicensePlate'] ?? ''); ?></span>
                                </div>

                                <div class="car-spec-grid">
                                    <div class="car-spec-item">
                                        <i class="fas fa-cogs"></i>
                                        <div>
                                            <span>Hộp số</span>
                                            <strong><?php echo htmlspecialchars($booking['Transmission'] ?? 'N/A'); ?></strong>
                                        </div>
                                    </div>
                                    <div class="car-spec-item">
                                        <i class="fas fa-gas-pump"></i>
                                        <div>
                                            <span>Nhiên liệu</span>
                                            <strong><?php echo htmlspecialchars($booking['FuelType'] ?? 'N/A'); ?></strong>
                                        </div>
                                    </div>
                                    <div class="car-spec-item">
                                        <i class="fas fa-users"></i>
                                        <div>
                                            <span>Số ghế</span>
                                            <strong><?php echo (int)($booking['Seats'] ?? 0); ?> chỗ</strong>
                                        </div>
                                    </div>
                                    <div class="car-spec-item">
                                        <i class="fas fa-palette"></i>
                                        <div>
                                            <span>Màu xe</span>
                                            <strong><?php echo htmlspecialchars($booking['Color'] ?? 'N/A'); ?></strong>
                                        </div>
                                    </div>
                                    <div class="car-spec-item">
                                        <i class="fas fa-map-marker-alt"></i>
                                        <div>
                                            <span>Vị trí xe</span>
                                            <strong><?php echo htmlspecialchars($booking['Location'] ?? 'N/A'); ?></strong>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>

                    </div>

                    <div class="col-lg-7">
                        <div class="row">
                            <div class="col-md-6 mb-4">
                                <div class="section-card location-compact-card location-card-inline">
                                    <div class="section-title">
                                        <i class="fas fa-route text-primary"></i>
                                        Địa điểm giao nhận
                                    </div>
                                    <div class="location-list">
                                        <div class="location-item">
                                            <div class="location-icon">
                                                <i class="fas fa-sign-out-alt"></i>
                                            </div>
                                            <div>
                                                <span>Nhận xe</span>
                                                <strong><?php echo htmlspecialchars($booking['PickupLocation']); ?></strong>
                                            </div>
                                        </div>
                                        <div class="location-item">
                                            <div class="location-icon">
                                                <i class="fas fa-sign-in-alt"></i>
                                            </div>
                                            <div>
                                                <span>Trả xe</span>
                                                <strong><?php echo htmlspecialchars($booking['ReturnLocation']); ?></strong>
                                            </div>
                                        </div>
                                    </div>
                                </div>
                            </div>

                            <div class="col-md-6 mb-4">
                                <div class="section-card">
                                    <div class="section-title">
                                        <i class="fas fa-user text-primary"></i>
                                        Thông tin khách hàng
                                    </div>
                                    <div class="info-row">
                                        <div class="info-label">Họ tên</div>
                                        <div class="info-value"><?php echo htmlspecialchars($booking['FullName'] ?? 'N/A'); ?></div>
                                    </div>
                                    <div class="info-row">
                                        <div class="info-label">Email</div>
                                        <div class="info-value"><?php echo htmlspecialchars($booking['Email'] ?? ''); ?></div>
                                    </div>
                                    <div class="info-row">
                                        <div class="info-label">Số điện thoại</div>
                                        <div class="info-value"><?php echo htmlspecialchars($booking['Phone'] ?? ''); ?></div>
                                    </div>
                                    <div class="info-row">
                                        <div class="info-label">Địa chỉ</div>
                                        <div class="info-value"><?php echo htmlspecialchars($booking['Address'] ?? ''); ?></div>
                                    </div>
                                </div>
                            </div>

                            <div class="col-md-6 mb-4">
                                <div class="section-card">
                                    <div class="section-title">
                                        <i class="fas fa-calendar-alt text-primary"></i>
                                        Thời gian thuê
                                    </div>
                                    <div class="info-row">
                                        <div class="info-label">Ngày nhận</div>
                                        <div class="info-value"><?php echo htmlspecialchars($booking['StartDate']); ?></div>
                                    </div>
                                    <div class="info-row">
                                        <div class="info-label">Ngày trả</div>
                                        <div class="info-value"><?php echo htmlspecialchars($booking['EndDate']); ?></div>
                                    </div>
                                    <div class="info-row">
                                        <div class="info-label">Số ngày</div>
                                        <div class="info-value"><?php echo (int)$booking['RentalDays']; ?> ngày</div>
                                    </div>
                                    <div class="info-row">
                                        <div class="info-label">Ngày tạo đơn</div>
                                        <div class="info-value"><?php echo htmlspecialchars($booking['CreatedAt']); ?></div>
                                    </div>
                                </div>
                            </div>

                            <div class="col-12 mb-4">
                                <div class="section-card">
                                    <div class="section-title">
                                        <i class="fas fa-money-bill-wave text-primary"></i>
                                        Thông tin thanh toán
                                    </div>

                                    <div class="payment-summary">
                                        <div class="payment-summary-item">
                                            <span>Tổng chi phí</span>
                                            <strong><?php echo formatMoney($grandTotal); ?></strong>
                                        </div>
                                        <div class="payment-summary-item">
                                            <span>Đã thu</span>
                                            <strong class="text-success"><?php echo formatMoney($paidAmount); ?></strong>
                                        </div>
                                        <div class="payment-summary-item">
                                            <span>Còn phải thu</span>
                                            <strong class="<?php echo $remainingAmount > 0 ? 'text-danger' : 'text-success'; ?>">
                                                <?php echo formatMoney($remainingAmount); ?>
                                            </strong>
                                        </div>
                                    </div>

                                    <div class="row">
                                        <div class="col-lg-6 mb-3 mb-lg-0">
                                            <h6 class="font-weight-bold text-gray-800 mb-3">Chi phí thuê xe</h6>
                                            <div class="info-row">
                                                <div class="info-label">Giá/ngày</div>
                                                <div class="info-value"><?php echo formatMoney($pricePerDay); ?></div>
                                            </div>
                                            <div class="info-row">
                                                <div class="info-label">Số ngày thuê</div>
                                                <div class="info-value"><?php echo $rentalDays; ?> ngày</div>
                                            </div>
                                            <div class="info-row">
                                                <div class="info-label">Tiền thuê gốc</div>
                                                <div class="info-value"><?php echo formatMoney($rentalSubtotal); ?></div>
                                            </div>
                                            <div class="info-row">
                                                <div class="info-label">Giảm giá</div>
                                                <div class="info-value"><?php echo formatMoney($discountAmount); ?></div>
                                            </div>
                                            <div class="info-row">
                                                <div class="info-label">Tiền thuê sau giảm</div>
                                                <div class="info-value text-primary font-weight-bold"><?php echo formatMoney($rentalTotal); ?></div>
                                            </div>
                                            <div class="info-row">
                                                <div class="info-label">Tiền cọc giữ xe</div>
                                                <div class="info-value"><?php echo formatMoney($depositAmount); ?></div>
                                            </div>
                                        </div>

                                        <div class="col-lg-6">
                                            <h6 class="font-weight-bold text-gray-800 mb-3">Phí phát sinh khi trả xe</h6>
                                            <div class="info-row">
                                                <div class="info-label">Phí quá giờ</div>
                                                <div class="info-value"><?php echo formatMoney($overtimeFee); ?></div>
                                            </div>
                                            <div class="info-row">
                                                <div class="info-label">Phí hư hỏng</div>
                                                <div class="info-value"><?php echo formatMoney($damageFee); ?></div>
                                            </div>
                                            <div class="info-row">
                                                <div class="info-label">Phí vệ sinh</div>
                                                <div class="info-value"><?php echo formatMoney($cleaningFee); ?></div>
                                            </div>
                                            <div class="info-row">
                                                <div class="info-label">Phí khác</div>
                                                <div class="info-value"><?php echo formatMoney($otherFee); ?></div>
                                            </div>
                                            <div class="info-row">
                                                <div class="info-label">Tổng phí/phạt</div>
                                                <div class="info-value text-danger font-weight-bold"><?php echo formatMoney($totalPenalty); ?></div>
                                            </div>
                                            <div class="info-row">
                                                <div class="info-label">Thanh toán cuối dự kiến</div>
                                                <div class="info-value text-danger font-weight-bold"><?php echo formatMoney($displayFinalAmount); ?></div>
                                            </div>
                                        </div>
                                    </div>

                                    <div class="payment-total-row">
                                        <div class="info-row">
                                            <div class="info-label">Cách tính thanh toán cuối</div>
                                            <div class="info-value text-left">
                                                (Tiền thuê sau giảm - tiền cọc) + tổng phí/phạt = <?php echo formatMoney($displayFinalAmount); ?>.
                                            </div>
                                        </div>
                                    </div>

                                </div>
                            </div>

                            <?php if (($booking['ReturnStatus'] ?? '') === 'Approved' && ($booking['FinalPaymentStatus'] ?? '') === 'Pending'): ?>
                                <div class="col-12 mb-4">
                                    <div class="section-card final-payment-card">
                                        <div class="section-title">
                                            <i class="fas fa-cash-register text-primary"></i>
                                            Xác nhận thanh toán cuối
                                        </div>
                                        <form action="../../CarRental_Backend/api/admin/payments/confirm_final.php" method="POST" class="row">
                                            <input type="hidden" name="PaymentID" value="<?php echo (int)$booking['FinalPaymentID']; ?>">
                                            <input type="hidden" name="Redirect" value="detail">
                                            <div class="col-md-4 mb-2">
                                                <input type="text" class="form-control" value="<?php echo formatMoney($displayFinalAmount); ?>" disabled>
                                            </div>
                                            <div class="col-md-4 mb-2">
                                                <select name="PaymentMethod" class="form-control" required>
                                                    <option value="Cash">Tiền mặt</option>
                                                    <option value="BankTransfer">Chuyển khoản</option>
                                                </select>
                                            </div>
                                            <div class="col-md-4 mb-2">
                                                <button type="submit" class="btn btn-warning btn-block">
                                                    Xác nhận đã thu
                                                </button>
                                            </div>
                                        </form>
                                    </div>
                                </div>
                            <?php endif; ?>

                            <div class="col-12 mb-4">
                                <div class="section-card">
                                    <div class="section-title">
                                        <i class="fas fa-sticky-note text-primary"></i>
                                        Ghi chú của khách
                                    </div>
                                    <div class="note-box">
                                        <?php echo !empty($booking['Note']) ? htmlspecialchars($booking['Note']) : 'Không có ghi chú'; ?>
                                    </div>
                                </div>
                            </div>

                            <div class="col-12 mb-4">
                                <div class="section-card status-update-card">
                                    <div class="section-title">
                                        <i class="fas fa-edit text-primary"></i>
                                        Cập nhật trạng thái
                                    </div>

                                    <form action="../../CarRental_Backend/api/admin/bookings/update.php" method="POST" class="row">
                                        <input type="hidden" name="BookingID" value="<?php echo (int)$booking['BookingID']; ?>">
                                        <input type="hidden" name="Redirect" value="detail">

                                        <div class="col-md-8 mb-2">
                                            <?php $current = $booking['Status']; ?>
                                            <select name="Status" class="form-control"
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
                                                <?php endif; ?>
                                            </select>
                                        </div>

                                        <div class="col-md-4 mb-2">
                                            <button type="submit" class="btn btn-primary btn-block"
                                                <?php echo in_array($current, ['Paid', 'Completed', 'Cancelled']) ? 'disabled' : ''; ?>>
                                                Cập nhật
                                            </button>
                                        </div>
                                    </form>
                                </div>
                            </div>

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
</body>
</html>
