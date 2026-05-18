<?php
$pageTitle = 'Blog - VinaDrive';
$activePage = 'blog';
include 'includes/header.php';
require_once __DIR__ . '/../CarRental_Backend/helpers/blogs.php';

$stmt = $conn->prepare("
    SELECT b.BlogID, b.Title, b.Slug, b.Content, b.Thumbnail, b.CreatedAt, u.FullName
    FROM blogs b
    LEFT JOIN users u ON b.AuthorID = u.UserID
    WHERE b.Status = 'Published'
    ORDER BY b.CreatedAt DESC, b.BlogID DESC
");
$stmt->execute();
$posts = $stmt->get_result();
?>

<div class="container-fluid bg-breadcrumb mb-5">
    <div class="container text-center py-5" style="max-width: 900px;">
        <h4 class="text-white display-4 mb-4">Blog</h4>
        <p class="text-white-50 mb-0">Thông tin hữu ích giúp bạn thuê xe nhanh, đúng nhu cầu và an tâm hơn trên mọi hành trình.</p>
    </div>
</div>

<div class="container-fluid blog py-5">
    <div class="container">
        <div class="text-center mx-auto pb-5" style="max-width: 760px;">
            <h1 class="display-5 text-capitalize mb-3 section-heading">Kinh nghiệm <span class="text-primary">thuê xe</span></h1>
            <p class="mb-0 section-copy">Cập nhật các mẹo chọn xe, quy trình nhận trả xe và những lưu ý cần biết khi sử dụng dịch vụ VinaDrive.</p>
        </div>

        <div class="row g-4">
            <?php if ($posts && $posts->num_rows > 0): ?>
                <?php while ($post = $posts->fetch_assoc()): ?>
                    <?php
                        $imageSrc = blogThumbnailSrc($post['Thumbnail'] ?? '', 'assets/img/', 'assets/img/cars/blog-1.jpg');
                        $date = $post['CreatedAt'];
                        $excerpt = makeBlogExcerpt($post['Content'] ?? '');
                        $author = $post['FullName'] ?? 'VinaDrive';
                    ?>
                    <div class="col-md-6 col-lg-4">
                        <article class="blog-item h-60 shadow-sm">
                            <div class="blog-img blog-thumb">
                                <img src="<?php echo htmlspecialchars($imageSrc); ?>" class="rounded-top w-100" alt="<?php echo htmlspecialchars($post['Title']); ?>">
                            </div>
                            <div class="blog-content rounded-bottom p-4 h-60">
                                <div class="blog-date"><?php echo htmlspecialchars(date('d/m/Y', strtotime($date))); ?></div>
                                <div class="blog-comment my-3">
                                    <div class="small"><span class="fa fa-tag text-primary"></span><span class="ms-2">Blog</span></div>
                                    <div class="small"><span class="fa fa-user text-primary"></span><span class="ms-2"><?php echo htmlspecialchars($author); ?></span></div>
                                </div>
                                <h3 class="h5 mb-3"><?php echo htmlspecialchars($post['Title']); ?></h3>
                                <p class="mb-4"><?php echo htmlspecialchars($excerpt); ?></p>
                                <a href="blog-detail.php?slug=<?php echo urlencode($post['Slug']); ?>" class="btn btn-outline-primary rounded-pill px-4">Đọc tiếp</a>
                            </div>
                        </article>
                    </div>
                <?php endwhile; ?>
            <?php else: ?>
                <div class="col-12">
                    <div class="text-center bg-light rounded p-5">
                        <h5 class="mb-2">Chưa có bài viết nào</h5>
                        <p class="mb-0">Các bài viết được bật trạng thái hiển thị trong trang quản trị sẽ xuất hiện tại đây.</p>
                    </div>
                </div>
            <?php endif; ?>
        </div>
    </div>
</div>

<?php include 'includes/footer.php'; ?>
