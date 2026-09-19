<?php
/** @var array $booking */
use App\Core\Auth;

function imgUrl(?string $path): string
{
    if (!$path) {
        return '';
    }
    return '/Carrental/public/frontend/' . ltrim($path, '/');
}
?>
<div class="d-sm-flex align-items-center justify-content-between mb-4">
    <h1 class="h3 mb-0 text-gray-800">Kiểm tra trả xe - Đơn #<?= (int) $booking['BookingID'] ?></h1>
    <a href="/Carrental/admin/bookings" class="btn btn-light btn-sm"><i class="fas fa-arrow-left mr-1"></i> Quay lại</a>
</div>

<div class="card shadow mb-4">
    <div class="card-body">
        <p><strong>Khách hàng:</strong> <?= htmlspecialchars($booking['FullName']) ?></p>
        <p><strong>SĐT:</strong> <?= htmlspecialchars($booking['Phone']) ?></p>
        <p><strong>Xe:</strong> <?= htmlspecialchars($booking['CarName']) ?> - <?= htmlspecialchars($booking['LicensePlate']) ?></p>
        <p><strong>Ngày trả dự kiến:</strong> <?= htmlspecialchars($booking['EndDate']) ?></p>
        <p><strong>Ngày trả thực tế:</strong> <?= htmlspecialchars($booking['ActualReturnDate']) ?></p>
        <p><strong>Phí quá giờ:</strong> <?= number_format($booking['OvertimeFee'], 0, ',', '.') ?> VNĐ</p>
        <p class="mb-0"><strong>Ghi chú khách:</strong> <?= htmlspecialchars($booking['ReturnNote']) ?></p>
    </div>
</div>

<div class="row mb-4">
    <div class="col-md-6 mb-4 mb-md-0">
        <div class="card shadow h-100">
            <div class="card-header font-weight-bold">Ảnh đầu xe</div>
            <div class="card-body">
                <img src="<?= htmlspecialchars(imgUrl($booking['ReturnFrontImage'])) ?>" style="width:100%;height:300px;object-fit:cover;border-radius:8px;">
            </div>
        </div>
    </div>
    <div class="col-md-6">
        <div class="card shadow h-100">
            <div class="card-header font-weight-bold">Ảnh sau xe</div>
            <div class="card-body">
                <img src="<?= htmlspecialchars(imgUrl($booking['ReturnBackImage'])) ?>" style="width:100%;height:300px;object-fit:cover;border-radius:8px;">
            </div>
        </div>
    </div>
</div>

<div class="card shadow mb-4">
    <div class="card-header font-weight-bold">Nhập tiền phạt nếu có</div>
    <div class="card-body">
        <form action="/Carrental/admin/bookings/<?= (int) $booking['BookingID'] ?>/return-approve" method="POST">
            <?= Auth::csrfField() ?>

            <div class="form-group">
                <label>Phí hư hỏng xe</label>
                <input type="number" name="DamageFee" class="form-control" value="<?= (float) $booking['DamageFee'] ?>">
            </div>
            <div class="form-group">
                <label>Phí vệ sinh</label>
                <input type="number" name="CleaningFee" class="form-control" value="<?= (float) $booking['CleaningFee'] ?>">
            </div>
            <div class="form-group">
                <label>Phí khác</label>
                <input type="number" name="OtherFee" class="form-control" value="<?= (float) $booking['OtherFee'] ?>">
            </div>
            <div class="form-group">
                <label>Lý do phạt</label>
                <textarea name="PenaltyReason" class="form-control" rows="3"><?= htmlspecialchars($booking['PenaltyReason']) ?></textarea>
            </div>

            <button type="submit" class="btn btn-success">Xác nhận trả xe</button>
            <a href="/Carrental/admin/bookings" class="btn btn-secondary">Hủy</a>
        </form>
    </div>
</div>
