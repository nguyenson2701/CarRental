<?php
/**
 * @var array $featuredCars
 * @var array $latestPosts
 */
?>
<div class="header-carousel mb-5">
    <div id="carouselId" class="carousel slide" data-bs-ride="carousel" data-bs-interval="false">
        <div class="carousel-inner" role="listbox">
            <div class="carousel-item active">
                <img src="/Carrental/CarRental_Frontend/assets/img/fact-bg.jpg" class="img-fluid w-100" alt="slide"/>
                <div class="carousel-caption">
                    <div class="container py-4">
                        <div class="row g-5">
                            <div class="col-lg-6">
                                <div class="bg-secondary rounded p-5">
                                    <h4 class="text-white mb-4">Tiếp tục đặt xe</h4>
                                    <form action="/Carrental/vehicle" method="GET">
                                        <div class="row g-3">
                                            <div class="col-12">
                                                <input class="form-control" type="text" name="keyword" placeholder="Nhập tên xe bạn muốn tìm">
                                            </div>
                                            <div class="col-12">
                                                <button class="btn btn-light w-100 py-2" type="submit">Tìm xe ngay</button>
                                            </div>
                                        </div>
                                    </form>
                                </div>
                            </div>

                            <div class="col-lg-6 d-flex align-items-center">
                                <div>
                                    <h5 class="text-white text-uppercase mb-3">Thuê xe nhanh chóng</h5>
                                    <h1 class="display-4 text-white mb-4">Dịch vụ thuê xe uy tín và tiện lợi</h1>
                                    <p class="text-white mb-4">Chọn xe phù hợp, đặt lịch nhanh, thanh toán dễ dàng.</p>
                                    <a href="/Carrental/vehicle" class="btn btn-primary rounded-pill py-3 px-5">Xem danh sách xe</a>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
     </div>
</div>

<div class="container-fluid overflow-hidden about py-5">
    <div class="container">
        <div class="row g-5">
            <div class="col-xl-6 wow fadeInLeft" data-wow-delay="0.2s">
                <div class="about-item">
                    <div class="pb-5">
                        <h1 class="display-5 text-capitalize">Giới thiệu <span class="text-primary">VinaDrive</span></h1>
                        <p class="mb-0">VinaDrive cung cấp dịch vụ thuê xe nhanh chóng, minh bạch và tiện lợi. Chúng tôi giúp khách hàng dễ dàng chọn mẫu xe phù hợp cho công việc, du lịch, đưa đón hoặc nhu cầu di chuyển hằng ngày.</p>
                    </div>
                    <div class="row g-4">
                        <div class="col-lg-6">
                            <div class="about-item-inner border p-4">
                                <div class="about-icon mb-4"><img src="/Carrental/CarRental_Frontend/assets/img/about-icon-1.png" class="img-fluid w-50 h-50" alt="Icon"></div>
                                <h5 class="mb-3">Tầm nhìn</h5>
                                <p class="mb-0">Trở thành nền tảng thuê xe đáng tin cậy, giúp việc đặt xe trở nên đơn giản và an toàn hơn.</p>
                            </div>
                        </div>
                        <div class="col-lg-6">
                            <div class="about-item-inner border p-4">
                                <div class="about-icon mb-4"><img src="/Carrental/CarRental_Frontend/assets/img/about-icon-2.png" class="img-fluid h-50 w-50" alt="Icon"></div>
                                <h5 class="mb-3">Sứ mệnh</h5>
                                <p class="mb-0">Mang đến nhiều lựa chọn xe chất lượng với mức giá rõ ràng và quy trình đặt xe thuận tiện.</p>
                            </div>
                        </div>
                    </div>
                    <p class="text-item my-4">Với hệ thống xe đa dạng, thông tin giá thuê rõ ràng và hỗ trợ khách hàng trong suốt quá trình sử dụng, VinaDrive luôn hướng đến trải nghiệm thuê xe nhanh gọn, đúng nhu cầu và tiết kiệm thời gian.</p>
                    <div class="row g-4">
                        <div class="col-lg-6">
                            <div class="text-center rounded bg-secondary p-4">
                                <h1 class="display-6 text-white">17</h1>
                                <h5 class="text-light mb-0">Năm kinh nghiệm</h5>
                            </div>
                        </div>
                        <div class="col-lg-6">
                            <div class="rounded">
                                <p class="mb-2"><i class="fa fa-check-circle text-primary me-1"></i> Nhiều dòng xe phù hợp từng nhu cầu</p>
                                <p class="mb-2"><i class="fa fa-check-circle text-primary me-1"></i> Giá thuê minh bạch theo ngày</p>
                                <p class="mb-2"><i class="fa fa-check-circle text-primary me-1"></i> Đặt xe nhanh qua website</p>
                                <p class="mb-0"><i class="fa fa-check-circle text-primary me-1"></i> Hỗ trợ khách hàng tận tình</p>
                            </div>
                        </div>
                        <div class="col-lg-5 d-flex align-items-center">
                            <a href="/Carrental/vehicle" class="btn btn-primary rounded py-3 px-5">Xem xe ngay</a>
                        </div>
                        <div class="col-lg-7">
                            <div class="d-flex align-items-center">
                                <img src="/Carrental/CarRental_Frontend/assets/img/avatars/attachment-img.jpg" class="img-fluid rounded-circle border border-4 border-secondary" style="width: 100px; height: 100px;" alt="Image">
                                <div class="ms-4">
                                    <h4>Đội ngũ VinaDrive</h4>
                                    <p class="mb-0">Luôn đồng hành cùng khách hàng</p>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
            <div class="col-xl-6 wow fadeInRight" data-wow-delay="0.2s">
                <div class="about-img">
                    <div class="img-1"><img src="/Carrental/CarRental_Frontend/assets/img/about-img.jpg" class="img-fluid rounded h-100 w-100" alt=""></div>
                    <div class="img-2"><img src="/Carrental/CarRental_Frontend/assets/img/cars/about-img-1.jpg" class="img-fluid rounded w-100" alt=""></div>
                </div>
            </div>
        </div>
    </div>
</div>

<div class="container-fluid categories py-5">
    <div class="container">
        <div class="text-center mx-auto pb-5 wow fadeInUp" data-wow-delay="0.1s" style="max-width: 800px;">
            <h1 class="display-5 text-capitalize mb-3">Xe <span class="text-primary">nổi bật</span></h1>
            <p class="mb-0">Chọn mẫu xe phù hợp với nhu cầu di chuyển, xem giá thuê theo ngày và đặt lịch nhanh chóng.</p>
        </div>

        <?php if (count($featuredCars) > 0): ?>
            <div class="categories-carousel owl-carousel wow fadeInUp" data-wow-delay="0.1s">
                <?php foreach ($featuredCars as $car): ?>
                    <div class="categories-item p-4">
                        <div class="categories-item-inner">
                            <div class="categories-img rounded-top">
                                <img src="/Carrental/CarRental_Frontend/assets/img/cars/<?= htmlspecialchars($car['MainImage']) ?>" class="img-fluid w-100 rounded-top home-car-img" alt="<?= htmlspecialchars($car['CarName']) ?>">
                            </div>
                            <div class="categories-content rounded-bottom p-4">
                                <h4><?= htmlspecialchars($car['CarName']) ?></h4>
                                <div class="categories-review mb-4">
                                    <div class="me-3">Xe sẵn sàng</div>
                                    <div class="d-flex justify-content-center text-secondary">
                                        <i class="fas fa-star"></i><i class="fas fa-star"></i><i class="fas fa-star"></i><i class="fas fa-star"></i><i class="fas fa-star"></i>
                                    </div>
                                </div>
                                <div class="mb-4">
                                    <h4 class="bg-white text-primary rounded-pill py-2 px-4 mb-0"><?= number_format($car['PricePerDay'], 0, ',', '.') ?> VNĐ/ngày</h4>
                                </div>
                                <div class="row gy-2 gx-0 text-center mb-4">
                                    <div class="col-4 border-end border-white"><i class="fa fa-users text-dark"></i> <span class="text-body ms-1"><?= (int) $car['Seats'] ?> ghế</span></div>
                                    <div class="col-4 border-end border-white"><i class="fa fa-cogs text-dark"></i> <span class="text-body ms-1"><?= htmlspecialchars($car['Transmission']) ?></span></div>
                                    <div class="col-4"><i class="fa fa-gas-pump text-dark"></i> <span class="text-body ms-1"><?= htmlspecialchars($car['FuelType']) ?></span></div>
                                </div>
                                <a href="/Carrental/vehicle/<?= (int) $car['CarID'] ?>" class="btn btn-primary rounded-pill d-flex justify-content-center py-3">Xem chi tiết</a>
                            </div>
                        </div>
                    </div>
                <?php endforeach; ?>
            </div>
        <?php else: ?>
            <div class="text-center bg-light rounded p-5">
                <h5 class="mb-2">Chưa có xe để hiển thị</h5>
                <p class="mb-0">Các xe được thêm trong trang quản trị sẽ xuất hiện tại đây.</p>
            </div>
        <?php endif; ?>
    </div>
</div>

<div class="container-fluid blog py-5 bg-light">
    <div class="container">
        <div class="text-center mx-auto pb-5" style="max-width: 800px;">
            <h1 class="display-5 text-capitalize mb-3 section-heading">Blog <span class="text-primary">thuê xe</span></h1>
            <p class="mb-0 section-copy">Cập nhật kinh nghiệm chọn xe, đặt lịch, nhận trả xe và những lưu ý giúp chuyến đi thuận tiện hơn.</p>
        </div>

        <div class="row g-4 home-blog-list">
            <?php if (count($latestPosts) > 0): ?>
                <?php foreach ($latestPosts as $post): ?>
                    <?php
                        $imageSrc = blogThumbnailSrc($post['Thumbnail'] ?? '', '/Carrental/CarRental_Frontend/assets/img/', '/Carrental/CarRental_Frontend/assets/img/cars/blog-1.jpg');
                        $excerpt = makeBlogExcerpt($post['Content'] ?? '', 120);
                        $author = $post['FullName'] ?? 'VinaDrive';
                    ?>
                    <div class="col-md-6 col-lg-4">
                        <article class="blog-item home-blog-card shadow-sm">
                            <div class="blog-img blog-thumb"><img src="<?= htmlspecialchars($imageSrc) ?>" class="rounded-top w-100" alt="<?= htmlspecialchars($post['Title']) ?>"></div>
                            <div class="blog-content rounded-bottom p-4">
                                <div class="blog-date"><?= htmlspecialchars(date('d/m/Y', strtotime($post['CreatedAt']))) ?></div>
                                <div class="blog-comment my-3">
                                    <div class="small"><span class="fa fa-tag text-primary"></span><span class="ms-2">Blog</span></div>
                                    <div class="small"><span class="fa fa-user text-primary"></span><span class="ms-2"><?= htmlspecialchars($author) ?></span></div>
                                </div>
                                <h3 class="h5 mb-3"><?= htmlspecialchars($post['Title']) ?></h3>
                                <p class="mb-4"><?= htmlspecialchars($excerpt) ?></p>
                                <a href="/Carrental/blog/<?= urlencode($post['Slug']) ?>" class="btn btn-outline-primary rounded-pill px-4">Đọc tiếp</a>
                            </div>
                        </article>
                    </div>
                <?php endforeach; ?>
            <?php else: ?>
                <div class="col-12">
                    <div class="text-center bg-white rounded p-5">
                        <h5 class="mb-2">Chưa có bài viết nào</h5>
                        <p class="mb-0">Các bài viết được bật trạng thái hiển thị trong trang quản trị sẽ xuất hiện tại đây.</p>
                    </div>
                </div>
            <?php endif; ?>
        </div>

        <div class="text-center mt-5">
            <a href="/Carrental/blog" class="btn btn-primary rounded-pill py-3 px-5">Xem tất cả bài viết</a>
        </div>
    </div>
</div>
