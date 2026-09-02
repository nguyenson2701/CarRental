<?php

namespace App\Controllers\Admin;

use App\Core\Auth;
use App\Core\Controller;
use App\Models\MenuModel;

/**
 * Quan ly menu frontend. Hanh vi giu dung nhu cac file cu
 * CarRental_Admin/menus/{list,add,edit}.php +
 * CarRental_Backend/api/admin/menus/{store,update,delete}.php.
 */
class MenuController extends Controller
{
    private MenuModel $menuModel;

    public function __construct()
    {
        Auth::start();
        Auth::requireAdminOrStaff('/Carrental/CarRental_Admin/login.php');
        $this->menuModel = new MenuModel();
    }

    public function index(): void
    {
        $this->view('admin/menus/list', [
            'pageTitle' => 'Quản lý menu',
            'menus' => $this->menuModel->allWithParentName(),
            'success' => isset($_GET['success']),
        ]);
    }

    public function create(): void
    {
        $this->view('admin/menus/add', [
            'pageTitle' => 'Thêm menu',
            'parents' => $this->menuModel->candidatesForParent(),
        ]);
    }

    public function store(): void
    {
        $menuName = $this->input('MenuName');
        $url = $this->input('URL');

        if ($menuName === '' || $url === '') {
            die('Thiếu tên menu hoặc URL.');
        }

        $parentRaw = $this->input('ParentID');

        $this->menuModel->create([
            'MenuName' => $menuName,
            'URL' => $url,
            'ParentID' => $parentRaw === '' ? null : (int) $parentRaw,
            'DisplayOrder' => $this->inputInt('DisplayOrder'),
            'IsActive' => $this->inputInt('IsActive', 1),
        ]);

        $this->redirect('/Carrental/admin/menus?success=1');
    }

    public function edit(string $id): void
    {
        $menuId = (int) $id;
        $menu = $this->menuModel->find($menuId);
        if (!$menu) {
            $this->redirect('/Carrental/admin/menus');
        }

        $this->view('admin/menus/edit', [
            'pageTitle' => 'Sửa menu',
            'menu' => $menu,
            'parents' => $this->menuModel->candidatesForParent($menuId),
        ]);
    }

    public function update(string $id): void
    {
        $menuId = (int) $id;
        $menuName = $this->input('MenuName');
        $url = $this->input('URL');

        if ($menuId <= 0 || $menuName === '' || $url === '') {
            die('Dữ liệu không hợp lệ.');
        }

        $parentRaw = $this->input('ParentID');

        $this->menuModel->update($menuId, [
            'MenuName' => $menuName,
            'URL' => $url,
            'ParentID' => $parentRaw === '' ? null : (int) $parentRaw,
            'DisplayOrder' => $this->inputInt('DisplayOrder'),
            'IsActive' => $this->inputInt('IsActive', 1),
        ]);

        $this->redirect('/Carrental/admin/menus?success=1');
    }

    public function destroy(string $id): void
    {
        $this->menuModel->delete((int) $id);
        $this->redirect('/Carrental/admin/menus?success=1');
    }
}
