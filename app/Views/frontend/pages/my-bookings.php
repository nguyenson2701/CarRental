<?php
/**
 * @var array $bookings
 * @var bool $success
 * @var bool $returnRequest
 */
?>
<div class="container py-5">
    <div class="bg-white p-4 rounded shadow-sm">
        <h1 class="mb-4">Lịch sử đặt xe</h1>

        <?php if ($success): ?>
            <div class="alert alert-success">Đặt xe thành công.</div>
        <?php endif; ?>
        <?php if ($returnRequest): ?>
            <div class="alert alert-success">Đã gửi yêu cầu trả xe, vui lòng chờ admin xác nhận.</div>
        <?php endif; ?>

        <div class="table-responsive">
            <table class="table table-bordered align-middle">
                <thead class="table-light">
                    <tr>
                        <th>Mã đơn</th><th>Xe</th><th>Ngày nhận</th><th>Ngày trả</th>
                        <th>Số ngày</th><th>Tổng tiền</th><th>Trạng thái</th><th>Trả xe</th>
                    </tr>
                </thead>
                <tbody>
                    <?php if (count($bookings) > 0): ?>
                        <?php foreach ($bookings as $row): ?>
                            <?php
                                $status = $row['Status'] ?? '';
                                $returnStatus = $row['ReturnStatus'] ?? '';
                                $canReturn = ($status === 'Confirmed') && ($returnStatus === '' || $returnStatus === 'NotReturned');
                            ?>
                            <tr>
                                <td><?= (int) $row['BookingID'] ?></td>
                                <td><?= htmlspecialchars($row['CarName']) ?></td>
                                <td><?= htmlspecialchars($row['StartDate']) ?></td>
                                <td><?= htmlspecialchars($row['EndDate']) ?></td>
                                <td><?= (int) $row['RentalDays'] ?></td>
                                <td><?= number_format($row['TotalPrice'], 0, ',', '.') ?> VNĐ</td>
                                <td><?= htmlspecialchars($status) ?></td>
                                <td>
                                    <?php if (($returnStatus === 'Approved') && $status !== 'Completed'): ?>
                                        <?php
                                            $remainingAmount = max(0, (float) $row['TotalPrice'] - (float) $row['DepositAmount']);
                                            $finalAmount = $remainingAmount + (float) ($row['TotalPenalty'] ?? 0);
                                        ?>
                                        <div class="alert alert-warning py-2 px-3 mb-2">
                                            <strong>Cần thanh toán cuối:</strong> <?= number_format($finalAmount, 0, ',', '.') ?> VNĐ<br>
                                            <strong>Tiền còn lại:</strong> <?= number_format($remainingAmount, 0, ',', '.') ?> VNĐ<br>
                                            <strong>Tiền phạt:</strong> <?= number_format($row['TotalPenalty'] ?? 0, 0, ',', '.') ?> VNĐ<br>
                                            <strong>Lý do phạt:</strong> <?= htmlspecialchars($row['PenaltyReason'] ?: 'Không có') ?>
                                        </div>
                                        <div class="text-muted small">Admin sẽ xác nhận khoản này sau khi nhận tiền mặt hoặc chuyển khoản.</div>
                                    <?php endif; ?>

                                    <?php if ($canReturn): ?>
                                        <a href="/Carrental/my-bookings/<?= (int) $row['BookingID'] ?>/return" class="btn btn-success btn-sm rounded-pill">
                                            <i class="fas fa-undo-alt me-1"></i> Gửi trả xe
                                        </a>
                                    <?php elseif ($returnStatus === 'Pending'): ?>
                                        <span class="badge bg-warning text-dark">Đang chờ admin kiểm tra trả xe</span>
                                    <?php elseif ($returnStatus === 'Approved'): ?>
                                        <span class="badge bg-success">Đã xác nhận trả xe</span>
                                    <?php elseif ($returnStatus === 'Rejected'): ?>
                                        <span class="badge bg-danger">Bị từ chối</span>
                                    <?php else: ?>
                                        <span class="text-muted">-</span>
                                    <?php endif; ?>
                                </td>
                            </tr>
                        <?php endforeach; ?>
                    <?php else: ?>
                        <tr><td colspan="8" class="text-center text-muted py-4">Bạn chưa có đơn đặt xe nào.</td></tr>
                    <?php endif; ?>
                </tbody>
            </table>
        </div>
    </div>
</div>
