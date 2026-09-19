<?php
/**
 * @var array $cars
 * @var string $keyword
 */
?>
<div class="container-fluid bg-breadcrumb mb-5">
    <div class="container text-center py-5" style="max-width: 900px;">
        <h4 class="text-white display-4 mb-4">Danh sách xe</h4>
    </div>
</div>

<div class="container-fluid py-5">
    <div class="container">
        <form class="row g-3 mb-5" method="GET" action="/Carrental/vehicle">
            <div class="col-md-10">
                <input type="text" name="keyword" class="form-control" value="<?= htmlspecialchars($keyword) ?>" placeholder="Tìm theo tên xe...">
            </div>
            <div class="col-md-2">
                <button class="btn btn-primary w-100" type="submit">Tìm kiếm</button>
            </div>
        </form>

        <div class="row g-4">
            <?php foreach ($cars as $car): ?>
            <div class="col-md-6 col-lg-4">
                <div class="card border-0 shadow h-100">
                    <img src="/Carrental/public/frontend/assets/img/cars/<?= htmlspecialchars($car['MainImage']) ?>" class="card-img-top" style="height:240px; object-fit:cover;" alt="">
                    <div class="card-body">
                        <h5><?= htmlspecialchars($car['CarName']) ?></h5>
                        <p class="mb-2">Giá/ngày: <strong><?= number_format($car['PricePerDay'], 0, ',', '.') ?> VNĐ</strong></p>
                        <p class="mb-2">Trạng thái: <?= htmlspecialchars($car['Status']) ?></p>
                        <p class="mb-2">Hộp số: <?= htmlspecialchars($car['Transmission']) ?></p>
                        <p class="mb-2">Nhiên liệu: <?= htmlspecialchars($car['FuelType']) ?></p>
                        <p class="mb-3">Số ghế: <?= (int) $car['Seats'] ?></p>
                        <a href="/Carrental/vehicle/<?= (int) $car['CarID'] ?>" class="btn btn-primary rounded-pill px-4">Xem chi tiết</a>
                    </div>
                </div>
            </div>
            <?php endforeach; ?>
        </div>
    </div>
</div>
