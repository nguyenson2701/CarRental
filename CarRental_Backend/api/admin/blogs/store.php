<?php
require_once '../../../config/auth.php';
requireAdminOrStaff('../../../../CarRental_Admin/login.php');
require_once '../../../config/database.php';
require_once '../../../helpers/blogs.php';

$title = trim($_POST['Title'] ?? '');
$content = trim($_POST['Content'] ?? '');
$status = trim($_POST['Status'] ?? 'Published');
$authorID = (int)($_SESSION['user_id'] ?? 0);

if (!in_array($status, ['Published', 'Draft'], true)) {
    $status = 'Published';
}

if ($title === '' || $content === '') {
    die('Vui long nhap tieu de va noi dung bai viet.');
}

$slug = uniqueBlogSlug($conn, $title);
$thumbnail = uploadBlogThumbnail('Thumbnail');

$stmt = $conn->prepare("
    INSERT INTO blogs (Title, Slug, Content, Thumbnail, AuthorID, Status, CreatedAt, UpdatedAt)
    VALUES (?, ?, ?, ?, ?, ?, NOW(), NOW())
");
$stmt->bind_param("ssssis", $title, $slug, $content, $thumbnail, $authorID, $status);

if ($stmt->execute()) {
    header("Location: ../../../../CarRental_Admin/blogs/list.php?success=1");
    exit();
}

die('Loi them bai viet: ' . $stmt->error);
