<?php

namespace App\Models;

use App\Core\Model;

/**
 * Quan ly don dat xe. Nhieu truy van/giao dich phuc tap (JOIN nhieu bang,
 * transaction) nen dung truc tiep $this->db thay vi CRUD chung cua Model.
 */
class BookingModel extends Model
{
    protected string $table = 'bookings';
    protected string $primaryKey = 'BookingID';

    /**
     * Danh sach don kem khach hang/xe/tinh trang thanh toan, giu dung
     * truy van cua bookings/list.php cu.
     */
    public function listWithDetails(): array
    {
        $sql = "
            SELECT
                b.BookingID, b.UserID, b.CarID, b.StartDate, b.EndDate,
                b.PickupLocation, b.ReturnLocation, b.RentalDays, b.PricePerDay,
                b.DepositAmount, b.DiscountAmount, b.TotalPrice, b.TotalPenalty,
                b.Status, b.ReturnStatus, b.Note, b.CreatedAt,
                u.FullName, u.Email, u.Phone,
                c.CarName, c.LicensePlate,
                COALESCE(ps.TotalPaid, 0) AS TotalPaid,
                COALESCE(ps.TotalPending, 0) AS TotalPending,
                ip.PaymentID AS InitialPaymentID, ip.Amount AS InitialAmount,
                ip.PaymentType AS InitialPaymentType, ip.Status AS InitialPaymentStatus,
                fp.PaymentID AS FinalPaymentID, fp.Amount AS FinalAmount,
                fp.Status AS FinalPaymentStatus
            FROM bookings b
            LEFT JOIN users u ON b.UserID = u.UserID
            LEFT JOIN cars c ON b.CarID = c.CarID
            LEFT JOIN (
                SELECT BookingID,
                    SUM(CASE WHEN Status = 'Paid' THEN Amount ELSE 0 END) AS TotalPaid,
                    SUM(CASE WHEN Status = 'Pending' THEN Amount ELSE 0 END) AS TotalPending
                FROM payments GROUP BY BookingID
            ) ps ON ps.BookingID = b.BookingID
            LEFT JOIN payments ip ON ip.PaymentID = (
                SELECT p.PaymentID FROM payments p
                WHERE p.BookingID = b.BookingID AND p.PaymentType IN ('Deposit', 'Rental')
                ORDER BY CASE p.PaymentType WHEN 'Deposit' THEN 1 WHEN 'Rental' THEN 2 ELSE 3 END, p.PaymentID ASC
                LIMIT 1
            )
            LEFT JOIN payments fp ON fp.BookingID = b.BookingID AND fp.PaymentType = 'Final'
            ORDER BY b.BookingID DESC
        ";
        $result = $this->db->query($sql);
        return $result ? $result->fetch_all(MYSQLI_ASSOC) : [];
    }

    /**
     * 1 don kem day du thong tin khach/xe/thanh toan dau-cuoi, giu dung
     * truy van cua bookings/detail.php cu.
     */
    public function findWithDetails(int $id): ?array
    {
        $stmt = $this->db->prepare("
            SELECT
                b.*, u.FullName, u.Email, u.Phone, u.Address,
                c.CarName, c.LicensePlate, c.MainImage, c.Transmission, c.FuelType, c.Seats, c.Color, c.Location,
                fp.PaymentID AS FinalPaymentID, fp.Amount AS FinalAmount, fp.Status AS FinalPaymentStatus,
                fp.PaymentMethod AS FinalPaymentMethod, fp.PaymentDate AS FinalPaymentDate, fp.TransactionCode AS FinalTransactionCode,
                ip.PaymentID AS InitialPaymentID, ip.Amount AS InitialAmount, ip.PaymentType AS InitialPaymentType,
                ip.Status AS InitialPaymentStatus, ip.PaymentMethod AS InitialPaymentMethod, ip.PaymentDate AS InitialPaymentDate
            FROM bookings b
            LEFT JOIN users u ON b.UserID = u.UserID
            LEFT JOIN cars c ON b.CarID = c.CarID
            LEFT JOIN payments fp ON fp.BookingID = b.BookingID AND fp.PaymentType = 'Final'
            LEFT JOIN payments ip ON ip.PaymentID = (
                SELECT p.PaymentID FROM payments p
                WHERE p.BookingID = b.BookingID AND p.PaymentType IN ('Deposit', 'Rental')
                ORDER BY CASE p.PaymentType WHEN 'Deposit' THEN 1 WHEN 'Rental' THEN 2 ELSE 3 END, p.PaymentID ASC
                LIMIT 1
            )
            WHERE b.BookingID = ?
            LIMIT 1
        ");
        $stmt->bind_param('i', $id);
        $stmt->execute();
        $row = $stmt->get_result()->fetch_assoc();
        return $row ?: null;
    }

    /**
     * 1 don kem khach/xe don gian, cho trang kiem tra tra xe.
     */
    public function findForReturnCheck(int $id): ?array
    {
        $stmt = $this->db->prepare("
            SELECT b.*, c.CarName, c.LicensePlate, u.FullName, u.Phone
            FROM bookings b
            LEFT JOIN cars c ON b.CarID = c.CarID
            LEFT JOIN users u ON b.UserID = u.UserID
            WHERE b.BookingID = ?
            LIMIT 1
        ");
        $stmt->bind_param('i', $id);
        $stmt->execute();
        $row = $stmt->get_result()->fetch_assoc();
        return $row ?: null;
    }

    public function paymentsForBooking(int $bookingId): array
    {
        $stmt = $this->db->prepare("
            SELECT PaymentID, Amount, PaymentMethod, PaymentType, TransactionCode, PaymentDate, Status, Note
            FROM payments WHERE BookingID = ?
            ORDER BY CASE PaymentType WHEN 'Deposit' THEN 1 WHEN 'Rental' THEN 2 WHEN 'Final' THEN 3 ELSE 4 END, PaymentID ASC
        ");
        $stmt->bind_param('i', $bookingId);
        $stmt->execute();
        return $stmt->get_result()->fetch_all(MYSQLI_ASSOC);
    }

    /**
     * Huy don (chi cho phep tu Pending/Confirmed). Giu dung logic transaction
     * cua api/admin/bookings/update.php cu: huy don, huy cac thanh toan con
     * Pending, tra xe ve Available neu khong con don nao khac dang giu xe.
     *
     * @throws \RuntimeException neu khong huy duoc
     */
    public function cancel(int $bookingId): void
    {
        $this->db->begin_transaction();

        try {
            $stmt = $this->db->prepare('SELECT BookingID, Status, CarID FROM bookings WHERE BookingID = ? FOR UPDATE');
            $stmt->bind_param('i', $bookingId);
            $stmt->execute();
            $booking = $stmt->get_result()->fetch_assoc();

            if (!$booking) {
                throw new \RuntimeException('Không tìm thấy đơn.');
            }

            if (!in_array($booking['Status'], ['Pending', 'Confirmed'], true)) {
                throw new \RuntimeException("Không thể hủy đơn ở trạng thái {$booking['Status']}.");
            }

            $carId = (int) $booking['CarID'];

            $stmtBooking = $this->db->prepare("
                UPDATE bookings
                SET Status = 'Cancelled',
                    ReturnStatus = CASE WHEN ReturnStatus = 'Pending' THEN 'NotReturned' ELSE ReturnStatus END,
                    UpdatedAt = NOW()
                WHERE BookingID = ?
            ");
            $stmtBooking->bind_param('i', $bookingId);
            if (!$stmtBooking->execute()) {
                throw new \RuntimeException('Không thể hủy đơn.');
            }

            $stmtPayments = $this->db->prepare("
                UPDATE payments
                SET Status = 'Cancelled', Note = CONCAT(COALESCE(NULLIF(Note, ''), 'Thanh toan'), ' - Don da huy')
                WHERE BookingID = ? AND Status = 'Pending'
            ");
            $stmtPayments->bind_param('i', $bookingId);
            $stmtPayments->execute();

            $stmtCar = $this->db->prepare("
                UPDATE cars c
                SET c.Status = 'Available', c.UpdatedAt = NOW()
                WHERE c.CarID = ? AND c.Status <> 'Maintenance'
                  AND NOT EXISTS (
                      SELECT 1 FROM bookings b
                      WHERE b.CarID = c.CarID AND b.BookingID <> ?
                        AND b.Status IN ('Pending', 'Confirmed', 'Paid') AND b.EndDate >= CURDATE()
                  )
            ");
            $stmtCar->bind_param('ii', $carId, $bookingId);
            $stmtCar->execute();

            $this->db->commit();
        } catch (\Throwable $e) {
            $this->db->rollback();
            throw new \RuntimeException($e->getMessage());
        }
    }

    /**
     * Duyet yeu cau tra xe: luu phi phat sinh, tao/cap nhat khoan thanh
     * toan cuoi (hoac hoan tat don ngay neu khong con gi phai thu). Giu
     * dung logic cua api/admin/bookings/return_approve.php cu.
     *
     * @throws \RuntimeException neu khong xu ly duoc
     */
    public function approveReturn(int $bookingId, float $damageFee, float $cleaningFee, float $otherFee, string $penaltyReason): void
    {
        $stmt = $this->db->prepare('SELECT BookingID, CarID, OvertimeFee, TotalPrice, DepositAmount FROM bookings WHERE BookingID = ? LIMIT 1');
        $stmt->bind_param('i', $bookingId);
        $stmt->execute();
        $booking = $stmt->get_result()->fetch_assoc();

        if (!$booking) {
            throw new \RuntimeException('Không tìm thấy đơn.');
        }

        $totalPenalty = (float) $booking['OvertimeFee'] + $damageFee + $cleaningFee + $otherFee;
        $remainingAmount = max(0, (float) $booking['TotalPrice'] - (float) $booking['DepositAmount']);
        $finalAmount = $remainingAmount + $totalPenalty;

        $this->db->begin_transaction();

        $stmtUpdate = $this->db->prepare("
            UPDATE bookings
            SET DamageFee = ?, CleaningFee = ?, OtherFee = ?, TotalPenalty = ?, PenaltyReason = ?,
                ReturnStatus = 'Approved', UpdatedAt = NOW()
            WHERE BookingID = ?
        ");
        $stmtUpdate->bind_param('ddddsi', $damageFee, $cleaningFee, $otherFee, $totalPenalty, $penaltyReason, $bookingId);
        if (!$stmtUpdate->execute()) {
            $this->db->rollback();
            throw new \RuntimeException('Không thể xác nhận trả xe.');
        }

        if ($finalAmount > 0) {
            $note = 'Thanh toán cuối: tiền còn lại ' . number_format($remainingAmount, 0, ',', '.') .
                ' VNĐ, tiền phạt ' . number_format($totalPenalty, 0, ',', '.') . ' VNĐ';
            if ($penaltyReason !== '') {
                $note .= '. Lý do phạt: ' . $penaltyReason;
            }

            $stmtExisting = $this->db->prepare("SELECT PaymentID FROM payments WHERE BookingID = ? AND PaymentType = 'Final' LIMIT 1");
            $stmtExisting->bind_param('i', $bookingId);
            $stmtExisting->execute();
            $existingFinal = $stmtExisting->get_result()->fetch_assoc();

            if ($existingFinal) {
                $stmtPay = $this->db->prepare("
                    UPDATE payments SET Amount = ?, Status = 'Pending', Note = ?, PaymentMethod = NULL, TransactionCode = '', PaymentDate = NULL
                    WHERE PaymentID = ?
                ");
                $stmtPay->bind_param('dsi', $finalAmount, $note, $existingFinal['PaymentID']);
            } else {
                $paymentType = 'Final';
                $status = 'Pending';
                $stmtPay = $this->db->prepare("
                    INSERT INTO payments (BookingID, Amount, PaymentMethod, PaymentType, TransactionCode, PaymentDate, Status, Note)
                    VALUES (?, ?, NULL, ?, '', NULL, ?, ?)
                ");
                $stmtPay->bind_param('idsss', $bookingId, $finalAmount, $paymentType, $status, $note);
            }

            if (!$stmtPay->execute()) {
                $this->db->rollback();
                throw new \RuntimeException('Không thể tạo thanh toán cuối.');
            }
        } else {
            $stmtComplete = $this->db->prepare("UPDATE bookings SET Status = 'Completed', UpdatedAt = NOW() WHERE BookingID = ?");
            $stmtComplete->bind_param('i', $bookingId);
            $stmtComplete->execute();

            $stmtCar = $this->db->prepare("UPDATE cars SET Status = 'Available', UpdatedAt = NOW() WHERE CarID = ?");
            $stmtCar->bind_param('i', $booking['CarID']);
            $stmtCar->execute();
        }

        $this->db->commit();
    }
}
