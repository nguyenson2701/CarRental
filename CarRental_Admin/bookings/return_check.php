<?php
require_once '../../CarRental_Backend/config/auth.php';
requireAdminOrStaff('../login.php');
require_once '../../CarRental_Backend/config/database.php';

$id = (int)($_GET['id'] ?? 0);

$stmt = $conn->prepare("
    SELECT b.*, c.CarName, c.LicensePlate, u.FullName, u.Phone
    FROM bookings b
    LEFT JOIN cars c ON b.CarID = c.CarID
    LEFT JOIN users u ON b.UserID = u.UserID
    WHERE b.BookingID = ?
    LIMIT 1
");
$stmt->bind_param("i", $id);
$stmt->execute();
$booking = $stmt->get_result()->fetch_assoc();

if (!$booking) {
    die('Không tìm thấy đơn.');
}

function imgUrl($path) {
    if (!$path) return '';
    return '../../CarRental_Frontend/' . ltrim($path, '/');
}
?>

<!DOCTYPE html>
<html lang="vi">
<head>
    <meta charset="UTF-8">
    <title>Kiểm tra trả xe</title>
    <link href="../vendor/fontawesome-free/css/all.min.css" rel="stylesheet">
    <link href="../css/sb-admin-2.min.css" rel="stylesheet">
</head>
<body class="bg-light p-4">

<div class="container">
    <h3 class="mb-4">Kiểm tra trả xe - Đơn #<?php echo (int)$booking['BookingID']; ?></h3>

    <div class="card shadow mb-4">
        <div class="card-body">
            <p><strong>Khách hàng:</strong> <?php echo htmlspecialchars($booking['FullName']); ?></p>
            <p><strong>SĐT:</strong> <?php echo htmlspecialchars($booking['Phone']); ?></p>
            <p><strong>Xe:</strong> <?php echo htmlspecialchars($booking['CarName']); ?> - <?php echo htmlspecialchars($booking['LicensePlate']); ?></p>
            <p><strong>Ngày trả dự kiến:</strong> <?php echo htmlspecialchars($booking['EndDate']); ?></p>
            <p><strong>Ngày trả thực tế:</strong> <?php echo htmlspecialchars($booking['ActualReturnDate']); ?></p>
            <p><strong>Phí quá giờ:</strong> <?php echo number_format($booking['OvertimeFee'], 0, ',', '.'); ?> VNĐ</p>
            <p><strong>Ghi chú khách:</strong> <?php echo htmlspecialchars($booking['ReturnNote']); ?></p>
        </div>
    </div>

    <div class="row mb-4">
        <div class="col-md-6">
            <div class="card shadow">
                <div class="card-header font-weight-bold">Ảnh đầu xe</div>
                <div class="card-body">
                    <img src="<?php echo htmlspecialchars(imgUrl($booking['ReturnFrontImage'])); ?>"
                         style="width:100%;height:300px;object-fit:cover;">
                </div>
            </div>
        </div>

        <div class="col-md-6">
            <div class="card shadow">
                <div class="card-header font-weight-bold">Ảnh sau xe</div>
                <div class="card-body">
                    <img src="<?php echo htmlspecialchars(imgUrl($booking['ReturnBackImage'])); ?>"
                         style="width:100%;height:300px;object-fit:cover;">
                </div>
            </div>
        </div>
    </div>

    <div class="card shadow">
        <div class="card-header font-weight-bold">Nhập tiền phạt nếu có</div>
        <div class="card-body">
            <form action="../../CarRental_Backend/api/admin/bookings/return_approve.php" method="POST">
                <input type="hidden" name="BookingID" value="<?php echo (int)$booking['BookingID']; ?>">

                <div class="form-group">
                    <label>Phí hư hỏng xe</label>
                    <input type="number" name="DamageFee" class="form-control" value="<?php echo (float)$booking['DamageFee']; ?>">
                </div>

                <div class="form-group">
                    <label>Phí vệ sinh</label>
                    <input type="number" name="CleaningFee" class="form-control" value="<?php echo (float)$booking['CleaningFee']; ?>">
                </div>

                <div class="form-group">
                    <label>Phí khác</label>
                    <input type="number" name="OtherFee" class="form-control" value="<?php echo (float)$booking['OtherFee']; ?>">
                </div>

                <div class="form-group">
                    <label>Lý do phạt</label>
                    <textarea name="PenaltyReason" class="form-control" rows="3"><?php echo htmlspecialchars($booking['PenaltyReason']); ?></textarea>
                </div>

                <button type="submit" class="btn btn-success">
                    Xác nhận trả xe
                </button>

                <a href="list.php" class="btn btn-secondary">
                    Quay lại
                </a>
            </form>
        </div>
    </div>
</div>

</body>
</html>