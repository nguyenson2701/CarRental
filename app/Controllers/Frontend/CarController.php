<?php

namespace App\Controllers\Frontend;

use App\Core\Auth;
use App\Core\Controller;
use App\Models\CarModel;

/**
 * Xem danh sach xe / chi tiet xe / form dat xe (Frontend). Hanh vi giu
 * dung nhu CarRental_Frontend/{vehicle,vehicle-detail,booking}.php cu.
 */
class CarController extends Controller
{
    private CarModel $carModel;

    public function __construct()
    {
        Auth::start();
        $this->carModel = new CarModel();
    }

    public function index(): void
    {
        $keyword = $this->input('keyword');

        $this->view('frontend/pages/vehicle', [
            'pageTitle' => 'Thuê xe - VinaDrive',
            'activePage' => 'vehicle',
            'cars' => $this->carModel->searchByName($keyword),
            'keyword' => $keyword,
        ], 'frontend/layout/main');
    }

    public function show(string $id): void
    {
        $carId = (int) $id;
        $car = $this->carModel->find($carId);

        if (!$car) {
            $this->view('frontend/pages/vehicle-not-found', [
                'pageTitle' => 'Không tìm thấy xe - VinaDrive',
                'activePage' => 'vehicle',
            ], 'frontend/layout/main');
            return;
        }

        $this->view('frontend/pages/vehicle-detail', [
            'pageTitle' => 'Chi tiết xe - VinaDrive',
            'activePage' => 'vehicle',
            'car' => $car,
            'images' => $this->carModel->imagesForCar($carId),
        ], 'frontend/layout/main');
    }

    public function bookForm(string $id): void
    {
        $carId = (int) $id;
        $car = $this->carModel->find($carId);

        if (!$car) {
            $this->view('frontend/pages/vehicle-not-found', [
                'pageTitle' => 'Không tìm thấy xe - VinaDrive',
                'activePage' => 'vehicle',
            ], 'frontend/layout/main');
            return;
        }

        $images = $this->carModel->imagesForCar($carId);

        $this->view('frontend/pages/booking', [
            'pageTitle' => 'Đặt xe - VinaDrive',
            'activePage' => 'vehicle',
            'car' => $car,
            'images' => array_slice($images, 0, 6),
            'pageStyles' => ['/Carrental/public/frontend/assets/css/booking.css?v=1'],
            'pageScripts' => ['/Carrental/public/frontend/assets/js/booking.js?v=1'],
        ], 'frontend/layout/main');
    }
}
