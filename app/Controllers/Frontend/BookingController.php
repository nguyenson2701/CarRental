<?php

namespace App\Controllers\Frontend;

use App\Core\Auth;
use App\Core\Controller;
use App\Models\BookingModel;

require_once __DIR__ . '/../../../CarRental_Backend/helpers/bookings.php';
require_once __DIR__ . '/../../../CarRental_Backend/helpers/upload.php';

/**
 * Khach hang tao don dat xe moi + gui yeu cau tra xe. Hanh vi giu dung nhu
 * CarRental_Backend/api/bookings/{store,return_store}.php +
 * CarRental_Frontend/return-car.php cu.
 */
class BookingController extends Controller
{
    private BookingModel $bookingModel;

    public function __construct()
    {
        Auth::start();
        $this->bookingModel = new BookingModel();
    }

    public function store(): void
    {
        Auth::requireLogin('/Carrental/login');

        $userId = Auth::userId();
        $carId = $this->inputInt('CarID');
        $startDate = $this->input('StartDate');
        $endDate = $this->input('EndDate');
        $pickupLocation = $this->input('PickupLocation');
        $returnLocation = $this->input('ReturnLocation');
        $note = $this->input('Note');

        if ($userId <= 0 || $carId <= 0 || $startDate === '' || $endDate === '' || $pickupLocation === '' || $returnLocation === '') {
            die('Thiếu dữ liệu đặt xe.');
        }

        try {
            $this->bookingModel->createForCustomer($userId, $carId, $startDate, $endDate, $pickupLocation, $returnLocation, $note);
        } catch (\RuntimeException $e) {
            die(htmlspecialchars($e->getMessage()));
        }

        $this->redirect('/Carrental/my-payments?booking_success=1');
    }

    public function returnForm(string $id): void
    {
        Auth::requireLogin('/Carrental/login');

        $bookingId = (int) $id;
        $booking = $this->bookingModel->findReturnableForCustomer($bookingId, Auth::userId());

        if (!$booking) {
            die('Không tìm thấy đơn hoặc đơn không thể trả xe.');
        }

        $this->view('frontend/pages/return-car', [
            'pageTitle' => 'Gửi trả xe',
            'activePage' => 'my-bookings',
            'booking' => $booking,
            'error' => $_GET['error'] ?? '',
        ], 'frontend/layout/main');
    }

    public function submitReturn(string $id): void
    {
        Auth::requireLogin('/Carrental/login');

        $bookingId = (int) $id;
        $userId = Auth::userId();
        $actualReturnDate = bookingNormalizeDateTime($this->input('ActualReturnDate'));
        $returnNote = $this->input('ReturnNote');

        if ($bookingId <= 0 || $actualReturnDate === '' || !bookingValidDateTime($actualReturnDate)) {
            die('Dữ liệu không hợp lệ.');
        }

        $uploadDir = __DIR__ . '/../../../public/frontend/assets/img/returns/';
        if (!is_dir($uploadDir) && !mkdir($uploadDir, 0777, true)) {
            die('Không thể tạo thư mục upload ảnh trả xe.');
        }

        $frontUploaded = safeUploadImage('ReturnFrontImage', $uploadDir, 'front_' . $bookingId, ['jpg', 'jpeg', 'png', 'webp']);
        $backUploaded = safeUploadImage('ReturnBackImage', $uploadDir, 'back_' . $bookingId, ['jpg', 'jpeg', 'png', 'webp']);

        $frontImage = ($frontUploaded === null || $frontUploaded === false) ? '' : ('assets/img/returns/' . $frontUploaded);
        $backImage = ($backUploaded === null || $backUploaded === false) ? '' : ('assets/img/returns/' . $backUploaded);

        if ($frontImage === '' || $backImage === '') {
            $this->redirect('/Carrental/my-bookings/' . $bookingId . '/return?error=' . urlencode('Vui lòng tải ảnh đầu xe và sau xe.'));
        }

        try {
            $this->bookingModel->submitReturn($bookingId, $userId, $actualReturnDate, $frontImage, $backImage, $returnNote);
        } catch (\RuntimeException $e) {
            die(htmlspecialchars($e->getMessage()));
        }

        $this->redirect('/Carrental/my-bookings?return_request=1');
    }
}
