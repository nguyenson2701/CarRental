<?php
/**
 * @var array $car
 * @var array $images
 */
?>
<div class="container py-5">
    <div class="row g-5">
        <div class="col-lg-7">
            <img src="/Carrental/CarRental_Frontend/assets/img/cars/<?= htmlspecialchars($car['MainImage']) ?>" class="img-fluid rounded mb-4" style="width:100%; max-height:420px; object-fit:cover;" alt="">

            <div class="d-flex flex-wrap gap-2">
                <?php foreach ($images as $img): ?>
                    <img src="/Carrental/CarRental_Frontend/assets/img/cars/<?= htmlspecialchars($img['ImageURL']) ?>" style="width:120px;height:85px;object-fit:cover;border-radius:10px;border:1px solid #ddd;" alt="">
                <?php endforeach; ?>
            </div>
        </div>

        <div class="col-lg-5">
            <h2 class="mb-3"><?= htmlspecialchars($car['CarName']) ?></h2>
            <p><strong>Giá/ngày:</strong> <?= number_format($car['PricePerDay'], 0, ',', '.') ?> VNĐ</p>
            <p><strong>Tiền cọc:</strong> <?= number_format($car['DepositAmount'], 0, ',', '.') ?> VNĐ</p>
            <p><strong>Hộp số:</strong> <?= htmlspecialchars($car['Transmission']) ?></p>
            <p><strong>Nhiên liệu:</strong> <?= htmlspecialchars($car['FuelType']) ?></p>
            <p><strong>Số ghế:</strong> <?= (int) $car['Seats'] ?></p>
            <p><strong>Mô tả:</strong> <?= nl2br(htmlspecialchars($car['Description'])) ?></p>

            <a href="/Carrental/vehicle/<?= (int) $car['CarID'] ?>/book" class="btn btn-primary rounded-pill px-5 py-3 mt-3">
                Đặt xe ngay
            </a>
        </div>
    </div>
</div>
