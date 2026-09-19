<?php
/**
 * @var array $cars
 * @var string $keyword
 */
use App\Core\Auth;
?>
<div class="d-sm-flex align-items-center justify-content-between mb-4">
    <h1 class="h3 mb-0 text-gray-800">Danh sách xe</h1>
    <a href="/Carrental/admin/cars/create" class="d-none d-sm-inline-block btn btn-sm btn-primary shadow-sm">
        <i class="fas fa-plus fa-sm text-white-50"></i> Thêm xe
    </a>
</div>

<div class="card shadow mb-4">
    <div class="card-body">
        <form method="GET" action="/Carrental/admin/cars" class="mb-4">
            <div class="input-group">
                <input type="text" name="keyword" class="form-control"
                       placeholder="Tìm theo tên xe, biển số, màu hoặc trạng thái..."
                       value="<?= htmlspecialchars($keyword) ?>">
                <div class="input-group-append">
                    <button class="btn btn-primary" type="submit">
                        <i class="fas fa-search"></i> Tìm kiếm
                    </button>
                    <?php if ($keyword !== ''): ?>
                        <a href="/Carrental/admin/cars" class="btn btn-secondary">Xóa lọc</a>
                    <?php endif; ?>
                </div>
            </div>
        </form>

        <div class="table-responsive">
            <table class="table table-bordered table-hover" width="100%" cellspacing="0">
                <thead class="thead-light">
                    <tr>
                        <th>ID</th>
                        <th>Ảnh</th>
                        <th>Tên xe</th>
                        <th>Biển số</th>
                        <th>Màu</th>
                        <th>Số ghế</th>
                        <th>Giá/ngày</th>
                        <th>Trạng thái</th>
                        <th>Thao tác</th>
                    </tr>
                </thead>
                <tbody>
                <?php if (count($cars) > 0): ?>
                    <?php foreach ($cars as $row): ?>
                        <tr>
                            <td><?= $row['CarID'] ?></td>
                            <td>
                                <div style="display:flex;gap:6px;flex-wrap:wrap;max-width:320px;">
                                    <?php if (count($row['images']) > 0): ?>
                                        <?php foreach ($row['images'] as $img): ?>
                                            <div style="position:relative;">
                                                <?php if ((int) $img['IsMain'] === 1): ?>
                                                    <span style="position:absolute;top:2px;left:2px;background:#1cc88a;color:#fff;font-size:10px;padding:2px 6px;border-radius:12px;z-index:2;">
                                                        Chính
                                                    </span>
                                                <?php endif; ?>
                                                <img
                                                    src="/Carrental/public/frontend/assets/img/cars/<?= htmlspecialchars($img['ImageURL']) ?>"
                                                    alt="Car"
                                                    style="width:70px;height:52px;object-fit:cover;border-radius:6px;border:1px solid #dbe3ea;">
                                            </div>
                                        <?php endforeach; ?>
                                    <?php else: ?>
                                        <span class="text-muted">Chưa có ảnh</span>
                                    <?php endif; ?>
                                </div>
                            </td>
                            <td><?= htmlspecialchars($row['CarName']) ?></td>
                            <td><?= htmlspecialchars($row['LicensePlate']) ?></td>
                            <td><?= htmlspecialchars($row['Color']) ?></td>
                            <td><?= htmlspecialchars($row['Seats']) ?></td>
                            <td><?= number_format($row['PricePerDay'], 0, ',', '.') ?> VNĐ</td>
                            <td>
                                <?php if ($row['Status'] == 'Available'): ?>
                                    <span class="badge badge-success">Sẵn sàng</span>
                                <?php elseif ($row['Status'] == 'Booked'): ?>
                                    <span class="badge badge-danger">Đã thuê</span>
                                <?php else: ?>
                                    <span class="badge badge-warning">Bảo dưỡng</span>
                                <?php endif; ?>
                            </td>
                            <td>
                                <a href="/Carrental/admin/cars/<?= (int) $row['CarID'] ?>/edit" class="btn btn-warning btn-sm">
                                    <i class="fas fa-edit"></i>
                                </a>
                                <form action="/Carrental/admin/cars/<?= (int) $row['CarID'] ?>/delete" method="POST"
                                      class="d-inline" onsubmit="return confirm('Bạn có chắc muốn xóa xe này không?');">
                                    <?= Auth::csrfField() ?>
                                    <button type="submit" class="btn btn-danger btn-sm">
                                        <i class="fas fa-trash"></i>
                                    </button>
                                </form>
                            </td>
                        </tr>
                    <?php endforeach; ?>
                <?php else: ?>
                    <tr>
                        <td colspan="9" class="text-center">Không có dữ liệu xe</td>
                    </tr>
                <?php endif; ?>
                </tbody>
            </table>
        </div>
    </div>
</div>
