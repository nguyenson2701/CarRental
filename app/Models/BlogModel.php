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

    /**
     * Cac bai viet da xuat ban, moi nhat truoc, kem ten tac gia. Dung cho
     * trang chu va trang danh sach blog Frontend.
     */
    public function publishedLatest(int $limit = 3): array
    {
        $stmt = $this->db->prepare("
            SELECT b.BlogID, b.Title, b.Slug, b.Content, b.Thumbnail, b.CreatedAt, u.FullName
            FROM blogs b
            LEFT JOIN users u ON b.AuthorID = u.UserID
            WHERE b.Status = 'Published'
            ORDER BY b.CreatedAt DESC, b.BlogID DESC
            LIMIT ?
        ");
        $stmt->bind_param('i', $limit);
        $stmt->execute();
        return $stmt->get_result()->fetch_all(MYSQLI_ASSOC);
    }

    public function findPublishedBySlug(string $slug): ?array
    {
        $stmt = $this->db->prepare("
            SELECT b.*, u.FullName
            FROM blogs b
            LEFT JOIN users u ON b.AuthorID = u.UserID
            WHERE b.Slug = ? AND b.Status = 'Published'
            LIMIT 1
        ");
        $stmt->bind_param('s', $slug);
        $stmt->execute();
        $row = $stmt->get_result()->fetch_assoc();
        return $row ?: null;
    }

    public function relatedPublished(int $excludeId, int $limit = 3): array
    {
        $stmt = $this->db->prepare("
            SELECT Title, Slug, Thumbnail, CreatedAt
            FROM blogs
            WHERE Status = 'Published' AND BlogID <> ?
            ORDER BY CreatedAt DESC, BlogID DESC
            LIMIT ?
        ");
        $stmt->bind_param('ii', $excludeId, $limit);
        $stmt->execute();
        return $stmt->get_result()->fetch_all(MYSQLI_ASSOC);
    }
}
