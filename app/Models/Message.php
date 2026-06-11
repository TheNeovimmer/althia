<?php
namespace App\Models;

use App\Core\Model;

class Message extends Model
{
    protected static string $table = 'messages';
    protected static array $fillable = ['sender_id', 'receiver_id', 'subject', 'body', 'parent_id', 'is_read'];

    public static function getConversation(int $userId1, int $userId2): array
    {
        return self::query(
            "SELECT m.*,
                    u_s.first_name as sender_first_name, u_s.last_name as sender_last_name,
                    u_r.first_name as receiver_first_name, u_r.last_name as receiver_last_name
             FROM messages m
             JOIN users u_s ON m.sender_id = u_s.id
             JOIN users u_r ON m.receiver_id = u_r.id
             WHERE (m.sender_id = ? AND m.receiver_id = ?) OR (m.sender_id = ? AND m.receiver_id = ?)
             ORDER BY m.created_at ASC",
            [$userId1, $userId2, $userId2, $userId1]
        );
    }

    public static function getInbox(int $userId): array
    {
        return self::query(
            "SELECT m.*, u.first_name, u.last_name, u.avatar
             FROM messages m
             JOIN users u ON m.sender_id = u.id
             WHERE m.receiver_id = ?
             ORDER BY m.created_at DESC",
            [$userId]
        );
    }

    public static function getSent(int $userId): array
    {
        return self::query(
            "SELECT m.*, u.first_name, u.last_name, u.avatar
             FROM messages m
             JOIN users u ON m.receiver_id = u.id
             WHERE m.sender_id = ?
             ORDER BY m.created_at DESC",
            [$userId]
        );
    }

    public static function countUnread(int $userId): int
    {
        $db = \App\Core\Database::getInstance();
        return (int) $db->fetch("SELECT COUNT(*) as c FROM messages WHERE receiver_id = ? AND is_read = 0", [$userId])['c'];
    }
}
