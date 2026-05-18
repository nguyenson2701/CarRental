<?php
require_once '../CarRental_Backend/config/database.php';

$pageTitle = 'Đặt xe - VinaDrive';
$activePage = 'vehicle';
$pageStyles = ['assets/css/booking.css?v=1'];
$pageScripts = ['assets/js/booking.js?v=1'];
include 'includes/header.php';

$carID = (int)($_GET['car_id'] ?? 0);
if ($carID <= 0) {
    echo '<div class="container py-5"><h3>Xe không hợp lệ.</h3></div>';
    include 'includes/footer.php';
    exit();
}

$stmt = $conn->prepare("
    SELECT CarID, CarName, PricePerDay, DepositAmount, MainImage, Status, Transmission, FuelType, Seats, Description
    FROM cars
    WHERE CarID = ?
");
$stmt->bind_param("i", $carID);
$stmt->execute();
$result = $stmt->get_result();
$car = $result->fetch_assoc();

if (!$car) {
    echo '<div class="container py-5"><h3>Không tìm thấy xe.</h3></div>';
    include 'includes/footer.php';
    exit();
}

$stmtImgs = $conn->prepare("
    SELECT ImageURL, IsMain
    FROM carimages
    WHERE CarID = ?
    ORDER BY IsMain DESC, ImageID ASC
    LIMIT 6
");
$stmtImgs->bind_param("i", $carID);
$stmtImgs->execute();
$resultImgs = $stmtImgs->get_result();
?>



<div class="container booking-wrap">
    <div class="booking-hero text-center">
        <h1 class="display-5 text-white mb-3">Đặt lịch thuê xe nhanh chóng</h1>
        <p class="mb-0 fs-5">Kiểm tra lịch trống theo thời gian thực, xem giá thuê và hoàn tất đặt xe chỉ trong vài bước.</p>
    </div>

    <div class="booking-card">
        <div class="row g-0">
            <div class="col-lg-7">
                <div class="booking-left">
                    <img
                        id="mainPreview"
                        src="assets/img/cars/<?php echo htmlspecialchars($car['MainImage']); ?>"
                        alt="<?php echo htmlspecialchars($car['CarName']); ?>"
                        class="car-main-img">

                    <div class="thumb-list">
                        <img src="assets/img/cars/<?php echo htmlspecialchars($car['MainImage']); ?>" alt="main" onclick="changeMainImage(this.src)">
                        <?php while ($img = $resultImgs->fetch_assoc()): ?>
                            <img src="assets/img/cars/<?php echo htmlspecialchars($img['ImageURL']); ?>" alt="thumb" onclick="changeMainImage(this.src)">
                        <?php endwhile; ?>
                    </div>

                    <div class="mt-4">
                        <div class="car-title"><?php echo htmlspecialchars($car['CarName']); ?></div>

                        <div class="meta-grid">
                            <div class="meta-item">
                                <strong>Hộp số</strong>
                                <span><?php echo htmlspecialchars($car['Transmission']); ?></span>
                            </div>
                            <div class="meta-item">
                                <strong>Nhiên liệu</strong>
                                <span><?php echo htmlspecialchars($car['FuelType']); ?></span>
                            </div>
                            <div class="meta-item">
                                <strong>Số ghế</strong>
                                <span><?php echo (int)$car['Seats']; ?> chỗ</span>
                            </div>
                            <div class="meta-item">
                                <strong>Trạng thái</strong>
                                <span><?php echo htmlspecialchars($car['Status']); ?></span>
                            </div>
                        </div>

                        <div class="price-box">
                            <div class="mb-2">Giá thuê từ</div>
                            <div class="big"><?php echo number_format($car['PricePerDay'], 0, ',', '.'); ?> VNĐ/ngày</div>
                            <div class="mt-3">Tiền cọc: <?php echo number_format($car['DepositAmount'], 0, ',', '.'); ?> VNĐ</div>
                        </div>

                        <div class="calendar-card">
                            <h5>Lịch bận của xe</h5>
                            <ul id="calendarList" class="calendar-list">
                                <li>Đang tải dữ liệu...</li>
                            </ul>
                        </div>

                        <div class="mt-4">
                            <h5 class="fw-bold mb-3">Mô tả</h5>
                            <p class="text-muted mb-0">
                                <?php echo nl2br(htmlspecialchars($car['Description'] ?? 'Chưa có mô tả cho xe này.')); ?>
                            </p>
                        </div>
                    </div>
                </div>
            </div>

            <div class="col-lg-5">
                <div class="booking-right">
                    <div class="booking-form-title">Thông tin đặt lịch</div>
                    <div class="booking-form-subtitle">Điền đầy đủ thông tin để kiểm tra lịch trống và gửi yêu cầu đặt xe.</div>

                    <form action="../CarRental_Backend/api/bookings/store.php" method="POST" id="bookingForm">
                        <input type="hidden"
                            name="CarID"
                            id="CarID"
                            value="<?php echo (int)$car['CarID']; ?>"
                            data-price-per-day="<?php echo (float)$car['PricePerDay']; ?>">

                        <div class="mb-3">
                            <label class="form-label">Ngày nhận xe</label>
                            <input type="date" name="StartDate" id="StartDate" class="form-control" required>
                        </div>

                        <div class="mb-3">
                            <label class="form-label">Ngày trả xe</label>
                            <input type="date" name="EndDate" id="EndDate" class="form-control" required>
                        </div>

                        <div class="mb-3">
                            <label class="form-label">Địa điểm nhận xe</label>
                            <input type="text" name="PickupLocation" class="form-control" placeholder="Nhập địa điểm nhận xe" required>
                        </div>

                        <div class="mb-3">
                            <label class="form-label">Địa điểm trả xe</label>
                            <input type="text" name="ReturnLocation" class="form-control" placeholder="Nhập địa điểm trả xe" required>
                        </div>

                        <div class="mb-3">
                            <label class="form-label">Ghi chú</label>
                            <textarea name="Note" class="form-control" placeholder="Ví dụ: giao xe tại sân bay, cần ghế trẻ em..."></textarea>
                        </div>

                        <div id="availabilityMessage" class="availability-box"></div>

                        <div class="total-preview">
                            <div class="row-line">
                                <span>Giá thuê/ngày</span>
                                <strong id="pricePerDay"><?php echo number_format($car['PricePerDay'], 0, ',', '.'); ?> VNĐ</strong>
                            </div>
                            <div class="row-line">
                                <span>Số ngày thuê</span>
                                <strong id="rentalDaysPreview">0 ngày</strong>
                            </div>
                            <div class="row-line">
                                <span>Tiền cọc</span>
                                <strong id="depositPreview"><?php echo number_format($car['DepositAmount'], 0, ',', '.'); ?> VNĐ</strong>
                            </div>
                            <div class="row-line total">
                                <span>Tổng tiền dự kiến</span>
                                <strong id="totalPricePreview">0 VNĐ</strong>
                            </div>
                        </div>

                        <button type="submit" class="btn btn-primary booking-btn w-100 mt-4">
                            <i class="fas fa-calendar-check me-2"></i> Xác nhận đặt lịch
                        </button>
                    </form>
                </div>
            </div>
        </div>
    </div>
</div>

<?php include 'includes/footer.php'; ?>