<?php
require_once '../../CarRental_Backend/config/auth.php';
requireAdminOrStaff('../login.php');
require_once '../../CarRental_Backend/config/database.php';

$id = (int)($_GET['id'] ?? 0);
if ($id <= 0) {
    header("Location: list.php");
    exit();
}

$stmt = $conn->prepare("SELECT * FROM users WHERE UserID = ?");
$stmt->bind_param("i", $id);
$stmt->execute();
$result = $stmt->get_result();
$user = $result->fetch_assoc();

if (!$user) {
    header("Location: list.php");
    exit();
}
?>
<!DOCTYPE html>
<html lang="vi">
<head>
    <meta charset="utf-8">
    <title>Sửa người dùng</title>
    <meta name="viewport" content="width=device-width, initial-scale=1, shrink-to-fit=no">

    <link href="../vendor/fontawesome-free/css/all.min.css" rel="stylesheet">
    <link href="../vendor/nunito/nunito.css" rel="stylesheet">
    <link href="../css/sb-admin-2.min.css" rel="stylesheet">
    <link href="../css/admin-theme.css?v=4" rel="stylesheet">
</head>
<body id="page-top">
<div id="wrapper">

    <?php include '../partials/sidebar.php'; ?>

    <div id="content-wrapper" class="d-flex flex-column">
        <div id="content">

            <?php include '../partials/topbar.php'; ?>

            <div class="container-fluid">
                <h1 class="h3 mb-4 text-gray-800">Sửa người dùng</h1>

                <div class="card shadow mb-4">
                    <div class="card-body">
                        <form action="../../CarRental_Backend/api/admin/users/update.php" method="POST" enctype="multipart/form-data">
                            <?php echo csrf_field(); ?>
                            <input type="hidden" name="UserID" value="<?php echo $user['UserID']; ?>">
                            <input type="hidden" name="OldAvatar" value="<?php echo htmlspecialchars($user['Avatar']); ?>">
                            <input type="hidden" name="OldLicenseFrontImage" value="<?php echo htmlspecialchars($user['LicenseFrontImage']); ?>">
                            <input type="hidden" name="OldLicenseBackImage" value="<?php echo htmlspecialchars($user['LicenseBackImage']); ?>">
                            <div class="row">
                                <div class="col-md-6 form-group">
                                    <label>Họ tên</label>
                                    <input type="text" name="FullName" class="form-control"
                                           value="<?php echo htmlspecialchars($user['FullName']); ?>" required>
                                </div>
                                
                                <div class="col-md-6 form-group">
                                    <label>Email</label>
                                    <input type="email" name="Email" class="form-control"
                                           value="<?php echo htmlspecialchars($user['Email']); ?>" required>
                                </div>

                                <div class="col-md-4 form-group text-center">
                                    <label class="font-weight-bold d-block">Avatar hiện tại</label>

                                    <?php if (!empty($user['Avatar'])): ?>
                                        <img src="../../CarRental_Frontend/assets/img/avatars/<?php echo htmlspecialchars($user['Avatar']); ?>"
                                            alt="Avatar"
                                            style="width:110px; height:110px; object-fit:cover; border-radius:50%; border:1px solid #ddd; margin-bottom:10px;">
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
                                        <img src="../../CarRental_Frontend/assets/img/gplx/<?php echo htmlspecialchars($user['LicenseFrontImage']); ?>"
                                            alt="GPLX mặt trước"
                                            style="width:160px; height:100px; object-fit:cover; border-radius:8px; border:1px solid #ddd; margin-bottom:10px;">
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
                                        <img src="../../CarRental_Frontend/assets/img/gplx/<?php echo htmlspecialchars($user['LicenseBackImage']); ?>"
                                            alt="GPLX mặt sau"
                                            style="width:160px; height:100px; object-fit:cover; border-radius:8px; border:1px solid #ddd; margin-bottom:10px;">
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
                                    <input type="text" name="Phone" class="form-control"
                                           value="<?php echo htmlspecialchars($user['Phone']); ?>">
                                </div>

                                <div class="col-md-6 form-group">
                                    <label>Mật khẩu mới</label>
                                    <input type="password" name="PasswordHash" class="form-control"
                                           placeholder="Để trống nếu không đổi mật khẩu" autocomplete="new-password">
                                </div>

                                <div class="col-md-6 form-group">
                                    <label>Địa chỉ</label>
                                    <input type="text" name="Address" class="form-control"
                                           value="<?php echo htmlspecialchars($user['Address']); ?>">
                                </div>

                                <div class="col-md-3 form-group">
                                    <label>Quyền</label>
                                    <select name="RoleID" class="form-control">
                                        <option value="1" <?php echo ($user['RoleID'] == 1) ? 'selected' : ''; ?>>Admin</option>
                                        <option value="2" <?php echo ($user['RoleID'] == 2) ? 'selected' : ''; ?>>Staff</option>
                                        <option value="3" <?php echo ($user['RoleID'] == 3) ? 'selected' : ''; ?>>Customer</option>
                                    </select>
                                </div>

                                <div class="col-md-3 form-group">
                                    <label>Trạng thái</label>
                                    <select name="Status" class="form-control">
                                        <option value="Active" <?php echo ($user['Status'] == 'Active') ? 'selected' : ''; ?>>Active</option>
                                        <option value="Inactive" <?php echo ($user['Status'] == 'Inactive') ? 'selected' : ''; ?>>Inactive</option>
                                    </select>
                                </div>

                                <div class="col-md-6 form-group">
                                    <label>Số GPLX</label>
                                    <input type="text" name="LicenseNumber" class="form-control"
                                           value="<?php echo htmlspecialchars($user['LicenseNumber']); ?>">
                                </div>

                                <div class="col-md-6 form-group">
                                    <label>Trạng thái xác minh GPLX</label>
                                    <select name="LicenseVerifiedStatus" class="form-control">
                                        <option value="Pending" <?php echo ($user['LicenseVerifiedStatus'] == 'Pending') ? 'selected' : ''; ?>>Pending</option>
                                        <option value="Verified" <?php echo ($user['LicenseVerifiedStatus'] == 'Verified') ? 'selected' : ''; ?>>Verified</option>
                                        <option value="Rejected" <?php echo ($user['LicenseVerifiedStatus'] == 'Rejected') ? 'selected' : ''; ?>>Rejected</option>
                                    </select>
                                </div>
                            </div>

                            <button type="submit" class="btn btn-primary">Cập nhật</button>
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