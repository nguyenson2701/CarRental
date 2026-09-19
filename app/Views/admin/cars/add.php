<?php
/**
 * @var array $brands
 * @var array $types
 */
use App\Core\Auth;
?>
<h1 class="h3 mb-4 text-gray-800">Thêm xe mới</h1>

<div class="card shadow mb-4">
    <div class="card-body">
        <form action="/Carrental/admin/cars" method="POST" enctype="multipart/form-data">
            <?= Auth::csrfField() ?>
            <div class="row">
                <div class="col-md-6 form-group">
                    <label>Tên xe</label>
                    <input type="text" name="CarName" class="form-control" required>
                </div>

                <div class="col-md-3 form-group">
                    <label>Hãng xe</label>
                    <select name="BrandID" class="form-control" required>
                        <option value="">-- Chọn hãng xe --</option>
                        <?php foreach ($brands as $brand): ?>
                            <option value="<?= $brand['BrandID'] ?>"><?= htmlspecialchars($brand['BrandName']) ?></option>
                        <?php endforeach; ?>
                    </select>
                </div>

                <div class="col-md-3 form-group">
                    <label>Loại xe</label>
                    <select name="TypeID" class="form-control" required>
                        <option value="">-- Chọn loại xe --</option>
                        <?php foreach ($types as $type): ?>
                            <option value="<?= $type['TypeID'] ?>"><?= htmlspecialchars($type['TypeName']) ?></option>
                        <?php endforeach; ?>
                    </select>
                </div>

                <div class="col-md-3 form-group">
                    <label>Năm sản xuất</label>
                    <input type="number" name="Year" class="form-control" min="1900" max="2099" required>
                </div>

                <div class="col-md-3 form-group">
                    <label>Biển số</label>
                    <input type="text" name="LicensePlate" class="form-control" required>
                </div>

                <div class="col-md-3 form-group">
                    <label>Màu</label>
                    <select name="Color" class="form-control">
                        <option value="">-- Chọn màu --</option>
                        <option value="Đen">Đen</option>
                        <option value="Trắng">Trắng</option>
                        <option value="Đỏ">Đỏ</option>
                        <option value="Xanh">Xanh</option>
                        <option value="Bạc">Bạc</option>
                        <option value="Xám">Xám</option>
                        <option value="Vàng">Vàng</option>
                    </select>
                </div>

                <div class="col-md-3 form-group">
                    <label>Số ghế</label>
                    <input type="number" name="Seats" class="form-control" min="1">
                </div>

                <div class="col-md-3 form-group">
                    <label>Hộp số</label>
                    <select name="Transmission" class="form-control">
                        <option value="Automatic">Tự động</option>
                        <option value="Manual">Số sàn</option>
                    </select>
                </div>

                <div class="col-md-3 form-group">
                    <label>Nhiên liệu</label>
                    <select name="FuelType" class="form-control">
                        <option value="Gasoline">Xăng</option>
                        <option value="Diesel">Dầu diesel</option>
                        <option value="Electric">Điện</option>
                        <option value="Hybrid">Hybrid</option>
                    </select>
                </div>

                <div class="col-md-3 form-group">
                    <label>Giá/ngày</label>
                    <input type="number" step="0.01" name="PricePerDay" class="form-control" min="0">
                </div>

                <div class="col-md-3 form-group">
                    <label>Tiền cọc</label>
                    <input type="number" step="0.01" name="DepositAmount" class="form-control" min="0">
                </div>

                <div class="col-md-3 form-group">
                    <label>Trạng thái</label>
                    <select name="Status" class="form-control">
                        <option value="Available">Sẵn sàng</option>
                        <option value="Booked">Đã đặt</option>
                        <option value="Maintenance">Bảo trì</option>
                    </select>
                </div>

                <div class="col-md-3 form-group">
                    <label>Số km đã đi</label>
                    <input type="number" name="Mileage" class="form-control" min="0">
                </div>

                <div class="col-md-6 form-group">
                    <label>Địa điểm</label>
                    <input type="text" name="Location" class="form-control">
                </div>

                <div class="col-md-6 form-group">
                    <label>Ảnh xe</label>
                    <input type="file" name="Images[]" id="Images" class="form-control-file" accept="image/*" multiple>
                    <small class="form-text text-muted">Có thể chọn nhiều ảnh. Ảnh đầu tiên sẽ là ảnh chính.</small>
                    <div id="preview-box" style="display:flex;gap:8px;flex-wrap:wrap;margin-top:12px;"></div>
                </div>

                <div class="col-md-12 form-group">
                    <label>Mô tả</label>
                    <textarea name="Description" class="form-control" rows="4"></textarea>
                </div>
            </div>

            <button type="submit" class="btn btn-primary">Lưu xe</button>
            <a href="/Carrental/admin/cars" class="btn btn-secondary">Quay lại</a>
        </form>
    </div>
</div>

<script src="/Carrental/public/admin/assets/js/cars-admin.js"></script>
