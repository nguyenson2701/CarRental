<?php
require_once '../../CarRental_Backend/config/auth.php';
requireAdminOrStaff('../login.php');
require_once '../../CarRental_Backend/config/database.php';
?>
<!DOCTYPE html>
<html lang="vi">
<head>
    <meta charset="UTF-8">
    <title>Thêm bài viết</title>
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
                <h1 class="h3 mb-4 text-gray-800">Thêm bài viết</h1>

                <div class="card shadow">
                    <div class="card-body">
                        <form action="../../CarRental_Backend/api/admin/blogs/store.php" method="POST" enctype="multipart/form-data">
                            <?php echo csrf_field(); ?>
                            <div class="row">
                                <div class="col-md-9 form-group">
                                    <label>Tiêu đề</label>
                                    <input type="text" name="Title" class="form-control" required>
                                </div>
                                <div class="col-md-3 form-group">
                                    <label>Trạng thái</label>
                                    <select name="Status" class="form-control">
                                        <option value="Published">Đang hiển thị</option>
                                        <option value="Draft">Bản nháp</option>
                                    </select>
                                </div>
                                <div class="col-md-12 form-group">
                                    <label>Ảnh đại diện</label>
                                    <input type="file" name="Thumbnail" class="form-control-file" accept="image/*">
                                </div>
                                <div class="col-md-12 form-group">
                                    <label>Nội dung</label>
                                    <textarea name="Content" class="form-control" rows="12" required></textarea>
                                </div>
                            </div>

                            <button type="submit" class="btn btn-primary">Lưu bài viết</button>
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
<script src="../js/sb-admin-2.min.js"></script>
</body>
</html>
