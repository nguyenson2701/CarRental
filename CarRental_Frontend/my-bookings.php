<?php
require_once '../CarRental_Backend/config/auth.php';
requireLogin('login.php');
require_once '../CarRental_Backend/config/database.php';

$pageTitle = 'Lịch sử đặt xe - VinaDrive';
$activePage = 'my-bookings';

$userID = (int)($_SESSION['user_id'] ?? 0);

$stmt = $conn->prepare("
    SELECT b.*, b.TotalPenalty, b.PenaltyReason, c.CarName
    FROM bookings b
    INNER JOIN cars c ON b.CarID = c.CarID
    WHERE b.UserID = ?
    ORDER BY b.BookingID DESC
");
$stmt->bind_param("i", $userID);
$stmt->execute();
$result = $stmt->get_result();

include 'includes/header.php';
?>

<div class="container py-5">
    <div class="bg-white p-4 rounded shadow-sm">
        <h1 class="mb-4">Lịch sử đặt xe</h1>

        <?php if (isset($_GET['success'])): ?>
            <div class="alert alert-success">Đặt xe thành công.</div>
        <?php endif; ?>

        <div class="table-responsive">
            <table class="table table-bordered align-middle">
                <thead class="table-light">
                    <tr>
                        <th>Mã đơn</th>
                        <th>Xe</th>
                        <th>Ngày nhận</th>
                        <th>Ngày trả</th>
                        <th>Số ngày</th>
                        <th>Tổng tiền</th>
                        <th>Trạng thái</th>
                        <th>Trả xe</th>
                    </tr>
                </thead>
                <tbody>
                    <?php if ($result && $result->num_rows > 0): ?>
                        <?php while ($row = $result->fetch_assoc()): ?>
                            <?php
                                $status = $row['Status'] ?? '';
                                $returnStatus = $row['ReturnStatus'] ?? '';
                                $canReturn = ($status === 'Confirmed')
                                    && ($returnStatus === '' || $returnStatus === 'NotReturned');
                            ?>
                            <tr>
                                <td><?php echo (int)$row['BookingID']; ?></td>
                                <td><?php echo htmlspecialchars($row['CarName']); ?></td>
                                <td><?php echo htmlspecialchars($row['StartDate']); ?></td>
                                <td><?php echo htmlspecialchars($row['EndDate']); ?></td>
                                <td><?php echo (int)$row['RentalDays']; ?></td>
                                <td><?php echo number_format($row['TotalPrice'], 0, ',', '.'); ?> VNĐ</td>
                                <td><?php echo htmlspecialchars($status); ?></td>
                                <td>
                                    <?php if (($returnStatus === 'Approved') && $status !== 'Completed'): ?>
                                        <?php
                                            $remainingAmount = max(0, (float)$row['TotalPrice'] - (float)$row['DepositAmount']);
                                            $finalAmount = $remainingAmount + (float)($row['TotalPenalty'] ?? 0);
                                        ?>
                                        <div class="alert alert-warning py-2 px-3 mb-2">
                                            <strong>Cần thanh toán cuối:</strong>
                                            <?php echo number_format($finalAmount, 0, ',', '.'); ?> VNĐ
                                            <br>
                                            <strong>Tiền còn lại:</strong>
                                            <?php echo number_format($remainingAmount, 0, ',', '.'); ?> VNĐ
                                            <br>
                                            <strong>Tiền phạt:</strong>
                                            <?php echo number_format($row['TotalPenalty'] ?? 0, 0, ',', '.'); ?> VNĐ
                                            <br>
                                            <strong>Lý do phạt:</strong>
                                            <?php echo htmlspecialchars($row['PenaltyReason'] ?: 'Không có'); ?>
                                        </div>

                                        <div class="text-muted small">
                                            Admin sẽ xác nhận khoản này sau khi nhận tiền mặt hoặc chuyển khoản.
                                        </div>
                                    <?php endif; ?>

                                    <?php if ($canReturn): ?>
                                        <a href="return-car.php?booking_id=<?php echo (int)$row['BookingID']; ?>" class="btn btn-success btn-sm rounded-pill">
                                            <i class="fas fa-undo-alt me-1"></i>
                                            Gửi trả xe
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
                        <?php endwhile; ?>
                    <?php else: ?>
                        <tr>
                            <td colspan="8" class="text-center text-muted py-4">
                                Bạn chưa có đơn đặt xe nào.
                            </td>
                        </tr>
                    <?php endif; ?>
                </tbody>
            </table>
        </div>
    </div>
</div>

<?php include 'includes/footer.php'; ?>
