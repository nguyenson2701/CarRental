<?php
require_once '../CarRental_Backend/config/database.php';
$pageTitle = 'Chi tiết xe - VinaDrive';
$activePage = 'vehicle';
$pageStyles = ['assets/css/booking.css?v=1'];
$pageScripts = ['assets/js/booking.js?v=1'];
include 'includes/header.php';


$id = (int)($_GET['id'] ?? 0);
$stmt = $conn->prepare("SELECT * FROM cars WHERE CarID = ?");
$stmt->bind_param("i", $id);
$stmt->execute();
$result = $stmt->get_result();
$car = $result->fetch_assoc();

if (!$car) {
    echo '<div class="container py-5"><h3>Không tìm thấy xe.</h3></div>';
    include 'includes/footer.php';
    exit();
}

$stmtImgs = $conn->prepare("SELECT ImageURL, IsMain FROM carimages WHERE CarID = ? ORDER BY IsMain DESC, ImageID ASC");
$stmtImgs->bind_param("i", $id);
$stmtImgs->execute();
$resultImgs = $stmtImgs->get_result();
?>

<div class="container py-5">
    <div class="row g-5">
        <div class="col-lg-7">
            <img src="assets/img/cars/<?php echo htmlspecialchars($car['MainImage']); ?>" class="img-fluid rounded mb-4" style="width:100%; max-height:420px; object-fit:cover;" alt="">

            <div class="d-flex flex-wrap gap-2">
                <?php while ($img = $resultImgs->fetch_assoc()): ?>
                    <img src="assets/img/cars/<?php echo htmlspecialchars($img['ImageURL']); ?>" style="width:120px;height:85px;object-fit:cover;border-radius:10px;border:1px solid #ddd;" alt="">
                <?php endwhile; ?>
            </div>
        </div>

        <div class="col-lg-5">
            <h2 class="mb-3"><?php echo htmlspecialchars($car['CarName']); ?></h2>
            <p><strong>Giá/ngày:</strong> <?php echo number_format($car['PricePerDay'], 0, ',', '.'); ?> VNĐ</p>
            <p><strong>Tiền cọc:</strong> <?php echo number_format($car['DepositAmount'], 0, ',', '.'); ?> VNĐ</p>
            <p><strong>Hộp số:</strong> <?php echo htmlspecialchars($car['Transmission']); ?></p>
            <p><strong>Nhiên liệu:</strong> <?php echo htmlspecialchars($car['FuelType']); ?></p>
            <p><strong>Số ghế:</strong> <?php echo (int)$car['Seats']; ?></p>
            <p><strong>Mô tả:</strong> <?php echo nl2br(htmlspecialchars($car['Description'])); ?></p>

            <a href="booking.php?car_id=<?php echo (int)$car['CarID']; ?>" class="btn btn-primary rounded-pill px-5 py-3 mt-3">
                Đặt xe ngay
            </a>
        </div>
    </div>
</div>

<?php include 'includes/footer.php'; ?>