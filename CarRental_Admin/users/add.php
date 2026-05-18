<?php
require_once '../../CarRental_Backend/config/auth.php';
requireAdminOrStaff('../login.php');
?>
<!DOCTYPE html>
<html lang="vi">
<head>
    <meta charset="utf-8">
    <title>Thêm người dùng</title>
    <meta name="viewport" content="width=device-width, initial-scale=1, shrink-to-fit=no">

    <link href="../vendor/fontawesome-free/css/all.min.css" rel="stylesheet">
    <link href="https://fonts.googleapis.com/css?family=Nunito:200,300,400,600,700,800,900" rel="stylesheet">
    <link href="../css/sb-admin-2.min.css" rel="stylesheet">
</head>
<body id="page-top">
<div id="wrapper">

    <?php include '../partials/sidebar.php'; ?>

    <div id="content-wrapper" class="d-flex flex-column">
        <div id="content">
            <?php include '../partials/topbar.php'; ?>

            <div class="container-fluid">
                <h1 class="h3 mb-4 text-gray-800">Thêm người dùng</h1>

                <div class="card shadow mb-4">
                    <div class="card-body">
                        <form action="../../CarRental_Backend/api/admin/users/store.php" method="POST" enctype="multipart/form-data">
                            <div class="row">
                                <div class="col-md-6 form-group">
                                    <label>Họ tên</label>
                                    <input type="text" name="FullName" class="form-control" required>
                                </div>

                                <div class="col-md-6 form-group">
                                    <label>Ảnh avatar</label>
                                    <input type="file" name="MainImage" class="form-control-file" accept="image/*">
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
                                    <input type="text" name="PasswordHash" class="form-control" required>
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
                            <a href="list.php" class="btn btn-secondary">Quay lại</a>
                        </form>
                    </div>
                </div>
            </div>
            <?php include '../partials/footer.php'; ?>
        </div>
    </div>
</div>

<script src="../vendor/jquery/jquery.min.js"></script>
<script src="../vendor/bootstrap/js/bootstrap.bundle.min.js"></script>
<script src="../vendor/jquery-easing/jquery.easing.min.js"></script>
<script src="../js/sb-admin-2.min.js"></script>
</body>
</html>