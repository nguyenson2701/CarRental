<?php
session_start();

if (isset($_SESSION['user_id']) && isset($_SESSION['role_id'])) {
    if ($_SESSION['role_id'] == 1 || $_SESSION['role_id'] == 2) {
        header("Location: ../CarRental_Admin/index.php");
        exit();
    } elseif ($_SESSION['role_id'] == 3) {
        header("Location: index.php");
        exit();
    }
}

$error = $_SESSION['register_error'] ?? '';
$success = $_SESSION['register_success'] ?? '';
unset($_SESSION['register_error'], $_SESSION['register_success']);
?>
<!DOCTYPE html>
<html lang="vi">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>Đăng ký tài khoản</title>

    <link href="../CarRental_Admin/vendor/fontawesome-free/css/all.min.css" rel="stylesheet" type="text/css">
    <link href="https://fonts.googleapis.com/css?family=Nunito:200,300,400,600,700,800,900" rel="stylesheet">
    <link href="../CarRental_Admin/css/sb-admin-2.min.css" rel="stylesheet">
</head>
<body class="bg-gradient-primary">

<div class="container">
    <div class="row justify-content-center">
        <div class="col-xl-8 col-lg-10 col-md-10">
            <div class="card o-hidden border-0 shadow-lg my-5">
                <div class="card-body p-0">
                    <div class="p-5">
                        <div class="text-center mb-4">
                            <h1 class="h4 text-gray-900">Tạo tài khoản khách hàng</h1>
                            <p class="text-muted mb-0">Đăng ký để đặt xe và theo dõi đơn thuê</p>
                        </div>

                        <?php if (!empty($error)): ?>
                            <div class="alert alert-danger">
                                <?php echo htmlspecialchars($error); ?>
                            </div>
                        <?php endif; ?>

                        <?php if (!empty($success)): ?>
                            <div class="alert alert-success">
                                <?php echo htmlspecialchars($success); ?>
                            </div>
                        <?php endif; ?>

                        <form method="POST" action="../CarRental_Backend/api/auth/register.php">
                            <div class="form-group">
                                <label>Họ và tên</label>
                                <input type="text" name="full_name" class="form-control" required>
                            </div>

                            <div class="form-row">
                                <div class="form-group col-md-6">
                                    <label>Email</label>
                                    <input type="email" name="email" class="form-control" required>
                                </div>
                                <div class="form-group col-md-6">
                                    <label>Số điện thoại</label>
                                    <input type="text" name="phone" class="form-control" required>
                                </div>
                            </div>

                            <div class="form-group">
                                <label>Địa chỉ</label>
                                <input type="text" name="address" class="form-control">
                            </div>

                            <div class="form-group">
                                <label>Mật khẩu</label>
                                <input type="password" name="password" class="form-control" required>
                            </div>

                            <div class="form-group">
                                <label>Nhập lại mật khẩu</label>
                                <input type="password" name="confirm_password" class="form-control" required>
                            </div>

                            <button type="submit" class="btn btn-primary btn-user btn-block">
                                Đăng ký
                            </button>
                        </form>

                        <hr>
                        <div class="text-center">
                            <a class="small" href="login.php">Đã có tài khoản? Đăng nhập</a>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

<script src="../CarRental_Admin/vendor/jquery/jquery.min.js"></script>
<script src="../CarRental_Admin/vendor/bootstrap/js/bootstrap.bundle.min.js"></script>
<script src="../CarRental_Admin/vendor/jquery-easing/jquery.easing.min.js"></script>
<script src="../CarRental_Admin/js/sb-admin-2.min.js"></script>
</body>
</html>