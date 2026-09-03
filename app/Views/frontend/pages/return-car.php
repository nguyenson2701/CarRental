<?php
/**
 * @var array $booking
 * @var string $error
 */
use App\Core\Auth;
?>
<div class="container py-5">
    <div class="card shadow border-0 rounded-4">
        <div class="card-body p-4 p-lg-5">
            <h2 class="fw-bold mb-4">Gửi yêu cầu trả xe</h2>

            <?php if ($error !== ''): ?>
                <div class="alert alert-danger"><?= htmlspecialchars($error) ?></div>
            <?php endif; ?>

            <div class="row mb-4">
                <div class="col-md-4">
                    <img src="/Carrental/CarRental_Frontend/assets/img/cars/<?= htmlspecialchars($booking['MainImage']) ?>" class="img-fluid rounded-4" style="height:220px;width:100%;object-fit:cover;">
                </div>
                <div class="col-md-8">
                    <h4><?= htmlspecialchars($booking['CarName']) ?></h4>
                    <p><strong>Biển số:</strong> <?= htmlspecialchars($booking['LicensePlate']) ?></p>
                    <p><strong>Ngày nhận:</strong> <?= htmlspecialchars($booking['StartDate']) ?></p>
                    <p><strong>Ngày trả dự kiến:</strong> <?= htmlspecialchars($booking['EndDate']) ?></p>
                    <p><strong>Tổng tiền thuê:</strong> <?= number_format($booking['TotalPrice'], 0, ',', '.') ?> VNĐ</p>
                </div>
            </div>

            <form id="returnForm" action="/Carrental/my-bookings/<?= (int) $booking['BookingID'] ?>/return" method="POST" enctype="multipart/form-data">
                <?= Auth::csrfField() ?>

                <div class="mb-3">
                    <label class="form-label fw-bold">Ngày giờ trả xe thực tế</label>
                    <input type="datetime-local" name="ActualReturnDate" class="form-control" required>
                </div>

                <div class="row">
                    <div class="col-md-6 mb-3">
                        <label class="form-label">Ảnh đầu xe</label>
                        <input type="file" name="ReturnFrontImage" class="form-control" accept="image/*" capture="environment" required>
                    </div>
                    <div class="col-md-6 mb-3">
                        <label class="form-label">Ảnh sau xe</label>
                        <input type="file" name="ReturnBackImage" class="form-control" accept="image/*" capture="environment" required>
                    </div>
                </div>

                <div class="mb-4">
                    <label class="form-label">Ghi chú tình trạng xe</label>
                    <textarea name="ReturnNote" class="form-control" rows="4" placeholder="Ví dụ: xe bình thường, có xước nhẹ bên phải..."></textarea>
                </div>

                <button type="submit" class="btn btn-primary rounded-pill px-4">Gửi yêu cầu trả xe</button>
                <a href="/Carrental/my-bookings" class="btn btn-secondary rounded-pill px-4">Quay lại</a>
            </form>
        </div>
    </div>
</div>
