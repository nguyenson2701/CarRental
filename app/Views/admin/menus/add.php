<?php
/** @var array $parents */
use App\Core\Auth;
?>
<h1 class="h3 mb-4 text-gray-800">Thêm menu</h1>

<div class="card shadow">
    <div class="card-body">
        <form action="/Carrental/admin/menus" method="POST">
            <?= Auth::csrfField() ?>
            <div class="form-group">
                <label>Tên menu</label>
                <input type="text" name="MenuName" class="form-control" required>
            </div>

            <div class="form-group">
                <label>URL</label>
                <input type="text" name="URL" class="form-control" required placeholder="VD: index.php">
            </div>

            <div class="form-group">
                <label>Menu cha</label>
                <select name="ParentID" class="form-control">
                    <option value="">Không có</option>
                    <?php foreach ($parents as $p): ?>
                        <option value="<?= (int) $p['MenuID'] ?>"><?= htmlspecialchars($p['MenuName']) ?></option>
                    <?php endforeach; ?>
                </select>
            </div>

            <div class="form-group">
                <label>Thứ tự hiển thị</label>
                <input type="number" name="DisplayOrder" class="form-control" value="0">
            </div>

            <div class="form-group">
                <label>Hiển thị</label>
                <select name="IsActive" class="form-control">
                    <option value="1">Hiện</option>
                    <option value="0">Ẩn</option>
                </select>
            </div>

            <button type="submit" class="btn btn-primary">Lưu</button>
            <a href="/Carrental/admin/menus" class="btn btn-secondary">Quay lại</a>
        </form>
    </div>
</div>
