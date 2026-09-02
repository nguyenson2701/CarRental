<?php

namespace App\Models;

use App\Core\Model;

class CarImageModel extends Model
{
    protected string $table = 'CarImages';
    protected string $primaryKey = 'ImageID';

    public function forCar(int $carId, ?int $limit = null): array
    {
        $sql = "SELECT ImageID, ImageURL, IsMain, ImageType FROM CarImages
                WHERE CarID = ? ORDER BY IsMain DESC, ImageID ASC";
        if ($limit !== null) {
            $sql .= ' LIMIT ' . (int) $limit;
        }
        $stmt = $this->db->prepare($sql);
        $stmt->bind_param('i', $carId);
        $stmt->execute();
        return $stmt->get_result()->fetch_all(MYSQLI_ASSOC);
    }

    /**
     * Tim 1 anh, dam bao anh do thuoc dung xe $carId (chong sua/xoa cheo xe
     * bang cach truyen ID xe khac trong form).
     */
    public function findForCar(int $imageId, int $carId): ?array
    {
        $stmt = $this->db->prepare(
            'SELECT * FROM CarImages WHERE ImageID = ? AND CarID = ? LIMIT 1'
        );
        $stmt->bind_param('ii', $imageId, $carId);
        $stmt->execute();
        $row = $stmt->get_result()->fetch_assoc();
        return $row ?: null;
    }

    public function updateType(int $imageId, int $carId, string $imageType): void
    {
        $stmt = $this->db->prepare(
            'UPDATE CarImages SET ImageType = ? WHERE ImageID = ? AND CarID = ?'
        );
        $stmt->bind_param('sii', $imageType, $imageId, $carId);
        $stmt->execute();
    }

    public function clearMain(int $carId): void
    {
        $stmt = $this->db->prepare('UPDATE CarImages SET IsMain = 0 WHERE CarID = ?');
        $stmt->bind_param('i', $carId);
        $stmt->execute();
    }

    public function setMain(int $imageId, int $carId): void
    {
        $stmt = $this->db->prepare('UPDATE CarImages SET IsMain = 1 WHERE ImageID = ? AND CarID = ?');
        $stmt->bind_param('ii', $imageId, $carId);
        $stmt->execute();
    }

    public function deleteForCar(int $imageId, int $carId): void
    {
        $stmt = $this->db->prepare('DELETE FROM CarImages WHERE ImageID = ? AND CarID = ?');
        $stmt->bind_param('ii', $imageId, $carId);
        $stmt->execute();
    }

    /**
     * Anh cu nhat con lai cua 1 xe (dung de chon anh chinh moi sau khi xoa
     * anh chinh cu).
     */
    public function oldestForCar(int $carId): ?array
    {
        $stmt = $this->db->prepare(
            'SELECT ImageID, ImageURL FROM CarImages WHERE CarID = ? ORDER BY ImageID ASC LIMIT 1'
        );
        $stmt->bind_param('i', $carId);
        $stmt->execute();
        $row = $stmt->get_result()->fetch_assoc();
        return $row ?: null;
    }
}
