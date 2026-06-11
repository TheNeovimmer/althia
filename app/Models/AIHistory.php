<?php
namespace App\Models;

use App\Core\Model;

class AIHistory extends Model
{
    protected static string $table = 'ai_conversations';
    protected static array $fillable = ['user_id', 'title', 'is_active'];

    public static function getUserConversations(int $userId): array
    {
        return self::query(
            "SELECT * FROM ai_conversations WHERE user_id = ? ORDER BY updated_at DESC",
            [$userId]
        );
    }

    public static function getMessages(int $conversationId): array
    {
        $db = \App\Core\Database::getInstance();
        return $db->fetchAll(
            "SELECT * FROM ai_messages WHERE conversation_id = ? ORDER BY created_at ASC",
            [$conversationId]
        );
    }

    public static function addMessage(int $conversationId, string $role, string $content): int
    {
        $db = \App\Core\Database::getInstance();
        return $db->insert(
            "INSERT INTO ai_messages (conversation_id, role, content) VALUES (?, ?, ?)",
            [$conversationId, $role, $content]
        );
    }
}
