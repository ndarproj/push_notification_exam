<?php

namespace App\Services;

use PDO;

abstract class BaseModelService
{
    protected PDO $pdo;
    protected $model;

    public function __construct(string $model)
    {
        $this->pdo = DatabaseManagerService::getConnection();
        $this->model = new $model($this->pdo);
    }
}
