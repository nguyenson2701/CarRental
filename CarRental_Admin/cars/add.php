<?php
require_once '../../CarRental_Backend/config/auth.php';
requireAdminOrStaff('../login.php');
require_once '../../CarRental_Backend/config/database.php';

$brands = $conn->query("SELECT BrandID, BrandName FROM Brands ORDER BY BrandName ASC");
$types  = $conn->query("SELECT TypeID, TypeName FROM CarTypes ORDER BY TypeName ASC");
?>
<!DOCTYPE html>
<html lang="vi">
<head>
    <meta charset="utf-8">
    <title>Thêm xe</title>
    <meta name="viewport" content="width=device-width, initial-scale=1, shrink-to-fit=no">

    <link href="../vendor/fontawesome-free/css/all.min.css" rel="stylesheet">
    <link href="https://fonts.googleapis.com/css?family=Nunito:200,300,400,600,700,800,900" rel="stylesheet">
    <link href="../css/sb-admin-2.min.css" rel="stylesheet">
    <link href="../assets/css/cars-admin.css?v=1" rel="stylesheet">
</head>
<body id="page-top">
<div id="wrapper">

    <?php include '../partials/sidebar.php'; ?>

    <div id="content-wrapper" class="d-flex flex-column">
        <div id="content">
            <?php include '../partials/topbar.php'; ?>
            <nav class="navbar navbar-expand navbar-light bg-white topbar mb-4 static-top shadow">
                <button id="sidebarToggleTop" class="btn btn-link d-md-none rounded-circle mr-3">
                    <i class="fa fa-bars"></i>
                </button>
                <ul class="navbar-nav ml-auto">
                    <li class="nav-item">
                        <a class="nav-link" href="../../CarRental_Backend/api/auth/logout.php">
                            <span class="mr-2 d-none d-lg-inline text-gray-600 small">
                                <?php echo htmlspecialchars($_SESSION['name'] ?? 'Admin'); ?>
                            </span>
                            <i class="fas fa-sign-out-alt"></i>
                        </a>
                    </li>
                </ul>
            </nav>

            <div class="container-fluid">
                <h1 class="h3 mb-4 text-gray-800">Thêm xe mới</h1>

                <div class="card shadow mb-4">
                    <div class="card-body">
                        <form action="../../CarRental_Backend/api/admin/cars/store.php" method="POST" enctype="multipart/form-data">
                            <div class="row">
                                <div class="col-md-6 form-group">
                                    <label>Tên xe</label>
                                    <input type="text" name="CarName" class="form-control" required>
                                </div>

                                <div class="col-md-3 form-group">
                                    <label>Hãng xe</label>
                                    <select name="BrandID" class="form-control" required>
                                        <option value="">-- Chọn hãng xe --</option>
                                        <?php while ($brand = $brands->fetch_assoc()): ?>
                                            <option value="<?php echo $brand['BrandID']; ?>">
                                                <?php echo htmlspecialchars($brand['BrandName']); ?>
                                            </option>
                                        <?php endwhile; ?>
                                    </select>
                                </div>

                                <div class="col-md-3 form-group">
                                    <label>Loại xe</label>
                                    <select name="TypeID" class="form-control" required>
                                        <option value="">-- Chọn loại xe --</option>
                                        <?php while ($type = $types->fetch_assoc()): ?>
                                            <option value="<?php echo $type['TypeID']; ?>">
                                                <?php echo htmlspecialchars($type['TypeName']); ?>
                                            </option>
                                        <?php endwhile; ?>
                                    </select>
                                </div>

                                <div class="col-md-3 form-group">
                                    <label>Năm sản xuất</label>
                                    <input type="number" name="Year" class="form-control" min="1900" max="2099" required>
                                </div>

                                <div class="col-md-3 form-group">
                                    <label>Biển số</label>
                                    <input type="text" name="LicensePlate" class="form-control" required>
                                </div>

                                <div class="col-md-3 form-group">
                                    <label>Màu</label>
                                    <select name="Color" class="form-control">
                                        <option value="">-- Chọn màu --</option>
                                        <option value="Đen">Đen</option>
                                        <option value="Trắng">Trắng</option>
                                        <option value="Đỏ">Đỏ</option>
                                        <option value="Xanh">Xanh</option>
                                        <option value="Bạc">Bạc</option>
                                        <option value="Xám">Xám</option>
                                        <option value="Vàng">Vàng</option>
                                    </select>
                                </div>

                                <div class="col-md-3 form-group">
                                    <label>Số ghế</label>
                                    <input type="number" name="Seats" class="form-control" min="1">
                                </div>

                                <div class="col-md-3 form-group">
                                    <label>Hộp số</label>
                                    <select name="Transmission" class="form-control">
                                        <option value="Automatic">Tự động</option>
                                        <option value="Manual">Số sàn</option>
                                    </select>
                                </div>

                                <div class="col-md-3 form-group">
                                    <label>Nhiên liệu</label>
                                    <select name="FuelType" class="form-control">
                                        <option value="Gasoline">Xăng</option>
                                        <option value="Diesel">Dầu diesel</option>
                                        <option value="Electric">Điện</option>
                                        <option value="Hybrid">Hybrid</option>
                                    </select>
                                </div>

                                <div class="col-md-3 form-group">
                                    <label>Giá/ngày</label>
                                    <input type="number" step="0.01" name="PricePerDay" class="form-control" min="0">
                                </div>

                                <div class="col-md-3 form-group">
                                    <label>Tiền cọc</label>
                                    <input type="number" step="0.01" name="DepositAmount" class="form-control" min="0">
                                </div>

                                <div class="col-md-3 form-group">
                                    <label>Trạng thái</label>
                                    <select name="Status" class="form-control">
                                        <option value="Available">Sẵn sàng</option>
                                        <option value="Booked">Đã đặt</option>
                                        <option value="Maintenance">Bảo trì</option>
                                    </select>
                                </div>

                                <div class="col-md-3 form-group">
                                    <label>Số km đã đi</label>
                                    <input type="number" name="Mileage" class="form-control" min="0">
                                </div>

                                <div class="col-md-6 form-group">
                                    <label>Địa điểm</label>
                                    <input type="text" name="Location" class="form-control">
                                </div>

                                <div class="col-md-6 form-group">
                                    <label>Ảnh xe</label>
                                    <input type="file" name="Images[]" id="Images" class="form-control-file" accept="image/*" multiple>
                                    <small class="form-text text-muted">Có thể chọn nhiều ảnh. Ảnh đầu tiên sẽ là ảnh chính.</small>
                                    <div id="preview-box" style="display:flex;gap:8px;flex-wrap:wrap;margin-top:12px;"></div>
                                </div>

                                <div class="col-md-12 form-group">
                                    <label>Mô tả</label>
                                    <textarea name="Description" class="form-control" rows="4"></textarea>
                                </div>
                            </div>

                            <button type="submit" class="btn btn-primary">Lưu xe</button>
                            <a href="list.php" class="btn btn-secondary">Quay lại</a>
                        </form>
                    </div>
                </div>
            </div>
        </div>

        <?php include '../partials/footer.php'; ?>
    </div>
</div>

<script src="../vendor/jquery/jquery.min.js"></script>
<script src="../vendor/bootstrap/js/bootstrap.bundle.min.js"></script>
<script src="../vendor/jquery-easing/jquery.easing.min.js"></script>
<script src="../js/sb-admin-2.min.js"></script>
<script src="../assets/js/cars-admin.js"></script>
</body>
</html>