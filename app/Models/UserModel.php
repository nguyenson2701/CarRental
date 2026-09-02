<?php

namespace App\Models;

use App\Core\Model;

class UserModel extends Model
{
    protected string $table = 'users';
    protected string $primaryKey = 'UserID';

    /**
     * Tim nguoi dung theo ten/email/dien thoai, giu dung hanh vi loc cua
     * users/list.php cu. Tra ve tat ca neu $keyword rong.
     */
    public function search(string $keyword): array
    {
        if ($keyword === '') {
            return $this->all('UserID DESC');
        }

        $stmt = $this->db->prepare(
            'SELECT * FROM users WHERE FullName LIKE ? OR Email LIKE ? OR Phone LIKE ? ORDER BY UserID DESC'
        );
        $search = "%$keyword%";
        $stmt->bind_param('sss', $search, $search, $search);
        $stmt->execute();
        return $stmt->get_result()->fetch_all(MYSQLI_ASSOC);
    }
}
