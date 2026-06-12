<?php
namespace App\Models;

use App\Core\Model;

class RagDocument extends Model
{
    protected static string $table = 'rag_documents';
    protected static array $fillable = ['title', 'content', 'source', 'is_active'];

    public static function getActive(): array
    {
        try {
            return self::where('is_active', 1);
        } catch (\PDOException $e) {
            return [];
        }
    }

    public static function search(string $query): array
    {
        try {
            $db = \App\Core\Database::getInstance();
            return $db->fetchAll(
                "SELECT * FROM rag_documents WHERE is_active = 1 AND (title LIKE ? OR content LIKE ?) LIMIT 5",
                ["%{$query}%", "%{$query}%"]
            );
        } catch (\PDOException $e) {
            return [];
        }
    }
}
