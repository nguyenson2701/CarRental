<?php

namespace App\Models;

use App\Core\Database;

/**
 * Cac truy van rieng cho trang Dashboard - khong ke thua Model co so vi
 * hau het la truy van tong hop nhieu bang (COUNT/SUM/JOIN), khong phai
 * CRUD tren 1 bang duy nhat.
 */
class DashboardModel
{
    private \mysqli $db;

    public function __construct()
    {
        $this->db = Database::connection();
    }

    public function countTable(string $table): int
    {
        $result = $this->db->query("SELECT COUNT(*) AS total FROM `$table`");
        if (!$result) {
            return 0;
        }
        return (int) $result->fetch_assoc()['total'];
    }

    public function pendingBookingsCount(): int
    {
        return $this->scalarInt("SELECT COUNT(*) FROM bookings WHERE Status = 'Pending'");
    }

    public function pendingReturnsCount(): int
    {
        return $this->scalarInt("SELECT COUNT(*) FROM bookings WHERE ReturnStatus = 'Pending'");
    }

    public function pendingFinalPaymentsCount(): int
    {
        return $this->scalarInt("
            SELECT COUNT(*)
            FROM bookings b
            INNER JOIN payments p ON p.BookingID = b.BookingID
            WHERE b.ReturnStatus = 'Approved'
              AND p.PaymentType = 'Final'
              AND p.Status = 'Pending'
        ");
    }

    public function maintenanceCarsCount(): int
    {
        return $this->scalarInt("SELECT COUNT(*) FROM cars WHERE Status = 'Maintenance'");
    }

    public function recentCars(int $limit = 5): array
    {
        $stmt = $this->db->prepare("
            SELECT CarID, CarName, LicensePlate, Color, Seats, PricePerDay, Status, MainImage
            FROM cars ORDER BY CarID DESC LIMIT ?
        ");
        $stmt->bind_param('i', $limit);
        $stmt->execute();
        return $stmt->get_result()->fetch_all(MYSQLI_ASSOC);
    }

    public function recentBookings(int $limit = 5): array
    {
        $stmt = $this->db->prepare("
            SELECT b.BookingID, b.StartDate, b.EndDate, b.RentalDays, b.TotalPrice, b.Status, b.CreatedAt,
                   u.FullName, u.Email, u.Phone, c.CarName, c.LicensePlate
            FROM bookings b
            LEFT JOIN users u ON b.UserID = u.UserID
            LEFT JOIN cars c ON b.CarID = c.CarID
            ORDER BY b.BookingID DESC LIMIT ?
        ");
        $stmt->bind_param('i', $limit);
        $stmt->execute();
        return $stmt->get_result()->fetch_all(MYSQLI_ASSOC);
    }

    public function recentUsers(int $limit = 5): array
    {
        $stmt = $this->db->prepare("
            SELECT UserID, Avatar, FullName, Email, Phone, RoleID, Status
            FROM users ORDER BY UserID DESC LIMIT ?
        ");
        $stmt->bind_param('i', $limit);
        $stmt->execute();
        return $stmt->get_result()->fetch_all(MYSQLI_ASSOC);
    }

    /**
     * Doanh thu theo ky (today/month/year, hoac 1 ngay cu the neu $date co
     * gia tri) + tim kiem theo ma don/khach hang/sdt/xe/bien so. Giu dung
     * hanh vi loc cua CarRental_Admin/index.php cu, nhung dung prepared
     * statement thay vi noi chuoi SQL truc tiep.
     *
     * @return array{total: float, count: int, label: string, rows: array}
     */
    public function revenueSummary(string $period, string $date, string $search): array
    {
        $paidStatus = "b.Status IN ('Paid', 'Completed')";

        $periodSql = 'YEAR(b.CreatedAt) = YEAR(CURRENT_DATE())';
        $label = 'Năm nay';
        $periodParams = [];
        $periodTypes = '';

        if ($date !== '') {
            $periodSql = 'DATE(b.CreatedAt) = ?';
            $periodTypes = 's';
            $periodParams = [$date];
            $label = 'Ngày ' . date('d/m/Y', strtotime($date));
        } elseif ($period === 'today') {
            $periodSql = 'DATE(b.CreatedAt) = CURRENT_DATE()';
            $label = 'Hôm nay';
        } elseif ($period === 'month') {
            $periodSql = 'MONTH(b.CreatedAt) = MONTH(CURRENT_DATE()) AND YEAR(b.CreatedAt) = YEAR(CURRENT_DATE())';
            $label = 'Tháng này';
        }

        $searchSql = '';
        $searchParams = [];
        $searchTypes = '';
        if ($search !== '') {
            $searchSql = "AND (
                CAST(b.BookingID AS CHAR) LIKE ?
                OR u.FullName LIKE ?
                OR u.Phone LIKE ?
                OR c.CarName LIKE ?
                OR c.LicensePlate LIKE ?
            )";
            $like = '%' . $search . '%';
            $searchParams = [$like, $like, $like, $like, $like];
            $searchTypes = 'sssss';
        }

        $baseFrom = "
            FROM bookings b
            LEFT JOIN users u ON b.UserID = u.UserID
            LEFT JOIN cars c ON b.CarID = c.CarID
            WHERE $paidStatus AND $periodSql $searchSql
        ";

        $types = $periodTypes . $searchTypes;
        $params = array_merge($periodParams, $searchParams);

        $totalStmt = $this->db->prepare("SELECT COALESCE(SUM(b.TotalPrice), 0) AS total $baseFrom");
        if ($types !== '') {
            $totalStmt->bind_param($types, ...$params);
        }
        $totalStmt->execute();
        $total = (float) $totalStmt->get_result()->fetch_assoc()['total'];

        $countStmt = $this->db->prepare("SELECT COUNT(*) AS cnt $baseFrom");
        if ($types !== '') {
            $countStmt->bind_param($types, ...$params);
        }
        $countStmt->execute();
        $count = (int) $countStmt->get_result()->fetch_assoc()['cnt'];

        $rowsStmt = $this->db->prepare("
            SELECT b.BookingID, b.TotalPrice, b.Status, b.CreatedAt, u.FullName, u.Phone, c.CarName, c.LicensePlate
            $baseFrom
            ORDER BY b.CreatedAt DESC, b.BookingID DESC
            LIMIT 20
        ");
        if ($types !== '') {
            $rowsStmt->bind_param($types, ...$params);
        }
        $rowsStmt->execute();
        $rows = $rowsStmt->get_result()->fetch_all(MYSQLI_ASSOC);

        return ['total' => $total, 'count' => $count, 'label' => $label, 'rows' => $rows];
    }

    private function scalarInt(string $sql): int
    {
        $result = $this->db->query($sql);
        if (!$result) {
            return 0;
        }
        $row = $result->fetch_assoc();
        return $row ? (int) array_values($row)[0] : 0;
    }
}
