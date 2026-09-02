<?php
/**
 * Xac thuc va luu anh upload mot cach an toan.
 *
 * Kiem tra: loi upload, kich thuoc, duoi file, MIME type thuc te (qua finfo)
 * va noi dung thuc su la anh (qua getimagesize) truoc khi di chuyen file.
 * Ten file dich duoc sinh ngau nhien, khong dung ten goc cua nguoi dung.
 *
 * @return string|null|false  Ten file moi khi thanh cong,
 *                             null khi khong co file nao duoc gui len,
 *                             false khi file khong hop le / upload that bai.
 */
function safeUploadImage(
    string $fileKey,
    string $destDirAbsolute,
    string $prefix,
    array $allowedExts = ['jpg', 'jpeg', 'png', 'gif', 'webp'],
    int $maxBytes = 5 * 1024 * 1024
) {
    if (!isset($_FILES[$fileKey]) || $_FILES[$fileKey]['error'] === UPLOAD_ERR_NO_FILE) {
        return null;
    }

    $file = $_FILES[$fileKey];

    if ($file['error'] !== UPLOAD_ERR_OK) {
        return false;
    }

    if (!is_uploaded_file($file['tmp_name'])) {
        return false;
    }

    $ext = strtolower(pathinfo($file['name'], PATHINFO_EXTENSION));
    if (!isRealImageUpload($file['tmp_name'], $ext, $allowedExts, $maxBytes)) {
        return false;
    }

    return moveValidatedUpload($file['tmp_name'], $destDirAbsolute, $prefix, $ext);
}

/**
 * Goi safeUploadImage() va tu xu ly ket qua: dung script voi thong bao loi
 * neu file khong hop le, hoac tra ve gia tri cu neu khong co file nao duoc
 * gui len. Giup cac form co nhieu anh (Avatar, GPLX mat truoc/sau...) khong
 * phai lap lai cung 1 khoi if/die o moi truong hop.
 */
function requireValidImageOrDie(
    string $fileKey,
    string $destDirAbsolute,
    string $prefix,
    string $label,
    ?string $currentValue = null,
    array $allowedExts = ['jpg', 'jpeg', 'png', 'gif', 'webp']
): ?string {
    $uploaded = safeUploadImage($fileKey, $destDirAbsolute, $prefix, $allowedExts);

    if ($uploaded === false) {
        die("$label khong hop le (chi chap nhan " . implode(', ', $allowedExts) . ").");
    }

    return $uploaded ?? $currentValue;
}

/**
 * Kiem tra mot file tam da upload co thuc su la anh hop le khong:
 * duoi file nam trong danh sach cho phep, MIME thuc te (finfo) khop voi duoi,
 * va noi dung giai ma duoc bang getimagesize (chan file .php doi ten thanh .jpg).
 */
function isRealImageUpload(string $tmpName, string $ext, array $allowedExts, int $maxBytes = 5 * 1024 * 1024): bool
{
    if (!in_array($ext, $allowedExts, true)) {
        return false;
    }

    $size = @filesize($tmpName);
    if ($size === false || $size <= 0 || $size > $maxBytes) {
        return false;
    }

    $mimeByExt = [
        'jpg'  => ['image/jpeg'],
        'jpeg' => ['image/jpeg'],
        'png'  => ['image/png'],
        'gif'  => ['image/gif'],
        'webp' => ['image/webp'],
    ];

    if (!isset($mimeByExt[$ext])) {
        return false;
    }

    $finfo = finfo_open(FILEINFO_MIME_TYPE);
    $realMime = $finfo ? finfo_file($finfo, $tmpName) : false;
    if ($finfo) {
        finfo_close($finfo);
    }

    if ($realMime === false || !in_array($realMime, $mimeByExt[$ext], true)) {
        return false;
    }

    return @getimagesize($tmpName) !== false;
}

/**
 * Di chuyen mot file tam DA duoc xac thuc (isRealImageUpload) sang thu muc dich,
 * voi ten file ngau nhien. Tra ve ten file moi hoac false neu that bai.
 */
function moveValidatedUpload(string $tmpName, string $destDirAbsolute, string $prefix, string $ext)
{
    if (!is_dir($destDirAbsolute)) {
        if (!mkdir($destDirAbsolute, 0777, true)) {
            return false;
        }
    }

    $newFileName = $prefix . '_' . time() . '_' . bin2hex(random_bytes(4)) . '.' . $ext;
    $targetPath = rtrim($destDirAbsolute, '/\\') . DIRECTORY_SEPARATOR . $newFileName;

    if (!move_uploaded_file($tmpName, $targetPath)) {
        return false;
    }

    return $newFileName;
}
