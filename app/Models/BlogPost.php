<?php
namespace App\Models;

use App\Core\Model;

class BlogPost extends Model
{
    protected static string $table = 'blog_posts';
    protected static array $fillable = ['author_id', 'category_id', 'title', 'slug', 'excerpt', 'content', 'featured_image', 'tags', 'is_published', 'published_at'];

    public static function getPublished(): array
    {
        return self::query(
            "SELECT bp.*, u.first_name, u.last_name, bc.name as category_name, bc.slug as category_slug
             FROM blog_posts bp
             JOIN users u ON bp.author_id = u.id
             LEFT JOIN blog_categories bc ON bp.category_id = bc.id
             WHERE bp.is_published = 1
             ORDER BY bp.published_at DESC"
        );
    }

    public static function getRecent(int $limit = 3): array
    {
        return self::query(
            "SELECT bp.*, u.first_name, u.last_name, bc.name as category_name
             FROM blog_posts bp
             JOIN users u ON bp.author_id = u.id
             LEFT JOIN blog_categories bc ON bp.category_id = bc.id
             WHERE bp.is_published = 1
             ORDER BY bp.published_at DESC
             LIMIT ?",
            [$limit]
        );
    }

    public static function findBySlug(string $slug): ?array
    {
        return self::queryFirst(
            "SELECT bp.*, u.first_name, u.last_name, u.avatar,
                    bc.name as category_name, bc.slug as category_slug
             FROM blog_posts bp
             JOIN users u ON bp.author_id = u.id
             LEFT JOIN blog_categories bc ON bp.category_id = bc.id
             WHERE bp.slug = ?",
            [$slug]
        );
    }

    public static function getByCategory(string $categorySlug): array
    {
        return self::query(
            "SELECT bp.*, u.first_name, u.last_name, bc.name as category_name
             FROM blog_posts bp
             JOIN users u ON bp.author_id = u.id
             LEFT JOIN blog_categories bc ON bp.category_id = bc.id
             WHERE bc.slug = ? AND bp.is_published = 1
             ORDER BY bp.published_at DESC",
            [$categorySlug]
        );
    }
}
