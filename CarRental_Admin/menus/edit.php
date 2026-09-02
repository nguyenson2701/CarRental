<?php
require_once '../../CarRental_Backend/config/auth.php';
requireAdminOrStaff('../login.php');
require_once '../../CarRental_Backend/config/database.php';

$id = (int)($_GET['id'] ?? 0);
if ($id <= 0) {
    header("Location: list.php");
    exit();
}

$stmt = $conn->prepare("SELECT * FROM menus WHERE MenuID = ?");
$stmt->bind_param("i", $id);
$stmt->execute();
$result = $stmt->get_result();
$menu = $result->fetch_assoc();

if (!$menu) {
    header("Location: list.php");
    exit();
}

$parents = $conn->query("SELECT MenuID, MenuName FROM menus WHERE MenuID <> $id ORDER BY DisplayOrder ASC, MenuName ASC");
?>
<!DOCTYPE html>
<html lang="vi">
<head>
    <meta charset="UTF-8">
    <title>Sửa menu</title>
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
                <h1 class="h3 mb-4 text-gray-800">Sửa menu</h1>

                <div class="card shadow">
                    <div class="card-body">
                        <form action="../../CarRental_Backend/api/admin/menus/update.php" method="POST">
                            <?php echo csrf_field(); ?>
                            <input type="hidden" name="MenuID" value="<?php echo (int)$menu['MenuID']; ?>">

                            <div class="form-group">
                                <label>Tên menu</label>
                                <input type="text" name="MenuName" class="form-control" value="<?php echo htmlspecialchars($menu['MenuName']); ?>" required>
                            </div>

                            <div class="form-group">
                                <label>URL</label>
                                <input type="text" name="URL" class="form-control" value="<?php echo htmlspecialchars($menu['URL']); ?>" required>
                            </div>

                            <div class="form-group">
                                <label>Menu cha</label>
                                <select name="ParentID" class="form-control">
                                    <option value="">Không có</option>
                                    <?php while ($p = $parents->fetch_assoc()): ?>
                                        <option value="<?php echo (int)$p['MenuID']; ?>"
                                            <?php echo ((int)$menu['ParentID'] === (int)$p['MenuID']) ? 'selected' : ''; ?>>
                                            <?php echo htmlspecialchars($p['MenuName']); ?>
                                        </option>
                                    <?php endwhile; ?>
                                </select>
                            </div>

                            <div class="form-group">
                                <label>Thứ tự hiển thị</label>
                                <input type="number" name="DisplayOrder" class="form-control" value="<?php echo (int)$menu['DisplayOrder']; ?>">
                            </div>

                            <div class="form-group">
                                <label>Hiển thị</label>
                                <select name="IsActive" class="form-control">
                                    <option value="1" <?php echo (int)$menu['IsActive'] === 1 ? 'selected' : ''; ?>>Hiện</option>
                                    <option value="0" <?php echo (int)$menu['IsActive'] === 0 ? 'selected' : ''; ?>>Ẩn</option>
                                </select>
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
</body>
</html>