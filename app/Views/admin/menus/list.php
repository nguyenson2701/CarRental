<?php
/**
 * @var array $menus
 * @var bool $success
 */
use App\Core\Auth;
?>
<div class="d-sm-flex align-items-center justify-content-between mb-4">
    <h1 class="h3 mb-0 text-gray-800">Quản lý menu frontend</h1>
    <a href="/Carrental/admin/menus/create" class="btn btn-primary btn-sm">
        <i class="fas fa-plus mr-1"></i> Thêm menu
    </a>
</div>

<?php if ($success): ?>
    <div class="alert alert-success">Thao tác thành công.</div>
<?php endif; ?>

<div class="card shadow mb-4">
    <div class="card-body">
        <div class="table-responsive">
            <table class="table table-bordered table-hover">
                <thead class="thead-light">
                    <tr>
                        <th>ID</th>
                        <th>Tên menu</th>
                        <th>URL</th>
                        <th>Menu cha</th>
                        <th>Thứ tự</th>
                        <th>Hiển thị</th>
                        <th width="160">Thao tác</th>
                    </tr>
                </thead>
                <tbody>
                    <?php if (count($menus) > 0): ?>
                        <?php foreach ($menus as $row): ?>
                            <tr>
                                <td><?= (int) $row['MenuID'] ?></td>
                                <td><?= htmlspecialchars($row['MenuName']) ?></td>
                                <td><?= htmlspecialchars($row['URL']) ?></td>
                                <td><?= htmlspecialchars($row['ParentName'] ?? 'Không có') ?></td>
                                <td><?= (int) $row['DisplayOrder'] ?></td>
                                <td>
                                    <?php if ((int) $row['IsActive'] === 1): ?>
                                        <span class="badge badge-success">Hiện</span>
                                    <?php else: ?>
                                        <span class="badge badge-secondary">Ẩn</span>
                                    <?php endif; ?>
                                </td>
                                <td>
                                    <a href="/Carrental/admin/menus/<?= (int) $row['MenuID'] ?>/edit" class="btn btn-warning btn-sm">Sửa</a>
                                    <form action="/Carrental/admin/menus/<?= (int) $row['MenuID'] ?>/delete" method="POST"
                                          class="d-inline" onsubmit="return confirm('Bạn có chắc muốn xóa menu này?');">
                                        <?= Auth::csrfField() ?>
                                        <button type="submit" class="btn btn-danger btn-sm">Xóa</button>
                                    </form>
                                </td>
                            </tr>
                        <?php endforeach; ?>
                    <?php else: ?>
                        <tr>
                            <td colspan="7" class="text-center text-muted">Chưa có menu nào.</td>
                        </tr>
                    <?php endif; ?>
                </tbody>
            </table>
        </div>
    </div>
</div>
