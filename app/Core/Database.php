<?php

namespace App\Core;

/**
 * Ket noi DB dung chung (singleton). Van dung mysqli - giu nguyen driver
 * voi toan bo code cu de giam rui ro, chi bao boc lai thanh 1 diem truy cap
 * duy nhat thay vi moi file tu require config/database.php rieng.
 */
class Database
{
    private static ?\mysqli $connection = null;

    public static function connection(): \mysqli
    {
        if (self::$connection === null) {
            $host = 'localhost';
            $username = 'root';
            $password = '';
            $database = 'carrentaldb';

            $conn = new \mysqli($host, $username, $password, $database);

            if ($conn->connect_error) {
                die('Ket noi that bai: ' . $conn->connect_error);
            }

            $conn->set_charset('utf8mb4');
            self::$connection = $conn;
        }

        return self::$connection;
    }
}
