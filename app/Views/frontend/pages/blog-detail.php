<?php
/**
 * @var array $post
 * @var array $relatedPosts
 */
$imageSrc = blogThumbnailSrc($post['Thumbnail'] ?? '', '/Carrental/public/frontend/assets/img/', '/Carrental/public/frontend/assets/img/cars/blog-1.jpg');
$author = $post['FullName'] ?? 'VinaDrive';
?>
<div class="container-fluid bg-breadcrumb mb-5">
    <div class="container text-center py-5" style="max-width: 900px;">
        <h4 class="text-white display-4 mb-4">Blog</h4>
        <p class="text-white-50 mb-0">VinaDrive</p>
    </div>
</div>

<div class="container-fluid py-5">
    <div class="container">
        <div class="row g-5">
            <div class="col-lg-8">
                <article>
                    <img src="<?= htmlspecialchars($imageSrc) ?>" class="blog-detail-thumb rounded w-100 mb-4" alt="<?= htmlspecialchars($post['Title']) ?>">
                    <div class="d-flex flex-wrap text-muted mb-3">
                        <span class="me-4"><i class="fa fa-calendar text-primary me-2"></i><?= htmlspecialchars(date('d/m/Y H:i', strtotime($post['CreatedAt']))) ?></span>
                        <span class="me-4"><i class="fa fa-user text-primary me-2"></i><?= htmlspecialchars($author) ?></span>
                        <span><i class="fa fa-tag text-primary me-2"></i>Blog</span>
                    </div>
                    <h1 class="display-6 mb-4"><?= htmlspecialchars($post['Title']) ?></h1>
                    <p class="lead"><?= htmlspecialchars(makeBlogExcerpt($post['Content'] ?? '', 220)) ?></p>
                    <div class="bg-light rounded p-4"><?= nl2br(htmlspecialchars($post['Content'])) ?></div>
                </article>
            </div>

            <div class="col-lg-4">
                <div class="bg-light rounded p-4 mb-4">
                    <h5 class="mb-3">Bài viết khác</h5>
                    <?php if (count($relatedPosts) > 0): ?>
                        <?php foreach ($relatedPosts as $related): ?>
                            <?php $relatedImageSrc = blogThumbnailSrc($related['Thumbnail'] ?? '', '/Carrental/public/frontend/assets/img/', '/Carrental/public/frontend/assets/img/cars/blog-2.jpg'); ?>
                            <a href="/Carrental/blog/<?= urlencode($related['Slug']) ?>" class="d-flex text-decoration-none mb-3">
                                <img src="<?= htmlspecialchars($relatedImageSrc) ?>" alt="" style="width:88px;height:64px;object-fit:cover;border-radius:6px;">
                                <span class="ms-3">
                                    <strong class="d-block text-dark"><?= htmlspecialchars($related['Title']) ?></strong>
                                    <small class="text-muted"><?= htmlspecialchars(date('d/m/Y', strtotime($related['CreatedAt']))) ?></small>
                                </span>
                            </a>
                        <?php endforeach; ?>
                    <?php else: ?>
                        <p class="mb-0 text-muted">Chưa có bài viết liên quan.</p>
                    <?php endif; ?>
                </div>

                <div class="bg-secondary rounded p-4">
                    <h5 class="text-white mb-3">Cần thuê xe?</h5>
                    <p class="text-white-50">Xem danh sách xe đang sẵn sàng và chọn mẫu phù hợp với lịch trình của bạn.</p>
                    <a href="/Carrental/vehicle" class="btn btn-light rounded-pill px-4">Xem xe</a>
                </div>
            </div>
        </div>
    </div>
</div>
