<?php


namespace App\Models;

use Exception;
use PDO;

class PushNotification extends BaseModel
{
    public function __construct(PDO $pdo)
    {
        parent::__construct($pdo, 'push_notifications');
    }

    /**
     * @throws Exception
     */
    public static function send(string $title, string $message, string $token): bool
    {
        return random_int(1, 10) > 1;
    }
}
