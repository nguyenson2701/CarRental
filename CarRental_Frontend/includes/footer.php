<div class="container-fluid footer py-5 wow fadeIn" data-wow-delay="0.2s">
    <div class="container py-5">
        <div class="row g-5">
            <div class="col-md-6 col-lg-4">
                <h5 class="text-white mb-4">VinaDrive</h5>
                <p class="text-white-50">Dịch vụ thuê xe nhanh chóng, an toàn và tiện lợi.</p>
            </div>
            <div class="col-md-6 col-lg-4">
                <h5 class="text-white mb-4">Liên hệ</h5>
                <p class="text-white-50 mb-2"><i class="fa fa-map-marker-alt me-2"></i>20 Nguyễn Duy Trinh, Nghệ An</p>
                <p class="text-white-50 mb-2"><i class="fa fa-phone-alt me-2"></i>+01234567890</p>
                <p class="text-white-50 mb-0"><i class="fa fa-envelope me-2"></i>example@gmail.com</p>
            </div>
            <div class="col-md-6 col-lg-4">
                <h5 class="text-white mb-4">Điều hướng</h5>
                <a class="btn btn-link text-white-50" href="index.php">Trang chủ</a>
                <a class="btn btn-link text-white-50" href="about.php">Giới thiệu</a>
                <a class="btn btn-link text-white-50" href="vehicle.php">Thuê xe</a>
                <a class="btn btn-link text-white-50" href="contact.php">Liên hệ</a>
            </div>
        </div>
    </div>
</div>

<div class="container-fluid copyright py-4">
    <div class="container text-center">
        <span>© <?php echo date('Y'); ?> VinaDrive. All rights reserved.</span>
    </div>
</div>

<div class="ai-chat-widget" data-ai-chat>
    <button class="ai-chat-toggle" type="button" aria-label="Mở trợ lý AI" aria-expanded="false">
        <i class="fas fa-comments"></i>
        <span class="ai-chat-notice">AI</span>
    </button>

    <section class="ai-chat-panel" aria-label="Trợ lý AI VinaDrive" hidden>
        <div class="ai-chat-header">
            <div class="ai-chat-avatar">
                <i class="fas fa-robot"></i>
            </div>
            <div>
                <h6>Trợ lý AI VinaDrive</h6>
                <p>Sẵn sàng hỗ trợ khách hàng</p>
            </div>
            <button class="ai-chat-close" type="button" aria-label="Đóng trợ lý AI">
                <i class="fas fa-times"></i>
            </button>
        </div>

        <div class="ai-chat-body" role="log" aria-live="polite">
            <div class="ai-message ai-message-bot">
                <span>Xin chào! Mình có thể tư vấn cách đặt xe, giấy tờ cần thiết, thanh toán và trả xe. Bạn cần hỗ trợ gì?</span>
            </div>
        </div>

        <div class="ai-chat-suggestions" aria-label="Câu hỏi gợi ý">
            <button type="button" data-ai-question="Có xe nào còn trống?">Xe còn trống</button>
            <button type="button" data-ai-question="Có xe nào dưới 1 triệu mỗi ngày?">Dưới 1 triệu</button>
            <button type="button" data-ai-question="Đơn của tôi">Đơn của tôi</button>
        </div>

        <form class="ai-chat-form">
            <input type="text" name="message" autocomplete="off" placeholder="Nhập câu hỏi của bạn..." aria-label="Nhập câu hỏi">
            <button type="submit" aria-label="Gửi câu hỏi">
                <i class="fas fa-paper-plane"></i>
            </button>
        </form>
    </section>
</div>

<script src="https://ajax.googleapis.com/ajax/libs/jquery/3.6.4/jquery.min.js"></script>
<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.0.0/dist/js/bootstrap.bundle.min.js"></script>
<script src="lib/wow/wow.min.js"></script>
<script src="lib/owlcarousel/owl.carousel.min.js"></script>
<script src="assets/js/main.js?v=8"></script>
<?php if (!empty($pageScripts)): ?>
    <?php foreach ($pageScripts as $script): ?>
        <script src="<?php echo htmlspecialchars($script); ?>"></script>
    <?php endforeach; ?>
<?php endif; ?>
</body>
</html>
