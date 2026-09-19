<?php

namespace App\Models;

use App\Core\Model;

class PaymentModel extends Model
{
    protected string $table = 'payments';
    protected string $primaryKey = 'PaymentID';

    /**
     * Danh sach thanh toan cua 1 khach hang (JOIN bookings+cars), giu dung
     * truy van cua my-payments.php cu.
     */
    public function forCustomer(int $userId): array
    {
        $stmt = $this->db->prepare("
            SELECT
                p.PaymentID, p.BookingID, p.Amount, p.PaymentMethod, p.PaymentType,
                p.TransactionCode, p.PaymentDate, p.Status, p.Note,
                b.PenaltyReason, b.TotalPrice, b.DepositAmount, b.OvertimeFee, b.DamageFee,
                b.CleaningFee, b.OtherFee, b.TotalPenalty,
                c.CarName, c.LicensePlate
            FROM payments p
            INNER JOIN bookings b ON p.BookingID = b.BookingID
            INNER JOIN cars c ON b.CarID = c.CarID
            WHERE b.UserID = ?
            ORDER BY p.PaymentID DESC
        ");
        $stmt->bind_param('i', $userId);
        $stmt->execute();
        return $stmt->get_result()->fetch_all(MYSQLI_ASSOC);
    }

    /**
     * Xac nhan da thu tien coc/tien thue (PaymentType Deposit/Rental). Giu
     * dung logic cua api/admin/payments/confirm.php cu: cap nhat thanh
     * toan, chuyen trang thai don (Confirmed neu la Deposit, Paid neu la
     * Rental), danh dau xe la Booked.
     *
     * @return array{bookingId: int, alreadyPaid: bool}
     * @throws \RuntimeException neu du lieu khong hop le / khong xac nhan duoc
     */
    public function confirmInitial(int $paymentId, string $paymentMethod): array
    {
        $allowedMethods = ['Cash', 'BankTransfer'];
        if ($paymentId <= 0 || !in_array($paymentMethod, $allowedMethods, true)) {
            throw new \RuntimeException('Dữ liệu thanh toán không hợp lệ.');
        }

        $stmt = $this->db->prepare("
            SELECT p.PaymentID, p.BookingID, p.Status AS PaymentStatus, p.PaymentType,
                   b.Status AS BookingStatus, b.CarID
            FROM payments p
            INNER JOIN bookings b ON p.BookingID = b.BookingID
            WHERE p.PaymentID = ? AND p.PaymentType IN ('Deposit', 'Rental')
            LIMIT 1
        ");
        $stmt->bind_param('i', $paymentId);
        $stmt->execute();
        $payment = $stmt->get_result()->fetch_assoc();

        if (!$payment) {
            throw new \RuntimeException('Không tìm thấy khoản thanh toán.');
        }

        $bookingId = (int) $payment['BookingID'];

        if ($payment['PaymentStatus'] === 'Paid') {
            return ['bookingId' => $bookingId, 'alreadyPaid' => true];
        }

        if ($payment['PaymentStatus'] !== 'Pending') {
            throw new \RuntimeException('Khoản thanh toán này chưa đủ điều kiện xác nhận.');
        }

        if (in_array($payment['BookingStatus'], ['Cancelled', 'Completed'], true)) {
            throw new \RuntimeException('Đơn này không còn đủ điều kiện xác nhận thanh toán.');
        }

        $transactionCode = 'ADM' . time() . rand(1000, 9999);
        $carId = (int) $payment['CarID'];

        $this->db->begin_transaction();
        try {
            $stmtUpdate = $this->db->prepare("
                UPDATE payments SET PaymentMethod = ?, TransactionCode = ?, PaymentDate = NOW(), Status = 'Paid'
                WHERE PaymentID = ? AND Status = 'Pending' AND PaymentType IN ('Deposit', 'Rental')
            ");
            $stmtUpdate->bind_param('ssi', $paymentMethod, $transactionCode, $paymentId);
            if (!$stmtUpdate->execute() || $stmtUpdate->affected_rows <= 0) {
                throw new \RuntimeException('Không thể cập nhật thanh toán.');
            }

            $newBookingStatus = $payment['PaymentType'] === 'Deposit' ? 'Confirmed' : 'Paid';
            $stmtBooking = $this->db->prepare('UPDATE bookings SET Status = ?, UpdatedAt = NOW() WHERE BookingID = ?');
            $stmtBooking->bind_param('si', $newBookingStatus, $bookingId);
            $stmtBooking->execute();

            $stmtCar = $this->db->prepare("UPDATE cars SET Status = 'Booked', UpdatedAt = NOW() WHERE CarID = ?");
            $stmtCar->bind_param('i', $carId);
            $stmtCar->execute();

            $this->db->commit();
        } catch (\Throwable $e) {
            $this->db->rollback();
            throw new \RuntimeException($e->getMessage());
        }

        return ['bookingId' => $bookingId, 'alreadyPaid' => false];
    }

    /**
     * Xac nhan thanh toan cuoi (PaymentType Final). Giu dung logic cua
     * api/admin/payments/confirm_final.php cu: cap nhat thanh toan, hoan
     * tat don, tra xe ve Available.
     *
     * @return array{bookingId: int, alreadyPaid: bool}
     * @throws \RuntimeException neu du lieu khong hop le / khong xac nhan duoc
     */
    public function confirmFinal(int $paymentId, string $paymentMethod): array
    {
        $allowedMethods = ['Cash', 'BankTransfer'];
        if ($paymentId <= 0 || !in_array($paymentMethod, $allowedMethods, true)) {
            throw new \RuntimeException('Dữ liệu thanh toán không hợp lệ.');
        }

        $stmt = $this->db->prepare("
            SELECT p.PaymentID, p.BookingID, p.Status AS PaymentStatus, p.PaymentType,
                   b.Status AS BookingStatus, b.ReturnStatus, b.CarID
            FROM payments p
            INNER JOIN bookings b ON p.BookingID = b.BookingID
            WHERE p.PaymentID = ? AND p.PaymentType = 'Final'
            LIMIT 1
        ");
        $stmt->bind_param('i', $paymentId);
        $stmt->execute();
        $payment = $stmt->get_result()->fetch_assoc();

        if (!$payment) {
            throw new \RuntimeException('Không tìm thấy thanh toán cuối.');
        }

        $bookingId = (int) $payment['BookingID'];

        if ($payment['PaymentStatus'] === 'Paid') {
            return ['bookingId' => $bookingId, 'alreadyPaid' => true];
        }

        if ($payment['PaymentStatus'] !== 'Pending' || $payment['ReturnStatus'] !== 'Approved') {
            throw new \RuntimeException('Khoản thanh toán này chưa đủ điều kiện xác nhận.');
        }

        if (in_array($payment['BookingStatus'], ['Completed', 'Cancelled'], true)) {
            throw new \RuntimeException('Đơn này không còn đủ điều kiện xác nhận thanh toán.');
        }

        $transactionCode = 'ADM' . time() . rand(1000, 9999);
        $carId = (int) $payment['CarID'];

        $this->db->begin_transaction();
        try {
            $stmtUpdate = $this->db->prepare("
                UPDATE payments SET PaymentMethod = ?, TransactionCode = ?, PaymentDate = NOW(), Status = 'Paid'
                WHERE PaymentID = ? AND Status = 'Pending' AND PaymentType = 'Final'
            ");
            $stmtUpdate->bind_param('ssi', $paymentMethod, $transactionCode, $paymentId);
            if (!$stmtUpdate->execute() || $stmtUpdate->affected_rows <= 0) {
                throw new \RuntimeException('Không thể cập nhật thanh toán cuối.');
            }

            $stmtBooking = $this->db->prepare("UPDATE bookings SET Status = 'Completed', UpdatedAt = NOW() WHERE BookingID = ?");
            $stmtBooking->bind_param('i', $bookingId);
            $stmtBooking->execute();

            $stmtCar = $this->db->prepare("UPDATE cars SET Status = 'Available', UpdatedAt = NOW() WHERE CarID = ?");
            $stmtCar->bind_param('i', $carId);
            $stmtCar->execute();

            $this->db->commit();
        } catch (\Throwable $e) {
            $this->db->rollback();
            throw new \RuntimeException($e->getMessage());
        }

        return ['bookingId' => $bookingId, 'alreadyPaid' => false];
    }
}
