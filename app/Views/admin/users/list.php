<?php
/**
 * @var array $users
 * @var string $keyword
 */
use App\Core\Auth;
?>
<div class="d-sm-flex align-items-center justify-content-between mb-4">
    <h1 class="h3 mb-0 text-gray-800">Danh sách người dùng</h1>
    <a href="/Carrental/admin/users/create" class="btn btn-primary btn-sm shadow-sm">
        <i class="fas fa-plus fa-sm text-white-50"></i> Thêm người dùng
    </a>
</div>

<div class="card shadow mb-4">
    <div class="card-body">
        <form method="GET" action="/Carrental/admin/users" class="mb-4">
            <div class="input-group">
                <input type="text" name="keyword" class="form-control"
                       placeholder="Tìm người dùng..."
                       value="<?= htmlspecialchars($keyword) ?>">
                <div class="input-group-append">
                    <button class="btn btn-primary" type="submit">
                        <i class="fas fa-search"></i> Tìm kiếm
                    </button>
                    <?php if ($keyword !== ''): ?>
                        <a href="/Carrental/admin/users" class="btn btn-secondary">Xóa lọc</a>
                    <?php endif; ?>
                </div>
            </div>
        </form>
        <div class="table-responsive">
            <table class="table table-bordered table-hover">
                <thead class="thead-light">
                    <tr>
                        <th>ID</th>
                        <th>Avatar</th>
                        <th>Họ tên</th>
                        <th>Email</th>
                        <th>Điện thoại</th>
                        <th>GPLX mặt trước</th>
                        <th>GPLX mặt sau</th>
                        <th>Quyền</th>
                        <th>Trạng thái</th>
                        <th width="140">Thao tác</th>
                    </tr>
                </thead>
                <tbody>
                    <?php if (count($users) > 0): ?>
                        <?php foreach ($users as $row): ?>
                            <tr>
                                <td><?= $row['UserID'] ?></td>
                                <td>
                                    <?php if (!empty($row['Avatar'])): ?>
                                        <img src="/Carrental/public/frontend/assets/img/avatars/<?= htmlspecialchars($row['Avatar']) ?>"
                                            alt="Avatar" style="width:60px; height:60px; object-fit:cover; border-radius:50%; border:1px solid #ddd;">
                                    <?php else: ?>
                                        <span>Chưa có</span>
                                    <?php endif; ?>
                                </td>
                                <td><?= htmlspecialchars($row['FullName']) ?></td>
                                <td><?= htmlspecialchars($row['Email']) ?></td>
                                <td><?= htmlspecialchars($row['Phone']) ?></td>
                                <td>
                                    <?php if (!empty($row['LicenseFrontImage'])): ?>
                                        <a href="/Carrental/public/frontend/assets/img/GPLX/<?= htmlspecialchars($row['LicenseFrontImage']) ?>" target="_blank">
                                            <img src="/Carrental/public/frontend/assets/img/GPLX/<?= htmlspecialchars($row['LicenseFrontImage']) ?>"
                                                alt="GPLX trước" style="width:90px; height:60px; object-fit:cover; border-radius:6px; border:1px solid #ddd;">
                                        </a>
                                    <?php else: ?>
                                        <span>Chưa có</span>
                                    <?php endif; ?>
                                </td>
                                <td>
                                    <?php if (!empty($row['LicenseBackImage'])): ?>
                                        <a href="/Carrental/public/frontend/assets/img/GPLX/<?= htmlspecialchars($row['LicenseBackImage']) ?>" target="_blank">
                                            <img src="/Carrental/public/frontend/assets/img/GPLX/<?= htmlspecialchars($row['LicenseBackImage']) ?>"
                                                alt="GPLX sau" style="width:90px; height:60px; object-fit:cover; border-radius:6px; border:1px solid #ddd;">
                                        </a>
                                    <?php else: ?>
                                        <span>Chưa có</span>
                                    <?php endif; ?>
                                </td>
                                <td>
                                    <?php
                                    if ($row['RoleID'] == 1) echo 'Admin';
                                    elseif ($row['RoleID'] == 2) echo 'Staff';
                                    else echo 'Customer';
                                    ?>
                                </td>
                                <td><?= htmlspecialchars($row['Status']) ?></td>
                                <td>
                                    <a href="/Carrental/admin/users/<?= (int) $row['UserID'] ?>/edit" class="btn btn-warning btn-sm">
                                        <i class="fas fa-edit"></i>
                                    </a>
                                    <form action="/Carrental/admin/users/<?= (int) $row['UserID'] ?>/delete" method="POST"
                                          class="d-inline" onsubmit="return confirm('Bạn có chắc muốn xóa người dùng này không?');">
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
                            <td colspan="10" class="text-center">Không có dữ liệu người dùng</td>
                        </tr>
                    <?php endif; ?>
                </tbody>
            </table>
        </div>
    </div>
</div>
