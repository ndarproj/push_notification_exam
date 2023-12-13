<?php


namespace App\Models;

use PDO;

class Device extends BaseModel
{
    public function __construct(PDO $pdo)
    {
        parent::__construct($pdo, 'devices');
    }
}
