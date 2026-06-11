<?php
namespace App\Controllers;

use App\Core\Controller;
use App\Models\BlogPost;

class BlogController extends Controller
{
    public function index(): void
    {
        $posts = BlogPost::getPublished();
        $this->render('public/blog', compact('posts'));
    }

    public function show(string $slug): void
    {
        $post = BlogPost::findBySlug($slug);
        if (!$post) {
            $this->redirect('/blog');
            return;
        }
        $recentPosts = BlogPost::getRecent(3);
        $this->render('public/blog-single', compact('post', 'recentPosts'));
    }
}
