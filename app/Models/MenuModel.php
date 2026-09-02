<?php

namespace App\Models;

use App\Core\Model;

class MenuModel extends Model
{
    protected string $table = 'menus';
    protected string $primaryKey = 'MenuID';

    /**
     * Danh sach menu kem ten menu cha (self JOIN), giu dung truy van cua
     * menus/list.php cu.
     */
    public function allWithParentName(): array
    {
        $sql = "SELECT m.*, p.MenuName AS ParentName
                FROM menus m
                LEFT JOIN menus p ON m.ParentID = p.MenuID
                ORDER BY m.DisplayOrder ASC, m.MenuID ASC";
        $result = $this->db->query($sql);
        return $result ? $result->fetch_all(MYSQLI_ASSOC) : [];
    }

    /**
     * Danh sach menu de chon lam "menu cha", loai tru chinh no (dung khi sua).
     */
    public function candidatesForParent(int $excludeId = 0): array
    {
        if ($excludeId > 0) {
            $stmt = $this->db->prepare(
                'SELECT MenuID, MenuName FROM menus WHERE MenuID <> ? ORDER BY DisplayOrder ASC, MenuName ASC'
            );
            $stmt->bind_param('i', $excludeId);
            $stmt->execute();
            return $stmt->get_result()->fetch_all(MYSQLI_ASSOC);
        }

        $result = $this->db->query('SELECT MenuID, MenuName FROM menus ORDER BY DisplayOrder ASC, MenuName ASC');
        return $result ? $result->fetch_all(MYSQLI_ASSOC) : [];
    }
}
