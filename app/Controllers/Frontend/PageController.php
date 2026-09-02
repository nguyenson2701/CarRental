<?php

namespace App\Controllers\Frontend;

use App\Core\Auth;
use App\Core\Controller;
use App\Models\BlogModel;
use App\Models\CarModel;

require_once __DIR__ . '/../../../CarRental_Backend/helpers/blogs.php';

/**
 * Cac trang tinh cua Frontend (trang chu, gioi thieu, lien he). Hanh vi
 * giu dung nhu CarRental_Frontend/{index,about,contact}.php cu.
 */
class PageController extends Controller
{
    public function __construct()
    {
        Auth::start();
    }

    public function index(): void
    {
        $carModel = new CarModel();
        $blogModel = new BlogModel();

        $this->view('frontend/pages/index', [
            'pageTitle' => 'Trang chủ - VinaDrive',
            'activePage' => 'index',
            'featuredCars' => $carModel->all('CarID DESC'),
            'latestPosts' => $blogModel->publishedLatest(3),
        ], 'frontend/layout/main');
    }

    public function about(): void
    {
        $this->view('frontend/pages/about', [
            'pageTitle' => 'Giới thiệu - VinaDrive',
            'activePage' => 'about',
        ], 'frontend/layout/main');
    }

    public function contact(): void
    {
        $this->view('frontend/pages/contact', [
            'pageTitle' => 'Liên hệ - VinaDrive',
            'activePage' => 'contact',
        ], 'frontend/layout/main');
    }
}
