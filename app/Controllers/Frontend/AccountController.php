<?php

namespace App\Controllers\Frontend;

use App\Core\Auth;
use App\Core\Controller;
use App\Models\BookingModel;
use App\Models\PaymentModel;
use App\Models\UserModel;

require_once __DIR__ . '/../../../CarRental_Backend/helpers/upload.php';

/**
 * Khu vuc tai khoan khach hang: ho so, lich su dat xe, thanh toan cua toi.
 * Hanh vi giu dung nhu CarRental_Frontend/{profile,my-bookings,
 * my-payments}.php + CarRental_Backend/api/user/update_profile.php cu.
 */
class AccountController extends Controller
{
    private UserModel $userModel;

    public function __construct()
    {
        Auth::start();
        $this->userModel = new UserModel();
    }

    public function profile(): void
    {
        Auth::requireLogin('/Carrental/login');

        $user = $this->userModel->find(Auth::userId());
        if (!$user) {
            die('Không tìm thấy thông tin người dùng.');
        }

        $statusText = $user['LicenseVerifiedStatus'] ?? 'Chưa xác minh';
        $statusClass = 'secondary';
        if (in_array($statusText, ['Approved', 'Đã xác minh'], true)) {
            $statusClass = 'success';
        } elseif (in_array($statusText, ['Pending', 'Chờ xác minh'], true)) {
            $statusClass = 'warning';
        } elseif (in_array($statusText, ['Rejected', 'Từ chối'], true)) {
            $statusClass = 'danger';
        }

        $this->view('frontend/pages/profile', [
            'pageTitle' => 'Hồ sơ cá nhân',
            'activePage' => '',
            'user' => $user,
            'statusText' => $statusText,
            'statusClass' => $statusClass,
            'success' => isset($_GET['success']),
            'error' => $_GET['error'] ?? '',
        ], 'frontend/layout/main');
    }

    public function updateProfile(): void
    {
        Auth::requireLogin('/Carrental/login');

        $userId = Auth::userId();
        $fullName = $this->input('FullName');
        $email = $this->input('Email');
        $phone = $this->input('Phone');
        $address = $this->input('Address');
        $licenseNumber = $this->input('LicenseNumber');

        if ($fullName === '' || $email === '' || $phone === '') {
            $this->redirect('/Carrental/profile?error=' . urlencode('Vui lòng nhập đầy đủ thông tin bắt buộc.'));
        }

        if (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
            $this->redirect('/Carrental/profile?error=' . urlencode('Email không hợp lệ.'));
        }

        $oldUser = $this->userModel->find($userId);
        if (!$oldUser) {
            $this->redirect('/Carrental/profile?error=' . urlencode('Không tìm thấy tài khoản.'));
        }

        $avatarDir = __DIR__ . '/../../../public/frontend/assets/img/avatars/';
        $gplxDir = __DIR__ . '/../../../public/frontend/assets/img/GPLX/';

        $avatarUploaded = safeUploadImage('Avatar', $avatarDir, 'avatar_' . $userId, ['jpg', 'jpeg', 'png', 'webp']);
        $avatarPath = ($avatarUploaded === null || $avatarUploaded === false) ? ($oldUser['Avatar'] ?? '') : ('assets/img/avatars/' . $avatarUploaded);

        $frontUploaded = safeUploadImage('LicenseFrontImage', $gplxDir, 'license_front_' . $userId, ['jpg', 'jpeg', 'png', 'webp']);
        $frontPath = ($frontUploaded === null || $frontUploaded === false) ? ($oldUser['LicenseFrontImage'] ?? '') : ('assets/img/GPLX/' . $frontUploaded);

        $backUploaded = safeUploadImage('LicenseBackImage', $gplxDir, 'license_back_' . $userId, ['jpg', 'jpeg', 'png', 'webp']);
        $backPath = ($backUploaded === null || $backUploaded === false) ? ($oldUser['LicenseBackImage'] ?? '') : ('assets/img/GPLX/' . $backUploaded);

        if ($this->userModel->emailUsedByOther($email, $userId)) {
            $this->redirect('/Carrental/profile?error=' . urlencode('Email đã được sử dụng bởi tài khoản khác.'));
        }

        $this->userModel->update($userId, [
            'FullName' => $fullName,
            'Email' => $email,
            'Phone' => $phone,
            'Address' => $address,
            'Avatar' => $avatarPath,
            'LicenseNumber' => $licenseNumber,
            'LicenseFrontImage' => $frontPath,
            'LicenseBackImage' => $backPath,
        ]);

        $_SESSION['name'] = $fullName;
        $this->redirect('/Carrental/profile?success=1');
    }

    public function myBookings(): void
    {
        Auth::requireLogin('/Carrental/login');

        $bookingModel = new BookingModel();
        $this->view('frontend/pages/my-bookings', [
            'pageTitle' => 'Lịch sử đặt xe - VinaDrive',
            'activePage' => 'my-bookings',
            'bookings' => $bookingModel->forCustomer(Auth::userId()),
            'success' => isset($_GET['success']),
            'returnRequest' => isset($_GET['return_request']),
        ], 'frontend/layout/main');
    }

    public function myPayments(): void
    {
        Auth::requireLogin('/Carrental/login');

        $paymentModel = new PaymentModel();
        $this->view('frontend/pages/my-payments', [
            'pageTitle' => 'Thanh toán của tôi',
            'activePage' => 'my-payments',
            'payments' => $paymentModel->forCustomer(Auth::userId()),
            'bookingSuccess' => isset($_GET['booking_success']),
        ], 'frontend/layout/main');
    }
}
