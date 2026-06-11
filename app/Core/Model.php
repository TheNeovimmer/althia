<?php
namespace App\Core;

abstract class Model
{
    protected static string $table;
    protected static string $primaryKey = 'id';
    protected static array $fillable = [];

    public static function all(): array
    {
        $db = Database::getInstance();
        return $db->fetchAll("SELECT * FROM " . static::$table . " ORDER BY " . static::$primaryKey . " DESC");
    }

    public static function find(int $id): ?array
    {
        $db = Database::getInstance();
        return $db->fetch(
            "SELECT * FROM " . static::$table . " WHERE " . static::$primaryKey . " = ?",
            [$id]
        );
    }

    public static function where(string $column, $value): array
    {
        $db = Database::getInstance();
        return $db->fetchAll(
            "SELECT * FROM " . static::$table . " WHERE {$column} = ?",
            [$value]
        );
    }

    public static function whereFirst(string $column, $value): ?array
    {
        $db = Database::getInstance();
        return $db->fetch(
            "SELECT * FROM " . static::$table . " WHERE {$column} = ?",
            [$value]
        );
    }

    public static function create(array $data): int
    {
        if (!empty(static::$fillable)) {
            $data = array_intersect_key($data, array_flip(static::$fillable));
        }
        $columns = implode(', ', array_keys($data));
        $placeholders = implode(', ', array_fill(0, count($data), '?'));
        $db = Database::getInstance();
        return $db->insert(
            "INSERT INTO " . static::$table . " ({$columns}) VALUES ({$placeholders})",
            array_values($data)
        );
    }

    public static function update(int $id, array $data): int
    {
        if (!empty(static::$fillable)) {
            $data = array_intersect_key($data, array_flip(static::$fillable));
        }
        $sets = implode(', ', array_map(fn($col) => "{$col} = ?", array_keys($data)));
        $db = Database::getInstance();
        return $db->execute(
            "UPDATE " . static::$table . " SET {$sets} WHERE " . static::$primaryKey . " = ?",
            array_merge(array_values($data), [$id])
        );
    }

    public static function delete(int $id): int
    {
        $db = Database::getInstance();
        return $db->execute(
            "DELETE FROM " . static::$table . " WHERE " . static::$primaryKey . " = ?",
            [$id]
        );
    }

    public static function count(): int
    {
        $db = Database::getInstance();
        return (int) $db->fetch("SELECT COUNT(*) as count FROM " . static::$table)['count'];
    }

    public static function countWhere(string $column, $value): int
    {
        $db = Database::getInstance();
        return (int) $db->fetch("SELECT COUNT(*) as count FROM " . static::$table . " WHERE {$column} = ?", [$value])['count'];
    }

    public static function paginate(int $page = 1, int $perPage = 10): array
    {
        $db = Database::getInstance();
        $offset = ($page - 1) * $perPage;
        $total = static::count();
        $items = $db->fetchAll(
            "SELECT * FROM " . static::$table . " ORDER BY " . static::$primaryKey . " DESC LIMIT ? OFFSET ?",
            [$perPage, $offset]
        );
        return [
            'items' => $items,
            'total' => $total,
            'page' => $page,
            'perPage' => $perPage,
            'lastPage' => max(1, ceil($total / $perPage)),
        ];
    }

    public static function query(string $sql, array $params = []): array
    {
        $db = Database::getInstance();
        return $db->fetchAll($sql, $params);
    }

    public static function queryFirst(string $sql, array $params = []): ?array
    {
        $db = Database::getInstance();
        return $db->fetch($sql, $params);
    }
}
