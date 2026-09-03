<?php

namespace App\Controllers\Admin;

use App\Core\Auth;
use App\Core\Controller;
use App\Models\UserModel;

require_once __DIR__ . '/../../../CarRental_Backend/helpers/upload.php';

/**
 * Quan ly nguoi dung. Hanh vi giu dung nhu cac file cu
 * CarRental_Admin/users/{list,add,edit}.php +
 * CarRental_Backend/api/admin/users/{store,update,delete}.php.
 */
class UserController extends Controller
{
    private UserModel $userModel;

    public function __construct()
    {
        Auth::start();
        Auth::requireAdminOrStaff('/Carrental/login');
        $this->userModel = new UserModel();
    }

    public function index(): void
    {
        $keyword = $this->input('keyword');
        $this->view('admin/users/list', [
            'pageTitle' => 'Danh sách người dùng',
            'users' => $this->userModel->search($keyword),
            'keyword' => $keyword,
        ]);
    }

    public function create(): void
    {
        $this->view('admin/users/add', ['pageTitle' => 'Thêm người dùng']);
    }

    public function store(): void
    {
        $password = $this->input('PasswordHash');
        if ($password === '') {
            die('Vui lòng nhập mật khẩu.');
        }

        $avatarDir = __DIR__ . '/../../../CarRental_Frontend/assets/img/avatars/';
        $gplxDir = __DIR__ . '/../../../CarRental_Frontend/assets/img/GPLX/';

        $avatar = requireValidImageOrDie('Avatar', $avatarDir, 'avatar', 'Ảnh đại diện', '');
        $licenseFront = requireValidImageOrDie('LicenseFrontImage', $gplxDir, 'license_front', 'Ảnh GPLX mặt trước', '');
        $licenseBack = requireValidImageOrDie('LicenseBackImage', $gplxDir, 'license_back', 'Ảnh GPLX mặt sau', '');

        $this->userModel->create([
            'FullName' => $this->input('FullName'),
            'Email' => $this->input('Email'),
            'Phone' => $this->input('Phone'),
            'PasswordHash' => password_hash($password, PASSWORD_DEFAULT),
            'Address' => $this->input('Address'),
            'Avatar' => (string) $avatar,
            'LicenseNumber' => $this->input('LicenseNumber'),
            'LicenseFrontImage' => (string) $licenseFront,
            'LicenseBackImage' => (string) $licenseBack,
            'LicenseVerifiedStatus' => $this->input('LicenseVerifiedStatus', 'Pending'),
            'RoleID' => $this->inputInt('RoleID', 3),
            'Status' => $this->input('Status', 'Active'),
        ]);

        $this->redirect('/Carrental/admin/users');
    }

    public function edit(string $id): void
    {
        $user = $this->userModel->find((int) $id);
        if (!$user) {
            $this->redirect('/Carrental/admin/users');
        }

        $this->view('admin/users/edit', [
            'pageTitle' => 'Sửa người dùng',
            'user' => $user,
        ]);
    }

    public function update(string $id): void
    {
        $userId = (int) $id;
        $user = $this->userModel->find($userId);
        if (!$user) {
            die('Không tìm thấy người dùng.');
        }

        $avatarDir = __DIR__ . '/../../../CarRental_Frontend/assets/img/avatars/';
        $gplxDir = __DIR__ . '/../../../CarRental_Frontend/assets/img/GPLX/';

        $avatar = requireValidImageOrDie('Avatar', $avatarDir, 'avatar', 'Ảnh đại diện', $user['Avatar']);
        $licenseFront = requireValidImageOrDie('LicenseFrontImage', $gplxDir, 'license_front', 'Ảnh GPLX mặt trước', $user['LicenseFrontImage']);
        $licenseBack = requireValidImageOrDie('LicenseBackImage', $gplxDir, 'license_back', 'Ảnh GPLX mặt sau', $user['LicenseBackImage']);

        $newPassword = $this->input('PasswordHash');
        $passwordHash = $newPassword !== '' ? password_hash($newPassword, PASSWORD_DEFAULT) : $user['PasswordHash'];

        $this->userModel->update($userId, [
            'FullName' => $this->input('FullName'),
            'Email' => $this->input('Email'),
            'Phone' => $this->input('Phone'),
            'PasswordHash' => $passwordHash,
            'Address' => $this->input('Address'),
            'Avatar' => (string) $avatar,
            'LicenseNumber' => $this->input('LicenseNumber'),
            'LicenseFrontImage' => (string) $licenseFront,
            'LicenseBackImage' => (string) $licenseBack,
            'LicenseVerifiedStatus' => $this->input('LicenseVerifiedStatus', 'Pending'),
            'RoleID' => $this->inputInt('RoleID', 3),
            'Status' => $this->input('Status', 'Active'),
        ]);

        $this->redirect('/Carrental/admin/users');
    }

    public function destroy(string $id): void
    {
        $this->userModel->delete((int) $id);
        $this->redirect('/Carrental/admin/users');
    }
}
