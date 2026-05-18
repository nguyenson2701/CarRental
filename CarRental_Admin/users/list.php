<?php
require_once '../../CarRental_Backend/config/auth.php';
requireAdminOrStaff('../login.php');
require_once '../../CarRental_Backend/config/database.php';

$keyword = trim($_GET['keyword'] ?? '');

if ($keyword !== '') {
    $sql = "SELECT * FROM users
            WHERE FullName LIKE ? OR Email LIKE ? OR Phone LIKE ?
            ORDER BY UserID DESC";
    $stmt = $conn->prepare($sql);
    $search = "%$keyword%";
    $stmt->bind_param("sss", $search, $search, $search);
    $stmt->execute();
    $result = $stmt->get_result();
} else {
    $result = $conn->query("SELECT * FROM users ORDER BY UserID DESC");
}
?>
<!DOCTYPE html>
<html lang="vi">
<head>
    <meta charset="utf-8">
    <title>Quản lý người dùng</title>
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
            <nav class="navbar navbar-expand navbar-light bg-white topbar mb-4 static-top shadow">
                <button id="sidebarToggleTop" class="btn btn-link d-md-none rounded-circle mr-3">
                    <i class="fa fa-bars"></i>
                </button>

                <form method="GET" class="d-none d-sm-inline-block form-inline mr-auto ml-md-3 my-2 my-md-0 mw-100 navbar-search">
                    <div class="input-group">
                        <input type="text" name="keyword" class="form-control bg-light border-0 small"
                               placeholder="Tìm người dùng..."
                               value="<?php echo htmlspecialchars($keyword); ?>">
                        <div class="input-group-append">
                            <button class="btn btn-primary" type="submit">
                                <i class="fas fa-search fa-sm"></i>
                            </button>
                        </div>
                    </div>
                </form>

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
                <div class="d-sm-flex align-items-center justify-content-between mb-4">
                    <h1 class="h3 mb-0 text-gray-800">Danh sách người dùng</h1>
                    <a href="add.php" class="btn btn-primary btn-sm shadow-sm">
                        <i class="fas fa-plus fa-sm text-white-50"></i> Thêm người dùng
                    </a>
                </div>

                <div class="card shadow mb-4">
                    <div class="card-body">
                        <div class="table-responsive">
                            <table class="table table-bordered table-hover">
                                <thead class="thead-light">
                                    <tr>
                                        <th>ID</th>
                                        <th>Avatar</th>
                                        <th>Họ tên</th>
                                        <th>Email</th>
                                        <th>Điện thoại</th>
                                        <th>GPLX mặt trước</th>
                                        <th>GPLX mặt sau</th>
                                        <th>Quyền</th>
                                        <th>Trạng thái</th>
                                        <th width="140">Thao tác</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    <?php if ($result && $result->num_rows > 0): ?>
                                        <?php while ($row = $result->fetch_assoc()): ?>
                                            <tr>
                                                <td><?php echo $row['UserID']; ?></td>

                                                <td>
                                                    <?php if (!empty($row['Avatar'])): ?>
                                                        <img src="../../CarRental_Frontend/assets/img/avatars/<?php echo htmlspecialchars($row['Avatar']); ?>"
                                                            alt="Avatar"
                                                            style="width:60px; height:60px; object-fit:cover; border-radius:50%; border:1px solid #ddd;">
                                                    <?php else: ?>
                                                        <span>Chưa có</span>
                                                    <?php endif; ?>
                                                </td>

                                                <td><?php echo htmlspecialchars($row['FullName']); ?></td>
                                                <td><?php echo htmlspecialchars($row['Email']); ?></td>
                                                <td><?php echo htmlspecialchars($row['Phone']); ?></td>

                                                <td>
                                                    <?php if (!empty($row['LicenseFrontImage'])): ?>
                                                        <a href="../../CarRental_Frontend/assets/img/GPLX/<?php echo htmlspecialchars($row['LicenseFrontImage']); ?>" target="_blank">
                                                            <img src="../../CarRental_Frontend/assets/img/GPLX/<?php echo htmlspecialchars($row['LicenseFrontImage']); ?>"
                                                                alt="GPLX trước"
                                                                style="width:90px; height:60px; object-fit:cover; border-radius:6px; border:1px solid #ddd;">
                                                        </a>
                                                    <?php else: ?>
                                                        <span>Chưa có</span>
                                                    <?php endif; ?>
                                                </td>

                                                <td>
                                                    <?php if (!empty($row['LicenseBackImage'])): ?>
                                                        <a href="../../CarRental_Frontend/assets/img/GPLX/<?php echo htmlspecialchars($row['LicenseBackImage']); ?>" target="_blank">
                                                            <img src="../../CarRental_Frontend/assets/img/GPLX/<?php echo htmlspecialchars($row['LicenseBackImage']); ?>"
                                                                alt="GPLX sau"
                                                                style="width:90px; height:60px; object-fit:cover; border-radius:6px; border:1px solid #ddd;">
                                                        </a>
                                                    <?php else: ?>
                                                        <span>Chưa có</span>
                                                    <?php endif; ?>
                                                </td>

                                                <td>
                                                    <?php
                                                    if ($row['RoleID'] == 1) echo 'Admin';
                                                    elseif ($row['RoleID'] == 2) echo 'Staff';
                                                    else echo 'Customer';
                                                    ?>
                                                </td>

                                                <td><?php echo htmlspecialchars($row['Status']); ?></td>

                                                <td>
                                                    <a href="edit.php?id=<?php echo $row['UserID']; ?>" class="btn btn-warning btn-sm">
                                                        <i class="fas fa-edit"></i>
                                                    </a>
                                                    <a href="../../CarRental_Backend/api/admin/users/delete.php?id=<?php echo $row['UserID']; ?>"
                                                    class="btn btn-danger btn-sm"
                                                    onclick="return confirm('Bạn có chắc muốn xóa người dùng này không?');">
                                                        <i class="fas fa-trash"></i>
                                                    </a>
                                                </td>
                                            </tr>
                                        <?php endwhile; ?>
                                    <?php else: ?>
                                        <tr>
                                            <td colspan="10" class="text-center">Không có dữ liệu người dùng</td>
                                        </tr>
                                    <?php endif; ?>
                                </tbody>
                            </table>
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
</body>
</html>