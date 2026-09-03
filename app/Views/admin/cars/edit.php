<?php
/**
 * @var array $car
 * @var array $images
 * @var array $brands
 * @var array $types
 */
use App\Core\Auth;

$carId = (int) $car['CarID'];
?>
<style>
    .img-card {
        width: 190px; border: 1px solid #e3e6f0; border-radius: 12px; padding: 10px;
        background: #fff; box-shadow: 0 0.15rem 0.5rem rgba(58, 59, 69, 0.08);
    }
    .img-wrap { position: relative; }
    .img-wrap img {
        width: 100%; height: 120px; object-fit: cover; border-radius: 8px;
        border: 1px solid #dbe3ea; background: #f8f9fc; display: block;
    }
    .badge-main {
        position: absolute; top: 6px; left: 6px; background: #1cc88a; color: #fff;
        font-size: 11px; padding: 4px 8px; border-radius: 20px; font-weight: 600;
    }
    .preview-box { display: flex; gap: 10px; flex-wrap: wrap; margin-top: 12px; }
    .preview-item { width: 120px; height: 80px; border: 1px solid #dbe3ea; border-radius: 8px; overflow: hidden; background: #f8f9fc; }
    .preview-item img { width: 100%; height: 100%; object-fit: cover; display: block; }
</style>

<h1 class="h3 mb-4 text-gray-800">Sửa xe</h1>

<div class="card shadow mb-4">
    <div class="card-body">

        <form action="/Carrental/admin/cars/<?= $carId ?>/update" method="POST" enctype="multipart/form-data">
            <?= Auth::csrfField() ?>

            <div class="row">
                <div class="col-md-6 form-group">
                    <label>Tên xe</label>
                    <input type="text" name="CarName" class="form-control" value="<?= htmlspecialchars($car['CarName']) ?>" required>
                </div>

                <div class="col-md-3 form-group">
                    <label>Hãng xe</label>
                    <select name="BrandID" class="form-control" required>
                        <?php foreach ($brands as $brand): ?>
                            <option value="<?= (int) $brand['BrandID'] ?>" <?= ((int) $car['BrandID'] === (int) $brand['BrandID']) ? 'selected' : '' ?>>
                                <?= htmlspecialchars($brand['BrandName']) ?>
                            </option>
                        <?php endforeach; ?>
                    </select>
                </div>

                <div class="col-md-3 form-group">
                    <label>Loại xe</label>
                    <select name="TypeID" class="form-control" required>
                        <?php foreach ($types as $type): ?>
                            <option value="<?= (int) $type['TypeID'] ?>" <?= ((int) $car['TypeID'] === (int) $type['TypeID']) ? 'selected' : '' ?>>
                                <?= htmlspecialchars($type['TypeName']) ?>
                            </option>
                        <?php endforeach; ?>
                    </select>
                </div>

                <div class="col-md-3 form-group">
                    <label>Năm sản xuất</label>
                    <input type="number" name="Year" class="form-control" value="<?= (int) $car['Year'] ?>" min="1900" max="2099">
                </div>

                <div class="col-md-3 form-group">
                    <label>Biển số</label>
                    <input type="text" name="LicensePlate" class="form-control" value="<?= htmlspecialchars($car['LicensePlate']) ?>" required>
                </div>

                <div class="col-md-3 form-group">
                    <label>Màu</label>
                    <input type="text" name="Color" class="form-control" value="<?= htmlspecialchars($car['Color']) ?>">
                </div>

                <div class="col-md-3 form-group">
                    <label>Số ghế</label>
                    <input type="number" name="Seats" class="form-control" value="<?= (int) $car['Seats'] ?>" min="1">
                </div>

                <div class="col-md-3 form-group">
                    <label>Hộp số</label>
                    <select name="Transmission" class="form-control">
                        <option value="Automatic" <?= ($car['Transmission'] === 'Automatic') ? 'selected' : '' ?>>Automatic</option>
                        <option value="Manual" <?= ($car['Transmission'] === 'Manual') ? 'selected' : '' ?>>Manual</option>
                    </select>
                </div>

                <div class="col-md-3 form-group">
                    <label>Nhiên liệu</label>
                    <select name="FuelType" class="form-control">
                        <option value="Gasoline" <?= ($car['FuelType'] === 'Gasoline') ? 'selected' : '' ?>>Gasoline</option>
                        <option value="Diesel" <?= ($car['FuelType'] === 'Diesel') ? 'selected' : '' ?>>Diesel</option>
                        <option value="Electric" <?= ($car['FuelType'] === 'Electric') ? 'selected' : '' ?>>Electric</option>
                        <option value="Hybrid" <?= ($car['FuelType'] === 'Hybrid') ? 'selected' : '' ?>>Hybrid</option>
                    </select>
                </div>

                <div class="col-md-3 form-group">
                    <label>Giá/ngày</label>
                    <input type="number" step="0.01" name="PricePerDay" class="form-control" value="<?= htmlspecialchars($car['PricePerDay']) ?>" min="0">
                </div>

                <div class="col-md-3 form-group">
                    <label>Tiền cọc</label>
                    <input type="number" step="0.01" name="DepositAmount" class="form-control" value="<?= htmlspecialchars($car['DepositAmount']) ?>" min="0">
                </div>

                <div class="col-md-3 form-group">
                    <label>Trạng thái</label>
                    <select name="Status" class="form-control">
                        <option value="Available" <?= ($car['Status'] === 'Available') ? 'selected' : '' ?>>Available</option>
                        <option value="Booked" <?= ($car['Status'] === 'Booked') ? 'selected' : '' ?>>Booked</option>
                        <option value="Maintenance" <?= ($car['Status'] === 'Maintenance') ? 'selected' : '' ?>>Maintenance</option>
                    </select>
                </div>

                <div class="col-md-3 form-group">
                    <label>Số km đã đi</label>
                    <input type="number" name="Mileage" class="form-control" value="<?= (int) $car['Mileage'] ?>" min="0">
                </div>

                <div class="col-md-6 form-group">
                    <label>Địa điểm</label>
                    <input type="text" name="Location" class="form-control" value="<?= htmlspecialchars($car['Location']) ?>">
                </div>

                <div class="col-md-12 form-group mt-2">
                    <label>Thêm ảnh mới</label>
                    <input type="file" name="Images[]" id="Images" class="form-control-file" accept="image/*" multiple>
                    <small class="form-text text-muted">Có thể thêm nhiều ảnh mới cùng lúc.</small>
                    <div id="preview-box" class="preview-box"></div>
                </div>

                <div class="col-md-12 form-group">
                    <label>Mô tả</label>
                    <textarea name="Description" class="form-control" rows="4"><?= htmlspecialchars($car['Description']) ?></textarea>
                </div>
            </div>

            <button type="submit" class="btn btn-primary">Cập nhật xe</button>
            <a href="/Carrental/admin/cars" class="btn btn-secondary">Quay lại</a>
        </form>

        <hr class="my-4">

        <div class="mt-3">
            <label class="font-weight-bold">Danh sách ảnh hiện tại</label>

            <div style="display:flex;gap:12px;flex-wrap:wrap;">
                <?php if (count($images) > 0): ?>
                    <?php foreach ($images as $img): ?>
                        <div class="img-card">
                            <div class="img-wrap">
                                <?php if ((int) $img['IsMain'] === 1): ?>
                                    <span class="badge-main">Ảnh chính</span>
                                <?php endif; ?>
                                <img src="/Carrental/public/frontend/assets/img/cars/<?= htmlspecialchars($img['ImageURL']) ?>" alt="Car image">
                            </div>

                            <form action="/Carrental/admin/cars/<?= $carId ?>/images/update" method="POST" class="mt-2">
                                <?= Auth::csrfField() ?>
                                <input type="hidden" name="ImageID" value="<?= (int) $img['ImageID'] ?>">

                                <select name="ImageType" class="form-control form-control-sm mb-2">
                                    <option value="gallery" <?= ($img['ImageType'] === 'gallery') ? 'selected' : '' ?>>Gallery</option>
                                    <option value="front" <?= ($img['ImageType'] === 'front') ? 'selected' : '' ?>>Mặt trước</option>
                                    <option value="back" <?= ($img['ImageType'] === 'back') ? 'selected' : '' ?>>Mặt sau</option>
                                    <option value="interior" <?= ($img['ImageType'] === 'interior') ? 'selected' : '' ?>>Nội thất</option>
                                </select>

                                <button type="submit" class="btn btn-warning btn-sm btn-block mb-2">Sửa loại ảnh</button>
                            </form>

                            <?php if ((int) $img['IsMain'] !== 1): ?>
                                <form action="/Carrental/admin/cars/<?= $carId ?>/images/set-main" method="POST" class="mb-2">
                                    <?= Auth::csrfField() ?>
                                    <input type="hidden" name="ImageID" value="<?= (int) $img['ImageID'] ?>">
                                    <button type="submit" class="btn btn-info btn-sm btn-block">Đặt ảnh chính</button>
                                </form>
                            <?php endif; ?>

                            <form action="/Carrental/admin/cars/<?= $carId ?>/images/delete" method="POST"
                                  onsubmit="return confirm('Bạn có chắc muốn xóa ảnh này?');">
                                <?= Auth::csrfField() ?>
                                <input type="hidden" name="ImageID" value="<?= (int) $img['ImageID'] ?>">
                                <button type="submit" class="btn btn-danger btn-sm btn-block">Xóa ảnh</button>
                            </form>
                        </div>
                    <?php endforeach; ?>
                <?php elseif (!empty($car['MainImage'])): ?>
                    <div class="img-card">
                        <div class="img-wrap">
                            <span class="badge-main">Ảnh chính</span>
                            <img src="/Carrental/public/frontend/assets/img/cars/<?= htmlspecialchars($car['MainImage']) ?>" alt="Main image">
                        </div>
                        <div class="mt-2 text-muted small text-center">Ảnh cũ từ MainImage</div>
                    </div>
                <?php else: ?>
                    <span class="text-muted">Chưa có ảnh</span>
                <?php endif; ?>
            </div>
        </div>

    </div>
</div>

<script src="/Carrental/public/admin/assets/js/cars-admin.js"></script>
