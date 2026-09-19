<?php
/**
 * @var array $bookings
 * @var bool $updated
 * @var bool $finalPaid
 * @var bool $paymentPaid
 */
use App\Core\Auth;

function formatMoney($value): string
{
    return number_format((float) $value, 0, ',', '.') . ' VNĐ';
}

function bookingBadgeClass(string $status): string
{
    switch ($status) {
        case 'Pending': return 'warning';
        case 'Confirmed': return 'info';
        case 'Paid': return 'primary';
        case 'Cancelled': return 'danger';
        case 'Completed': return 'success';
        default: return 'secondary';
    }
}

function bookingStatusText(string $status): string
{
    switch ($status) {
        case 'Pending': return 'Chưa xử lý';
        case 'Confirmed': return 'Đã xác nhận';
        case 'Paid': return 'Đã thanh toán';
        case 'Cancelled': return 'Đã hủy';
        case 'Completed': return 'Hoàn thành';
        default: return $status;
    }
}

function paymentTypeText(string $type): string
{
    switch ($type) {
        case 'Deposit': return 'Tiền cọc';
        case 'Rental': return 'Tiền thuê';
        case 'Final': return 'Thanh toán cuối';
        default: return $type;
    }
}
?>
<style>
    .table-booking th { white-space: nowrap; vertical-align: middle; }
    .table-booking td { vertical-align: middle; }
    .customer-box, .car-box { min-width: 180px; }
    .date-box { min-width: 150px; }
    .money-box { min-width: 130px; }
    .note-text { max-width: 220px; white-space: normal; color: #5a5c69; }
    .filter-bar .form-control, .filter-bar select { height: 42px; }
</style>

<div class="d-sm-flex align-items-center justify-content-between mb-4">
    <h1 class="h3 mb-0 text-gray-800">Quản lý đơn đặt xe</h1>
</div>

<?php if ($updated): ?>
    <div class="alert alert-success">Cập nhật trạng thái đơn đặt xe thành công.</div>
<?php endif; ?>
<?php if ($finalPaid): ?>
    <div class="alert alert-success">Admin đã xác nhận thanh toán cuối và hoàn tất đơn.</div>
<?php endif; ?>
<?php if ($paymentPaid): ?>
    <div class="alert alert-success">Admin đã xác nhận thanh toán thành công.</div>
<?php endif; ?>

<div class="card shadow mb-4">
    <div class="card-body">
        <div class="row filter-bar mb-4">
            <div class="col-md-6 mb-2">
                <input type="text" id="searchInput" class="form-control" placeholder="Tìm theo mã đơn, tên khách, email, tên xe, biển số...">
            </div>
            <div class="col-md-3 mb-2">
                <select id="statusFilter" class="form-control">
                    <option value="">Tất cả trạng thái</option>
                    <option value="chưa xử lý">Chưa giải quyết</option>
                    <option value="đã xác nhận">Đã xác nhận</option>
                    <option value="đã thanh toán">Đã thanh toán</option>
                    <option value="đã hủy">Đã hủy</option>
                    <option value="hoàn thành">Hoàn thành</option>
                </select>
            </div>
        </div>

        <div class="table-responsive">
            <table class="table table-bordered table-hover table-booking" id="bookingsTable" width="100%" cellspacing="0">
                <thead class="thead-light">
                    <tr>
                        <th>Mã đơn</th><th>Khách hàng</th><th>Xe</th><th>Ngày thuê</th>
                        <th>Địa điểm</th><th>Tiền</th><th>Trạng thái</th><th>Ghi chú</th><th width="170">Thao tác</th>
                    </tr>
                </thead>
                <tbody>
                <?php if (count($bookings) > 0): ?>
                    <?php foreach ($bookings as $row): ?>
                        <?php
                            $current = $row['Status'];
                            $totalReceivable = (float) $row['TotalPrice'] + (float) ($row['TotalPenalty'] ?? 0);
                            $totalPaid = (float) ($row['TotalPaid'] ?? 0);
                            $totalPending = (float) ($row['TotalPending'] ?? 0);
                            $remainingReceivable = max(0, $totalReceivable - $totalPaid);
                        ?>
                        <tr>
                            <td><strong>#<?= (int) $row['BookingID'] ?></strong><br><small class="text-muted"><?= htmlspecialchars($row['CreatedAt']) ?></small></td>
                            <td class="customer-box">
                                <strong><?= htmlspecialchars($row['FullName'] ?? 'N/A') ?></strong><br>
                                <small><?= htmlspecialchars($row['Email'] ?? '') ?></small><br>
                                <small><?= htmlspecialchars($row['Phone'] ?? '') ?></small>
                            </td>
                            <td class="car-box">
                                <strong><?= htmlspecialchars($row['CarName'] ?? 'N/A') ?></strong><br>
                                <small>Biển số: <?= htmlspecialchars($row['LicensePlate'] ?? '') ?></small>
                            </td>
                            <td class="date-box">
                                <strong>Nhận:</strong> <?= htmlspecialchars($row['StartDate']) ?><br>
                                <strong>Trả:</strong> <?= htmlspecialchars($row['EndDate']) ?><br>
                                <small><?= (int) $row['RentalDays'] ?> ngày</small>
                            </td>
                            <td>
                                <strong>Nhận:</strong> <?= htmlspecialchars($row['PickupLocation']) ?><br>
                                <strong>Trả:</strong> <?= htmlspecialchars($row['ReturnLocation']) ?>
                            </td>
                            <td class="money-box">
                                <strong>Tổng tiền:</strong> <?= formatMoney($totalReceivable) ?><br>
                                <small class="text-success">Đã thu: <?= formatMoney($totalPaid) ?></small><br>
                                <small class="text-danger">Còn lại: <?= formatMoney($remainingReceivable) ?></small>
                                <?php if ($totalPending > 0): ?>
                                    <br><small class="text-warning">Đang chờ: <?= formatMoney($totalPending) ?></small>
                                <?php endif; ?>
                            </td>
                            <td><span class="badge badge-<?= bookingBadgeClass($row['Status']) ?> badge-status"><?= htmlspecialchars(bookingStatusText($row['Status'])) ?></span></td>
                            <td class="note-text"><?= !empty($row['Note']) ? nl2br(htmlspecialchars($row['Note'])) : '<span class="text-muted">Không có</span>' ?></td>
                            <td>
                                <div class="d-flex flex-column" style="gap:6px;">
                                    <a href="/Carrental/admin/bookings/<?= (int) $row['BookingID'] ?>" class="btn btn-info btn-sm btn-block">Xem chi tiết</a>

                                    <?php if (($row['ReturnStatus'] ?? '') === 'Pending'): ?>
                                        <a href="/Carrental/admin/bookings/<?= (int) $row['BookingID'] ?>/return-check" class="btn btn-success btn-sm btn-block">Kiểm tra trả xe</a>
                                    <?php endif; ?>

                                    <?php if (($row['InitialPaymentStatus'] ?? '') === 'Pending' && !in_array($current, ['Cancelled', 'Completed'], true)): ?>
                                        <form action="/Carrental/admin/payments/<?= (int) $row['InitialPaymentID'] ?>/confirm" method="POST">
                                            <?= Auth::csrfField() ?>
                                            <input type="hidden" name="Redirect" value="list">
                                            <div class="input-group input-group-sm">
                                                <select name="PaymentMethod" class="form-control" required title="<?= htmlspecialchars(paymentTypeText($row['InitialPaymentType'])) ?>: <?= formatMoney($row['InitialAmount']) ?>">
                                                    <option value="Cash">Tiền mặt</option>
                                                    <option value="BankTransfer">Chuyển khoản</option>
                                                </select>
                                                <div class="input-group-append">
                                                    <button type="submit" class="btn btn-warning" title="Xác nhận đã thu <?= htmlspecialchars(paymentTypeText($row['InitialPaymentType'])) ?> <?= formatMoney($row['InitialAmount']) ?>">Thu cọc</button>
                                                </div>
                                            </div>
                                        </form>
                                    <?php endif; ?>

                                    <?php if (($row['ReturnStatus'] ?? '') === 'Approved' && ($row['FinalPaymentStatus'] ?? '') === 'Pending'): ?>
                                        <form action="/Carrental/admin/payments/<?= (int) $row['FinalPaymentID'] ?>/confirm-final" method="POST">
                                            <?= Auth::csrfField() ?>
                                            <input type="hidden" name="Redirect" value="list">
                                            <div class="input-group input-group-sm">
                                                <select name="PaymentMethod" class="form-control" required title="Thanh toán cuối: <?= formatMoney($row['FinalAmount']) ?>">
                                                    <option value="Cash">Tiền mặt</option>
                                                    <option value="BankTransfer">Chuyển khoản</option>
                                                </select>
                                                <div class="input-group-append">
                                                    <button type="submit" class="btn btn-warning" title="Xác nhận thanh toán cuối <?= formatMoney($row['FinalAmount']) ?>">Thu cuối</button>
                                                </div>
                                            </div>
                                        </form>
                                    <?php endif; ?>

                                    <?php if (in_array($current, ['Pending', 'Confirmed'], true)): ?>
                                        <form action="/Carrental/admin/bookings/<?= (int) $row['BookingID'] ?>/cancel" method="POST"
                                              onsubmit="return confirm('Hủy đơn #<?= (int) $row['BookingID'] ?>? Không thể hoàn tác.');">
                                            <?= Auth::csrfField() ?>
                                            <input type="hidden" name="Redirect" value="list">
                                            <button type="submit" class="btn btn-outline-danger btn-sm btn-block">Hủy đơn</button>
                                        </form>
                                    <?php endif; ?>
                                </div>
                            </td>
                        </tr>
                    <?php endforeach; ?>
                <?php else: ?>
                    <tr><td colspan="9" class="text-center text-muted">Chưa có đơn đặt xe nào.</td></tr>
                <?php endif; ?>
                </tbody>
            </table>
        </div>
    </div>
</div>

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
