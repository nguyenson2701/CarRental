<?php
/** @var array $brand */
use App\Core\Auth;
?>
<h1 class="h3 mb-4 text-gray-800">Sửa hãng xe</h1>

<div class="card shadow mb-4">
    <div class="card-body">
        <form action="/Carrental/admin/brands/<?= (int) $brand['BrandID'] ?>/update" method="POST">
            <?= Auth::csrfField() ?>

            <div class="form-group">
                <label>Tên hãng xe</label>
                <input type="text" name="BrandName" class="form-control"
                       value="<?= htmlspecialchars($brand['BrandName']) ?>" required>
            </div>

            <button type="submit" class="btn btn-primary">Cập nhật</button>
            <a href="/Carrental/admin/brands" class="btn btn-secondary">Quay lại</a>
        </form>
    </div>
</div>
