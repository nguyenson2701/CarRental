<?php
/**
 * @var array $brands
 * @var string $keyword
 */
use App\Core\Auth;
?>
<div class="d-sm-flex align-items-center justify-content-between mb-4">
    <h1 class="h3 mb-0 text-gray-800">Danh sách hãng xe</h1>
    <a href="/Carrental/admin/brands/create" class="btn btn-primary btn-sm shadow-sm">
        <i class="fas fa-plus fa-sm text-white-50"></i> Thêm hãng xe
    </a>
</div>

<div class="card shadow mb-4">
    <div class="card-body">
        <form method="GET" action="/Carrental/admin/brands" class="mb-4">
            <div class="input-group">
                <input type="text" name="keyword" class="form-control"
                       placeholder="Tìm hãng xe..."
                       value="<?= htmlspecialchars($keyword) ?>">
                <div class="input-group-append">
                    <button class="btn btn-primary" type="submit">
                        <i class="fas fa-search"></i> Tìm kiếm
                    </button>
                    <?php if ($keyword !== ''): ?>
                        <a href="/Carrental/admin/brands" class="btn btn-secondary">Xóa lọc</a>
                    <?php endif; ?>
                </div>
            </div>
        </form>
        <div class="table-responsive">
            <table class="table table-bordered table-hover" width="100%" cellspacing="0">
                <thead class="thead-light">
                    <tr>
                        <th>ID</th>
                        <th>Tên hãng</th>
                        <th width="140">Thao tác</th>
                    </tr>
                </thead>
                <tbody>
                    <?php if (count($brands) > 0): ?>
                        <?php foreach ($brands as $row): ?>
                            <tr>
                                <td><?= $row['BrandID'] ?></td>
                                <td><?= htmlspecialchars($row['BrandName']) ?></td>
                                <td>
                                    <a href="/Carrental/admin/brands/<?= (int) $row['BrandID'] ?>/edit" class="btn btn-warning btn-sm">
                                        <i class="fas fa-edit"></i>
                                    </a>
                                    <form action="/Carrental/admin/brands/<?= (int) $row['BrandID'] ?>/delete" method="POST"
                                          class="d-inline" onsubmit="return confirm('Bạn có chắc muốn xóa hãng xe này không?');">
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
                            <td colspan="3" class="text-center">Không có dữ liệu hãng xe</td>
                        </tr>
                    <?php endif; ?>
                </tbody>
            </table>
        </div>
    </div>
</div>
