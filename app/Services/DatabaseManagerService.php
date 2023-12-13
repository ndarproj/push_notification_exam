<?php

namespace App\Services;

use PDO;
use PDOException;

class DatabaseManagerService
{
    private static ?PDO $connection = null;

    public static function getConnection(): PDO
    {
        if (self::$connection === null) {
            $host = config('DB_HOST');
            $db   = config('DB_NAME');
            $user = config('DB_USER_NAME');
            $pass = config('DB_PASSWORD');

            try {
                self::$connection = new PDO("mysql:host=$host;dbname=$db;charset=utf8mb4", $user, $pass);
                self::$connection->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
            } catch (PDOException $e) {
                throw new PDOException($e->getMessage(), (int)$e->getCode());
            }
        }

        return self::$connection;
    }
}
