<?php
require_once '../../CarRental_Backend/config/auth.php';
requireAdminOrStaff('../login.php');
require_once '../../CarRental_Backend/config/database.php';

$result = $conn->query("
    SELECT m.*, p.MenuName AS ParentName
    FROM menus m
    LEFT JOIN menus p ON m.ParentID = p.MenuID
    ORDER BY m.DisplayOrder ASC, m.MenuID ASC
");
?>
<!DOCTYPE html>
<html lang="vi">
<head>
    <meta charset="UTF-8">
    <title>Quản lý menu</title>
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
            <div class="container-fluid py-4">
                <div class="d-sm-flex align-items-center justify-content-between mb-4">
                    <h1 class="h3 mb-0 text-gray-800">Quản lý menu frontend</h1>
                    <a href="add.php" class="btn btn-primary btn-sm">
                        <i class="fas fa-plus mr-1"></i> Thêm menu
                    </a>
                </div>

                <?php if (isset($_GET['success'])): ?>
                    <div class="alert alert-success">Thao tác thành công.</div>
                <?php endif; ?>

                <div class="card shadow mb-4">
                    <div class="card-body">
                    <div class="table-responsive">
                        <table class="table table-bordered table-hover">
                            <thead class="thead-light">
                                <tr>
                                    <th>ID</th>
                                    <th>Tên menu</th>
                                    <th>URL</th>
                                    <th>Menu cha</th>
                                    <th>Thứ tự</th>
                                    <th>Hiển thị</th>
                                    <th width="160">Thao tác</th>
                                </tr>
                            </thead>
                            <tbody>
                                <?php if ($result && $result->num_rows > 0): ?>
                                    <?php while ($row = $result->fetch_assoc()): ?>
                                        <tr>
                                            <td><?php echo (int)$row['MenuID']; ?></td>
                                            <td><?php echo htmlspecialchars($row['MenuName']); ?></td>
                                            <td><?php echo htmlspecialchars($row['URL']); ?></td>
                                            <td><?php echo htmlspecialchars($row['ParentName'] ?? 'Không có'); ?></td>
                                            <td><?php echo (int)$row['DisplayOrder']; ?></td>
                                            <td>
                                                <?php if ((int)$row['IsActive'] === 1): ?>
                                                    <span class="badge badge-success">Hiện</span>
                                                <?php else: ?>
                                                    <span class="badge badge-secondary">Ẩn</span>
                                                <?php endif; ?>
                                            </td>
                                            <td>
                                                <a href="edit.php?id=<?php echo (int)$row['MenuID']; ?>" class="btn btn-warning btn-sm">
                                                    Sửa
                                                </a>
                                                <a href="<?php echo csrf_url('../../CarRental_Backend/api/admin/menus/delete.php?id=' . (int)$row['MenuID']); ?>"
                                                   class="btn btn-danger btn-sm"
                                                   onclick="return confirm('Bạn có chắc muốn xóa menu này?');">
                                                    Xóa
                                                </a>
                                            </td>
                                        </tr>
                                    <?php endwhile; ?>
                                <?php else: ?>
                                    <tr>
                                        <td colspan="7" class="text-center text-muted">Chưa có menu nào.</td>
                                    </tr>
                                <?php endif; ?>
                            </tbody>
                        </table>
                    </div>
                    </div>
                </div>
            </div>
            <?php include '../partials/footer.php'; ?>
        </div>
    </div>
</div>

<script src="../vendor/jquery/jquery.min.js"></script>
<script src="../vendor/bootstrap/js/bootstrap.bundle.min.js"></script>
<script src="../js/sb-admin-2.min.js"></script>
</body>
</html>