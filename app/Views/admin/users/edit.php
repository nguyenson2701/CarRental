<?php
/** @var array $user */
use App\Core\Auth;

$userId = (int) $user['UserID'];
?>
<h1 class="h3 mb-4 text-gray-800">Sửa người dùng</h1>

<div class="card shadow mb-4">
    <div class="card-body">
        <form action="/Carrental/admin/users/<?= $userId ?>/update" method="POST" enctype="multipart/form-data">
            <?= Auth::csrfField() ?>
            <div class="row">
                <div class="col-md-6 form-group">
                    <label>Họ tên</label>
                    <input type="text" name="FullName" class="form-control" value="<?= htmlspecialchars($user['FullName']) ?>" required>
                </div>

                <div class="col-md-6 form-group">
                    <label>Email</label>
                    <input type="email" name="Email" class="form-control" value="<?= htmlspecialchars($user['Email']) ?>" required>
                </div>

                <div class="col-md-4 form-group text-center">
                    <label class="font-weight-bold d-block">Avatar hiện tại</label>
                    <?php if (!empty($user['Avatar'])): ?>
                        <img src="/Carrental/public/frontend/assets/img/avatars/<?= htmlspecialchars($user['Avatar']) ?>"
                            alt="Avatar" style="width:110px; height:110px; object-fit:cover; border-radius:50%; border:1px solid #ddd; margin-bottom:10px;">
                    <?php else: ?>
                        <div style="width:110px; height:110px; margin:0 auto 10px; border:1px dashed #ccc; border-radius:50%; display:flex; align-items:center; justify-content:center; color:#888;">
                            Chưa có ảnh
                        </div>
                    <?php endif; ?>
                    <label class="d-block">Đổi ảnh mới</label>
                    <input type="file" name="Avatar" class="form-control-file">
                </div>

                <div class="col-md-4 form-group text-center">
                    <label class="font-weight-bold d-block">GPLX mặt trước hiện tại</label>
                    <?php if (!empty($user['LicenseFrontImage'])): ?>
                        <img src="/Carrental/public/frontend/assets/img/GPLX/<?= htmlspecialchars($user['LicenseFrontImage']) ?>"
                            alt="GPLX mặt trước" style="width:160px; height:100px; object-fit:cover; border-radius:8px; border:1px solid #ddd; margin-bottom:10px;">
                    <?php else: ?>
                        <div style="width:160px; height:100px; margin:0 auto 10px; border:1px dashed #ccc; border-radius:8px; display:flex; align-items:center; justify-content:center; color:#888;">
                            Chưa có ảnh
                        </div>
                    <?php endif; ?>
                    <label class="d-block">Đổi ảnh mới</label>
                    <input type="file" name="LicenseFrontImage" class="form-control-file">
                </div>

                <div class="col-md-4 form-group text-center">
                    <label class="font-weight-bold d-block">GPLX mặt sau hiện tại</label>
                    <?php if (!empty($user['LicenseBackImage'])): ?>
                        <img src="/Carrental/public/frontend/assets/img/GPLX/<?= htmlspecialchars($user['LicenseBackImage']) ?>"
                            alt="GPLX mặt sau" style="width:160px; height:100px; object-fit:cover; border-radius:8px; border:1px solid #ddd; margin-bottom:10px;">
                    <?php else: ?>
                        <div style="width:160px; height:100px; margin:0 auto 10px; border:1px dashed #ccc; border-radius:8px; display:flex; align-items:center; justify-content:center; color:#888;">
                            Chưa có ảnh
                        </div>
                    <?php endif; ?>
                    <label class="d-block">Đổi ảnh mới</label>
                    <input type="file" name="LicenseBackImage" class="form-control-file">
                </div>

                <div class="col-md-6 form-group">
                    <label>Điện thoại</label>
                    <input type="text" name="Phone" class="form-control" value="<?= htmlspecialchars($user['Phone']) ?>">
                </div>

                <div class="col-md-6 form-group">
                    <label>Mật khẩu mới</label>
                    <input type="password" name="PasswordHash" class="form-control"
                           placeholder="Để trống nếu không đổi mật khẩu" autocomplete="new-password">
                </div>

                <div class="col-md-6 form-group">
                    <label>Địa chỉ</label>
                    <input type="text" name="Address" class="form-control" value="<?= htmlspecialchars($user['Address']) ?>">
                </div>

                <div class="col-md-3 form-group">
                    <label>Quyền</label>
                    <select name="RoleID" class="form-control">
                        <option value="1" <?= ($user['RoleID'] == 1) ? 'selected' : '' ?>>Admin</option>
                        <option value="2" <?= ($user['RoleID'] == 2) ? 'selected' : '' ?>>Staff</option>
                        <option value="3" <?= ($user['RoleID'] == 3) ? 'selected' : '' ?>>Customer</option>
                    </select>
                </div>

                <div class="col-md-3 form-group">
                    <label>Trạng thái</label>
                    <select name="Status" class="form-control">
                        <option value="Active" <?= ($user['Status'] == 'Active') ? 'selected' : '' ?>>Active</option>
                        <option value="Inactive" <?= ($user['Status'] == 'Inactive') ? 'selected' : '' ?>>Inactive</option>
                    </select>
                </div>

                <div class="col-md-6 form-group">
                    <label>Số GPLX</label>
                    <input type="text" name="LicenseNumber" class="form-control" value="<?= htmlspecialchars($user['LicenseNumber']) ?>">
                </div>

                <div class="col-md-6 form-group">
                    <label>Trạng thái xác minh GPLX</label>
                    <select name="LicenseVerifiedStatus" class="form-control">
                        <option value="Pending" <?= ($user['LicenseVerifiedStatus'] == 'Pending') ? 'selected' : '' ?>>Pending</option>
                        <option value="Verified" <?= ($user['LicenseVerifiedStatus'] == 'Verified') ? 'selected' : '' ?>>Verified</option>
                        <option value="Rejected" <?= ($user['LicenseVerifiedStatus'] == 'Rejected') ? 'selected' : '' ?>>Rejected</option>
                    </select>
                </div>
            </div>

            <button type="submit" class="btn btn-primary">Cập nhật</button>
            <a href="/Carrental/admin/users" class="btn btn-secondary">Quay lại</a>
        </form>
    </div>
</div>
