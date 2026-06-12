<?php
namespace App\Models;

use App\Core\Database;

class Setting
{
    public static function get(string $key, string $default = ''): string
    {
        try {
            $db = Database::getInstance();
            $row = $db->fetch("SELECT value FROM settings WHERE key_name = ?", [$key]);
            return $row['value'] ?? $default;
        } catch (\PDOException $e) {
            return $default;
        }
    }

    public static function set(string $key, string $value): void
    {
        try {
            $db = Database::getInstance();
            $existing = $db->fetch("SELECT id FROM settings WHERE key_name = ?", [$key]);
            if ($existing) {
                $db->execute("UPDATE settings SET value = ? WHERE key_name = ?", [$value, $key]);
            } else {
                $db->insert("INSERT INTO settings (key_name, value) VALUES (?, ?)", [$key, $value]);
            }
        } catch (\PDOException $e) {
            // Table may not exist yet
        }
    }

    public static function getAll(): array
    {
        try {
            $db = Database::getInstance();
            return $db->fetchAll("SELECT * FROM settings ORDER BY key_name ASC");
        } catch (\PDOException $e) {
            return [];
        }
    }
}
