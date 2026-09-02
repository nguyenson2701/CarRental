<?php
require_once '../../CarRental_Backend/config/auth.php';
requireAdminOrStaff('../login.php');
require_once '../../CarRental_Backend/config/database.php';

$currentPage = 'cars';

$keyword = isset($_GET['keyword']) ? trim($_GET['keyword']) : '';

if ($keyword != '') {
    $sql = "SELECT * FROM Cars
            WHERE CarName LIKE ? OR LicensePlate LIKE ? OR Color LIKE ? OR Status LIKE ?
            ORDER BY CarID DESC";
    $stmt = $conn->prepare($sql);
    $search = "%$keyword%";
    $stmt->bind_param("ssss", $search, $search, $search, $search);
    $stmt->execute();
    $result = $stmt->get_result();
} else {
    $sql = "SELECT * FROM Cars ORDER BY CarID DESC";
    $result = $conn->query($sql);
}
?>
<!DOCTYPE html>
<html lang="vi">

<head>
    <meta charset="utf-8">
    <meta http-equiv="X-UA-Compatible" content="IE=edge">
    <meta name="viewport" content="width=device-width, initial-scale=1, shrink-to-fit=no">
    <title>Quản lý xe</title>

    <link href="../vendor/fontawesome-free/css/all.min.css" rel="stylesheet" type="text/css">
    <link href="../vendor/nunito/nunito.css" rel="stylesheet">
    <link href="../css/sb-admin-2.min.css" rel="stylesheet">
    <link href="../vendor/datatables/dataTables.bootstrap4.min.css" rel="stylesheet">
    <link href="../assets/css/cars-admin.css?v=1" rel="stylesheet">


    <style>
        
    </style>
    <link href="../css/admin-theme.css?v=4" rel="stylesheet">
</head>

<body id="page-top">
<div id="wrapper">

    <?php include '../partials/sidebar.php'; ?>

    <div id="content-wrapper" class="d-flex flex-column">
        <div id="content">

            <?php include '../partials/topbar.php'; ?>

            <!-- Nội dung -->
            <div class="container-fluid">
                <div class="d-sm-flex align-items-center justify-content-between mb-4">
                    <h1 class="h3 mb-0 text-gray-800">Danh sách xe</h1>
                    <a href="add.php" class="d-none d-sm-inline-block btn btn-sm btn-primary shadow-sm">
                        <i class="fas fa-plus fa-sm text-white-50"></i> Thêm xe
                    </a>
                </div>

                <div class="card shadow mb-4">
                    <div class="card-body">
                        <form method="GET" class="mb-4">
                            <div class="input-group">
                                <input
                                    type="text"
                                    name="keyword"
                                    class="form-control"
                                    placeholder="Tìm theo tên xe, biển số, màu hoặc trạng thái..."
                                    value="<?php echo htmlspecialchars($keyword); ?>">
                                <div class="input-group-append">
                                    <button class="btn btn-primary" type="submit">
                                        <i class="fas fa-search"></i> Tìm kiếm
                                    </button>
                                    <?php if ($keyword !== ''): ?>
                                        <a href="list.php" class="btn btn-secondary">
                                            Xóa lọc
                                        </a>
                                    <?php endif; ?>
                                </div>
                            </div>
                        </form>

                        <div class="table-responsive">
                            <table class="table table-bordered table-hover" width="100%" cellspacing="0">
                                <thead class="thead-light">
                                    <tr>
                                        <th>ID</th>
                                        <th>Ảnh</th>
                                        <th>Tên xe</th>
                                        <th>Biển số</th>
                                        <th>Màu</th>
                                        <th>Số ghế</th>
                                        <th>Giá/ngày</th>
                                        <th>Trạng thái</th>
                                        <th>Thao tác</th>
                                    </tr>
                                </thead>
                                <tbody>
                                <?php if ($result && $result->num_rows > 0): ?>
                                    <?php while ($row = $result->fetch_assoc()): ?>
                                        <tr>
                                            <td><?php echo $row['CarID']; ?></td>
                                            <td>
                                                <?php
                                                $carID = (int)$row['CarID'];
                                                $stmtImgs = $conn->prepare("
                                                    SELECT ImageID, ImageURL, IsMain
                                                    FROM carimages
                                                    WHERE CarID = ?
                                                    ORDER BY IsMain DESC, ImageID ASC
                                                    LIMIT 4
                                                ");
                                                $stmtImgs->bind_param("i", $carID);
                                                $stmtImgs->execute();
                                                $resultImgs = $stmtImgs->get_result();
                                                ?>

                                                <div style="display:flex;gap:6px;flex-wrap:wrap;max-width:320px;">
                                                    <?php if ($resultImgs->num_rows > 0): ?>
                                                        <?php while ($img = $resultImgs->fetch_assoc()): ?>
                                                            <div style="position:relative;">
                                                                <?php if ((int)$img['IsMain'] === 1): ?>
                                                                    <span style="position:absolute;top:2px;left:2px;background:#1cc88a;color:#fff;font-size:10px;padding:2px 6px;border-radius:12px;z-index:2;">
                                                                        Chính
                                                                    </span>
                                                                <?php endif; ?>

                                                                <img
                                                                    src="../../CarRental_Frontend/assets/img/cars/<?php echo htmlspecialchars($img['ImageURL']); ?>"
                                                                    alt="Car"
                                                                    style="width:70px;height:52px;object-fit:cover;border-radius:6px;border:1px solid #dbe3ea;">
                                                            </div>
                                                        <?php endwhile; ?>
                                                    <?php else: ?>
                                                        <span class="text-muted">Chưa có ảnh</span>
                                                    <?php endif; ?>
                                                </div>
                                            </td>
                                            <td><?php echo htmlspecialchars($row['CarName']); ?></td>
                                            <td><?php echo htmlspecialchars($row['LicensePlate']); ?></td>
                                            <td><?php echo htmlspecialchars($row['Color']); ?></td>
                                            <td><?php echo htmlspecialchars($row['Seats']); ?></td>
                                            <td><?php echo number_format($row['PricePerDay'], 0, ',', '.'); ?> VNĐ</td>
                                            <td>
                                                <?php if ($row['Status'] == 'Available'): ?>
                                                    <span class="badge badge-success">Sẵn sàng</span>
                                                <?php elseif ($row['Status'] == 'Booked'): ?>
                                                    <span class="badge badge-danger">Đã thuê</span>
                                                <?php else: ?>
                                                    <span class="badge badge-warning">Bảo dưỡng</span>
                                                <?php endif; ?>
                                            </td>
                                            <td>
                                                <a href="edit.php?id=<?php echo $row['CarID']; ?>" class="btn btn-warning btn-sm">
                                                    <i class="fas fa-edit"></i>
                                                </a>
                                                <a href="<?php echo csrf_url('../../CarRental_Backend/api/admin/cars/delete.php?id=' . (int)$row['CarID']); ?>"
                                                   class="btn btn-danger btn-sm"
                                                   onclick="return confirm('Bạn có chắc muốn xóa xe này không?');">
                                                    <i class="fas fa-trash"></i>
                                                </a>
                                            </td>
                                        </tr>
                                    <?php endwhile; ?>
                                <?php else: ?>
                                    <tr>
                                        <td colspan="9" class="text-center">Không có dữ liệu xe</td>
                                    </tr>
                                <?php endif; ?>
                                </tbody>
                            </table>
                        </div>
                    </div>
                </div>
            </div>
            <!-- End nội dung -->
        </div>

        <?php include '../partials/footer.php'; ?>
    </div>
</div>

<script src="../vendor/jquery/jquery.min.js"></script>
<script src="../vendor/bootstrap/js/bootstrap.bundle.min.js"></script>
<script src="../vendor/jquery-easing/jquery.easing.min.js"></script>
<script src="../js/sb-admin-2.min.js"></script>
<script src="../vendor/datatables/jquery.dataTables.min.js"></script>
<script src="../vendor/datatables/dataTables.bootstrap4.min.js"></script>
<script src="../assets/js/cars-admin.js"></script>
</body>
</html>
