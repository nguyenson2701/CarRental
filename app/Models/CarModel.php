<?php

namespace App\Models;

use App\Core\Model;

class CarModel extends Model
{
    protected string $table = 'Cars';
    protected string $primaryKey = 'CarID';

    /**
     * Tim xe theo tu khoa (ten xe, bien so, mau, trang thai), giu dung
     * hanh vi loc cua cars/list.php cu. Tra ve tat ca neu $keyword rong.
     */
    public function search(string $keyword): array
    {
        if ($keyword === '') {
            return $this->all('CarID DESC');
        }

        $stmt = $this->db->prepare(
            "SELECT * FROM `{$this->table}`
             WHERE CarName LIKE ? OR LicensePlate LIKE ? OR Color LIKE ? OR Status LIKE ?
             ORDER BY CarID DESC"
        );
        $search = "%$keyword%";
        $stmt->bind_param('ssss', $search, $search, $search, $search);
        $stmt->execute();
        return $stmt->get_result()->fetch_all(MYSQLI_ASSOC);
    }

    public function setFolderName(int $carId, string $folderName): void
    {
        $stmt = $this->db->prepare("UPDATE `{$this->table}` SET FolderName = ? WHERE CarID = ?");
        $stmt->bind_param('si', $folderName, $carId);
        $stmt->execute();
    }

    public function setMainImage(int $carId, string $imageUrl): void
    {
        $stmt = $this->db->prepare("UPDATE `{$this->table}` SET MainImage = ?, UpdatedAt = NOW() WHERE CarID = ?");
        $stmt->bind_param('si', $imageUrl, $carId);
        $stmt->execute();
    }
}
