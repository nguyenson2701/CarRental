<?php
require_once '../CarRental_Backend/config/auth.php';
requireLogin('login.php');
require_once '../CarRental_Backend/config/database.php';

$pageTitle = 'Thanh toán của tôi';
$activePage = 'my-payments';

$userID = (int)($_SESSION['user_id'] ?? 0);

$stmt = $conn->prepare("
    SELECT 
        p.PaymentID,
        p.BookingID,
        p.Amount,
        p.PaymentMethod,
        p.PaymentType,
        p.TransactionCode,
        p.PaymentDate,
        p.Status,
        p.Note,

        b.PenaltyReason,
        b.TotalPrice,
        b.DepositAmount,
        b.OvertimeFee,
        b.DamageFee,
        b.CleaningFee,
        b.OtherFee,
        b.TotalPenalty,

        c.CarName,
        c.LicensePlate
    FROM payments p
    INNER JOIN bookings b ON p.BookingID = b.BookingID
    INNER JOIN cars c ON b.CarID = c.CarID
    WHERE b.UserID = ?
    ORDER BY p.PaymentID DESC
");
$stmt->bind_param("i", $userID);
$stmt->execute();
$result = $stmt->get_result();

include 'includes/header.php';

function money($value) {
    return number_format((float)$value, 0, ',', '.') . ' VNĐ';
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
            return $status;
    }
}

function paymentBadge($status) {
    switch ($status) {
        case 'Pending':
            return 'warning';
        case 'Paid':
            return 'success';
        case 'Failed':
            return 'danger';
        case 'Cancelled':
            return 'secondary';
        default:
            return 'secondary';
    }
}

function paymentTypeText($type) {
    switch ($type) {
        case 'Penalty':
            return 'Tiền phạt';
        case 'Rental':
            return 'Tiền thuê xe';
        case 'Deposit':
            return 'Tiền cọc';
        case 'Final':
            return 'Thanh toán cuối';
        case 'Refund':
            return 'Hoàn tiền';
        default:
            return $type;
    }
}

function paymentMethodText($method) {
    switch ($method) {
        case 'Cash':
            return 'Tiền mặt';
        case 'BankTransfer':
            return 'Chuyển khoản';
        case 'Momo':
            return 'Momo';
        default:
            return $method ?: 'Chưa có';
    }
}
?>

<div class="container py-5">
    <h2 class="fw-bold mb-4">Thanh toán của tôi</h2>

    <style>
        .payment-list {
            display: grid;
            gap: 14px;
        }
        .payment-item {
            background: #fff;
            border: 1px solid #edf0f3;
            border-radius: 10px;
            box-shadow: 0 0.15rem 0.75rem rgba(0, 0, 0, 0.05);
            padding: 16px;
        }
        .payment-head {
            display: flex;
            justify-content: space-between;
            gap: 16px;
            border-bottom: 1px solid #f1f3f5;
            padding-bottom: 12px;
            margin-bottom: 12px;
        }
        .payment-title {
            min-width: 0;
        }
        .payment-title strong,
        .payment-title span {
            display: block;
        }
        .payment-amount {
            min-width: 150px;
            text-align: right;
        }
        .payment-body {
            display: grid;
            grid-template-columns: minmax(190px, 0.8fr) minmax(260px, 1.2fr);
            gap: 16px;
        }
        .payment-line {
            display: flex;
            justify-content: space-between;
            gap: 12px;
            margin-bottom: 4px;
        }
        .payment-line span:first-child {
            color: #6c757d;
        }
        .payment-note {
            color: #6c757d;
            font-size: 0.9rem;
        }
        @media (max-width: 768px) {
            .payment-head,
            .payment-body {
                display: block;
            }
            .payment-amount {
                text-align: left;
                margin-top: 10px;
            }
            .payment-body > div + div {
                margin-top: 12px;
            }
        }
    </style>

    <?php if (isset($_GET['booking_success'])): ?>
        <div class="alert alert-info">
            Đặt xe thành công. Admin sẽ xác nhận thanh toán sau khi nhận tiền mặt hoặc chuyển khoản.
        </div>
    <?php endif; ?>

    <?php if ($result->num_rows <= 0): ?>
        <div class="alert alert-info">
            Bạn chưa có khoản thanh toán nào.
        </div>
    <?php else: ?>
        <div class="payment-list">
            <?php while ($row = $result->fetch_assoc()): ?>
                <?php
                    $remainingAmount = max(0, (float)$row['TotalPrice'] - (float)$row['DepositAmount']);
                ?>
                <div class="payment-item">
                    <div class="payment-head">
                        <div class="payment-title">
                            <strong>#<?php echo (int)$row['PaymentID']; ?> - <?php echo paymentTypeText($row['PaymentType']); ?></strong>
                            <span>Đơn #<?php echo (int)$row['BookingID']; ?> - <?php echo htmlspecialchars($row['CarName']); ?></span>
                            <small class="text-muted"><?php echo htmlspecialchars($row['LicensePlate']); ?></small>
                        </div>
                        <div class="payment-amount">
                            <div class="fw-bold text-danger"><?php echo money($row['Amount']); ?></div>
                            <span class="badge bg-<?php echo paymentBadge($row['Status']); ?>">
                                <?php echo paymentStatusText($row['Status']); ?>
                            </span>
                        </div>
                    </div>

                    <div class="payment-body">
                        <div>
                            <div class="payment-line">
                                <span>Phương thức</span>
                                <strong><?php echo htmlspecialchars(paymentMethodText($row['PaymentMethod'])); ?></strong>
                            </div>
                            <div class="payment-line">
                                <span>Mã giao dịch</span>
                                <strong><?php echo htmlspecialchars($row['TransactionCode'] ?: 'Chưa có'); ?></strong>
                            </div>
                            <div class="payment-line">
                                <span>Ngày thanh toán</span>
                                <strong><?php echo htmlspecialchars($row['PaymentDate'] ?: 'Chưa có'); ?></strong>
                            </div>
                        </div>

                        <div>
                            <?php if ($row['PaymentType'] === 'Final'): ?>
                                <div class="payment-line"><span>Tổng thuê</span><strong><?php echo money($row['TotalPrice']); ?></strong></div>
                                <div class="payment-line"><span>Đã cọc</span><strong><?php echo money($row['DepositAmount']); ?></strong></div>
                                <div class="payment-line"><span>Còn lại</span><strong><?php echo money($remainingAmount); ?></strong></div>
                                <div class="payment-line"><span>Tiền phạt</span><strong><?php echo money($row['TotalPenalty']); ?></strong></div>
                                <div class="payment-note">
                                    Quá giờ: <?php echo money($row['OvertimeFee']); ?>,
                                    hư hỏng: <?php echo money($row['DamageFee']); ?>,
                                    vệ sinh: <?php echo money($row['CleaningFee']); ?>,
                                    khác: <?php echo money($row['OtherFee']); ?>.
                                    Lý do: <?php echo htmlspecialchars($row['PenaltyReason'] ?: 'Không có'); ?>
                                </div>
                            <?php elseif ($row['PaymentType'] === 'Penalty'): ?>
                                <div class="payment-line"><span>Tổng phạt</span><strong><?php echo money($row['TotalPenalty']); ?></strong></div>
                                <div class="payment-note">
                                    Quá giờ: <?php echo money($row['OvertimeFee']); ?>,
                                    hư hỏng: <?php echo money($row['DamageFee']); ?>,
                                    vệ sinh: <?php echo money($row['CleaningFee']); ?>,
                                    khác: <?php echo money($row['OtherFee']); ?>.
                                    Lý do: <?php echo htmlspecialchars($row['PenaltyReason'] ?: 'Không có'); ?>
                                </div>
                            <?php else: ?>
                                <div class="payment-note"><?php echo htmlspecialchars($row['Note'] ?: 'Không có ghi chú'); ?></div>
                            <?php endif; ?>
                        </div>
                    </div>
                </div>
            <?php endwhile; ?>
        </div>
    <?php endif; ?>
</div>

<?php include 'includes/footer.php'; ?>
