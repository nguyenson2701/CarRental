<?php
/** @var array $blog */
use App\Core\Auth;

$blogId = (int) $blog['BlogID'];
?>
<h1 class="h3 mb-4 text-gray-800">Sửa bài viết</h1>

<div class="card shadow">
    <div class="card-body">
        <form action="/Carrental/admin/blogs/<?= $blogId ?>/update" method="POST" enctype="multipart/form-data">
            <?= Auth::csrfField() ?>
            <input type="hidden" name="CurrentThumbnail" value="<?= htmlspecialchars($blog['Thumbnail'] ?? '') ?>">

            <div class="row">
                <div class="col-md-9 form-group">
                    <label>Tiêu đề</label>
                    <input type="text" name="Title" class="form-control" value="<?= htmlspecialchars($blog['Title']) ?>" required>
                </div>
                <div class="col-md-3 form-group">
                    <label>Trạng thái</label>
                    <select name="Status" class="form-control">
                        <option value="Published" <?= ($blog['Status'] ?? '') === 'Published' ? 'selected' : '' ?>>Đang hiển thị</option>
                        <option value="Draft" <?= ($blog['Status'] ?? '') === 'Draft' ? 'selected' : '' ?>>Bản nháp</option>
                    </select>
                </div>
                <div class="col-md-12 form-group">
                    <label>Ảnh đại diện</label>
                    <?php if (!empty($blog['Thumbnail'])): ?>
                        <div class="mb-2">
                            <?php
                                $thumbnailSrc = blogThumbnailSrc(
                                    $blog['Thumbnail'],
                                    '/Carrental/public/frontend/assets/img/',
                                    '/Carrental/public/frontend/assets/img/cars/blog-1.jpg'
                                );
                            ?>
                            <img src="<?= htmlspecialchars($thumbnailSrc) ?>" alt="" style="width:160px;height:100px;object-fit:cover;border-radius:4px;">
                        </div>
                    <?php endif; ?>
                    <input type="file" name="Thumbnail" class="form-control-file" accept="image/*">
                    <small class="form-text text-muted">Bỏ trong nếu muốn giữ ảnh hiện tại.</small>
                </div>
                <div class="col-md-12 form-group">
                    <label>Nội dung</label>
                    <textarea name="Content" class="form-control" rows="12" required><?= htmlspecialchars($blog['Content']) ?></textarea>
                </div>
            </div>

            <button type="submit" class="btn btn-primary">Cập nhật</button>
            <a href="/Carrental/admin/blogs" class="btn btn-secondary">Quay lại</a>
        </form>
    </div>
</div>
