<?php

namespace App\Models;

use PDO;

abstract class BaseModel
{
    protected PDO $pdo;
    protected string $table;

    public function __construct(PDO $pdo, string $table)
    {
        $this->pdo = $pdo;
        $this->table = $table;
    }

    public function getAll(?array $conditions = null): array
    {
        $sql = "SELECT * FROM {$this->table}";

        if ($conditions) {
            $whereClauses = array_map(function ($column) {
                return "$column = :$column";
            }, array_keys($conditions));

            $sql .= " WHERE " . implode(' AND ', $whereClauses);
        }

        $stmt = $this->pdo->prepare($sql);

        if ($conditions) {
            foreach ($conditions as $column => &$value) {
                $stmt->bindParam(":$column", $value);
            }
        }

        $stmt->execute();

        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }

    public function find(int $id): ?array
    {
        $sql = "SELECT * FROM {$this->table} WHERE id = :id";
        $stmt = $this->pdo->prepare($sql);
        $stmt->bindParam(':id', $id, PDO::PARAM_INT);
        $stmt->execute();

        return $stmt->fetch(PDO::FETCH_ASSOC) ?: null;
    }

    public function findIds(array $ids): array
    {
        if (empty($ids)) {
            return [];
        }

        $placeholders = implode(',', array_fill(0, count($ids), '?'));

        $sql = "SELECT * FROM {$this->table} WHERE id IN ($placeholders)";
        $stmt = $this->pdo->prepare($sql);

        foreach ($ids as $index => $id) {
            $stmt->bindValue($index + 1, $id, PDO::PARAM_INT);
        }

        $stmt->execute();

        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }

    public function create(array $data): int
    {
        $columns = implode(', ', array_keys($data));
        $placeholders = ':' . implode(', :', array_keys($data));

        $sql = "INSERT INTO {$this->table} ($columns) VALUES ($placeholders)";
        $stmt = $this->pdo->prepare($sql);

        foreach ($data as $key => &$value) {
            $stmt->bindParam(":$key", $value);
        }

        $stmt->execute();
        return $this->pdo->lastInsertId();
    }

    public function update(int $id, array $data): bool
    {
        $setParts = [];
        foreach ($data as $column => $value) {
            $setParts[] = "$column = :$column";
        }
        $setClause = implode(', ', $setParts);

        $sql = "UPDATE {$this->table} SET $setClause WHERE id = :id";
        $stmt = $this->pdo->prepare($sql);

        foreach ($data as $column => &$value) {
            $stmt->bindParam(":$column", $value);
        }
        $stmt->bindParam(':id', $id, PDO::PARAM_INT);

        return $stmt->execute();
    }

    public function updateMultiple(array $ids, array $data): bool
    {
        if (empty($ids)) {
            return false;
        }

        $setParts = [];
        foreach ($data as $column => $value) {
            $setParts[] = "$column = :$column";
        }
        $setClause = implode(', ', $setParts);

        $idPlaceholders = [];
        foreach ($ids as $index => $id) {
            $idPlaceholders[] = ":id$index";
        }
        $inQuery = implode(', ', $idPlaceholders);

        $sql = "UPDATE {$this->table} SET $setClause WHERE id IN ($inQuery)";
        $stmt = $this->pdo->prepare($sql);

        foreach ($data as $column => $value) {
            $stmt->bindParam(":$column", $data[$column]);
        }

        foreach ($ids as $index => $id) {
            $stmt->bindParam(":id$index", $ids[$index], PDO::PARAM_INT);
        }

        return $stmt->execute();
    }
}
