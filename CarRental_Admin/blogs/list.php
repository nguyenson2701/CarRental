<?php
require_once '../../CarRental_Backend/config/auth.php';
requireAdminOrStaff('../login.php');
require_once '../../CarRental_Backend/config/database.php';
require_once '../../CarRental_Backend/helpers/blogs.php';

$result = $conn->query("
    SELECT b.BlogID, b.Title, b.Slug, b.Thumbnail, b.Status, b.CreatedAt, b.UpdatedAt, u.FullName
    FROM blogs b
    LEFT JOIN users u ON b.AuthorID = u.UserID
    ORDER BY b.CreatedAt DESC, b.BlogID DESC
");
?>
<!DOCTYPE html>
<html lang="vi">
<head>
    <meta charset="UTF-8">
    <title>Quan ly blog</title>
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
                <div class="d-sm-flex align-items-center justify-content-between mb-4">
                    <h1 class="h3 mb-0 text-gray-800">Quản lý blog</h1>
                    <a href="add.php" class="btn btn-primary btn-sm">
                        <i class="fas fa-plus mr-1"></i> Thêm bài viết
                    </a>
                </div>

                <?php if (isset($_GET['success'])): ?>
                    <div class="alert alert-success">Thao tác thành công.</div>
                <?php endif; ?>

                <div class="card shadow">
                    <div class="card-body table-responsive">
                        <table class="table table-bordered table-hover">
                            <thead class="thead-light">
                                <tr>
                                    <th width="80">Ảnh</th>
                                    <th>Tiêu đề</th>
                                    <th>Tác giả</th>
                                    <th>Ngày tạo</th>
                                    <th>Trạng thái</th>
                                    <th width="190">Thao tác</th>
                                </tr>
                            </thead>
                            <tbody>
                                <?php if ($result && $result->num_rows > 0): ?>
                                    <?php while ($row = $result->fetch_assoc()): ?>
                                        <?php
                                            $imageSrc = blogThumbnailSrc($row['Thumbnail'] ?? '', '../../CarRental_Frontend/assets/img/', '../../CarRental_Frontend/assets/img/cars/blog-1.jpg');
                                            $author = $row['FullName'] ?? 'VinaDrive';
                                        ?>
                                        <tr>
                                            <td>
                                                <img src="<?php echo htmlspecialchars($imageSrc); ?>" alt="" style="width:70px;height:48px;object-fit:cover;border-radius:4px;">
                                            </td>
                                            <td>
                                                <strong><?php echo htmlspecialchars($row['Title']); ?></strong>
                                                <div class="small text-muted"><?php echo htmlspecialchars($row['Slug']); ?></div>
                                            </td>
                                            <td><?php echo htmlspecialchars($author); ?></td>
                                            <td><?php echo htmlspecialchars($row['CreatedAt'] ? date('d/m/Y H:i', strtotime($row['CreatedAt'])) : ''); ?></td>
                                            <td>
                                                <?php if (($row['Status'] ?? '') === 'Published'): ?>
                                                    <span class="badge badge-success">Đang hiển thị</span>
                                                <?php else: ?>
                                                    <span class="badge badge-secondary">Bản nháp</span>
                                                <?php endif; ?>
                                            </td>
                                            <td>
                                                <a href="../../CarRental_Frontend/blog-detail.php?slug=<?php echo urlencode($row['Slug']); ?>" target="_blank" class="btn btn-info btn-sm">Xem</a>
                                                <a href="edit.php?id=<?php echo (int)$row['BlogID']; ?>" class="btn btn-warning btn-sm">Sửa</a>
                                                <a href="../../CarRental_Backend/api/admin/blogs/delete.php?id=<?php echo (int)$row['BlogID']; ?>"
                                                   class="btn btn-danger btn-sm"
                                                   onclick="return confirm('Ban co chac muon xoa bai viet nay?');">Xóa</a>
                                            </td>
                                        </tr>
                                    <?php endwhile; ?>
                                <?php else: ?>
                                    <tr>
                                        <td colspan="6" class="text-center text-muted">Chưa có bài viết nào.</td>
                                    </tr>
                                <?php endif; ?>
                            </tbody>
                        </table>
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
