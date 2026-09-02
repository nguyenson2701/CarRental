<?php

namespace App\Controllers\Admin;

use App\Core\Auth;
use App\Core\Controller;
use App\Models\BlogModel;

require_once __DIR__ . '/../../../CarRental_Backend/helpers/blogs.php';

/**
 * Quan ly blog. Hanh vi giu dung nhu cac file cu
 * CarRental_Admin/blogs/{list,add,edit}.php +
 * CarRental_Backend/api/admin/blogs/{store,update,delete}.php.
 */
class BlogController extends Controller
{
    private BlogModel $blogModel;

    public function __construct()
    {
        Auth::start();
        Auth::requireAdminOrStaff('/Carrental/CarRental_Admin/login.php');
        $this->blogModel = new BlogModel();
    }

    public function index(): void
    {
        $this->view('admin/blogs/list', [
            'pageTitle' => 'Quản lý blog',
            'blogs' => $this->blogModel->allWithAuthor(),
            'success' => isset($_GET['success']),
        ]);
    }

    public function create(): void
    {
        $this->view('admin/blogs/add', ['pageTitle' => 'Thêm bài viết']);
    }

    public function store(): void
    {
        $title = $this->input('Title');
        $content = $this->input('Content');
        $status = $this->input('Status', 'Published');
        if (!in_array($status, ['Published', 'Draft'], true)) {
            $status = 'Published';
        }

        if ($title === '' || $content === '') {
            die('Vui lòng nhập tiêu đề và nội dung bài viết.');
        }

        $slug = uniqueBlogSlug($this->db(), $title);
        $thumbnail = uploadBlogThumbnail('Thumbnail');

        $this->blogModel->create([
            'Title' => $title,
            'Slug' => $slug,
            'Content' => $content,
            'Thumbnail' => $thumbnail,
            'AuthorID' => Auth::userId(),
            'Status' => $status,
        ]);

        $this->redirect('/Carrental/admin/blogs?success=1');
    }

    public function edit(string $id): void
    {
        $blog = $this->blogModel->find((int) $id);
        if (!$blog) {
            $this->redirect('/Carrental/admin/blogs');
        }

        $this->view('admin/blogs/edit', [
            'pageTitle' => 'Sửa bài viết',
            'blog' => $blog,
        ]);
    }

    public function update(string $id): void
    {
        $blogId = (int) $id;
        $title = $this->input('Title');
        $content = $this->input('Content');
        $status = $this->input('Status', 'Published');
        if (!in_array($status, ['Published', 'Draft'], true)) {
            $status = 'Published';
        }
        $currentThumbnail = $this->input('CurrentThumbnail');

        if ($title === '' || $content === '') {
            die('Dữ liệu bài viết không hợp lệ.');
        }

        $slug = uniqueBlogSlug($this->db(), $title, $blogId);
        $uploaded = uploadBlogThumbnail('Thumbnail');
        $thumbnail = $uploaded !== '' ? $uploaded : $currentThumbnail;

        $this->blogModel->update($blogId, [
            'Title' => $title,
            'Slug' => $slug,
            'Content' => $content,
            'Thumbnail' => $thumbnail,
            'Status' => $status,
        ]);

        $this->redirect('/Carrental/admin/blogs?success=1');
    }

    public function destroy(string $id): void
    {
        $this->blogModel->delete((int) $id);
        $this->redirect('/Carrental/admin/blogs?success=1');
    }

    private function db(): \mysqli
    {
        return \App\Core\Database::connection();
    }
}
