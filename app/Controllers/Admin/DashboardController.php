<?php

namespace App\Controllers\Admin;

use App\Core\Auth;
use App\Core\Controller;
use App\Models\DashboardModel;

/**
 * Trang chu quan tri. Hanh vi giu dung nhu CarRental_Admin/index.php cu.
 */
class DashboardController extends Controller
{
    private DashboardModel $dashboardModel;

    public function __construct()
    {
        Auth::start();
        Auth::requireAdminOrStaff('/Carrental/CarRental_Admin/login.php');

        // Tu dong cap nhat trang thai xe theo don dat con hieu luc, giu dung
        // hanh vi cu (file nay dung bien $conn toan cuc tu config/database.php).
        require_once __DIR__ . '/../../../CarRental_Backend/config/database.php';
        require_once __DIR__ . '/../../../CarRental_Backend/api/bookings/auto_update_status.php';

        $this->dashboardModel = new DashboardModel();
    }

    public function index(): void
    {
        $revenuePeriod = $this->input('revenue_period', 'month');
        if (!in_array($revenuePeriod, ['today', 'month', 'year'], true)) {
            $revenuePeriod = 'month';
        }

        $revenueDate = $this->input('revenue_date');
        if ($revenueDate !== '' && !preg_match('/^\d{4}-\d{2}-\d{2}$/', $revenueDate)) {
            $revenueDate = '';
        }

        $revenueSearch = $this->input('revenue_q');

        $this->view('admin/dashboard/index', [
            'pageTitle' => 'Dashboard quản trị',
            'totalCars' => $this->dashboardModel->countTable('cars'),
            'totalBrands' => $this->dashboardModel->countTable('brands'),
            'totalUsers' => $this->dashboardModel->countTable('users'),
            'totalBookings' => $this->dashboardModel->countTable('bookings'),
            'pendingBookingsCount' => $this->dashboardModel->pendingBookingsCount(),
            'pendingReturnsCount' => $this->dashboardModel->pendingReturnsCount(),
            'pendingFinalPaymentsCount' => $this->dashboardModel->pendingFinalPaymentsCount(),
            'maintenanceCarsCount' => $this->dashboardModel->maintenanceCarsCount(),
            'cars' => $this->dashboardModel->recentCars(5),
            'bookings' => $this->dashboardModel->recentBookings(5),
            'users' => $this->dashboardModel->recentUsers(5),
            'revenuePeriod' => $revenuePeriod,
            'revenueDate' => $revenueDate,
            'revenueSearch' => $revenueSearch,
            'revenue' => $this->dashboardModel->revenueSummary($revenuePeriod, $revenueDate, $revenueSearch),
            'updated' => isset($_GET['updated']),
            'finalPaid' => isset($_GET['final_paid']),
        ]);
    }
}
