<?php
namespace App\Models;

use App\Core\Model;

class Service extends Model
{
    protected static string $table = 'services';
    protected static array $fillable = ['name', 'description', 'icon', 'is_active'];

    public static function getActive(): array
    {
        return self::where('is_active', 1);
    }
}
