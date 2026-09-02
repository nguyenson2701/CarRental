<?php

namespace App\Core;

/**
 * Boc lai toan bo logic phan quyen + CSRF da co san trong
 * CarRental_Backend/config/auth.php (khong doi logic, chi doi thanh
 * static method de dung tu Controller). Giu nguyen quy uoc RoleID:
 * 1 = Admin, 2 = Staff, 3 = Customer.
 */
class Auth
{
    public static function start(): void
    {
        if (session_status() === PHP_SESSION_NONE) {
            session_start();
        }
    }

    public static function isLoggedIn(): bool
    {
        return isset($_SESSION['user_id']);
    }

    public static function isAdmin(): bool
    {
        return isset($_SESSION['role_id']) && (int) $_SESSION['role_id'] === 1;
    }

    public static function isStaff(): bool
    {
        return isset($_SESSION['role_id']) && (int) $_SESSION['role_id'] === 2;
    }

    public static function isCustomer(): bool
    {
        return isset($_SESSION['role_id']) && (int) $_SESSION['role_id'] === 3;
    }

    public static function userId(): int
    {
        return (int) ($_SESSION['user_id'] ?? 0);
    }

    public static function requireLogin(string $redirect): void
    {
        if (!self::isLoggedIn()) {
            header("Location: $redirect");
            exit();
        }
        self::requirePostCsrf($redirect);
    }

    public static function requireAdminOrStaff(string $redirect): void
    {
        if (!self::isLoggedIn() || (!self::isAdmin() && !self::isStaff())) {
            header("Location: $redirect");
            exit();
        }
        self::requirePostCsrf($redirect);
    }

    public static function requireAdmin(string $redirect): void
    {
        if (!self::isLoggedIn() || !self::isAdmin()) {
            header("Location: $redirect");
            exit();
        }
        self::requirePostCsrf($redirect);
    }

    public static function requireCustomer(string $redirect): void
    {
        if (!self::isLoggedIn() || !self::isCustomer()) {
            header("Location: $redirect");
            exit();
        }
        self::requirePostCsrf($redirect);
    }

    private static function requirePostCsrf(string $redirect): void
    {
        if (($_SERVER['REQUEST_METHOD'] ?? 'GET') === 'POST') {
            self::requireCsrf($redirect);
        }
    }

    public static function csrfToken(): string
    {
        if (empty($_SESSION['csrf_token'])) {
            $_SESSION['csrf_token'] = bin2hex(random_bytes(32));
        }
        return $_SESSION['csrf_token'];
    }

    public static function csrfField(): string
    {
        return '<input type="hidden" name="csrf_token" value="' . htmlspecialchars(self::csrfToken(), ENT_QUOTES, 'UTF-8') . '">';
    }

    public static function csrfUrl(string $url): string
    {
        $separator = (strpos($url, '?') === false) ? '?' : '&';
        return $url . $separator . 'csrf_token=' . urlencode(self::csrfToken());
    }

    public static function csrfVerify(?string $token): bool
    {
        return isset($_SESSION['csrf_token']) && is_string($token) && $token !== '' && hash_equals($_SESSION['csrf_token'], $token);
    }

    public static function requireCsrf(?string $redirect = null): void
    {
        $token = $_POST['csrf_token'] ?? $_GET['csrf_token'] ?? '';

        if (!self::csrfVerify($token)) {
            if ($redirect) {
                header("Location: $redirect");
                exit();
            }
            http_response_code(403);
            die('Yeu cau khong hop le (CSRF token sai hoac da het han). Vui long tai lai trang va thu lai.');
        }
    }
}
