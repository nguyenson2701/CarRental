<?php

namespace App\Controllers\Admin;

use App\Core\Auth;
use App\Core\Controller;
use App\Models\BookingModel;

/**
 * Quan ly don dat xe. Hanh vi giu dung nhu cac file cu
 * CarRental_Admin/bookings/{list,detail,return_check}.php +
 * CarRental_Backend/api/admin/bookings/{update,return_approve}.php.
 */
class BookingController extends Controller
{
    private BookingModel $bookingModel;

    public function __construct()
    {
        Auth::start();
        Auth::requireAdminOrStaff('/Carrental/login');

        // Tu dong cap nhat trang thai xe theo don con hieu luc, giu dung
        // hanh vi cu (file nay dung bien $conn toan cuc tu config/database.php).
        require_once __DIR__ . '/../../../CarRental_Backend/config/database.php';
        require_once __DIR__ . '/../../../CarRental_Backend/api/bookings/auto_update_status.php';

        $this->bookingModel = new BookingModel();
    }

    public function index(): void
    {
        $this->view('admin/bookings/list', [
            'pageTitle' => 'Quản lý đơn đặt xe',
            'bookings' => $this->bookingModel->listWithDetails(),
            'updated' => isset($_GET['updated']),
            'finalPaid' => isset($_GET['final_paid']),
            'paymentPaid' => isset($_GET['payment_paid']),
        ]);
    }

    public function show(string $id): void
    {
        $bookingId = (int) $id;
        $booking = $this->bookingModel->findWithDetails($bookingId);
        if (!$booking) {
            $this->redirect('/Carrental/admin/bookings');
        }

        $paymentRows = $this->bookingModel->paymentsForBooking($bookingId);

        $this->view('admin/bookings/detail', [
            'pageTitle' => 'Chi tiết đơn đặt xe',
            'booking' => $booking,
            'paymentRows' => $paymentRows,
            'finalPaid' => isset($_GET['final_paid']),
            'paymentPaid' => isset($_GET['payment_paid']),
        ]);
    }

    public function returnCheck(string $id): void
    {
        $booking = $this->bookingModel->findForReturnCheck((int) $id);
        if (!$booking) {
            die('Không tìm thấy đơn.');
        }

        $this->view('admin/bookings/return_check', [
            'pageTitle' => 'Kiểm tra trả xe',
            'booking' => $booking,
        ]);
    }

    public function cancel(string $id): void
    {
        $bookingId = (int) $id;
        $redirect = $this->input('Redirect', 'list');

        try {
            $this->bookingModel->cancel($bookingId);
        } catch (\RuntimeException $e) {
            die(htmlspecialchars($e->getMessage()));
        }

        $this->redirectAfter($redirect, $bookingId, 'updated=1');
    }

    public function returnApprove(string $id): void
    {
        $bookingId = (int) $id;
        $damageFee = $this->inputFloat('DamageFee');
        $cleaningFee = $this->inputFloat('CleaningFee');
        $otherFee = $this->inputFloat('OtherFee');
        $penaltyReason = $this->input('PenaltyReason');

        try {
            $this->bookingModel->approveReturn($bookingId, $damageFee, $cleaningFee, $otherFee, $penaltyReason);
        } catch (\RuntimeException $e) {
            die(htmlspecialchars($e->getMessage()));
        }

        $this->redirect('/Carrental/admin/bookings?returned=1');
    }

    private function redirectAfter(string $redirect, int $bookingId, string $query): void
    {
        $target = $redirect === 'detail'
            ? "/Carrental/admin/bookings/$bookingId?$query"
            : "/Carrental/admin/bookings?$query";
        $this->redirect($target);
    }
}
