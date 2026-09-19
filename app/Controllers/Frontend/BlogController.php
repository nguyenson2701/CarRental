<?php

namespace App\Controllers\Frontend;

use App\Core\Auth;
use App\Core\Controller;
use App\Models\BlogModel;

require_once __DIR__ . '/../../../CarRental_Backend/helpers/blogs.php';

/**
 * Blog Frontend (danh sach + chi tiet). Hanh vi giu dung nhu
 * CarRental_Frontend/{blog,blog-detail}.php cu.
 */
class BlogController extends Controller
{
    private BlogModel $blogModel;

    public function __construct()
    {
        Auth::start();
        $this->blogModel = new BlogModel();
    }

    public function index(): void
    {
        $this->view('frontend/pages/blog', [
            'pageTitle' => 'Blog - VinaDrive',
            'activePage' => 'blog',
            'posts' => $this->blogModel->publishedLatest(1000),
        ], 'frontend/layout/main');
    }

    public function show(string $slug): void
    {
        $post = $this->blogModel->findPublishedBySlug($slug);
        if (!$post) {
            $this->redirect('/Carrental/blog');
        }

        $this->view('frontend/pages/blog-detail', [
            'pageTitle' => $post['Title'] . ' - VinaDrive',
            'activePage' => 'blog',
            'post' => $post,
            'relatedPosts' => $this->blogModel->relatedPublished((int) $post['BlogID'], 3),
        ], 'frontend/layout/main');
    }
}
