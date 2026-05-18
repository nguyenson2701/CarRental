<?php
$pageTitle = 'Thuê xe - VinaDrive';
$activePage = 'vehicle';
include 'includes/header.php';
require_once '../CarRental_Backend/config/database.php';

$keyword = trim($_GET['keyword'] ?? '');
$sql = "SELECT CarID, CarName, MainImage, PricePerDay, Transmission, FuelType, Seats, Status FROM cars";
if ($keyword !== '') {
    $safe = $conn->real_escape_string($keyword);
    $sql .= " WHERE CarName LIKE '%$safe%'";
}
$sql .= " ORDER BY CarID DESC";
$result = $conn->query($sql);
?>

<div class="container-fluid bg-breadcrumb mb-5">
    <div class="container text-center py-5" style="max-width: 900px;">
        <h4 class="text-white display-4 mb-4">Danh sách xe</h4>
    </div>
</div>

<div class="container-fluid py-5">
    <div class="container">
        <form class="row g-3 mb-5" method="GET">
            <div class="col-md-10">
                <input type="text" name="keyword" class="form-control" value="<?php echo htmlspecialchars($keyword); ?>" placeholder="Tìm theo tên xe...">
            </div>
            <div class="col-md-2">
                <button class="btn btn-primary w-100" type="submit">Tìm kiếm</button>
            </div>
        </form>

        <div class="row g-4">
            <?php while ($car = $result->fetch_assoc()): ?>
            <div class="col-md-6 col-lg-4">
                <div class="card border-0 shadow h-100">
                    <img src="assets/img/cars/<?php echo htmlspecialchars($car['MainImage']); ?>" class="card-img-top" style="height:240px; object-fit:cover;" alt="">
                    <div class="card-body">
                        <h5><?php echo htmlspecialchars($car['CarName']); ?></h5>
                        <p class="mb-2">Giá/ngày: <strong><?php echo number_format($car['PricePerDay'], 0, ',', '.'); ?> VNĐ</strong></p>
                        <p class="mb-2">Trạng thái: <?php echo htmlspecialchars($car['Status']); ?></p>
                        <p class="mb-2">Hộp số: <?php echo htmlspecialchars($car['Transmission']); ?></p>
                        <p class="mb-2">Nhiên liệu: <?php echo htmlspecialchars($car['FuelType']); ?></p>
                        <p class="mb-3">Số ghế: <?php echo (int)$car['Seats']; ?></p>
                        <a href="vehicle-detail.php?id=<?php echo (int)$car['CarID']; ?>" class="btn btn-primary rounded-pill px-4">Xem chi tiết</a>
                    </div>
                </div>
            </div>
            <?php endwhile; ?>
        </div>
    </div>
</div>

<?php include 'includes/footer.php'; ?>