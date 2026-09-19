<?php

namespace App\Core;

/**
 * Controller co so. Cac Controller cu the (BrandController...) ke thua
 * va goi $this->view('admin/brands/list', [...]) de render, hoac
 * $this->redirect('/admin/brands') de chuyen huong.
 */
abstract class Controller
{
    /**
     * Render 1 file View, boc trong layout ($layout, mac dinh layout admin).
     * File view CHI duoc chua HTML + bieu thuc PHP hien thi du lieu - khong
     * duoc tu query DB (do la viec cua Model, goi tu Controller).
     *
     * Cach hoat dong: render noi dung view truoc vao buffer, roi truyen
     * bien $content do sang file layout de layout tu quyet dinh dat noi
     * dung o dau (giua sidebar/topbar/footer). Day la cach lam View long
     * trong Layout chuan, tranh moi view phai tu include sidebar/topbar.
     */
    protected function view(string $viewName, array $data = [], ?string $layout = 'admin/layout/main'): void
    {
        extract($data);
        $viewFile = __DIR__ . '/../Views/' . $viewName . '.php';

        if (!is_file($viewFile)) {
            http_response_code(500);
            die("Khong tim thay view: $viewName");
        }

        if ($layout === null) {
            require $viewFile;
            return;
        }

        ob_start();
        require $viewFile;
        $content = ob_get_clean();

        $layoutFile = __DIR__ . '/../Views/' . $layout . '.php';
        if (!is_file($layoutFile)) {
            http_response_code(500);
            die("Khong tim thay layout: $layout");
        }
        require $layoutFile;
    }

    protected function redirect(string $path): void
    {
        header('Location: ' . $path);
        exit();
    }

    /**
     * Lay 1 gia tri tu $_POST, tu dong trim chuoi.
     */
    protected function input(string $key, $default = '')
    {
        $value = $_POST[$key] ?? $_GET[$key] ?? $default;
        return is_string($value) ? trim($value) : $value;
    }

    protected function inputInt(string $key, int $default = 0): int
    {
        return (int) ($_POST[$key] ?? $_GET[$key] ?? $default);
    }

    protected function inputFloat(string $key, float $default = 0.0): float
    {
        return (float) ($_POST[$key] ?? $_GET[$key] ?? $default);
    }
}
