<?php
require_once '../../CarRental_Backend/config/auth.php';
requireAdminOrStaff('../login.php');
require_once '../../CarRental_Backend/config/database.php';

$parents = $conn->query("SELECT MenuID, MenuName FROM menus ORDER BY DisplayOrder ASC, MenuName ASC");
?>
<!DOCTYPE html>
<html lang="vi">
<head>
    <meta charset="UTF-8">
    <title>Thêm menu</title>
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
                <h1 class="h3 mb-4 text-gray-800">Thêm menu</h1>

                <div class="card shadow">
                    <div class="card-body">
                        <form action="../../CarRental_Backend/api/admin/menus/store.php" method="POST">
                            <?php echo csrf_field(); ?>
                            <div class="form-group">
                                <label>Tên menu</label>
                                <input type="text" name="MenuName" class="form-control" required>
                            </div>

                            <div class="form-group">
                                <label>URL</label>
                                <input type="text" name="URL" class="form-control" required placeholder="VD: index.php">
                            </div>

                            <div class="form-group">
                                <label>Menu cha</label>
                                <select name="ParentID" class="form-control">
                                    <option value="">Không có</option>
                                    <?php while ($p = $parents->fetch_assoc()): ?>
                                        <option value="<?php echo (int)$p['MenuID']; ?>">
                                            <?php echo htmlspecialchars($p['MenuName']); ?>
                                        </option>
                                    <?php endwhile; ?>
                                </select>
                            </div>

                            <div class="form-group">
                                <label>Thứ tự hiển thị</label>
                                <input type="number" name="DisplayOrder" class="form-control" value="0">
                            </div>

                            <div class="form-group">
                                <label>Hiển thị</label>
                                <select name="IsActive" class="form-control">
                                    <option value="1">Hiện</option>
                                    <option value="0">Ẩn</option>
                                </select>
                            </div>

                            <button type="submit" class="btn btn-primary">Lưu</button>
                            <a href="list.php" class="btn btn-secondary">Quay lại</a>
                        </form>
                    </div>
                </div>
            </div>
            <?php include '../partials/footer.php'; ?>
        </div>
    </div>
</div>
</body>
</html>