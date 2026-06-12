<?php
namespace App\Models;

use App\Core\Model;

class Specialization extends Model
{
    protected static string $table = 'specializations';
    protected static array $fillable = ['name', 'description', 'icon'];

    public static function getAll(): array
    {
        try {
            return self::query("SELECT * FROM specializations ORDER BY name ASC");
        } catch (\PDOException $e) {
            return [];
        }
    }
}
