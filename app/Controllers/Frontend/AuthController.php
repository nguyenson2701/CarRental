<?php

namespace App\Controllers\Frontend;

use App\Core\Auth;
use App\Core\Controller;
use App\Models\UserModel;

/**
 * Dang nhap / dang ky / dang xuat. Hanh vi giu dung nhu
 * CarRental_Frontend/{login,register}.php +
 * CarRental_Backend/api/auth/{login,register,logout}.php cu.
 */
class AuthController extends Controller
{
    private UserModel $userModel;

    public function __construct()
    {
        Auth::start();
        $this->userModel = new UserModel();
    }

    public function showLogin(): void
    {
        // Giu dung hanh vi cu: dang la admin/staff ma vao lai /login thi
        // bi day ve trang chu Frontend (khong phai trang admin).
        if (Auth::isLoggedIn() && (Auth::isAdmin() || Auth::isStaff())) {
            $this->redirect('/Carrental/CarRental_Frontend/index.php');
        }

        $error = $_SESSION['login_error'] ?? '';
        unset($_SESSION['login_error']);

        $this->view('auth/login', ['pageTitle' => 'Đăng nhập', 'error' => $error], null);
    }

    public function login(): void
    {
        Auth::requireCsrf('/Carrental/login');

        $email = $this->input('email');
        $password = $this->input('password');

        $user = $this->userModel->findByEmail($email);

        if (!$user) {
            $_SESSION['login_error'] = 'Email không tồn tại!';
            $this->redirect('/Carrental/login');
        }

        $storedHash = $user['PasswordHash'];
        $passwordOk = password_verify($password, $storedHash);

        // Tuong thich nguoc: tai khoan cu con luu mat khau dang thuong.
        if (!$passwordOk && $password === $storedHash) {
            $passwordOk = true;
            $this->userModel->updatePasswordHash((int) $user['UserID'], password_hash($password, PASSWORD_DEFAULT));
        }

        if (!$passwordOk) {
            $_SESSION['login_error'] = 'Sai mật khẩu!';
            $this->redirect('/Carrental/login');
        }

        $roleId = (int) ($user['RoleID'] ?? 0);
        $_SESSION['user_id'] = $user['UserID'];
        $_SESSION['role_id'] = $roleId;
        $_SESSION['name'] = $user['FullName'];

        if ($roleId === 1 || $roleId === 2) {
            $this->redirect('/Carrental/admin/dashboard');
        } elseif ($roleId === 3) {
            $this->redirect('/Carrental/CarRental_Frontend/index.php');
        } else {
            $_SESSION['login_error'] = 'Tài khoản chưa được gán quyền hợp lệ!';
            $this->redirect('/Carrental/login');
        }
    }

    public function showRegister(): void
    {
        if (Auth::isLoggedIn()) {
            if (Auth::isAdmin() || Auth::isStaff()) {
                $this->redirect('/Carrental/admin/dashboard');
            } elseif (Auth::isCustomer()) {
                $this->redirect('/Carrental/CarRental_Frontend/index.php');
            }
        }

        $error = $_SESSION['register_error'] ?? '';
        $success = $_SESSION['register_success'] ?? '';
        unset($_SESSION['register_error'], $_SESSION['register_success']);

        $this->view('auth/register', [
            'pageTitle' => 'Đăng ký tài khoản',
            'error' => $error,
            'success' => $success,
        ], null);
    }

    public function register(): void
    {
        Auth::requireCsrf('/Carrental/register');

        $fullName = $this->input('full_name');
        $email = $this->input('email');
        $phone = $this->input('phone');
        $address = $this->input('address');
        $password = $this->input('password');
        $confirmPassword = $this->input('confirm_password');

        if ($fullName === '' || $email === '' || $phone === '' || $password === '' || $confirmPassword === '') {
            $_SESSION['register_error'] = 'Vui lòng nhập đầy đủ thông tin bắt buộc!';
            $this->redirect('/Carrental/register');
        }

        if (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
            $_SESSION['register_error'] = 'Email không hợp lệ!';
            $this->redirect('/Carrental/register');
        }

        if ($password !== $confirmPassword) {
            $_SESSION['register_error'] = 'Mật khẩu nhập lại không khớp!';
            $this->redirect('/Carrental/register');
        }

        if (strlen($password) < 6) {
            $_SESSION['register_error'] = 'Mật khẩu phải có ít nhất 6 ký tự!';
            $this->redirect('/Carrental/register');
        }

        if ($this->userModel->existsByEmailOrPhone($email, $phone)) {
            $_SESSION['register_error'] = 'Email hoặc số điện thoại đã tồn tại!';
            $this->redirect('/Carrental/register');
        }

        $this->userModel->create([
            'FullName' => $fullName,
            'Email' => $email,
            'Phone' => $phone,
            'PasswordHash' => password_hash($password, PASSWORD_DEFAULT),
            'Address' => $address,
            'RoleID' => 3,
            'Status' => 'Active',
        ]);

        $_SESSION['register_success'] = 'Đăng ký thành công! Bạn có thể đăng nhập ngay.';
        $this->redirect('/Carrental/login');
    }

    public function logout(): void
    {
        $_SESSION = [];
        session_destroy();

        if (ini_get('session.use_cookies')) {
            $params = session_get_cookie_params();
            setcookie(session_name(), '', time() - 42000, $params['path'], $params['domain'], $params['secure'], $params['httponly']);
        }

        $this->redirect('/Carrental/login');
    }
}
