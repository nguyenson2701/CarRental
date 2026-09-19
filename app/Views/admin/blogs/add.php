<?php
use App\Core\Auth;
?>
<h1 class="h3 mb-4 text-gray-800">Thêm bài viết</h1>

<div class="card shadow">
    <div class="card-body">
        <form action="/Carrental/admin/blogs" method="POST" enctype="multipart/form-data">
            <?= Auth::csrfField() ?>
            <div class="row">
                <div class="col-md-9 form-group">
                    <label>Tiêu đề</label>
                    <input type="text" name="Title" class="form-control" required>
                </div>
                <div class="col-md-3 form-group">
                    <label>Trạng thái</label>
                    <select name="Status" class="form-control">
                        <option value="Published">Đang hiển thị</option>
                        <option value="Draft">Bản nháp</option>
                    </select>
                </div>
                <div class="col-md-12 form-group">
                    <label>Ảnh đại diện</label>
                    <input type="file" name="Thumbnail" class="form-control-file" accept="image/*">
                </div>
                <div class="col-md-12 form-group">
                    <label>Nội dung</label>
                    <textarea name="Content" class="form-control" rows="12" required></textarea>
                </div>
            </div>

            <button type="submit" class="btn btn-primary">Lưu bài viết</button>
            <a href="/Carrental/admin/blogs" class="btn btn-secondary">Quay lại</a>
        </form>
    </div>
</div>
