<?php
require_once '../CarRental_Backend/config/auth.php';
requireLogin('login.php');
require_once '../CarRental_Backend/config/database.php';

$pageTitle = 'Gửi trả xe';
$activePage = 'my-bookings';

$userID = (int)($_SESSION['user_id'] ?? 0);
$bookingID = (int)($_GET['booking_id'] ?? 0);

$stmt = $conn->prepare("
    SELECT b.*, c.CarName, c.LicensePlate, c.MainImage
    FROM bookings b
    INNER JOIN cars c ON b.CarID = c.CarID
    WHERE b.BookingID = ?
      AND b.UserID = ?
      AND b.Status = 'Confirmed'
    LIMIT 1
");
$stmt->bind_param("ii", $bookingID, $userID);
$stmt->execute();
$booking = $stmt->get_result()->fetch_assoc();

if (!$booking) {
    die('Không tìm thấy đơn hoặc đơn không thể trả xe.');
}

include 'includes/header.php';
?>

<div class="container py-5">
    <div class="card shadow border-0 rounded-4">
        <div class="card-body p-4 p-lg-5">
            <h2 class="fw-bold mb-4">Gửi yêu cầu trả xe</h2>

            <?php if (isset($_GET['error'])): ?>
                <div class="alert alert-danger">
                    <?php echo htmlspecialchars($_GET['error']); ?>
                </div>
            <?php endif; ?>

            <div class="row mb-4">
                <div class="col-md-4">
                    <img src="assets/img/cars/<?php echo htmlspecialchars($booking['MainImage']); ?>"
                         class="img-fluid rounded-4"
                         style="height:220px;width:100%;object-fit:cover;">
                </div>
                <div class="col-md-8">
                    <h4><?php echo htmlspecialchars($booking['CarName']); ?></h4>
                    <p><strong>Biển số:</strong> <?php echo htmlspecialchars($booking['LicensePlate']); ?></p>
                    <p><strong>Ngày nhận:</strong> <?php echo htmlspecialchars($booking['StartDate']); ?></p>
                    <p><strong>Ngày trả dự kiến:</strong> <?php echo htmlspecialchars($booking['EndDate']); ?></p>
                    <p><strong>Tổng tiền thuê:</strong> <?php echo number_format($booking['TotalPrice'], 0, ',', '.'); ?> VNĐ</p>
                </div>
            </div>

            <form id="returnForm" action="../CarRental_Backend/api/bookings/return_store.php" method="POST" enctype="multipart/form-data">
                <?php echo csrf_field(); ?>
                <input type="hidden" name="BookingID" value="<?php echo (int)$booking['BookingID']; ?>">

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
                    <textarea name="ReturnNote" class="form-control" rows="4"
                              placeholder="Ví dụ: xe bình thường, có xước nhẹ bên phải..."></textarea>
                </div>

                <button type="submit" class="btn btn-primary rounded-pill px-4">
                    Gửi yêu cầu trả xe
                </button>

                <a href="my-bookings.php" class="btn btn-secondary rounded-pill px-4">
                    Quay lại
                </a>
            </form>

            <script>
                document.addEventListener('DOMContentLoaded', function () {
                    var f = document.getElementById('returnForm');
                    if (!f) return;
                    f.addEventListener('submit', function () {
                        console.log('returnForm: submit event fired');
                    });
                    var btn = f.querySelector('button[type="submit"]');
                    if (btn) {
                        btn.addEventListener('click', function () {
                            console.log('returnForm: submit button clicked');
                        });
                    }
                });
            </script>
        </div>
    </div>
</div>

<?php include 'includes/footer.php'; ?>
