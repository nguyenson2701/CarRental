<?php
require_once __DIR__ . '/../CarRental_Backend/config/database.php';
require_once __DIR__ . '/../CarRental_Backend/helpers/blogs.php';

$slug = trim($_GET['slug'] ?? '');
if ($slug === '') {
    header("Location: blog.php");
    exit();
}

$stmt = $conn->prepare("
    SELECT b.BlogID, b.Title, b.Slug, b.Content, b.Thumbnail, b.CreatedAt, u.FullName
    FROM blogs b
    LEFT JOIN users u ON b.AuthorID = u.UserID
    WHERE b.Slug = ? AND b.Status = 'Published'
    LIMIT 1
");
$stmt->bind_param("s", $slug);
$stmt->execute();
$post = $stmt->get_result()->fetch_assoc();

if (!$post) {
    header("Location: blog.php");
    exit();
}

$pageTitle = $post['Title'] . ' - VinaDrive';
$activePage = 'blog';
include 'includes/header.php';

$imageSrc = blogThumbnailSrc($post['Thumbnail'] ?? '', 'assets/img/', 'assets/img/cars/blog-1.jpg');
$date = $post['CreatedAt'];
$author = $post['FullName'] ?? 'VinaDrive';

$relatedStmt = $conn->prepare("
    SELECT Title, Slug, Thumbnail, CreatedAt
    FROM blogs
    WHERE Status = 'Published' AND BlogID <> ?
    ORDER BY CreatedAt DESC, BlogID DESC
    LIMIT 3
");
$relatedStmt->bind_param("i", $post['BlogID']);
$relatedStmt->execute();
$relatedPosts = $relatedStmt->get_result();
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
                    <img src="<?php echo htmlspecialchars($imageSrc); ?>" class="blog-detail-thumb rounded w-100 mb-4" alt="<?php echo htmlspecialchars($post['Title']); ?>">
                    <div class="d-flex flex-wrap text-muted mb-3">
                        <span class="me-4"><i class="fa fa-calendar text-primary me-2"></i><?php echo htmlspecialchars(date('d/m/Y H:i', strtotime($date))); ?></span>
                        <span class="me-4"><i class="fa fa-user text-primary me-2"></i><?php echo htmlspecialchars($author); ?></span>
                        <span><i class="fa fa-tag text-primary me-2"></i>Blog</span>
                    </div>
                    <h1 class="display-6 mb-4"><?php echo htmlspecialchars($post['Title']); ?></h1>
                    <p class="lead"><?php echo htmlspecialchars(makeBlogExcerpt($post['Content'] ?? '', 220)); ?></p>
                    <div class="bg-light rounded p-4">
                        <?php echo nl2br(htmlspecialchars($post['Content'])); ?>
                    </div>
                </article>
            </div>

            <div class="col-lg-4">
                <div class="bg-light rounded p-4 mb-4">
                    <h5 class="mb-3">Bài viết khác</h5>
                    <?php if ($relatedPosts && $relatedPosts->num_rows > 0): ?>
                        <?php while ($related = $relatedPosts->fetch_assoc()): ?>
                            <?php
                                $relatedImageSrc = blogThumbnailSrc($related['Thumbnail'] ?? '', 'assets/img/', 'assets/img/cars/blog-2.jpg');
                                $relatedDate = $related['CreatedAt'];
                            ?>
                            <a href="blog-detail.php?slug=<?php echo urlencode($related['Slug']); ?>" class="d-flex text-decoration-none mb-3">
                                <img src="<?php echo htmlspecialchars($relatedImageSrc); ?>" alt="" style="width:88px;height:64px;object-fit:cover;border-radius:6px;">
                                <span class="ms-3">
                                    <strong class="d-block text-dark"><?php echo htmlspecialchars($related['Title']); ?></strong>
                                    <small class="text-muted"><?php echo htmlspecialchars(date('d/m/Y', strtotime($relatedDate))); ?></small>
                                </span>
                            </a>
                        <?php endwhile; ?>
                    <?php else: ?>
                        <p class="mb-0 text-muted">Chưa có bài viết liên quan.</p>
                    <?php endif; ?>
                </div>

                <div class="bg-secondary rounded p-4">
                    <h5 class="text-white mb-3">Cần thuê xe?</h5>
                    <p class="text-white-50">Xem danh sách xe đang sẵn sàng và chọn mẫu phù hợp với lịch trình của bạn.</p>
                    <a href="vehicle.php" class="btn btn-light rounded-pill px-4">Xem xe</a>
                </div>
            </div>
        </div>
    </div>
</div>

<?php include 'includes/footer.php'; ?>
