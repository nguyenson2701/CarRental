<?php

namespace App\Models;

use App\Core\Model;

class BrandModel extends Model
{
    protected string $table = 'Brands';
    protected string $primaryKey = 'BrandID';

    /**
     * Tim hang xe theo tu khoa ten (dung LIKE, giu dung hanh vi cua
     * brands/list.php cu). Tra ve tat ca neu $keyword rong.
     */
    public function search(string $keyword): array
    {
        if ($keyword === '') {
            return $this->all('BrandID DESC');
        }

        $stmt = $this->db->prepare(
            "SELECT * FROM `{$this->table}` WHERE BrandName LIKE ? ORDER BY BrandID DESC"
        );
        $search = "%$keyword%";
        $stmt->bind_param('s', $search);
        $stmt->execute();
        return $stmt->get_result()->fetch_all(MYSQLI_ASSOC);
    }
}
