<?php
require_once '../CarRental_Backend/config/auth.php';
requireLogin('login.php');
require_once '../CarRental_Backend/config/database.php';

$pageTitle = 'Hồ sơ cá nhân';
$activePage = '';

include 'includes/header.php';

$userID = (int)($_SESSION['user_id'] ?? 0);

$stmt = $conn->prepare("
    SELECT 
        UserID,
        FullName,
        Email,
        Phone,
        Address,
        Avatar,
        LicenseNumber,
        LicenseFrontImage,
        LicenseBackImage,
        LicenseVerifiedStatus
    FROM users
    WHERE UserID = ?
    LIMIT 1
");
$stmt->bind_param("i", $userID);
$stmt->execute();
$result = $stmt->get_result();
$user = $result->fetch_assoc();

if (!$user) {
    echo '<div class="container py-5"><h3>Không tìm thấy thông tin người dùng.</h3></div>';
    include 'includes/footer.php';
    exit();
}

function frontendImageUrl($path, $type = 'avatar') {
    $path = trim((string)$path);
    if ($path === '') return '';
    if (preg_match('/^https?:\/\//i', $path)) return $path;

    $path = str_replace('\\', '/', ltrim($path, '/'));

    if (strpos($path, 'CarRental_Frontend/') === 0) {
        $path = substr($path, strlen('CarRental_Frontend/'));
    }

    if (strpos($path, 'assets/') === 0) {
        return $path;
    }

    if ($type === 'license') {
        return 'assets/img/GPLX/' . basename($path);
    }

    return 'assets/img/avatars/' . basename($path);
}

$statusText = $user['LicenseVerifiedStatus'] ?? 'Chưa xác minh';
$statusClass = 'secondary';

if ($statusText === 'Approved' || $statusText === 'Đã xác minh') {
    $statusClass = 'success';
} elseif ($statusText === 'Pending' || $statusText === 'Chờ xác minh') {
    $statusClass = 'warning';
} elseif ($statusText === 'Rejected' || $statusText === 'Từ chối') {
    $statusClass = 'danger';
}
?>

<style>
.profile-wrapper {
    padding: 60px 0;
}
.profile-card {
    border: none;
    border-radius: 24px;
    overflow: hidden;
    box-shadow: 0 18px 45px rgba(0,0,0,.08);
}
.profile-left {
    background: linear-gradient(180deg, #f8fbff 0%, #eef5ff 100%);
    padding: 32px 26px;
    height: 100%;
    border-right: 1px solid #e9eef6;
}
.profile-right {
    padding: 32px;
    background: #fff;
}
.avatar-box {
    text-align: center;
    margin-bottom: 24px;
}
.avatar-preview,
.avatar-empty {
    width: 170px;
    height: 170px;
    border-radius: 50%;
    margin: 0 auto 16px;
    display: flex;
    align-items: center;
    justify-content: center;
    overflow: hidden;
    border: 4px solid #fff;
    box-shadow: 0 14px 30px rgba(0,0,0,.12);
    background: #e9ecef;
}
.avatar-preview img {
    width: 100%;
    height: 100%;
    object-fit: cover;
}
.avatar-empty span {
    color: #6c757d;
    font-weight: 700;
}
.profile-name {
    font-size: 28px;
    font-weight: 800;
    color: #122033;
    margin-bottom: 6px;
}
.profile-email {
    color: #6b7a90;
    margin-bottom: 16px;
}
.info-list {
    background: #fff;
    border: 1px solid #e5edf6;
    border-radius: 18px;
    padding: 18px 18px 8px;
}
.info-item {
    margin-bottom: 12px;
}
.info-item strong {
    display: block;
    color: #44556c;
    font-size: 13px;
    margin-bottom: 3px;
}
.info-item span {
    color: #122033;
    font-weight: 600;
}
.section-title {
    font-size: 28px;
    font-weight: 800;
    color: #122033;
    margin-bottom: 8px;
}
.section-subtitle {
    color: #73839a;
    margin-bottom: 24px;
}
.form-label {
    font-weight: 700;
    color: #243247;
}
.form-control {
    min-height: 50px;
    border-radius: 14px;
    border: 1px solid #dce5f0;
    box-shadow: none !important;
    padding: 12px 16px;
}
.image-card {
    border: 1px solid #e4ebf5;
    border-radius: 18px;
    background: #f8fbff;
    overflow: hidden;
    height: 100%;
}
.image-card-header {
    padding: 14px 16px;
    font-weight: 700;
    color: #243247;
    border-bottom: 1px solid #e4ebf5;
    background: #fff;
}
.image-preview-area {
    padding: 16px;
    text-align: center;
}
.image-preview-box,
.image-empty-box {
    width: 100%;
    height: 220px;
    border-radius: 14px;
    overflow: hidden;
    background: #eef3f8;
    display: flex;
    align-items: center;
    justify-content: center;
    border: 1px dashed #cfd9e6;
}
.image-preview-box img {
    width: 100%;
    height: 100%;
    object-fit: cover;
}
.image-empty-box span {
    color: #6c757d;
    font-weight: 700;
}
.action-btn {
    border-radius: 999px;
    font-weight: 700;
    padding: 10px 18px;
}
.hidden-file-input {
    display: none;
}
@media (max-width: 991px) {
    .profile-left {
        border-right: 0;
        border-bottom: 1px solid #e9eef6;
    }
}
</style>

<div class="container profile-wrapper">
    <div class="card profile-card">
        <div class="row g-0">
            <div class="col-lg-4">
                <div class="profile-left">
                    <div class="avatar-box">
                        <?php if (!empty($user['Avatar'])): ?>
                            <div class="avatar-preview" id="avatarPreviewBox">
                                <img id="avatarPreviewImg" src="<?php echo htmlspecialchars(frontendImageUrl($user['Avatar'], 'avatar')); ?>" alt="Avatar">
                            </div>
                        <?php else: ?>
                            <div class="avatar-empty" id="avatarPreviewBox">
                                <span id="avatarEmptyText">Chưa có</span>
                            </div>
                        <?php endif; ?>

                        <div class="mb-3">
                            <label for="AvatarInput" class="btn btn-primary action-btn">
                                <?php echo !empty($user['Avatar']) ? 'Đổi ảnh' : 'Thêm ảnh'; ?>
                            </label>
                        </div>

                        <div class="profile-name"><?php echo htmlspecialchars($user['FullName'] ?? ''); ?></div>
                        <div class="profile-email"><?php echo htmlspecialchars($user['Email'] ?? ''); ?></div>

                        <span class="badge bg-<?php echo $statusClass; ?> px-3 py-2">
                            GPLX: <?php echo htmlspecialchars($statusText); ?>
                        </span>
                    </div>

                    <div class="info-list">
                        <div class="info-item">
                            <strong>Số điện thoại</strong>
                            <span><?php echo htmlspecialchars($user['Phone'] ?? 'Chưa có'); ?></span>
                        </div>
                        <div class="info-item">
                            <strong>Địa chỉ</strong>
                            <span><?php echo htmlspecialchars($user['Address'] ?? 'Chưa có'); ?></span>
                        </div>
                        <div class="info-item">
                            <strong>Số GPLX</strong>
                            <span><?php echo htmlspecialchars($user['LicenseNumber'] ?? 'Chưa có'); ?></span>
                        </div>
                    </div>
                </div>
            </div>

            <div class="col-lg-8">
                <div class="profile-right">
                    <div class="section-title">Hồ sơ cá nhân</div>
                    <div class="section-subtitle">Cập nhật thông tin tài khoản, ảnh đại diện và giấy phép lái xe của bạn.</div>

                    <?php if (isset($_GET['success'])): ?>
                        <div class="alert alert-success rounded-3">Cập nhật hồ sơ thành công.</div>
                    <?php endif; ?>

                    <?php if (isset($_GET['error'])): ?>
                        <div class="alert alert-danger rounded-3">
                            <?php echo htmlspecialchars($_GET['error']); ?>
                        </div>
                    <?php endif; ?>

                    <form action="../CarRental_Backend/api/user/update_profile.php" method="POST" enctype="multipart/form-data">
                        <input type="file" name="Avatar" id="AvatarInput" class="hidden-file-input" accept="image/*">

                        <div class="row">
                            <div class="col-md-6 mb-3">
                                <label class="form-label">Họ và tên</label>
                                <input type="text" name="FullName" class="form-control" value="<?php echo htmlspecialchars($user['FullName'] ?? ''); ?>" required>
                            </div>

                            <div class="col-md-6 mb-3">
                                <label class="form-label">Email</label>
                                <input type="email" name="Email" class="form-control" value="<?php echo htmlspecialchars($user['Email'] ?? ''); ?>" required>
                            </div>

                            <div class="col-md-6 mb-3">
                                <label class="form-label">Số điện thoại</label>
                                <input type="text" name="Phone" class="form-control" value="<?php echo htmlspecialchars($user['Phone'] ?? ''); ?>" required>
                            </div>

                            <div class="col-md-6 mb-3">
                                <label class="form-label">Số GPLX</label>
                                <input type="text" name="LicenseNumber" class="form-control" value="<?php echo htmlspecialchars($user['LicenseNumber'] ?? ''); ?>">
                            </div>

                            <div class="col-12 mb-4">
                                <label class="form-label">Địa chỉ</label>
                                <input type="text" name="Address" class="form-control" value="<?php echo htmlspecialchars($user['Address'] ?? ''); ?>">
                            </div>

                            <div class="col-md-6 mb-4">
                                <div class="image-card">
                                    <div class="image-card-header">GPLX mặt trước</div>
                                    <div class="image-preview-area">
                                        <?php if (!empty($user['LicenseFrontImage'])): ?>
                                            <div class="image-preview-box" id="frontPreviewBox">
                                                <img id="frontPreviewImg" src="<?php echo htmlspecialchars(frontendImageUrl($user['LicenseFrontImage'], 'license')); ?>" alt="GPLX trước">
                                            </div>
                                        <?php else: ?>
                                            <div class="image-empty-box" id="frontPreviewBox">
                                                <span id="frontEmptyText">Chưa có</span>
                                            </div>
                                        <?php endif; ?>

                                        <input type="file" name="LicenseFrontImage" id="LicenseFrontInput" class="hidden-file-input" accept="image/*">

                                        <div class="mt-3">
                                            <label for="LicenseFrontInput" class="btn btn-outline-primary action-btn">
                                                <?php echo !empty($user['LicenseFrontImage']) ? 'Đổi ảnh' : 'Thêm ảnh'; ?>
                                            </label>
                                        </div>
                                    </div>
                                </div>
                            </div>

                            <div class="col-md-6 mb-4">
                                <div class="image-card">
                                    <div class="image-card-header">GPLX mặt sau</div>
                                    <div class="image-preview-area">
                                        <?php if (!empty($user['LicenseBackImage'])): ?>
                                            <div class="image-preview-box" id="backPreviewBox">
                                                <img id="backPreviewImg" src="<?php echo htmlspecialchars(frontendImageUrl($user['LicenseBackImage'], 'license')); ?>" alt="GPLX sau">
                                            </div>
                                        <?php else: ?>
                                            <div class="image-empty-box" id="backPreviewBox">
                                                <span id="backEmptyText">Chưa có</span>
                                            </div>
                                        <?php endif; ?>

                                        <input type="file" name="LicenseBackImage" id="LicenseBackInput" class="hidden-file-input" accept="image/*">

                                        <div class="mt-3">
                                            <label for="LicenseBackInput" class="btn btn-outline-primary action-btn">
                                                <?php echo !empty($user['LicenseBackImage']) ? 'Đổi ảnh' : 'Thêm ảnh'; ?>
                                            </label>
                                        </div>
                                    </div>
                                </div>
                            </div>

                            <div class="col-12 d-flex gap-2 flex-wrap">
                                <button type="submit" class="btn btn-primary action-btn">
                                    Lưu thay đổi
                                </button>
                                <a href="my-bookings.php" class="btn btn-outline-secondary action-btn">
                                    Đơn đã đặt
                                </a>
                            </div>
                        </div>
                    </form>

                </div>
            </div>
        </div>
    </div>
</div>

<script>
function previewImage(inputId, previewBoxId, previewImgId, emptyTextId) {
    const input = document.getElementById(inputId);
    const previewBox = document.getElementById(previewBoxId);

    if (!input || !previewBox) return;

    input.addEventListener('change', function () {
        const file = this.files[0];
        if (!file) return;

        const reader = new FileReader();
        reader.onload = function (e) {
            let img = document.getElementById(previewImgId);

            if (!img) {
                previewBox.className = 'image-preview-box';
                previewBox.innerHTML = '<img id="' + previewImgId + '" alt="preview">';
                img = document.getElementById(previewImgId);
            }

            const emptyText = document.getElementById(emptyTextId);
            if (emptyText) emptyText.remove();

            img.src = e.target.result;
        };
        reader.readAsDataURL(file);
    });
}

function previewAvatar() {
    const input = document.getElementById('AvatarInput');
    const box = document.getElementById('avatarPreviewBox');

    if (!input || !box) return;

    input.addEventListener('change', function () {
        const file = this.files[0];
        if (!file) return;

        const reader = new FileReader();
        reader.onload = function (e) {
            box.className = 'avatar-preview';
            box.innerHTML = '<img id="avatarPreviewImg" alt="avatar">';
            document.getElementById('avatarPreviewImg').src = e.target.result;
        };
        reader.readAsDataURL(file);
    });
}

document.addEventListener('DOMContentLoaded', function () {
    previewAvatar();
    previewImage('LicenseFrontInput', 'frontPreviewBox', 'frontPreviewImg', 'frontEmptyText');
    previewImage('LicenseBackInput', 'backPreviewBox', 'backPreviewImg', 'backEmptyText');
});
</script>

<?php include 'includes/footer.php'; ?>
