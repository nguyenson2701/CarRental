<?php

namespace App\Models;

use App\Core\Model;

class BlogModel extends Model
{
    protected string $table = 'blogs';
    protected string $primaryKey = 'BlogID';

    /**
     * Danh sach blog kem ten tac gia (JOIN users), giu dung truy van cua
     * blogs/list.php cu.
     */
    public function allWithAuthor(): array
    {
        $sql = "SELECT b.BlogID, b.Title, b.Slug, b.Thumbnail, b.Status, b.CreatedAt, b.UpdatedAt, u.FullName
                FROM blogs b
                LEFT JOIN users u ON b.AuthorID = u.UserID
                ORDER BY b.CreatedAt DESC, b.BlogID DESC";
        $result = $this->db->query($sql);
        return $result ? $result->fetch_all(MYSQLI_ASSOC) : [];
    }
}
