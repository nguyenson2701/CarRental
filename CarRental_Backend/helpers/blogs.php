<?php

require_once __DIR__ . '/upload.php';

function makeBlogSlug(string $title): string
{
    $slug = trim($title);
    if (function_exists('iconv')) {
        $converted = iconv('UTF-8', 'ASCII//TRANSLIT//IGNORE', $slug);
        if ($converted !== false) {
            $slug = $converted;
        }
    }

    $slug = strtolower($slug);
    $slug = preg_replace('/[^a-z0-9]+/', '-', $slug);
    $slug = trim((string)$slug, '-');

    return $slug !== '' ? $slug : 'bai-viet';
}

function uniqueBlogSlug(mysqli $conn, string $title, int $ignoreID = 0): string
{
    $baseSlug = makeBlogSlug($title);
    $slug = $baseSlug;
    $counter = 2;

    while (true) {
        if ($ignoreID > 0) {
            $stmt = $conn->prepare("SELECT BlogID FROM blogs WHERE Slug = ? AND BlogID <> ? LIMIT 1");
            $stmt->bind_param("si", $slug, $ignoreID);
        } else {
            $stmt = $conn->prepare("SELECT BlogID FROM blogs WHERE Slug = ? LIMIT 1");
            $stmt->bind_param("s", $slug);
        }

        $stmt->execute();
        $exists = $stmt->get_result()->num_rows > 0;

        if (!$exists) {
            return $slug;
        }

        $slug = $baseSlug . '-' . $counter;
        $counter++;
    }
}

function uploadBlogThumbnail(string $fieldName = 'Thumbnail'): string
{
    if (empty($_FILES[$fieldName]['name']) || ($_FILES[$fieldName]['error'] ?? UPLOAD_ERR_NO_FILE) !== UPLOAD_ERR_OK) {
        return '';
    }

    $uploadDir = __DIR__ . '/../../public/frontend/assets/img/blogs';
    $fileName = safeUploadImage($fieldName, $uploadDir, 'blog', ['jpg', 'jpeg', 'png', 'gif', 'webp']);

    if ($fileName === null) {
        return '';
    }

    if ($fileName === false) {
        die('Anh blog khong hop le (chi chap nhan jpg, jpeg, png, gif, webp va phai la anh that).');
    }

    return $fileName;
}

function blogThumbnailSrc(string $thumbnail, string $publicPrefix = 'assets/img/', string $fallback = 'assets/img/cars/blog-1.jpg'): string
{
    $thumbnail = trim($thumbnail);
    if ($thumbnail === '') {
        return $fallback;
    }

    $normalized = str_replace('\\', '/', $thumbnail);
    $normalized = ltrim($normalized, '/');

    if (strpos($normalized, 'assets/img/') === 0) {
        $normalized = substr($normalized, strlen('assets/img/'));
    }

    if (strpos($normalized, '/') === false) {
        $normalized = 'blogs/' . $normalized;
    }

    $absolutePath = __DIR__ . '/../../public/frontend/assets/img/' . $normalized;
    if (!is_file($absolutePath)) {
        if (preg_match('/^blogs\/blog(\d+)\.(jpg|jpeg|png|webp|gif)$/i', $normalized, $matches)) {
            $legacyFallback = 'cars/blog-' . $matches[1] . '.' . strtolower($matches[2]);
            $legacyPath = __DIR__ . '/../../public/frontend/assets/img/' . $legacyFallback;
            if (is_file($legacyPath)) {
                return rtrim($publicPrefix, '/') . '/' . $legacyFallback;
            }
        }

        return $fallback;
    }

    return rtrim($publicPrefix, '/') . '/' . $normalized;
}

function makeBlogExcerpt(string $content, int $length = 150): string
{
    $plain = trim(preg_replace('/\s+/', ' ', strip_tags($content)));
    if (mb_strlen($plain, 'UTF-8') <= $length) {
        return $plain;
    }

    return mb_substr($plain, 0, $length, 'UTF-8') . '...';
}
