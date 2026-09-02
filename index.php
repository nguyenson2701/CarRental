<?php

/**
 * Front controller - diem vao duy nhat cho cac URL "sach" theo kien truc
 * MVC moi (vd /Carrental/admin/brands). Cac trang cu (CarRental_Admin/...,
 * CarRental_Frontend/...) van la file PHP rieng, khong di qua day - dang
 * chuyen doi dan tung module sang MVC, xem CLAUDE.md.
 *
 * BASE_PATH la phan duong dan truoc phan "logic" cua route. Vi du can chinh
 * lai neu doi ten thu muc du an tren XAMPP (hien la /Carrental).
 */

require_once __DIR__ . '/app/autoload.php';

use App\Core\Router;
use App\Controllers\Admin\BrandController;
use App\Controllers\Admin\CarController;
use App\Controllers\Admin\UserController;
use App\Controllers\Admin\BlogController;
use App\Controllers\Admin\MenuController;

const BASE_PATH = '/Carrental';

$router = new Router();

// --- Hang xe ---
$router->get('/admin/brands', [BrandController::class, 'index']);
$router->get('/admin/brands/create', [BrandController::class, 'create']);
$router->post('/admin/brands', [BrandController::class, 'store']);
$router->get('/admin/brands/{id}/edit', [BrandController::class, 'edit']);
$router->post('/admin/brands/{id}/update', [BrandController::class, 'update']);
$router->post('/admin/brands/{id}/delete', [BrandController::class, 'destroy']);

// --- Xe ---
$router->get('/admin/cars', [CarController::class, 'index']);
$router->get('/admin/cars/create', [CarController::class, 'create']);
$router->post('/admin/cars', [CarController::class, 'store']);
$router->get('/admin/cars/{id}/edit', [CarController::class, 'edit']);
$router->post('/admin/cars/{id}/update', [CarController::class, 'update']);
$router->post('/admin/cars/{id}/delete', [CarController::class, 'destroy']);
$router->post('/admin/cars/{id}/images/update', [CarController::class, 'updateImage']);
$router->post('/admin/cars/{id}/images/set-main', [CarController::class, 'setMainImage']);
$router->post('/admin/cars/{id}/images/delete', [CarController::class, 'deleteImage']);

// --- Nguoi dung ---
$router->get('/admin/users', [UserController::class, 'index']);
$router->get('/admin/users/create', [UserController::class, 'create']);
$router->post('/admin/users', [UserController::class, 'store']);
$router->get('/admin/users/{id}/edit', [UserController::class, 'edit']);
$router->post('/admin/users/{id}/update', [UserController::class, 'update']);
$router->post('/admin/users/{id}/delete', [UserController::class, 'destroy']);

// --- Blog ---
$router->get('/admin/blogs', [BlogController::class, 'index']);
$router->get('/admin/blogs/create', [BlogController::class, 'create']);
$router->post('/admin/blogs', [BlogController::class, 'store']);
$router->get('/admin/blogs/{id}/edit', [BlogController::class, 'edit']);
$router->post('/admin/blogs/{id}/update', [BlogController::class, 'update']);
$router->post('/admin/blogs/{id}/delete', [BlogController::class, 'destroy']);

// --- Menu ---
$router->get('/admin/menus', [MenuController::class, 'index']);
$router->get('/admin/menus/create', [MenuController::class, 'create']);
$router->post('/admin/menus', [MenuController::class, 'store']);
$router->get('/admin/menus/{id}/edit', [MenuController::class, 'edit']);
$router->post('/admin/menus/{id}/update', [MenuController::class, 'update']);
$router->post('/admin/menus/{id}/delete', [MenuController::class, 'destroy']);

$router->dispatch(BASE_PATH);
