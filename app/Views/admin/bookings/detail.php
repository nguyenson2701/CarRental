<?php
/**
 * @var array $booking
 * @var array $paymentRows
 * @var bool $finalPaid
 * @var bool $paymentPaid
 */
use App\Core\Auth;

function formatMoney($value): string
{
    return number_format((float) $value, 0, ',', '.') . ' VNĐ';
}

function paymentTypeText(?string $type): string
{
    switch ($type) {
        case 'Deposit': return 'Tiền cọc';
        case 'Rental': return 'Tiền thuê';
        case 'Final': return 'Thanh toán cuối';
        default: return (string) $type;
    }
}

/**
 * Hien thi gia tri dang text tu DB, tra ve $fallback neu rong/NULL hoac
 * chi la "0" (gia tri placeholder cu, khong phai du lieu that).
 */
function displayOrDefault($value, string $fallback = 'Chưa có'): string
{
    $value = trim((string) ($value ?? ''));
    return ($value === '' || $value === '0') ? $fallback : $value;
}

function transmissionText(?string $value): string
{
    switch ($value) {
        case 'Automatic': return 'Số tự động';
        case 'Manual': return 'Số sàn';
        default: return displayOrDefault($value);
    }
}

function fuelTypeText(?string $value): string
{
    switch ($value) {
        case 'Gasoline': return 'Xăng';
        case 'Diesel': return 'Dầu diesel';
        case 'Electric': return 'Điện';
        case 'Hybrid': return 'Hybrid';
        default: return displayOrDefault($value);
    }
}

function statusLabel(string $status): array
{
    switch ($status) {
        case 'Pending': return ['Chưa giải quyết', 'warning'];
        case 'Confirmed': return ['Đã xác nhận', 'info'];
        case 'Paid': return ['Đã thanh toán', 'primary'];
        case 'Cancelled': return ['Đã hủy', 'danger'];
        case 'Completed': return ['Hoàn thành', 'success'];
        default: return [$status, 'secondary'];
    }
}

[$statusText, $statusClass] = statusLabel($booking['Status']);

$rentalDays = (int) ($booking['RentalDays'] ?? 0);
$pricePerDay = (float) ($booking['PricePerDay'] ?? 0);
$rentalSubtotal = max(0, $rentalDays * $pricePerDay);
$discountAmount = (float) ($booking['DiscountAmount'] ?? 0);
$rentalTotal = (float) ($booking['TotalPrice'] ?? 0);
$depositAmount = (float) ($booking['DepositAmount'] ?? 0);
$overtimeFee = (float) ($booking['OvertimeFee'] ?? 0);
$damageFee = (float) ($booking['DamageFee'] ?? 0);
$cleaningFee = (float) ($booking['CleaningFee'] ?? 0);
$otherFee = (float) ($booking['OtherFee'] ?? 0);
$totalPenalty = isset($booking['TotalPenalty']) && $booking['TotalPenalty'] !== null
    ? (float) $booking['TotalPenalty']
    : ($overtimeFee + $damageFee + $cleaningFee + $otherFee);
$grandTotal = $rentalTotal + $totalPenalty;
$remainingRentalAmount = max(0, $rentalTotal - $depositAmount);
$computedFinalAmount = $remainingRentalAmount + $totalPenalty;
$displayFinalAmount = isset($booking['FinalAmount']) && $booking['FinalAmount'] !== null
    ? (float) $booking['FinalAmount']
    : $computedFinalAmount;

$paidAmount = 0;
foreach ($paymentRows as $payment) {
    if (($payment['Status'] ?? '') === 'Paid') {
        $paidAmount += (float) $payment['Amount'];
    }
}
$remainingAmount = max(0, $grandTotal - $paidAmount);
?>
<div class="container-fluid booking-detail-page">
    <div class="detail-page-header">
        <div>
            <span class="detail-kicker">Đơn đặt xe #<?= (int) $booking['BookingID'] ?></span>
            <h1>Chi tiết đơn đặt xe</h1>
        </div>
        <div class="detail-header-actions">
            <a href="/Carrental/admin/bookings" class="btn btn-light btn-sm"><i class="fas fa-arrow-left mr-1"></i> Quay lại</a>
        </div>
    </div>

    <?php if ($finalPaid): ?>
        <div class="alert alert-success">Admin đã xác nhận thanh toán cuối và hoàn tất đơn.</div>
    <?php endif; ?>
    <?php if ($paymentPaid): ?>
        <div class="alert alert-success">Admin đã xác nhận thanh toán thành công.</div>
    <?php endif; ?>

    <section class="detail-hero mb-4">
        <div class="detail-hero-main">
            <span class="badge badge-<?= $statusClass ?> badge-lg"><?= htmlspecialchars($statusText) ?></span>
            <h2><?= htmlspecialchars($booking['CarName'] ?? 'N/A') ?></h2>
            <div class="hero-meta">
                <span><i class="fas fa-user mr-1"></i><?= htmlspecialchars($booking['FullName'] ?? 'N/A') ?></span>
                <span><i class="fas fa-calendar-alt mr-1"></i><?= htmlspecialchars($booking['StartDate']) ?> - <?= htmlspecialchars($booking['EndDate']) ?></span>
                <span><i class="fas fa-map-marker-alt mr-1"></i><?= htmlspecialchars($booking['PickupLocation']) ?></span>
            </div>
        </div>
        <div class="detail-hero-total">
            <span>Tổng chi phí</span>
            <strong><?= formatMoney($grandTotal) ?></strong>
        </div>
    </section>

    <div class="row">
        <div class="col-lg-5 mb-4">
            <div class="detail-sticky-col">
                <div class="car-image-box mb-4">
                    <?php if (!empty($booking['MainImage'])): ?>
                        <img src="/Carrental/CarRental_Frontend/assets/img/cars/<?= htmlspecialchars($booking['MainImage']) ?>" alt="Car">
                    <?php else: ?>
                        <div class="car-image-placeholder"><i class="fas fa-car"></i><span>Chưa có ảnh xe</span></div>
                    <?php endif; ?>
                    <div class="car-body">
                        <div class="car-compact-title">
                            <h5 class="font-weight-bold"><?= htmlspecialchars($booking['CarName'] ?? 'N/A') ?></h5>
                            <span class="car-plate"><?= htmlspecialchars($booking['LicensePlate'] ?? '') ?></span>
                        </div>
                        <div class="car-spec-grid">
                            <div class="car-spec-item"><i class="fas fa-cogs"></i><div><span>Hộp số</span><strong><?= htmlspecialchars(transmissionText($booking['Transmission'] ?? '')) ?></strong></div></div>
                            <div class="car-spec-item"><i class="fas fa-gas-pump"></i><div><span>Nhiên liệu</span><strong><?= htmlspecialchars(fuelTypeText($booking['FuelType'] ?? '')) ?></strong></div></div>
                            <div class="car-spec-item"><i class="fas fa-users"></i><div><span>Số ghế</span><strong><?= (int) ($booking['Seats'] ?? 0) ?> chỗ</strong></div></div>
                            <div class="car-spec-item"><i class="fas fa-palette"></i><div><span>Màu xe</span><strong><?= htmlspecialchars(displayOrDefault($booking['Color'] ?? '')) ?></strong></div></div>
                            <div class="car-spec-item"><i class="fas fa-map-marker-alt"></i><div><span>Vị trí xe</span><strong><?= htmlspecialchars(displayOrDefault($booking['Location'] ?? '')) ?></strong></div></div>
                        </div>
                    </div>
                </div>

                <div class="section-card mb-4">
                    <div class="section-title"><i class="fas fa-user text-primary"></i> Thông tin khách hàng</div>
                    <div class="info-row"><div class="info-label">Họ tên</div><div class="info-value"><?= htmlspecialchars($booking['FullName'] ?? 'N/A') ?></div></div>
                    <div class="info-row"><div class="info-label">Email</div><div class="info-value"><?= htmlspecialchars($booking['Email'] ?? '') ?></div></div>
                    <div class="info-row"><div class="info-label">Số điện thoại</div><div class="info-value"><?= htmlspecialchars($booking['Phone'] ?? '') ?></div></div>
                    <div class="info-row"><div class="info-label">Địa chỉ</div><div class="info-value"><?= htmlspecialchars($booking['Address'] ?? '') ?></div></div>
                </div>

                <div class="section-card location-compact-card mb-4">
                    <div class="section-title"><i class="fas fa-route text-primary"></i> Địa điểm giao nhận</div>
                    <div class="location-list">
                        <div class="location-item"><div class="location-icon"><i class="fas fa-sign-out-alt"></i></div><div><span>Nhận xe</span><strong><?= htmlspecialchars($booking['PickupLocation']) ?></strong></div></div>
                        <div class="location-item"><div class="location-icon"><i class="fas fa-sign-in-alt"></i></div><div><span>Trả xe</span><strong><?= htmlspecialchars($booking['ReturnLocation']) ?></strong></div></div>
                    </div>
                </div>

                <div class="section-card">
                    <div class="section-title"><i class="fas fa-calendar-alt text-primary"></i> Thời gian thuê</div>
                    <div class="info-row"><div class="info-label">Ngày nhận</div><div class="info-value"><?= htmlspecialchars($booking['StartDate']) ?></div></div>
                    <div class="info-row"><div class="info-label">Ngày trả</div><div class="info-value"><?= htmlspecialchars($booking['EndDate']) ?></div></div>
                    <div class="info-row"><div class="info-label">Số ngày</div><div class="info-value"><?= (int) $booking['RentalDays'] ?> ngày</div></div>
                    <div class="info-row"><div class="info-label">Ngày tạo đơn</div><div class="info-value"><?= htmlspecialchars($booking['CreatedAt']) ?></div></div>
                </div>
            </div>
        </div>

        <div class="col-lg-7">
            <div class="row">
                <div class="col-12 mb-4">
                    <div class="section-card">
                        <div class="section-title"><i class="fas fa-money-bill-wave text-primary"></i> Thông tin thanh toán</div>

                        <div class="payment-summary">
                            <div class="payment-summary-item"><span>Tổng chi phí</span><strong><?= formatMoney($grandTotal) ?></strong></div>
                            <div class="payment-summary-item"><span>Đã thu</span><strong class="text-success"><?= formatMoney($paidAmount) ?></strong></div>
                            <div class="payment-summary-item"><span>Còn phải thu</span><strong class="<?= $remainingAmount > 0 ? 'text-danger' : 'text-success' ?>"><?= formatMoney($remainingAmount) ?></strong></div>
                        </div>

                        <div class="row">
                            <div class="col-lg-6 mb-3 mb-lg-0">
                                <h6 class="font-weight-bold text-gray-800 mb-3">Chi phí thuê xe</h6>
                                <div class="info-row"><div class="info-label">Giá/ngày</div><div class="info-value"><?= formatMoney($pricePerDay) ?></div></div>
                                <div class="info-row"><div class="info-label">Số ngày thuê</div><div class="info-value"><?= $rentalDays ?> ngày</div></div>
                                <div class="info-row"><div class="info-label">Tiền thuê gốc</div><div class="info-value"><?= formatMoney($rentalSubtotal) ?></div></div>
                                <div class="info-row"><div class="info-label">Giảm giá</div><div class="info-value"><?= formatMoney($discountAmount) ?></div></div>
                                <div class="info-row"><div class="info-label">Tiền thuê sau giảm</div><div class="info-value text-primary font-weight-bold"><?= formatMoney($rentalTotal) ?></div></div>
                                <div class="info-row"><div class="info-label">Tiền cọc giữ xe</div><div class="info-value"><?= formatMoney($depositAmount) ?></div></div>
                            </div>
                            <div class="col-lg-6">
                                <h6 class="font-weight-bold text-gray-800 mb-3">Phí phát sinh khi trả xe</h6>
                                <div class="info-row"><div class="info-label">Phí quá giờ</div><div class="info-value"><?= formatMoney($overtimeFee) ?></div></div>
                                <div class="info-row"><div class="info-label">Phí hư hỏng</div><div class="info-value"><?= formatMoney($damageFee) ?></div></div>
                                <div class="info-row"><div class="info-label">Phí vệ sinh</div><div class="info-value"><?= formatMoney($cleaningFee) ?></div></div>
                                <div class="info-row"><div class="info-label">Phí khác</div><div class="info-value"><?= formatMoney($otherFee) ?></div></div>
                                <div class="info-row"><div class="info-label">Tổng phí/phạt</div><div class="info-value text-danger font-weight-bold"><?= formatMoney($totalPenalty) ?></div></div>
                                <div class="info-row"><div class="info-label">Thanh toán cuối dự kiến</div><div class="info-value text-danger font-weight-bold"><?= formatMoney($displayFinalAmount) ?></div></div>
                            </div>
                        </div>

                        <div class="payment-total-row">
                            <div class="info-row">
                                <div class="info-label">Cách tính thanh toán cuối</div>
                                <div class="info-value text-left">(Tiền thuê sau giảm - tiền cọc) + tổng phí/phạt = <?= formatMoney($displayFinalAmount) ?>.</div>
                            </div>
                        </div>
                    </div>
                </div>

                <?php if (($booking['ReturnStatus'] ?? '') === 'Pending'): ?>
                    <div class="col-12 mb-4">
                        <div class="section-card final-payment-card">
                            <div class="section-title"><i class="fas fa-clipboard-check text-primary"></i> Khách đã gửi yêu cầu trả xe</div>
                            <p class="text-muted mb-3">Kiểm tra ảnh đầu/sau xe khách gửi lên và nhập phí phát sinh (nếu có) trước khi xác nhận.</p>
                            <a href="/Carrental/admin/bookings/<?= (int) $booking['BookingID'] ?>/return-check" class="btn btn-success"><i class="fas fa-clipboard-check mr-1"></i> Kiểm tra trả xe</a>
                        </div>
                    </div>
                <?php endif; ?>

                <?php if (($booking['InitialPaymentStatus'] ?? '') === 'Pending' && !in_array($booking['Status'], ['Cancelled', 'Completed'], true)): ?>
                    <div class="col-12 mb-4">
                        <div class="section-card final-payment-card">
                            <div class="section-title"><i class="fas fa-cash-register text-primary"></i> Xác nhận thanh toán <?= mb_strtolower(paymentTypeText($booking['InitialPaymentType']), 'UTF-8') ?></div>
                            <form action="/Carrental/admin/payments/<?= (int) $booking['InitialPaymentID'] ?>/confirm" method="POST" class="row">
                                <?= Auth::csrfField() ?>
                                <input type="hidden" name="Redirect" value="detail">
                                <div class="col-md-4 mb-2"><input type="text" class="form-control" value="<?= formatMoney($booking['InitialAmount']) ?>" disabled></div>
                                <div class="col-md-4 mb-2">
                                    <select name="PaymentMethod" class="form-control" required>
                                        <option value="Cash">Tiền mặt</option>
                                        <option value="BankTransfer">Chuyển khoản</option>
                                    </select>
                                </div>
                                <div class="col-md-4 mb-2"><button type="submit" class="btn btn-warning btn-block">Xác nhận đã thu</button></div>
                            </form>
                        </div>
                    </div>
                <?php endif; ?>

                <?php if (($booking['ReturnStatus'] ?? '') === 'Approved' && ($booking['FinalPaymentStatus'] ?? '') === 'Pending'): ?>
                    <div class="col-12 mb-4">
                        <div class="section-card final-payment-card">
                            <div class="section-title"><i class="fas fa-cash-register text-primary"></i> Xác nhận thanh toán cuối</div>
                            <form action="/Carrental/admin/payments/<?= (int) $booking['FinalPaymentID'] ?>/confirm-final" method="POST" class="row">
                                <?= Auth::csrfField() ?>
                                <input type="hidden" name="Redirect" value="detail">
                                <div class="col-md-4 mb-2"><input type="text" class="form-control" value="<?= formatMoney($displayFinalAmount) ?>" disabled></div>
                                <div class="col-md-4 mb-2">
                                    <select name="PaymentMethod" class="form-control" required>
                                        <option value="Cash">Tiền mặt</option>
                                        <option value="BankTransfer">Chuyển khoản</option>
                                    </select>
                                </div>
                                <div class="col-md-4 mb-2"><button type="submit" class="btn btn-warning btn-block">Xác nhận đã thu</button></div>
                            </form>
                        </div>
                    </div>
                <?php endif; ?>

                <div class="col-12 mb-4">
                    <div class="section-card">
                        <div class="section-title"><i class="fas fa-sticky-note text-primary"></i> Ghi chú của khách</div>
                        <div class="note-box"><?= !empty($booking['Note']) ? htmlspecialchars($booking['Note']) : 'Không có ghi chú' ?></div>
                    </div>
                </div>

                <?php if (in_array($booking['Status'], ['Pending', 'Confirmed'], true)): ?>
                    <div class="col-12 mb-4">
                        <div class="section-card status-update-card">
                            <div class="section-title"><i class="fas fa-edit text-primary"></i> Hủy đơn</div>
                            <p class="text-muted mb-3">Đơn đang ở trạng thái "<?= htmlspecialchars($statusText) ?>", vẫn có thể hủy nếu cần.</p>
                            <form action="/Carrental/admin/bookings/<?= (int) $booking['BookingID'] ?>/cancel" method="POST"
                                  onsubmit="return confirm('Hủy đơn #<?= (int) $booking['BookingID'] ?>? Không thể hoàn tác.');">
                                <?= Auth::csrfField() ?>
                                <input type="hidden" name="Redirect" value="detail">
                                <button type="submit" class="btn btn-outline-danger"><i class="fas fa-times mr-1"></i> Hủy đơn này</button>
                            </form>
                        </div>
                    </div>
                <?php endif; ?>
            </div>
        </div>
    </div>
</div>
