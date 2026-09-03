<?php
/**
 * @var array $blogs
 * @var bool $success
 */
use App\Core\Auth;
?>
<div class="d-sm-flex align-items-center justify-content-between mb-4">
    <h1 class="h3 mb-0 text-gray-800">Quản lý blog</h1>
    <a href="/Carrental/admin/blogs/create" class="btn btn-primary btn-sm">
        <i class="fas fa-plus mr-1"></i> Thêm bài viết
    </a>
</div>

<?php if ($success): ?>
    <div class="alert alert-success">Thao tác thành công.</div>
<?php endif; ?>

<div class="card shadow mb-4">
    <div class="card-body">
        <div class="table-responsive">
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
                    <?php if (count($blogs) > 0): ?>
                        <?php foreach ($blogs as $row): ?>
                            <?php
                                $imageSrc = blogThumbnailSrc(
                                    $row['Thumbnail'] ?? '',
                                    '/Carrental/CarRental_Frontend/assets/img/',
                                    '/Carrental/CarRental_Frontend/assets/img/cars/blog-1.jpg'
                                );
                                $author = $row['FullName'] ?? 'VinaDrive';
                            ?>
                            <tr>
                                <td>
                                    <img src="<?= htmlspecialchars($imageSrc) ?>" alt="" style="width:70px;height:48px;object-fit:cover;border-radius:4px;">
                                </td>
                                <td>
                                    <strong><?= htmlspecialchars($row['Title']) ?></strong>
                                    <div class="small text-muted"><?= htmlspecialchars($row['Slug']) ?></div>
                                </td>
                                <td><?= htmlspecialchars($author) ?></td>
                                <td><?= htmlspecialchars($row['CreatedAt'] ? date('d/m/Y H:i', strtotime($row['CreatedAt'])) : '') ?></td>
                                <td>
                                    <?php if (($row['Status'] ?? '') === 'Published'): ?>
                                        <span class="badge badge-success">Đang hiển thị</span>
                                    <?php else: ?>
                                        <span class="badge badge-secondary">Bản nháp</span>
                                    <?php endif; ?>
                                </td>
                                <td>
                                    <a href="/Carrental/blog/<?= urlencode($row['Slug']) ?>" target="_blank" class="btn btn-info btn-sm">Xem</a>
                                    <a href="/Carrental/admin/blogs/<?= (int) $row['BlogID'] ?>/edit" class="btn btn-warning btn-sm">Sửa</a>
                                    <form action="/Carrental/admin/blogs/<?= (int) $row['BlogID'] ?>/delete" method="POST"
                                          class="d-inline" onsubmit="return confirm('Ban co chac muon xoa bai viet nay?');">
                                        <?= Auth::csrfField() ?>
                                        <button type="submit" class="btn btn-danger btn-sm">Xóa</button>
                                    </form>
                                </td>
                            </tr>
                        <?php endforeach; ?>
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
