<?php
require_once '../../CarRental_Backend/config/auth.php';
requireAdminOrStaff('../login.php');
require_once '../../CarRental_Backend/config/database.php';
require_once '../../CarRental_Backend/helpers/blogs.php';

$id = (int)($_GET['id'] ?? 0);
if ($id <= 0) {
    header("Location: list.php");
    exit();
}

$stmt = $conn->prepare("SELECT * FROM blogs WHERE BlogID = ?");
$stmt->bind_param("i", $id);
$stmt->execute();
$blog = $stmt->get_result()->fetch_assoc();

if (!$blog) {
    header("Location: list.php");
    exit();
}
?>
<!DOCTYPE html>
<html lang="vi">
<head>
    <meta charset="UTF-8">
    <title>Sửa bài viết</title>
    <link href="../vendor/fontawesome-free/css/all.min.css" rel="stylesheet">
    <link href="../css/sb-admin-2.min.css" rel="stylesheet">
</head>
<body id="page-top">
<div id="wrapper">
    <?php include '../partials/sidebar.php'; ?>

    <div id="content-wrapper" class="d-flex flex-column">
        <div id="content">
            <?php include '../partials/topbar.php'; ?>

            <div class="container-fluid py-4">
                <h1 class="h3 mb-4 text-gray-800">Sửa bài viết</h1>

                <div class="card shadow">
                    <div class="card-body">
                        <form action="../../CarRental_Backend/api/admin/blogs/update.php" method="POST" enctype="multipart/form-data">
                            <input type="hidden" name="BlogID" value="<?php echo (int)$blog['BlogID']; ?>">
                            <input type="hidden" name="CurrentThumbnail" value="<?php echo htmlspecialchars($blog['Thumbnail'] ?? ''); ?>">

                            <div class="row">
                                <div class="col-md-9 form-group">
                                    <label>Tiêu đề</label>
                                    <input type="text" name="Title" class="form-control" value="<?php echo htmlspecialchars($blog['Title']); ?>" required>
                                </div>
                                <div class="col-md-3 form-group">
                                    <label>Trạng thái</label>
                                    <select name="Status" class="form-control">
                                        <option value="Published" <?php echo ($blog['Status'] ?? '') === 'Published' ? 'selected' : ''; ?>>Đang hiển thị</option>
                                        <option value="Draft" <?php echo ($blog['Status'] ?? '') === 'Draft' ? 'selected' : ''; ?>>Bản nháp</option>
                                    </select>
                                </div>
                                <div class="col-md-12 form-group">
                                    <label>Ảnh đại diện</label>
                                    <?php if (!empty($blog['Thumbnail'])): ?>
                                        <div class="mb-2">
                                            <?php $thumbnailSrc = blogThumbnailSrc($blog['Thumbnail'], '../../CarRental_Frontend/assets/img/', '../../CarRental_Frontend/assets/img/cars/blog-1.jpg'); ?>
                                            <img src="<?php echo htmlspecialchars($thumbnailSrc); ?>" alt="" style="width:160px;height:100px;object-fit:cover;border-radius:4px;">
                                        </div>
                                    <?php endif; ?>
                                    <input type="file" name="Thumbnail" class="form-control-file" accept="image/*">
                                    <small class="form-text text-muted">Bỏ trong nếu muốn giữ ảnh hiện tại.</small>
                                </div>
                                <div class="col-md-12 form-group">
                                    <label>Nội dung</label>
                                    <textarea name="Content" class="form-control" rows="12" required><?php echo htmlspecialchars($blog['Content']); ?></textarea>
                                </div>
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

<script src="../vendor/jquery/jquery.min.js"></script>
<script src="../vendor/bootstrap/js/bootstrap.bundle.min.js"></script>
<script src="../js/sb-admin-2.min.js"></script>
</body>
</html>
