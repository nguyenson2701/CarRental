<?php
/**
 * @var array $payments
 * @var bool $bookingSuccess
 */

function money($value): string
{
    return number_format((float) $value, 0, ',', '.') . ' VNĐ';
}

function paymentStatusText(string $status): string
{
    switch ($status) {
        case 'Pending': return 'Chờ thanh toán';
        case 'Paid': return 'Đã thanh toán';
        case 'Failed': return 'Thất bại';
        case 'Cancelled': return 'Đã hủy';
        default: return $status;
    }
}

function paymentBadge(string $status): string
{
    switch ($status) {
        case 'Pending': return 'warning';
        case 'Paid': return 'success';
        case 'Failed': return 'danger';
        case 'Cancelled': return 'secondary';
        default: return 'secondary';
    }
}

function paymentTypeText(string $type): string
{
    switch ($type) {
        case 'Penalty': return 'Tiền phạt';
        case 'Rental': return 'Tiền thuê xe';
        case 'Deposit': return 'Tiền cọc';
        case 'Final': return 'Thanh toán cuối';
        case 'Refund': return 'Hoàn tiền';
        default: return $type;
    }
}

function paymentMethodText(?string $method): string
{
    switch ($method) {
        case 'Cash': return 'Tiền mặt';
        case 'BankTransfer': return 'Chuyển khoản';
        case 'Momo': return 'Momo';
        default: return $method ?: 'Chưa có';
    }
}
?>
<div class="container py-5">
    <h2 class="fw-bold mb-4">Thanh toán của tôi</h2>

    <style>
        .payment-list { display: grid; gap: 14px; }
        .payment-item { background: #fff; border: 1px solid #edf0f3; border-radius: 10px; box-shadow: 0 0.15rem 0.75rem rgba(0, 0, 0, 0.05); padding: 16px; }
        .payment-head { display: flex; justify-content: space-between; gap: 16px; border-bottom: 1px solid #f1f3f5; padding-bottom: 12px; margin-bottom: 12px; }
        .payment-title { min-width: 0; }
        .payment-title strong, .payment-title span { display: block; }
        .payment-amount { min-width: 150px; text-align: right; }
        .payment-body { display: grid; grid-template-columns: minmax(190px, 0.8fr) minmax(260px, 1.2fr); gap: 16px; }
        .payment-line { display: flex; justify-content: space-between; gap: 12px; margin-bottom: 4px; }
        .payment-line span:first-child { color: #6c757d; }
        .payment-note { color: #6c757d; font-size: 0.9rem; }
        @media (max-width: 768px) {
            .payment-head, .payment-body { display: block; }
            .payment-amount { text-align: left; margin-top: 10px; }
            .payment-body > div + div { margin-top: 12px; }
        }
    </style>

    <?php if ($bookingSuccess): ?>
        <div class="alert alert-info">Đặt xe thành công. Admin sẽ xác nhận thanh toán sau khi nhận tiền mặt hoặc chuyển khoản.</div>
    <?php endif; ?>

    <?php if (count($payments) <= 0): ?>
        <div class="alert alert-info">Bạn chưa có khoản thanh toán nào.</div>
    <?php else: ?>
        <div class="payment-list">
            <?php foreach ($payments as $row): ?>
                <?php $remainingAmount = max(0, (float) $row['TotalPrice'] - (float) $row['DepositAmount']); ?>
                <div class="payment-item">
                    <div class="payment-head">
                        <div class="payment-title">
                            <strong>#<?= (int) $row['PaymentID'] ?> - <?= paymentTypeText($row['PaymentType']) ?></strong>
                            <span>Đơn #<?= (int) $row['BookingID'] ?> - <?= htmlspecialchars($row['CarName']) ?></span>
                            <small class="text-muted"><?= htmlspecialchars($row['LicensePlate']) ?></small>
                        </div>
                        <div class="payment-amount">
                            <div class="fw-bold text-danger"><?= money($row['Amount']) ?></div>
                            <span class="badge bg-<?= paymentBadge($row['Status']) ?>"><?= paymentStatusText($row['Status']) ?></span>
                        </div>
                    </div>

                    <div class="payment-body">
                        <div>
                            <div class="payment-line"><span>Phương thức</span><strong><?= htmlspecialchars(paymentMethodText($row['PaymentMethod'])) ?></strong></div>
                            <div class="payment-line"><span>Mã giao dịch</span><strong><?= htmlspecialchars($row['TransactionCode'] ?: 'Chưa có') ?></strong></div>
                            <div class="payment-line"><span>Ngày thanh toán</span><strong><?= htmlspecialchars($row['PaymentDate'] ?: 'Chưa có') ?></strong></div>
                        </div>

                        <div>
                            <?php if ($row['PaymentType'] === 'Final'): ?>
                                <div class="payment-line"><span>Tổng thuê</span><strong><?= money($row['TotalPrice']) ?></strong></div>
                                <div class="payment-line"><span>Đã cọc</span><strong><?= money($row['DepositAmount']) ?></strong></div>
                                <div class="payment-line"><span>Còn lại</span><strong><?= money($remainingAmount) ?></strong></div>
                                <div class="payment-line"><span>Tiền phạt</span><strong><?= money($row['TotalPenalty']) ?></strong></div>
                                <div class="payment-note">
                                    Quá giờ: <?= money($row['OvertimeFee']) ?>, hư hỏng: <?= money($row['DamageFee']) ?>,
                                    vệ sinh: <?= money($row['CleaningFee']) ?>, khác: <?= money($row['OtherFee']) ?>.
                                    Lý do: <?= htmlspecialchars($row['PenaltyReason'] ?: 'Không có') ?>
                                </div>
                            <?php elseif ($row['PaymentType'] === 'Penalty'): ?>
                                <div class="payment-line"><span>Tổng phạt</span><strong><?= money($row['TotalPenalty']) ?></strong></div>
                                <div class="payment-note">
                                    Quá giờ: <?= money($row['OvertimeFee']) ?>, hư hỏng: <?= money($row['DamageFee']) ?>,
                                    vệ sinh: <?= money($row['CleaningFee']) ?>, khác: <?= money($row['OtherFee']) ?>.
                                    Lý do: <?= htmlspecialchars($row['PenaltyReason'] ?: 'Không có') ?>
                                </div>
                            <?php else: ?>
                                <div class="payment-note"><?= htmlspecialchars($row['Note'] ?: 'Không có ghi chú') ?></div>
                            <?php endif; ?>
                        </div>
                    </div>
                </div>
            <?php endforeach; ?>
        </div>
    <?php endif; ?>
</div>
