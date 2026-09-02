<?php
session_start();

function isLoggedIn() {
    return isset($_SESSION['user_id']);
}

function isAdmin() {
    return isset($_SESSION['role_id']) && (int)$_SESSION['role_id'] === 1;
}

function isStaff() {
    return isset($_SESSION['role_id']) && (int)$_SESSION['role_id'] === 2;
}

function isCustomer() {
    return isset($_SESSION['role_id']) && (int)$_SESSION['role_id'] === 3;
}

/*
 * Moi ham requireXxx() ben duoi tu dong kiem tra CSRF khi request la POST,
 * ngay sau khi kiem tra quyen truy cap. Nho vay endpoint moi chi can goi
 * requireLogin()/requireAdmin()/requireAdminOrStaff()/requireCustomer() nhu
 * binh thuong la da duoc bao ve CSRF cho moi thao tac ghi (POST), khong can
 * nho goi requireCsrf() rieng va khong the vo tinh quen (xem requireCsrf() ben
 * duoi). Cac endpoint xoa qua link GET (delete.php...) van phai tu goi
 * requireCsrf() rieng vi day la GET, khong duoc ham nay tu dong bao ve.
 */
function requirePostCsrf($redirect) {
    if ($_SERVER['REQUEST_METHOD'] === 'POST') {
        requireCsrf($redirect);
    }
}

function requireLogin($redirect) {
    if (!isLoggedIn()) {
        header("Location: $redirect");
        exit();
    }
    requirePostCsrf($redirect);
}

function requireAdmin($redirect) {
    if (!isLoggedIn() || !isAdmin()) {
        header("Location: $redirect");
        exit();
    }
    requirePostCsrf($redirect);
}

function requireAdminOrStaff($redirect) {
    if (!isLoggedIn() || (!isAdmin() && !isStaff())) {
        header("Location: $redirect");
        exit();
    }
    requirePostCsrf($redirect);
}

function requireCustomer($redirect) {
    if (!isLoggedIn() || !isCustomer()) {
        header("Location: $redirect");
        exit();
    }
    requirePostCsrf($redirect);
}

/**
 * Tra ve CSRF token cua phien hien tai, tao moi neu chua co.
 */
function csrf_token() {
    if (empty($_SESSION['csrf_token'])) {
        $_SESSION['csrf_token'] = bin2hex(random_bytes(32));
    }
    return $_SESSION['csrf_token'];
}

/**
 * In ra input an chua CSRF token, dat ben trong <form>.
 */
function csrf_field() {
    return '<input type="hidden" name="csrf_token" value="' . htmlspecialchars(csrf_token(), ENT_QUOTES, 'UTF-8') . '">';
}

/**
 * Them CSRF token vao mot URL (dung cho lien ket GET nhu nut xoa).
 */
function csrf_url($url) {
    $separator = (strpos($url, '?') === false) ? '?' : '&';
    return $url . $separator . 'csrf_token=' . urlencode(csrf_token());
}

function csrf_verify($token) {
    return isset($_SESSION['csrf_token']) && is_string($token) && $token !== '' && hash_equals($_SESSION['csrf_token'], $token);
}

/**
 * Kiem tra CSRF token tu POST hoac GET. Neu khong hop le, chuyen huong
 * ve $redirect (neu co) hoac tra loi 403 va dung script.
 */
function requireCsrf($redirect = null) {
    $token = $_POST['csrf_token'] ?? $_GET['csrf_token'] ?? '';

    if (!csrf_verify($token)) {
        if ($redirect) {
            header("Location: $redirect");
            exit();
        }
        http_response_code(403);
        die('Yeu cau khong hop le (CSRF token sai hoac da het han). Vui long tai lai trang va thu lai.');
    }
}
?>