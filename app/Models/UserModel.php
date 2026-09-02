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

    public function findByEmail(string $email): ?array
    {
        $stmt = $this->db->prepare('SELECT * FROM users WHERE Email = ? LIMIT 1');
        $stmt->bind_param('s', $email);
        $stmt->execute();
        $row = $stmt->get_result()->fetch_assoc();
        return $row ?: null;
    }

    public function existsByEmailOrPhone(string $email, string $phone): bool
    {
        $stmt = $this->db->prepare('SELECT UserID FROM users WHERE Email = ? OR Phone = ? LIMIT 1');
        $stmt->bind_param('ss', $email, $phone);
        $stmt->execute();
        return $stmt->get_result()->num_rows > 0;
    }

    public function updatePasswordHash(int $userId, string $newHash): void
    {
        $stmt = $this->db->prepare('UPDATE users SET PasswordHash = ? WHERE UserID = ?');
        $stmt->bind_param('si', $newHash, $userId);
        $stmt->execute();
    }
}
