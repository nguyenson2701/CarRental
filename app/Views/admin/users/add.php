<?php
use App\Core\Auth;
?>
<h1 class="h3 mb-4 text-gray-800">Thêm người dùng</h1>

<div class="card shadow mb-4">
    <div class="card-body">
        <form action="/Carrental/admin/users" method="POST" enctype="multipart/form-data">
            <?= Auth::csrfField() ?>
            <div class="row">
                <div class="col-md-6 form-group">
                    <label>Họ tên</label>
                    <input type="text" name="FullName" class="form-control" required>
                </div>

                <div class="col-md-6 form-group">
                    <label>Ảnh avatar</label>
                    <input type="file" name="Avatar" class="form-control-file" accept="image/*">
                </div>
                <div class="col-md-6 form-group">
                    <label>Ảnh GPLX mặt trước</label>
                    <input type="file" name="LicenseFrontImage" class="form-control-file" accept="image/*">
                </div>

                <div class="col-md-6 form-group">
                    <label>Ảnh GPLX mặt sau</label>
                    <input type="file" name="LicenseBackImage" class="form-control-file" accept="image/*">
                </div>
                <div class="col-md-6 form-group">
                    <label>Email</label>
                    <input type="email" name="Email" class="form-control" required>
                </div>

                <div class="col-md-6 form-group">
                    <label>Điện thoại</label>
                    <input type="text" name="Phone" class="form-control">
                </div>

                <div class="col-md-6 form-group">
                    <label>Mật khẩu</label>
                    <input type="password" name="PasswordHash" class="form-control" autocomplete="new-password" required>
                </div>

                <div class="col-md-6 form-group">
                    <label>Địa chỉ</label>
                    <input type="text" name="Address" class="form-control">
                </div>

                <div class="col-md-3 form-group">
                    <label>Quyền</label>
                    <select name="RoleID" class="form-control" required>
                        <option value="1">Admin</option>
                        <option value="2">Staff</option>
                        <option value="3">Customer</option>
                    </select>
                </div>

                <div class="col-md-3 form-group">
                    <label>Trạng thái</label>
                    <select name="Status" class="form-control">
                        <option value="Active">Active</option>
                        <option value="Inactive">Inactive</option>
                    </select>
                </div>

                <div class="col-md-6 form-group">
                    <label>Số GPLX</label>
                    <input type="text" name="LicenseNumber" class="form-control">
                </div>

                <div class="col-md-6 form-group">
                    <label>Trạng thái xác minh GPLX</label>
                    <select name="LicenseVerifiedStatus" class="form-control">
                        <option value="Pending">Pending</option>
                        <option value="Verified">Verified</option>
                        <option value="Rejected">Rejected</option>
                    </select>
                </div>
            </div>

            <button type="submit" class="btn btn-primary">Lưu người dùng</button>
            <a href="/Carrental/admin/users" class="btn btn-secondary">Quay lại</a>
        </form>
    </div>
</div>
