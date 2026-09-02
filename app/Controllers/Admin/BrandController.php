<?php

namespace App\Controllers\Admin;

use App\Core\Auth;
use App\Core\Controller;
use App\Models\BrandModel;

/**
 * Quan ly hang xe (module mau cho toan bo kien truc MVC). Hanh vi giu dung
 * nhu cac file cu CarRental_Admin/brands/{list,add,edit}.php +
 * CarRental_Backend/api/admin/brands/{store,update,delete}.php - chi doi
 * cach to chuc, khong doi logic nghiep vu.
 */
class BrandController extends Controller
{
    private BrandModel $brandModel;

    public function __construct()
    {
        Auth::start();
        Auth::requireAdminOrStaff('/Carrental/CarRental_Admin/login.php');
        $this->brandModel = new BrandModel();
    }

    public function index(): void
    {
        $keyword = $this->input('keyword');
        $brands = $this->brandModel->search($keyword);

        $this->view('admin/brands/list', [
            'pageTitle' => 'Danh sách hãng xe',
            'brands' => $brands,
            'keyword' => $keyword,
        ]);
    }

    public function create(): void
    {
        $this->view('admin/brands/add', [
            'pageTitle' => 'Thêm hãng xe',
        ]);
    }

    public function store(): void
    {
        $brandName = $this->input('BrandName');

        if ($brandName === '') {
            die('BrandName đang rỗng');
        }

        $this->brandModel->create(['BrandName' => $brandName]);
        $this->redirect('/Carrental/admin/brands');
    }

    public function edit(string $id): void
    {
        $brand = $this->brandModel->find((int) $id);
        if (!$brand) {
            $this->redirect('/Carrental/admin/brands');
        }

        $this->view('admin/brands/edit', [
            'pageTitle' => 'Sửa hãng xe',
            'brand' => $brand,
        ]);
    }

    public function update(string $id): void
    {
        $brandName = $this->input('BrandName');
        $this->brandModel->update((int) $id, ['BrandName' => $brandName]);
        $this->redirect('/Carrental/admin/brands');
    }

    public function destroy(string $id): void
    {
        $this->brandModel->delete((int) $id);
        $this->redirect('/Carrental/admin/brands');
    }
}
