<?php
require_once '../../CarRental_Backend/config/auth.php';
requireAdminOrStaff('../login.php');
require_once '../../CarRental_Backend/config/database.php';

$id = (int)($_GET['id'] ?? 0);
if ($id <= 0) {
    header("Location: list.php");
    exit();
}

$stmt = $conn->prepare("SELECT * FROM Cars WHERE CarID = ?");
$stmt->bind_param("i", $id);
$stmt->execute();
$result = $stmt->get_result();
$car = $result->fetch_assoc();

if (!$car) {
    header("Location: list.php");
    exit();
}

$stmtImgs = $conn->prepare("
    SELECT ImageID, ImageURL, IsMain, ImageType
    FROM CarImages
    WHERE CarID = ?
    ORDER BY IsMain DESC, ImageID ASC
");
$stmtImgs->bind_param("i", $id);
$stmtImgs->execute();
$resultImgs = $stmtImgs->get_result();

$brands = $conn->query("SELECT BrandID, BrandName FROM Brands ORDER BY BrandName ASC");
$types  = $conn->query("SELECT TypeID, TypeName FROM CarTypes ORDER BY TypeName ASC");
?>
<!DOCTYPE html>
<html lang="vi">
<head>
    <meta charset="utf-8">
    <title>Sửa xe</title>
    <meta name="viewport" content="width=device-width, initial-scale=1, shrink-to-fit=no">

    <link href="../vendor/fontawesome-free/css/all.min.css" rel="stylesheet">
    <link href="../vendor/nunito/nunito.css" rel="stylesheet">
    <link href="../css/sb-admin-2.min.css" rel="stylesheet">
    <link href="../assets/css/cars-admin.css?v=1" rel="stylesheet">

    <style>
        .img-card {
            width: 190px;
            border: 1px solid #e3e6f0;
            border-radius: 12px;
            padding: 10px;
            background: #fff;
            box-shadow: 0 0.15rem 0.5rem rgba(58, 59, 69, 0.08);
        }

        .img-wrap {
            position: relative;
        }

        .img-wrap img {
            width: 100%;
            height: 120px;
            object-fit: cover;
            border-radius: 8px;
            border: 1px solid #dbe3ea;
            background: #f8f9fc;
            display: block;
        }

        .badge-main {
            position: absolute;
            top: 6px;
            left: 6px;
            background: #1cc88a;
            color: #fff;
            font-size: 11px;
            padding: 4px 8px;
            border-radius: 20px;
            font-weight: 600;
        }

        .preview-box {
            display: flex;
            gap: 10px;
            flex-wrap: wrap;
            margin-top: 12px;
        }

        .preview-item {
            width: 120px;
            height: 80px;
            border: 1px solid #dbe3ea;
            border-radius: 8px;
            overflow: hidden;
            background: #f8f9fc;
        }

        .preview-item img {
            width: 100%;
            height: 100%;
            object-fit: cover;
            display: block;
        }
    </style>
    <link href="../css/admin-theme.css?v=4" rel="stylesheet">
</head>
<body id="page-top">
<div id="wrapper">

    <?php include '../partials/sidebar.php'; ?>

    <div id="content-wrapper" class="d-flex flex-column">
        <div id="content">
            <?php include '../partials/topbar.php'; ?>

            <div class="container-fluid">
                <h1 class="h3 mb-4 text-gray-800">Sửa xe</h1>

                <div class="card shadow mb-4">
                    <div class="card-body">

                        <!-- FORM CẬP NHẬT XE -->
                        <form action="../../CarRental_Backend/api/admin/cars/update.php" method="POST" enctype="multipart/form-data">
                            <?php echo csrf_field(); ?>
                            <input type="hidden" name="CarID" value="<?php echo (int)$car['CarID']; ?>">

                            <div class="row">
                                <div class="col-md-6 form-group">
                                    <label>Tên xe</label>
                                    <input type="text" name="CarName" class="form-control" value="<?php echo htmlspecialchars($car['CarName']); ?>" required>
                                </div>

                                <div class="col-md-3 form-group">
                                    <label>Hãng xe</label>
                                    <select name="BrandID" class="form-control" required>
                                        <?php while ($brand = $brands->fetch_assoc()): ?>
                                            <option value="<?php echo (int)$brand['BrandID']; ?>" <?php echo ((int)$car['BrandID'] === (int)$brand['BrandID']) ? 'selected' : ''; ?>>
                                                <?php echo htmlspecialchars($brand['BrandName']); ?>
                                            </option>
                                        <?php endwhile; ?>
                                    </select>
                                </div>

                                <div class="col-md-3 form-group">
                                    <label>Loại xe</label>
                                    <select name="TypeID" class="form-control" required>
                                        <?php while ($type = $types->fetch_assoc()): ?>
                                            <option value="<?php echo (int)$type['TypeID']; ?>" <?php echo ((int)$car['TypeID'] === (int)$type['TypeID']) ? 'selected' : ''; ?>>
                                                <?php echo htmlspecialchars($type['TypeName']); ?>
                                            </option>
                                        <?php endwhile; ?>
                                    </select>
                                </div>

                                <div class="col-md-3 form-group">
                                    <label>Năm sản xuất</label>
                                    <input type="number" name="Year" class="form-control" value="<?php echo (int)$car['Year']; ?>" min="1900" max="2099">
                                </div>

                                <div class="col-md-3 form-group">
                                    <label>Biển số</label>
                                    <input type="text" name="LicensePlate" class="form-control" value="<?php echo htmlspecialchars($car['LicensePlate']); ?>" required>
                                </div>

                                <div class="col-md-3 form-group">
                                    <label>Màu</label>
                                    <input type="text" name="Color" class="form-control" value="<?php echo htmlspecialchars($car['Color']); ?>">
                                </div>

                                <div class="col-md-3 form-group">
                                    <label>Số ghế</label>
                                    <input type="number" name="Seats" class="form-control" value="<?php echo (int)$car['Seats']; ?>" min="1">
                                </div>

                                <div class="col-md-3 form-group">
                                    <label>Hộp số</label>
                                    <select name="Transmission" class="form-control">
                                        <option value="Automatic" <?php echo ($car['Transmission'] === 'Automatic') ? 'selected' : ''; ?>>Automatic</option>
                                        <option value="Manual" <?php echo ($car['Transmission'] === 'Manual') ? 'selected' : ''; ?>>Manual</option>
                                    </select>
                                </div>

                                <div class="col-md-3 form-group">
                                    <label>Nhiên liệu</label>
                                    <select name="FuelType" class="form-control">
                                        <option value="Gasoline" <?php echo ($car['FuelType'] === 'Gasoline') ? 'selected' : ''; ?>>Gasoline</option>
                                        <option value="Diesel" <?php echo ($car['FuelType'] === 'Diesel') ? 'selected' : ''; ?>>Diesel</option>
                                        <option value="Electric" <?php echo ($car['FuelType'] === 'Electric') ? 'selected' : ''; ?>>Electric</option>
                                        <option value="Hybrid" <?php echo ($car['FuelType'] === 'Hybrid') ? 'selected' : ''; ?>>Hybrid</option>
                                    </select>
                                </div>

                                <div class="col-md-3 form-group">
                                    <label>Giá/ngày</label>
                                    <input type="number" step="0.01" name="PricePerDay" class="form-control" value="<?php echo htmlspecialchars($car['PricePerDay']); ?>" min="0">
                                </div>

                                <div class="col-md-3 form-group">
                                    <label>Tiền cọc</label>
                                    <input type="number" step="0.01" name="DepositAmount" class="form-control" value="<?php echo htmlspecialchars($car['DepositAmount']); ?>" min="0">
                                </div>

                                <div class="col-md-3 form-group">
                                    <label>Trạng thái</label>
                                    <select name="Status" class="form-control">
                                        <option value="Available" <?php echo ($car['Status'] === 'Available') ? 'selected' : ''; ?>>Available</option>
                                        <option value="Booked" <?php echo ($car['Status'] === 'Booked') ? 'selected' : ''; ?>>Booked</option>
                                        <option value="Maintenance" <?php echo ($car['Status'] === 'Maintenance') ? 'selected' : ''; ?>>Maintenance</option>
                                    </select>
                                </div>

                                <div class="col-md-3 form-group">
                                    <label>Số km đã đi</label>
                                    <input type="number" name="Mileage" class="form-control" value="<?php echo (int)$car['Mileage']; ?>" min="0">
                                </div>

                                <div class="col-md-6 form-group">
                                    <label>Địa điểm</label>
                                    <input type="text" name="Location" class="form-control" value="<?php echo htmlspecialchars($car['Location']); ?>">
                                </div>

                                <div class="col-md-12 form-group mt-2">
                                    <label>Thêm ảnh mới</label>
                                    <input type="file" name="Images[]" id="Images" class="form-control-file" accept="image/*" multiple>
                                    <small class="form-text text-muted">Có thể thêm nhiều ảnh mới cùng lúc.</small>
                                    <div id="preview-box" class="preview-box"></div>
                                </div>

                                <div class="col-md-12 form-group">
                                    <label>Mô tả</label>
                                    <textarea name="Description" class="form-control" rows="4"><?php echo htmlspecialchars($car['Description']); ?></textarea>
                                </div>
                            </div>

                            <button type="submit" class="btn btn-primary">Cập nhật xe</button>
                            <a href="list.php" class="btn btn-secondary">Quay lại</a>
                        </form>

                        <hr class="my-4">

                        <!-- DANH SÁCH ẢNH HIỆN TẠI -->
                        <div class="mt-3">
                            <label class="font-weight-bold">Danh sách ảnh hiện tại</label>

                            <div style="display:flex;gap:12px;flex-wrap:wrap;">
                                <?php if ($resultImgs && $resultImgs->num_rows > 0): ?>
                                    <?php while ($img = $resultImgs->fetch_assoc()): ?>
                                        <div class="img-card">
                                            <div class="img-wrap">
                                                <?php if ((int)$img['IsMain'] === 1): ?>
                                                    <span class="badge-main">Ảnh chính</span>
                                                <?php endif; ?>

                                                <img
                                                    src="../../CarRental_Frontend/assets/img/cars/<?php echo htmlspecialchars($img['ImageURL']); ?>"
                                                    alt="Car image">
                                            </div>

                                            <form action="../../CarRental_Backend/api/admin/cars/update_image.php" method="POST" class="mt-2">
                                                <?php echo csrf_field(); ?>
                                                <input type="hidden" name="ImageID" value="<?php echo (int)$img['ImageID']; ?>">
                                                <input type="hidden" name="CarID" value="<?php echo (int)$car['CarID']; ?>">

                                                <select name="ImageType" class="form-control form-control-sm mb-2">
                                                    <option value="gallery" <?php echo ($img['ImageType'] === 'gallery') ? 'selected' : ''; ?>>Gallery</option>
                                                    <option value="front" <?php echo ($img['ImageType'] === 'front') ? 'selected' : ''; ?>>Mặt trước</option>
                                                    <option value="back" <?php echo ($img['ImageType'] === 'back') ? 'selected' : ''; ?>>Mặt sau</option>
                                                    <option value="interior" <?php echo ($img['ImageType'] === 'interior') ? 'selected' : ''; ?>>Nội thất</option>
                                                </select>

                                                <button type="submit" class="btn btn-warning btn-sm btn-block mb-2">
                                                    Sửa loại ảnh
                                                </button>
                                            </form>

                                            <?php if ((int)$img['IsMain'] !== 1): ?>
                                                <form action="../../CarRental_Backend/api/admin/cars/set_main_image.php" method="POST" class="mb-2">
                                                    <?php echo csrf_field(); ?>
                                                    <input type="hidden" name="ImageID" value="<?php echo (int)$img['ImageID']; ?>">
                                                    <input type="hidden" name="CarID" value="<?php echo (int)$car['CarID']; ?>">
                                                    <button type="submit" class="btn btn-info btn-sm btn-block">
                                                        Đặt ảnh chính
                                                    </button>
                                                </form>
                                            <?php endif; ?>

                                            <a href="<?php echo csrf_url('../../CarRental_Backend/api/admin/cars/delete_image.php?id=' . (int)$img['ImageID'] . '&car_id=' . (int)$car['CarID']); ?>"
                                               class="btn btn-danger btn-sm btn-block"
                                               onclick="return confirm('Bạn có chắc muốn xóa ảnh này?');">
                                                Xóa ảnh
                                            </a>
                                        </div>
                                    <?php endwhile; ?>
                                <?php elseif (!empty($car['MainImage'])): ?>
                                    <div class="img-card">
                                        <div class="img-wrap">
                                            <span class="badge-main">Ảnh chính</span>
                                            <img
                                                src="../../CarRental_Frontend/assets/img/cars/<?php echo htmlspecialchars($car['MainImage']); ?>"
                                                alt="Main image">
                                        </div>
                                        <div class="mt-2 text-muted small text-center">Ảnh cũ từ MainImage</div>
                                    </div>
                                <?php else: ?>
                                    <span class="text-muted">Chưa có ảnh</span>
                                <?php endif; ?>
                            </div>
                        </div>

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