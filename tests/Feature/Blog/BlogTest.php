<?php
namespace Tests\Feature\Blog;

use App\Core\Auth;
use App\Core\Database;
use App\Models\BlogPost;
use Tests\TestCase;

class BlogTest extends TestCase
{
    protected function setUp(): void
    {
        parent::setUp();
        $_SESSION = [];
    }

    public function test_viewing_blog_posts(): void
    {
        $this->createBlogPost(['title' => 'First Post', 'slug' => 'first-post']);
        $this->createBlogPost(['title' => 'Second Post', 'slug' => 'second-post']);

        $posts = BlogPost::getPublished();
        $this->assertCount(2, $posts);
    }

    public function test_unpublished_posts_are_not_shown(): void
    {
        $this->createBlogPost(['title' => 'Published', 'slug' => 'published', 'is_published' => 1]);
        $this->createBlogPost(['title' => 'Draft', 'slug' => 'draft', 'is_published' => 0]);

        $posts = BlogPost::getPublished();
        $this->assertCount(1, $posts);
        $this->assertEquals('Published', $posts[0]['title']);
    }

    public function test_can_get_recent_posts(): void
    {
        $this->createBlogPost(['title' => 'Old Post', 'slug' => 'old-post', 'published_at' => date('Y-m-d H:i:s', strtotime('-5 days'))]);
        $this->createBlogPost(['title' => 'Recent Post', 'slug' => 'recent-post', 'published_at' => date('Y-m-d H:i:s', strtotime('-1 hour'))]);

        $recent = BlogPost::getRecent(2);
        $this->assertCount(2, $recent);
        $this->assertEquals('Recent Post', $recent[0]['title']);
    }

    public function test_creating_blog_post_as_admin(): void
    {
        $admin = $this->createUser([
            'role' => 'admin',
            'email' => $this->uniqueEmail('adminblog'),
        ]);

        $this->simulateLogin($admin['id'], 'admin');

        $now = date('Y-m-d H:i:s');
        $postId = $this->insert('blog_posts', [
            'author_id' => $admin['id'],
            'title' => 'Admin Created Post',
            'slug' => 'admin-created-post',
            'excerpt' => 'Admin excerpt',
            'content' => 'Admin content',
            'tags' => '[]',
            'is_published' => 1,
            'published_at' => $now,
        ]);

        $post = BlogPost::find($postId);
        $this->assertNotNull($post);
        $this->assertEquals('Admin Created Post', $post['title']);
    }

    public function test_editing_blog_post_as_admin(): void
    {
        $post = $this->createBlogPost();

        $this->simulateLogin($post['author_id'], 'admin');

        BlogPost::update($post['id'], [
            'title' => 'Updated Title',
            'content' => 'Updated content for the blog post.',
        ]);

        $updated = BlogPost::find($post['id']);
        $this->assertEquals('Updated Title', $updated['title']);
    }

    public function test_deleting_blog_post_as_admin(): void
    {
        $post = $this->createBlogPost();
        $postId = $post['id'];

        $this->simulateLogin($post['author_id'], 'admin');

        BlogPost::delete($postId);

        $deleted = BlogPost::find($postId);
        $this->assertNull($deleted);
    }

    public function test_finding_post_by_slug(): void
    {
        $this->createBlogPost(['title' => 'Slug Post', 'slug' => 'slug-post']);

        $post = BlogPost::findBySlug('slug-post');
        $this->assertNotNull($post);
        $this->assertEquals('Slug Post', $post['title']);

        $notFound = BlogPost::findBySlug('non-existent-slug');
        $this->assertNull($notFound);
    }

    public function test_getting_posts_by_category(): void
    {
        $newsCategoryId = $this->insert('blog_categories', ['name' => 'News', 'slug' => 'news']);
        $tutorialCategoryId = $this->insert('blog_categories', ['name' => 'Tutorials', 'slug' => 'tutorials']);

        $admin = $this->createUser([
            'role' => 'admin',
            'email' => $this->uniqueEmail('catadmin'),
        ]);

        $now = date('Y-m-d H:i:s');
        $this->insert('blog_posts', [
            'author_id' => $admin['id'],
            'category_id' => $newsCategoryId,
            'title' => 'News Post',
            'slug' => 'news-post',
            'excerpt' => 'News',
            'content' => 'News content',
            'tags' => '[]',
            'is_published' => 1,
            'published_at' => $now,
        ]);

        $this->insert('blog_posts', [
            'author_id' => $admin['id'],
            'category_id' => $tutorialCategoryId,
            'title' => 'Tutorial Post',
            'slug' => 'tutorial-post',
            'excerpt' => 'Tutorial',
            'content' => 'Tutorial content',
            'tags' => '[]',
            'is_published' => 1,
            'published_at' => $now,
        ]);

        $newsPosts = BlogPost::getByCategory('news');
        $this->assertCount(1, $newsPosts);
        $this->assertEquals('News Post', $newsPosts[0]['title']);

        $tutorialPosts = BlogPost::getByCategory('tutorials');
        $this->assertCount(1, $tutorialPosts);
    }
}
