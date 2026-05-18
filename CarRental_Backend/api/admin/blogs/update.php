<?php
require_once '../../../config/auth.php';
requireAdminOrStaff('../../../../CarRental_Admin/login.php');
require_once '../../../config/database.php';
require_once '../../../helpers/blogs.php';

$id = (int)($_POST['BlogID'] ?? 0);
$title = trim($_POST['Title'] ?? '');
$content = trim($_POST['Content'] ?? '');
$status = trim($_POST['Status'] ?? 'Published');
$currentThumbnail = trim($_POST['CurrentThumbnail'] ?? '');

if (!in_array($status, ['Published', 'Draft'], true)) {
    $status = 'Published';
}

if ($id <= 0 || $title === '' || $content === '') {
    die('Du lieu bai viet khong hop le.');
}

$slug = uniqueBlogSlug($conn, $title, $id);
$uploadedThumbnail = uploadBlogThumbnail('Thumbnail');
$thumbnail = $uploadedThumbnail !== '' ? $uploadedThumbnail : $currentThumbnail;

$stmt = $conn->prepare("
    UPDATE blogs
    SET Title = ?, Slug = ?, Content = ?, Thumbnail = ?, Status = ?, UpdatedAt = NOW()
    WHERE BlogID = ?
");
$stmt->bind_param("sssssi", $title, $slug, $content, $thumbnail, $status, $id);

if ($stmt->execute()) {
    header("Location: ../../../../CarRental_Admin/blogs/list.php?success=1");
    exit();
}

die('Loi cap nhat bai viet: ' . $stmt->error);
