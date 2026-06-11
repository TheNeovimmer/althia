<?php
namespace App\Models;

use App\Core\Model;

class Notification extends Model
{
    protected static string $table = 'notifications';
    protected static array $fillable = ['user_id', 'type', 'title', 'message', 'link', 'is_read'];

    public static function getForUser(int $userId, int $limit = 10): array
    {
        return self::query(
            "SELECT * FROM notifications WHERE user_id = ? ORDER BY created_at DESC LIMIT ?",
            [$userId, $limit]
        );
    }

    public static function getUnreadForUser(int $userId): array
    {
        return self::query(
            "SELECT * FROM notifications WHERE user_id = ? AND is_read = 0 ORDER BY created_at DESC",
            [$userId]
        );
    }

    public static function countUnread(int $userId): int
    {
        return self::countWhere('user_id', $userId); // simplified
    }

    public static function markAllAsRead(int $userId): int
    {
        $db = \App\Core\Database::getInstance();
        return $db->execute("UPDATE notifications SET is_read = 1 WHERE user_id = ?", [$userId]);
    }

    public static function send(int $userId, string $type, string $title, string $message, ?string $link = null): int
    {
        return self::create([
            'user_id' => $userId,
            'type' => $type,
            'title' => $title,
            'message' => $message,
            'link' => $link,
        ]);
    }
}
