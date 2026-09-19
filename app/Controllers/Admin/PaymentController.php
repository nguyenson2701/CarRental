<?php

namespace App\Controllers\Admin;

use App\Core\Auth;
use App\Core\Controller;
use App\Models\PaymentModel;

/**
 * Xac nhan thanh toan (coc/thue va thanh toan cuoi) tu phia admin. Hanh vi
 * giu dung nhu CarRental_Backend/api/admin/payments/{confirm,confirm_final}.php.
 */
class PaymentController extends Controller
{
    private PaymentModel $paymentModel;

    public function __construct()
    {
        Auth::start();
        Auth::requireAdminOrStaff('/Carrental/login');
        $this->paymentModel = new PaymentModel();
    }

    public function confirm(string $id): void
    {
        $paymentMethod = $this->input('PaymentMethod');
        $redirect = $this->input('Redirect', 'list');

        try {
            $result = $this->paymentModel->confirmInitial((int) $id, $paymentMethod);
        } catch (\RuntimeException $e) {
            die(htmlspecialchars($e->getMessage()));
        }

        $this->redirectAfter($redirect, $result['bookingId'], 'payment_paid=1');
    }

    public function confirmFinal(string $id): void
    {
        $paymentMethod = $this->input('PaymentMethod');
        $redirect = $this->input('Redirect', 'list');

        try {
            $result = $this->paymentModel->confirmFinal((int) $id, $paymentMethod);
        } catch (\RuntimeException $e) {
            die(htmlspecialchars($e->getMessage()));
        }

        $this->redirectAfter($redirect, $result['bookingId'], 'final_paid=1');
    }

    private function redirectAfter(string $redirect, int $bookingId, string $query): void
    {
        $target = $redirect === 'detail'
            ? "/Carrental/admin/bookings/$bookingId?$query"
            : "/Carrental/admin/bookings?$query";
        $this->redirect($target);
    }
}
