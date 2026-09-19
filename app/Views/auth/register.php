<?php
/**
 * @var string $error
 * @var string $success
 */
use App\Core\Auth;
?>
<!DOCTYPE html>
<html lang="vi">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>Đăng ký tài khoản</title>

    <link href="/Carrental/public/admin/vendor/fontawesome-free/css/all.min.css" rel="stylesheet" type="text/css">
    <link href="https://fonts.googleapis.com/css?family=Nunito:200,300,400,600,700,800,900" rel="stylesheet">
    <link href="/Carrental/public/admin/css/sb-admin-2.min.css" rel="stylesheet">
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
                            <div class="alert alert-danger"><?= htmlspecialchars($error) ?></div>
                        <?php endif; ?>
                        <?php if (!empty($success)): ?>
                            <div class="alert alert-success"><?= htmlspecialchars($success) ?></div>
                        <?php endif; ?>

                        <form method="POST" action="/Carrental/register">
                            <?= Auth::csrfField() ?>
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
                            <button type="submit" class="btn btn-primary btn-user btn-block">Đăng ký</button>
                        </form>

                        <hr>
                        <div class="text-center">
                            <a class="small" href="/Carrental/login">Đã có tài khoản? Đăng nhập</a>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

<script src="/Carrental/public/admin/vendor/jquery/jquery.min.js"></script>
<script src="/Carrental/public/admin/vendor/bootstrap/js/bootstrap.bundle.min.js"></script>
<script src="/Carrental/public/admin/vendor/jquery-easing/jquery.easing.min.js"></script>
<script src="/Carrental/public/admin/js/sb-admin-2.min.js"></script>
</body>
</html>
